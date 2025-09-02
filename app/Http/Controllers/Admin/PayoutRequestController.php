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
        $payout->update(['status' => 'approved']);

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
        abort_if($payout->status !== 'approved', 409, 'Must be approved first.');

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
                }
            } catch (\Throwable $e) {
                Log::error('Auto disbursement failed', ['payout_id' => $payout->id, 'error' => $e->getMessage()]);
                return back()->withErrors(['paid' => 'Automatic disbursement failed: '.$e->getMessage()]);
            }
        }

        $payout->status  = 'paid';
        $payout->paid_at = now();
        $payout->meta    = $meta;
        $payout->save();

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

        return back()->with('success', 'Payout marked as paid.');
    }
}
