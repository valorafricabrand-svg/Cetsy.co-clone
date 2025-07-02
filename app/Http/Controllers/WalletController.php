<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Str;
use App\Models\Shop;
use App\Models\Order;
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



public function payOrder(Request $request, $id)
    {
       // Retrieve the order/invoice
        $order = Order::findOrFail($id);

        // Determine payment method: default to 'paypal'
        $method = $request->get('method', 'wallet');

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

        // Build the payment data array
        $paymentData = [
            'order_id'             => $order->id,
            'user_id'              => $order->user_id,
            'shop_id'              => $order->shop_id,
            'total_amount'         => $order->total_amount,
            'payment_method'       => $method,
            'status'               => '3',
            'currency'             => $currency,
            'local_transaction_id' => $localTxId,
        ];




        // If MPESA, you might want to capture the MPESA metadata (e.g., MpesaReceiptNumber)
        if ($method === 'mpesa' && $request->filled('mpesa_receipt')) {
            $paymentData['mpesa_receipt'] = $request->input('mpesa_receipt');
        }

        // Create the payment record
        $payment = Payment::create($paymentData);

        // Mark order as successful if payment record was created
        if ($payment) {
            $order->status = 'processing';
            $order->save();
        }


        Wallet::create([
            'user_id'    => Auth::id(),
            'credit'     => 0,
            'debit'      => $order->total_amount,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => strtoupper(uniqid('TXN-')),
            'method'     => 'wallet',
            'description'=> 'Paid via wallet ' . ucfirst($request->method),
        ]);


        $shop = Shop::find($order->shop_id);



        Wallet::create([
            'user_id'    => $shop->user_id,
            'credit'     => $order->total_amount,
            'debit'      => 0,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => $localTxId,
            'method'     => $method,
            'description'=> 'Order payment',
        ]);

        // Send email notifications for successful payment
        try {
            // Load relationships for email
            $order->load(['items.product', 'shop.user']);
            
            // Get the buyer (order user)
            $buyer = $order->user;
            
            // Get the shop owner
            $shopOwner = $shop->user;
            
            // Send email to shop owner
            \Mail::to($shopOwner->email)->send(new \App\Mail\PaymentSuccessShopOwnerMail(
                $order, 
                $shopOwner, 
                $buyer, 
                $shop,
                $payment
            ));
            
            // Send email to buyer
            \Mail::to($buyer->email)->send(new \App\Mail\PaymentSuccessBuyerMail(
                $order, 
                $buyer, 
                $shop,
                $payment
            ));
        } catch (\Exception $e) {
            // Log email sending error but don't fail the payment process
            \Log::error('Failed to send payment success emails: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'exception' => $e
            ]);
        }

     
      return redirect()->route('buyer.orders.show', $order->id)
            ->with('success', 'Your payment has been received. Your order is being processed; you will receive a call from our sales team shortly.');
    }






}

