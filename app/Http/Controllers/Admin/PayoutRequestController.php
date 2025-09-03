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

        // Only proceed if method is PayPal or M-Pesa
        abort_unless(str_contains($typeName, 'paypal') || str_contains($typeName, 'mpesa') || str_contains($typeName, 'm-pesa'), 400, 'Method not supported for auto resend');

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
}
