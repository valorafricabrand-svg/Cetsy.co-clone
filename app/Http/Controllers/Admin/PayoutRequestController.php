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
    /* ───────────────────────────────── LIST ─────────────────────────────── */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $payouts = PayoutRequest::when($status, fn($q) => $q->where('status',$status))
                     ->latest()
                     ->paginate(25)
                     ->withQueryString();

        return view('admin.payouts.index', compact('payouts','status'));
    }

    /* ───────────────────────────────── SHOW ─────────────────────────────── */
    public function show(PayoutRequest $payout)
    {
        return view('admin.payouts.show', compact('payout'));
    }

    /* ──────────────────────────── APPROVE ──────────────────────────────── */
    public function approve(PayoutRequest $payout)
    {
        abort_if($payout->status !== 'pending', 409, 'Not pending.');

        $payout->update(['status' => 'approved']);

        // (optionally) notify seller …

        return back()->with('success','Payout approved. Remember to send funds!');
    }

    /* ──────────────────────────── REJECT ──────────────────────────────── */
    public function reject(Request $request, PayoutRequest $payout)
    {
        abort_if($payout->status !== 'pending', 409, 'Not pending.');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($payout, $request) {
            // refund seller
            $wallet = $payout->wallet ?? Wallet::where('user_id',$payout->user_id)->first();

            $wallet->increment('credit',  $payout->amount);
            $wallet->increment('balance', $payout->amount);

            // set status
            $payout->update([
                'status'       => 'rejected',
                'admin_reason' => $request->reason,   // add this nullable column if you like
            ]);
        });

        // Create activity record for the seller
        Activity::create([
            'user_id' => $payout->user_id,
            'is_read' => false,
            'description' => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been rejected',
            'type' => \App\Models\Activity::TYPE_PAYOUT_REQUEST,
            'related_id' => $payout->id,
            'related_type' => 'payout'
        ]);

        // (optionally) notify seller …

        return back()->with('success','Payout rejected & amount refunded.');
    }

    /* ──────────────────────────── MARK PAID ────────────────────────────── */
    public function markPaid(Request $request, PayoutRequest $payout)
    {
        abort_if($payout->status !== 'approved', 409, 'Must be approved first.');

        $request->validate([
            'txn_reference' => 'nullable|string|max:255',
        ]);

        $payout->update([
            'status'        => 'paid',
            'meta->txn_reference' => $request->txn_reference,
            'paid_at'       => now(),          // add nullable column if desired
        ]);

        // Create activity record for the seller
        Activity::create([
            'user_id' => $payout->user_id,
            'is_read' => false,
            'description' => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been marked as paid',
            'type' => \App\Models\Activity::TYPE_PAYOUT_REQUEST,
            'related_id' => $payout->id,
            'related_type' => 'payout'
        ]);

        // (optionally) notify seller …

        return back()->with('success','Payout marked as paid.');
    }
}
