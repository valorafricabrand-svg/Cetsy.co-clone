<?php

// app/Http/Controllers/Seller/PayoutRequestController.php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\Activity;

class PayoutRequestController extends Controller
{
    public function index()
    {
        $balance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit) - SUM(debit) AS bal')
            ->value('bal') ?? 0;

        $requests = PayoutRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('seller.payouts.index', compact('balance', 'requests'));
    }

    public function store(Request $request)
    {
        // Balance and fee rate
        $balance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit) - SUM(debit) AS balance')
            ->value('balance') ?? 0;

        // Pull from settings table with sensible defaults
        // fee_rate is stored as percent in settings (e.g., 1.5). Normalize to decimal.
        $rawFee    = (float) (function_exists('setting') ? setting('fee_rate', 1.5) : 1.5);
        $feeRate   = $rawFee > 1 ? $rawFee / 100 : $rawFee;
        $minAmount = (float) (function_exists('setting') ? setting('min_amount', 1) : 1);

        $request->validate([
            'amount' => 'required|numeric|min:'.$minAmount,
            'method' => 'required|exists:payment_methods,id',
        ]);

        $amount = (float) $request->amount;
        $fee    = round($amount * $feeRate, 2);
        if (($amount + $fee) > $balance + 0.00001) {
            return back()->withErrors(['amount' => 'Amount plus fee exceeds your available balance.'])->withInput();
        }

        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', optional($shop)->id)
            ->where('id', $request->method)
            ->first();
        abort_if(!$paymentMethod, 403, 'Invalid payout method');

        DB::transaction(function () use ($request, $amount, $fee, $feeRate) {
            $debitRow = Wallet::create([
                'user_id'     => Auth::id(),
                'credit'      => 0,
                'debit'       => $amount,
                'balance'     => 0,
                'type'        => 'payout_request',
                'reference'   => \Illuminate\Support\Str::uuid(),
                'description' => 'Payout request (net) debit',
            ]);

            $feeRow = Wallet::create([
                'user_id'     => Auth::id(),
                'credit'      => 0,
                'debit'       => $fee,
                'balance'     => 0,
                'type'        => 'withdrawal_fee',
                'reference'   => \Illuminate\Support\Str::uuid(),
                'description' => 'Withdrawal fee',
                'meta'        => ['rate' => $feeRate],
            ]);

            PayoutRequest::create([
                'wallet_id'         => $debitRow->id,
                'user_id'           => Auth::id(),
                'amount'            => $amount,
                'payment_method_id' => $request->method,
                'status'            => 'pending',
                'meta'              => [
                    'method'        => $request->method,
                    'fee'           => $fee,
                    'fee_wallet_id' => $feeRow->id,
                    'fee_rate'      => $feeRate,
                ],
            ]);

            Activity::create([
                'user_id'      => Auth::id(),
                'is_read'      => false,
                'description'  => 'You submitted a new payout request of $' . number_format($amount, 2),
                'type'         => \App\Models\Activity::TYPE_PAYOUT,
                'related_id'   => $debitRow->id,
                'related_type' => 'wallet',
            ]);
        });

        return back()->with('success', 'Payout request submitted!');
    }
}
