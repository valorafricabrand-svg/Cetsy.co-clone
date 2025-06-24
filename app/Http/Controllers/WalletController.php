<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Shop;
use App\Models\PaymentMethod;

class WalletController extends Controller
{
  public function index(Request $request)
{
    $query = Wallet::where('user_id', Auth::id());

    if ($request->type === 'credit') {
        $query->where('credit', '>', 0);
    } elseif ($request->type === 'debit') {
        $query->where('debit', '>', 0);
    }

    if ($request->from) {
        $query->whereDate('created_at', '>=', $request->from);
    }

    if ($request->to) {
        $query->whereDate('created_at', '<=', $request->to);
    }

    $transactions = $query->orderBy('created_at', 'desc')->paginate(10);
    $balance = Wallet::where('user_id', Auth::id())
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?? 0;

    // Fetch payment methods for the current user's shop
    $shop = Shop::where('user_id', Auth::id())->first();
    $paymentMethods = collect();
    
    if ($shop) {
        $paymentMethods = PaymentMethod::where('shop_id', $shop->id)
            ->with('paymentType')
            ->get();
    }

    return view('wallet.index', compact('transactions', 'balance', 'paymentMethods'));
}


public function depositForm()
{
    $balance = Wallet::where('user_id', auth()->id())
        ->selectRaw('SUM(credit - debit) as balance')
        ->value('balance') ?? 0;

    return view('wallet.deposit', compact('balance'));
}

    public function storeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:mpesa,card,paypal',
        ]);

        // Stub: In production, integrate your payment gateway logic here
        // For now, simulate success deposit:
        Wallet::create([
            'user_id'    => Auth::id(),
            'credit'     => $request->amount,
            'debit'      => 0,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => strtoupper(uniqid('TXN-')),
            'method'     => $request->method,
            'description'=> 'Manual deposit via ' . ucfirst($request->method),
        ]);

        return redirect()->route('wallet.index')->with('success', 'Deposit recorded successfully!');
    }



public function handlePayPalDeposit(Request $request)
{

    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);

    try {

        Wallet::create([
            'user_id'     => Auth::id(),
            'credit'      => $request->amount,
            'debit'       => 0,
            'balance'     => 0, // Optionally recalculate this later
            'reference'   => strtoupper(uniqid('TXN-')),
            'method'      => 'paypal',
            'description' => 'Manual deposit via ' . ucfirst($request->method),
        ]);

        return response()->json(['success' => true]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'error'   => 'Something went wrong. ' . $e->getMessage()
        ], 500);

    }

 }





}

