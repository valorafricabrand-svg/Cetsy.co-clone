<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Str;
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


                public function payListing(Request $request, $id)
    {
        // Retrieve the order/invoice
        $product = Product::findOrFail($id);

          $product->update([
            'is_active'        => 1,
            'listing_paid_at'  => now(),     // add this column if desired
            'next_due_date'   => now()->addMonth(4), 
        ]);

        // Determine payment method: default to 'paypal'
        $method = $request->get('method', 'paypal');

        // Prepare a unique local transaction ID if not provided
        // (e.g., PayPal flow might not send one; MPESA flow might include its own)
        $localTxId = $request->get('transaction_id');
        if (!$localTxId) {
            do {
                $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
            } while (Payment::where('local_transaction_id', $localTxId)->exists());
        }

        // Determine currency sign dynamically
        // (assume order has a currency column; fallback to 'USD')
        $currency = $order->currency ?? 'USD';
        $listing_fee = $product->category?->listing_fee;
        // Build the payment data array
        $paymentData = [
            
           
            'shop_id'              => $product->shop_id,
            'total_amount'         => $product->category?->listing_fee,
            'payment_method'       => $method,
            'status'               => '3',
            'currency'             => $currency,
            'local_transaction_id' => $localTxId,
            'payment_name' => 'listing_fee',
        ];

        Wallet::create([
            'user_id'    => Auth::id(),
            'credit'     => 0,
            'debit'      => $listing_fee,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => strtoupper(uniqid('TXN-')),
            'method'     => $request->method,
            'description'=> 'Manual deposit via ' . ucfirst($request->method),
        ]);


        // Create the payment record
        $payment = Payment::create($paymentData);

      
        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Your payment has been received.');
    }





}

