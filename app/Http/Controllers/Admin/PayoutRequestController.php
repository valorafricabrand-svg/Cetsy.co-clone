<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;
use Illuminate\Support\Facades\Log;
use App\Helpers\SafaricomDarajaHelper;
use App\Helpers\PayPalHelper;
use App\Helpers\WiseHelper;

class PayoutRequestController extends Controller
{
    /* LIST */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $payouts = PayoutRequest::with(['user','paymentMethod'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.payouts.index', compact('payouts', 'status'));
    }

    /* SHOW */
    public function show(PayoutRequest $payout)
    {
        $payout->load(['user','paymentMethod']);
        return view('admin.payouts.show', compact('payout'));
    }

    /* APPROVE */
    public function approve(PayoutRequest $payout)
    {
        abort_if($payout->status !== 'pending', 409, 'Not pending.');
        $payout->update([
            'status'       => 'approved',
            'approved_at'  => now(),
            'approved_by'  => auth()->id(),
        ]);

        // Notify seller via email (best-effort)
        try {
            $payout->loadMissing('user');
            if ($payout->user && $payout->user->email) {
                \Mail::to($payout->user->email)->send(new \App\Mail\PayoutApprovedMail($payout, $payout->user));
            }
        } catch (\Throwable $e) { }

        return back()->with('success', 'Payout approved. Remember to send funds!');
    }

    /* REJECT */
    public function reject(Request $request, PayoutRequest $payout)
    {
        abort_if($payout->status !== 'pending', 409, 'Not pending.');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($payout, $request) {
            $fee = (float) (data_get($payout->meta, 'fee', 0));

            Wallet::create([
                'user_id'     => $payout->user_id,
                'credit'      => $payout->amount,
                'debit'       => 0,
                'balance'     => 0,
                'type'        => 'payout_reversal',
                'reference'   => \Illuminate\Support\Str::uuid(),
                'description' => 'Refund for rejected payout #' . $payout->id,
            ]);

            if ($fee > 0) {
                Wallet::create([
                    'user_id'     => $payout->user_id,
                    'credit'      => $fee,
                    'debit'       => 0,
                    'balance'     => 0,
                    'type'        => 'withdrawal_fee_refund',
                    'reference'   => \Illuminate\Support\Str::uuid(),
                    'description' => 'Withdrawal fee refund for payout #' . $payout->id,
                ]);
            }

            $payout->update([
                'status'       => 'rejected',
                'admin_reason' => $request->reason,
                'rejected_by'  => auth()->id(),
                'rejected_at'  => now(),
            ]);
        });

        Activity::create([
            'user_id'      => $payout->user_id,
            'is_read'      => false,
            'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been rejected',
            'type'         => \App\Models\Activity::TYPE_PAYOUT,
            'related_id'   => $payout->id,
            'related_type' => 'payout',
        ]);

        // Email notify
        try {
            $payout->loadMissing('user');
            if ($payout->user && $payout->user->email) {
                \Mail::to($payout->user->email)->send(new \App\Mail\PayoutRejectedMail($payout, $payout->user, (string) $request->reason));
            }
        } catch (\Throwable $e) { }

        return back()->with('success', 'Payout rejected & amount refunded.');
    }

    /* MARK PAID */
    public function markPaid(Request $request, PayoutRequest $payout)
    {
        abort_unless(in_array($payout->status, ['approved','sent']), 409, 'Must be approved or sent.');

        $request->validate([
            'txn_reference' => 'nullable|string|max:255',
            'disburse'      => 'nullable|in:manual,auto',
        ]);

        $disburse = $request->input('disburse', 'manual');

        // Build meta safely
        $meta = $payout->meta ?? [];
        if ($request->filled('txn_reference')) {
            $meta['txn_reference'] = $request->input('txn_reference');
        }

        $autoSent = false;
        if ($disburse === 'auto' && $payout->paymentMethod && $payout->paymentMethod->paymentType) {
            $typeName = strtolower($payout->paymentMethod->paymentType->name);
            $account  = (string) $payout->paymentMethod->account_number;
            $amount   = (float) $payout->amount;
            $note     = 'Seller payout #'.$payout->id;

            try {
                if (str_contains($typeName, 'paypal')) {
                    $resp = PayPalHelper::createPayout($account, $amount, $note, 'payout_'.$payout->id);
                    if (($resp['status'] ?? 'error') !== 'success') {
                        return back()->withErrors(['paid' => 'PayPal payout failed: '.($resp['message'] ?? 'Unknown error')]);
                    }
                    $data = $resp['data'] ?? [];
                    $meta['paypal'] = $data;
                    $meta['txn_reference'] = $meta['txn_reference'] ?? ($data['batch_header']['payout_batch_id'] ?? null);
                    $autoSent = true;
                } elseif (str_contains($typeName, 'wise')) {
                    $recipientId = (string) ($payout->paymentMethod->wise_recipient_id ?? '');
                    $profileId = $payout->paymentMethod->wise_profile_id ?? null;
                    $currency = (string) ($payout->paymentMethod->bank_currency ?? (function_exists('setting') ? setting('default_currency', 'USD') : 'USD'));
                    $resp = WiseHelper::createPayout($recipientId, $amount, $currency, $note, 'payout_'.$payout->id, $profileId);
                    if (($resp['status'] ?? 'error') !== 'success') {
                        return back()->withErrors(['paid' => 'Wise payout failed: '.($resp['message'] ?? 'Unknown error')]);
                    }
                    $data = $resp['data'] ?? [];
                    $meta['wise'] = $data;
                    $meta['txn_reference'] = $meta['txn_reference'] ?? ($data['transfer']['id'] ?? null);
                    $autoSent = true;
                } elseif (str_contains($typeName, 'mpesa') || str_contains($typeName, 'm-pesa')) {
                    // Normalize MSISDN to 2547XXXXXXXX
                    $msisdn = preg_replace('/\D+/', '', $account);
                    if (str_starts_with($msisdn, '0') && strlen($msisdn) === 10) {
                        $msisdn = '254'.substr($msisdn,1);
                    } elseif (str_starts_with($msisdn, '7') && strlen($msisdn) === 9) {
                        $msisdn = '254'.$msisdn;
                    }
                    if (!preg_match('/^2547\d{8}$/', $msisdn)) {
                        return back()->withErrors(['paid' => 'Invalid M-Pesa phone number format for B2C.']);
                    }
                    $ref = 'PAYOUT-'.$payout->id;
                    $resp = SafaricomDarajaHelper::initiateB2CPayment($msisdn, $amount, $ref);
                    if (($resp['status'] ?? 'error') !== 'success') {
                        return back()->withErrors(['paid' => 'M-Pesa payout failed: '.($resp['message'] ?? 'Unknown error')]);
                    }
                    $meta['mpesa'] = $resp['data'] ?? [];
                    $meta['txn_reference'] = $meta['txn_reference'] ?? ($meta['mpesa']['ConversationID'] ?? $ref);
                    $autoSent = true;
                }
            } catch (\Throwable $e) {
                Log::error('Auto disbursement failed', ['payout_id' => $payout->id, 'error' => $e->getMessage()]);
                return back()->withErrors(['paid' => 'Automatic disbursement failed: '.$e->getMessage()]);
            }
        }

        if ($autoSent) {
            // Mark as sent; final paid will be set by webhook callback
            $payout->status  = 'sent';
            $payout->paid_at = $payout->paid_at ?? null;
            $meta['sent_at'] = $meta['sent_at'] ?? now()->toISOString();
        } else {
            // Manual confirmation: mark as paid
            $payout->status  = 'paid';
            $payout->paid_at = now();
            $payout->paid_by = auth()->id();
        }
        $payout->meta    = $meta;
        $payout->save();

        if ($autoSent) {
            Activity::create([
                'user_id'      => $payout->user_id,
                'is_read'      => false,
                'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been sent and is awaiting confirmation',
                'type'         => \App\Models\Activity::TYPE_PAYOUT,
                'related_id'   => $payout->id,
                'related_type' => 'payout',
            ]);
        } else {
            Activity::create([
                'user_id'      => $payout->user_id,
                'is_read'      => false,
                'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been marked as paid',
                'type'         => \App\Models\Activity::TYPE_PAYOUT,
                'related_id'   => $payout->id,
                'related_type' => 'payout',
            ]);
            // Email notify
            try {
                $payout->loadMissing('user');
                if ($payout->user && $payout->user->email) {
                    \Mail::to($payout->user->email)->send(new \App\Mail\PayoutPaidMail($payout, $payout->user));
                }
            } catch (\Throwable $e) { }
        }

        return back()->with('success', $autoSent ? 'Payout sent; awaiting confirmation.' : 'Payout marked as paid.');
    }

    /**
     * Resend automatic disbursement using the selected payout method.
     * Only available for approved or sent payouts and for methods that support automation.
     */
    public function resendAuto(PayoutRequest $payout)
    {
        abort_unless(in_array($payout->status, ['approved','sent']), 409, 'Only approved or sent payouts can be resent.');
        abort_unless($payout->paymentMethod && $payout->paymentMethod->paymentType, 400, 'Missing payout method');

        $typeName = strtolower($payout->paymentMethod->paymentType->name);
        $account  = (string) $payout->paymentMethod->account_number;
        $amount   = (float) $payout->amount;
        $note     = 'Seller payout #'.$payout->id.' (resend)';

        // Only proceed if method is PayPal, M-Pesa, or Wise
        abort_unless(str_contains($typeName, 'paypal') || str_contains($typeName, 'mpesa') || str_contains($typeName, 'm-pesa') || str_contains($typeName, 'wise'), 400, 'Method not supported for auto resend');

        $meta = $payout->meta ?? [];

        try {
            if (str_contains($typeName, 'paypal')) {
                $resp = \App\Helpers\PayPalHelper::createPayout($account, $amount, $note, 'payout_'.$payout->id);
                if (($resp['status'] ?? 'error') !== 'success') {
                    return back()->withErrors(['resend' => 'PayPal payout failed: '.($resp['message'] ?? 'Unknown error')]);
                }
                $data = $resp['data'] ?? [];
                $meta['paypal'] = $data;
                $meta['txn_reference'] = $meta['txn_reference'] ?? ($data['batch_header']['payout_batch_id'] ?? null);
            } elseif (str_contains($typeName, 'wise')) {
                $recipientId = (string) ($payout->paymentMethod->wise_recipient_id ?? '');
                $profileId = $payout->paymentMethod->wise_profile_id ?? null;
                $currency = (string) ($payout->paymentMethod->bank_currency ?? (function_exists('setting') ? setting('default_currency', 'USD') : 'USD'));
                $resp = \App\Helpers\WiseHelper::createPayout($recipientId, $amount, $currency, $note, 'payout_'.$payout->id.'-RS', $profileId);
                if (($resp['status'] ?? 'error') !== 'success') {
                    return back()->withErrors(['resend' => 'Wise payout failed: '.($resp['message'] ?? 'Unknown error')]);
                }
                $data = $resp['data'] ?? [];
                $meta['wise'] = $data;
                $meta['txn_reference'] = $meta['txn_reference'] ?? ($data['transfer']['id'] ?? null);
            } else {
                // Normalize M-Pesa MSISDN
                $msisdn = preg_replace('/\D+/', '', $account);
                if (str_starts_with($msisdn, '0') && strlen($msisdn) === 10) {
                    $msisdn = '254'.substr($msisdn,1);
                } elseif (str_starts_with($msisdn, '7') && strlen($msisdn) === 9) {
                    $msisdn = '254'.$msisdn;
                }
                if (!preg_match('/^2547\d{8}$/', $msisdn)) {
                    return back()->withErrors(['resend' => 'Invalid M-Pesa phone number format for B2C.']);
                }
                $ref = 'PAYOUT-'.$payout->id.'-RS';
                $resp = \App\Helpers\SafaricomDarajaHelper::initiateB2CPayment($msisdn, $amount, $ref);
                if (($resp['status'] ?? 'error') !== 'success') {
                    return back()->withErrors(['resend' => 'M-Pesa payout failed: '.($resp['message'] ?? 'Unknown error')]);
                }
                $meta['mpesa'] = $resp['data'] ?? [];
                $meta['txn_reference'] = $meta['txn_reference'] ?? ($meta['mpesa']['ConversationID'] ?? $ref);
            }
        } catch (\Throwable $e) {
            \Log::error('Auto resend failed', ['payout_id' => $payout->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['resend' => 'Automatic resend failed: '.$e->getMessage()]);
        }

        // Update payout to sent and bump resend counter
        $meta['sent_at'] = now()->toISOString();
        $meta['resend_count'] = (int) ($meta['resend_count'] ?? 0) + 1;
        $payout->status = 'sent';
        $payout->meta   = $meta;
        $payout->save();

        // Activity for seller
        \App\Models\Activity::create([
            'user_id'      => $payout->user_id,
            'is_read'      => false,
            'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been re-sent and is awaiting confirmation',
            'type'         => \App\Models\Activity::TYPE_PAYOUT,
            'related_id'   => $payout->id,
            'related_type' => 'payout',
        ]);

        return back()->with('success', 'Payout re-sent; awaiting confirmation.');
    }

    /**
     * Mark a payout as failed and refund seller (manual override).
     */
    public function fail(Request $request, PayoutRequest $payout)
    {
        abort_unless(in_array($payout->status, ['approved','sent']), 409, 'Only approved or sent payouts can be marked failed.');

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($payout, $data) {
            $fee = (float) (data_get($payout->meta, 'fee', 0));

            Wallet::create([
                'user_id'     => $payout->user_id,
                'credit'      => $payout->amount,
                'debit'       => 0,
                'balance'     => 0,
                'type'        => 'payout_failure_refund',
                'reference'   => \Illuminate\Support\Str::uuid(),
                'description' => 'Refund for failed payout #' . $payout->id,
            ]);

            if ($fee > 0) {
                Wallet::create([
                    'user_id'     => $payout->user_id,
                    'credit'      => $fee,
                    'debit'       => 0,
                    'balance'     => 0,
                    'type'        => 'withdrawal_fee_refund',
                    'reference'   => \Illuminate\Support\Str::uuid(),
                    'description' => 'Withdrawal fee refund for payout #' . $payout->id,
                ]);
            }

            $meta = $payout->meta ?? [];
            $meta['failed_reason'] = $data['reason'];
            $meta['failed_at'] = now()->toISOString();

            $payout->update([
                'status'       => 'failed',
                'admin_reason' => $data['reason'],
                'meta'         => $meta,
            ]);
        });

        Activity::create([
            'user_id'      => $payout->user_id,
            'is_read'      => false,
            'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has failed and was refunded',
            'type'         => \App\Models\Activity::TYPE_PAYOUT,
            'related_id'   => $payout->id,
            'related_type' => 'payout',
        ]);

        // Optional email (reuse rejection template for failure notification)
        try {
            $payout->loadMissing('user');
            if ($payout->user && $payout->user->email) {
                \Mail::to($payout->user->email)->send(new \App\Mail\PayoutRejectedMail($payout, $payout->user, (string) $data['reason']));
            }
        } catch (\Throwable $e) { }

        return back()->with('success', 'Payout marked as failed and refunded.');
    }

    /**
     * Export filtered payout requests to CSV (applies same filters as index).
     */
    public function export(\Illuminate\Http\Request $request)
    {
        // Build (similar to index)
        $status       = $request->query('status');
        $q            = (string) $request->query('q', '');
        $paymentType  = $request->query('payment_type');
        $dateFrom     = $request->query('from');
        $dateTo       = $request->query('to');
        $minAmount    = $request->filled('min') ? (float) $request->input('min') : null;
        $maxAmount    = $request->filled('max') ? (float) $request->input('max') : null;

        $query = \App\Models\PayoutRequest::query()
            ->with(['user','paymentMethod.paymentType'])
            ->when($status, fn ($q2) => $q2->where('status', $status))
            ->when(strlen($q) > 0, function ($q2) use ($q) {
                $q2->where(function ($qq) use ($q) {
                    $qq->where('id', (int) $q)
                       ->orWhere('user_id', (int) $q)
                       ->orWhereHas('user', function ($u) use ($q) {
                           $u->where('name', 'like', "%{$q}%")
                             ->orWhere('email', 'like', "%{$q}%");
                       });
                });
            })
            ->when($paymentType, function ($q2) use ($paymentType) {
                $q2->whereHas('paymentMethod', function ($pm) use ($paymentType) {
                    $pm->where('payment_type_id', $paymentType);
                });
            })
            ->when($dateFrom, fn ($q2) => $q2->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q2) => $q2->whereDate('created_at', '<=', $dateTo))
            ->when($minAmount, fn ($q2) => $q2->where('amount', '>=', $minAmount))
            ->when($maxAmount, fn ($q2) => $q2->where('amount', '<=', $maxAmount))
            ->latest();

        $filename = 'payouts_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','User ID','User Name','User Email','Amount','Status','Method','Requested At','Approved At','Paid At']);
            $query->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $p) {
                    $user = $p->user;
                    $method = optional(optional($p->paymentMethod)->paymentType)->name;
                    fputcsv($out, [
                        $p->id,
                        $p->user_id,
                        $user->name ?? null,
                        $user->email ?? null,
                        number_format((float)$p->amount, 2, '.', ''),
                        $p->status,
                        $method,
                        optional($p->created_at)->toDateTimeString(),
                        optional($p->approved_at)->toDateTimeString(),
                        optional($p->paid_at)->toDateTimeString(),
                    ]);
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Bulk approve pending payouts.
     */
    public function bulkApprove(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required'],
        ]);
        // Accept CSV string or array
        $ids = is_array($request->ids) ? $request->ids : array_filter(array_map('intval', explode(',', (string)$request->ids)));
        if (empty($ids)) { return back()->withErrors('No selections.'); }

        $count = 0;
        $payouts = \App\Models\PayoutRequest::whereIn('id', $ids)->where('status','pending')->get();
        foreach ($payouts as $payout) {
            $payout->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
            $count++;
            // best-effort email
            try {
                $payout->loadMissing('user');
                if ($payout->user && $payout->user->email) {
                    \Mail::to($payout->user->email)->send(new \App\Mail\PayoutApprovedMail($payout, $payout->user));
                }
            } catch (\Throwable $e) {}
        }
        return back()->with('success', "$count payout(s) approved.");
    }

    /**
     * Bulk reject pending payouts and refund.
     */
    public function bulkReject(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'ids'    => ['required'],
            'reason' => ['required','string','max:500'],
        ]);
        $ids = is_array($request->ids) ? $request->ids : array_filter(array_map('intval', explode(',', (string)$request->ids)));
        if (empty($ids)) { return back()->withErrors('No selections.'); }

        $count = 0;
        $payouts = \App\Models\PayoutRequest::whereIn('id', $ids)->where('status','pending')->get();
        foreach ($payouts as $payout) {
            \DB::transaction(function () use ($payout, $request, &$count) {
                $fee = (float) (data_get($payout->meta, 'fee', 0));
                \App\Models\Wallet::create([
                    'user_id'     => $payout->user_id,
                    'credit'      => $payout->amount,
                    'debit'       => 0,
                    'balance'     => 0,
                    'type'        => 'payout_reversal',
                    'reference'   => \Illuminate\Support\Str::uuid(),
                    'description' => 'Refund for rejected payout #' . $payout->id,
                ]);
                if ($fee > 0) {
                    \App\Models\Wallet::create([
                        'user_id'     => $payout->user_id,
                        'credit'      => $fee,
                        'debit'       => 0,
                        'balance'     => 0,
                        'type'        => 'withdrawal_fee_refund',
                        'reference'   => \Illuminate\Support\Str::uuid(),
                        'description' => 'Withdrawal fee refund for payout #' . $payout->id,
                    ]);
                }
                $payout->update([
                    'status'       => 'rejected',
                    'admin_reason' => (string) $request->reason,
                    'rejected_by'  => auth()->id(),
                    'rejected_at'  => now(),
                ]);
                $count++;
            });
            // Notify
            try {
                $payout->loadMissing('user');
                if ($payout->user && $payout->user->email) {
                    \Mail::to($payout->user->email)->send(new \App\Mail\PayoutRejectedMail($payout, $payout->user, (string) $request->reason));
                }
            } catch (\Throwable $e) {}
        }
        return back()->with('success', "$count payout(s) rejected & refunded.");
    }
}
