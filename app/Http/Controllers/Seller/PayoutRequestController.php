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
        // available balance
        $balance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit) - SUM(debit) AS bal')
            ->value('bal') ?? 0;

        // latest payout requests (paginate 20 per page)
        $requests = PayoutRequest::where('user_id', Auth::id())
                     ->latest()
                     ->paginate(20);

        return view('seller.payouts.index', compact('balance', 'requests'));
    }


public function store(Request $request)
{
    /* -------------------------------------------------
       1️⃣  Get numeric balance for validation
    --------------------------------------------------*/
    $balance = Wallet::where('user_id', Auth::id())
        ->where('status', 'completed')
        ->selectRaw('SUM(credit) - SUM(debit) AS balance')
        ->value('balance') ?? 0;

    $request->validate([
        'amount'       => 'required|numeric|min:1|max:' . $balance,
        'method'       => 'required|exists:payment_methods,id',
    ]);

    $shop = Shop::where('user_id', Auth::id())->first();
    $paymentMethod = PaymentMethod::where('shop_id', $shop->id)->where('id', $request->method)->first();
    

    /* -------------------------------------------------
       2️⃣  Everything else in one DB transaction
    --------------------------------------------------*/
    DB::transaction(function () use ($request) {

        // 2-a create a *debit* row in wallets ledger
       $wallet_created = Wallet::create([
            'user_id'     => Auth::id(),
            'credit'      => 0,
            'debit'       => $request->amount,
            'balance'     => 0,                   // not used in ledger pattern
            'type'        => 'payout_request',
            'reference'   => \Illuminate\Support\Str::uuid(),
            'description' => 'Payout request debit',
        ]);

         Wallet::create([
            'user_id'     => Auth::id(),
            'credit'      => 0,
            'debit'       => $request->amount * 0.015,
            'balance'     => 0,                   // not used in ledger pattern
            'type'        => 'withdrawal_fee',
            'reference'   => \Illuminate\Support\Str::uuid(),
            'description' => 'Withdrawal fee',
        ]);


        // 2-b log the payout request
        PayoutRequest::create([
            'wallet_id' => $wallet_created->id,                  // optional — link if you added FK
            'user_id'   => Auth::id(),            // add this column if easier
            'amount'    => $request->amount,
            'payment_method_id' => $request->method,
            'status'    => 'pending',
            'meta'      => $request->only('method'),
        ]);

        // Create activity record for the seller
        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You submitted a new payout request of $' . number_format($request->amount, 2),
            'type' => \App\Models\Activity::TYPE_PAYOUT_REQUEST,
            'related_id' => $wallet_created->id,
            'related_type' => 'wallet'
        ]);
    });

    /* -------------------------------------------------
       3️⃣  Done
    --------------------------------------------------*/
    return back()->with('success', 'Payout request submitted!');
}

}
