<?php

// app/Http/Controllers/Seller/PayoutRequestController.php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutRequestController extends Controller
{

   public function index()
    {
        // available balance
        $balance = Wallet::where('user_id', Auth::id())
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
        ->selectRaw('SUM(credit) - SUM(debit) AS balance')
        ->value('balance') ?? 0;

    $request->validate([
        'amount'       => 'required|numeric|min:1|max:' . $balance,
        'method'       => 'required|in:mpesa,bank',
        'account_name' => 'required|string|max:255',
        'account_no'   => 'required|string|max:255',
    ]);

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

        // 2-b log the payout request
        PayoutRequest::create([
            'wallet_id' => $wallet_created->id,                  // optional — link if you added FK
            'user_id'   => Auth::id(),            // add this column if easier
            'amount'    => $request->amount,
            'status'    => 'pending',
            'meta'      => $request->only('method','account_name','account_no'),
        ]);
    });

    /* -------------------------------------------------
       3️⃣  Done
    --------------------------------------------------*/
    return back()->with('success', 'Payout request submitted!');
}

}
