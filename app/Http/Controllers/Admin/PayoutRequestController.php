<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;

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

        return back()->with('success', 'Payout rejected & amount refunded.');
    }

    /* MARK PAID */
    public function markPaid(Request $request, PayoutRequest $payout)
    {
        abort_if($payout->status !== 'approved', 409, 'Must be approved first.');

        $request->validate([
            'txn_reference' => 'nullable|string|max:255',
        ]);

        $payout->update([
            'status'              => 'paid',
            'meta->txn_reference' => $request->txn_reference,
            'paid_at'             => now(),
        ]);

        Activity::create([
            'user_id'      => $payout->user_id,
            'is_read'      => false,
            'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been marked as paid',
            'type'         => \App\Models\Activity::TYPE_PAYOUT,
            'related_id'   => $payout->id,
            'related_type' => 'payout',
        ]);

        return back()->with('success', 'Payout marked as paid.');
    }
}

