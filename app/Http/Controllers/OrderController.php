<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Product;          // ✅ correct
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders/payments for the seller's shop.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
// app/Http/Controllers/OrderController.php
public function index()
{
    $user   = auth()->user();
    $shopId = Shop::where('user_id', $user->id)->value('id');

    $orders = Order::with(['items.product'])      // eager-load to kill N+1
                   ->where('shop_id', $shopId)
                   ->orderByDesc('id')            // newest first
                   ->paginate(15)                // 15 per page
                   ->withQueryString();          // keep query params if you add filters later

    return view('seller.orders.index', compact('user', 'orders'));
}



    /**
     * Display a specific order by ID.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
public function show(Order $order)
{
   

    return view('seller.orders.show', compact('order'));
}



    /**
     * Show payments related to orders for the seller's shop.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */


public function orderPayments(Request $request)
{
    // Get the current user's shop (fail gracefully if none)
    $shop = Shop::firstWhere('user_id', Auth::id());
    if (! $shop) {
        return back()->withErrors('You don’t have a shop yet. Please create one first.');
    }

    // Build query with optional filters + safe pagination
    $payments = Payment::query()
        ->where('shop_id', $shop->id)
        ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
        ->when($request->filled('method'), fn ($q) => $q->where('method', $request->method))
        ->orderByDesc('id')        // or ->latest('id') if you don’t store paid_at
        ->paginate($request->integer('per_page', 20))
        ->withQueryString();

    return view('seller.orders.payments', [
        'payments' => $payments,
        'shop'     => $shop,
    ]);
}


    /**
     * Store a newly created order with validation.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

public function storeOrder(Request $request)
{
    // checkbox normalization
    $request->merge([
        'billing_same_as_shipping' => $request->input('billing_same_as_shipping') === '1',
    ]);

    $rules = [
        'full_name'                => 'required|string|max:255',
        'email'                    => 'required|email|max:255',
        'phone'                    => 'required|string|max:20',

        'shipping_country'         => 'required|integer|exists:countries,id',
        'shipping_address_1'       => 'required|string|max:255',
        'shipping_address_2'       => 'nullable|string|max:255',
        'shipping_city'            => 'required|string|max:255',
        'shipping_state'           => 'nullable|string|max:255',
        'shipping_postal_code'     => 'nullable|string|max:20',

        'billing_same_as_shipping' => 'required|boolean',

        'billing_country'          => 'nullable|integer|exists:countries,id',
        'billing_address_1'        => 'nullable|string|max:255',
        'billing_address_2'        => 'nullable|string|max:255',
        'billing_city'             => 'nullable|string|max:255',
        'billing_state'            => 'nullable|string|max:255',
        'billing_postal_code'      => 'nullable|string|max:20',

        'order_notes'              => 'nullable|string|max:1000',
        'promo_code'               => 'nullable|string|max:50',
    ];

    $validated = $request->validate($rules);

    $cart = $request->session()->get('cart', []);
    if (empty($cart)) {
        return back()->withErrors(['cart' => 'Your cart is empty.']);
    }

    try {
        DB::beginTransaction();

        // group cart rows by shop
        $itemsByShop = [];
        foreach ($cart as $rowId => $item) {
            $productId = $item['product_id'] ?? null;
            /** @var \App\Models\Product|null $product */
            $product   = \App\Models\Product::with('shop')->find($productId);
            if (!$product) continue;

            $itemsByShop[$product->shop_id][] = [
                'row_id'    => $rowId,
                'product'   => $product,
                'variation' => $item['product_variation_id'] ?? null,
                'quantity'  => (int) ($item['quantity'] ?? 1),
                'price'     => (float) ($item['price'] ?? 0),
                'profiles'  => $item['shipping_profiles'] ?? [],
                'ship_prof' => $item['selected_shipping_profile_id'] ?? null,
            ];
        }

        $orders = [];

        foreach ($itemsByShop as $shopId => $rows) {

            // ----- Compute shop totals FIRST -----
            $shopSubtotal  = 0;
            $shopShipTotal = 0;

            foreach ($rows as $r) {
                $qty   = $r['quantity'];
                $price = $r['price'];

                $profiles   = collect($r['profiles']);
                $selProfId  = $r['ship_prof'];
                $selProfile = $profiles->firstWhere('id', $selProfId);

                $unitShip   = $selProfile['base_rate'] ?? 0;
                $lineShip   = $unitShip * $qty;
                $lineSub    = $price * $qty;

                $shopSubtotal  += $lineSub;
                $shopShipTotal += $lineShip;
            }

            // Create & fill order (all required fields present)
            $order = new \App\Models\Order();
            $order->user_id    = auth()->id();
            $order->shop_id    = $shopId;

            $order->full_name  = $validated['full_name'];
            $order->email      = $validated['email'];
            $order->phone      = $validated['phone'];

            $order->shipping_country_id  = $validated['shipping_country'];
            $order->shipping_address_1   = $validated['shipping_address_1'];
            $order->shipping_address_2   = $validated['shipping_address_2'] ?? null;
            $order->shipping_city        = $validated['shipping_city'];
            $order->shipping_state       = $validated['shipping_state'] ?? null;
            $order->shipping_postal_code = $validated['shipping_postal_code'] ?? null;

            if ($validated['billing_same_as_shipping']) {
                $order->billing_same_as_shipping = true;
                $order->billing_country_id       = $order->shipping_country_id;
                $order->billing_address_1        = $order->shipping_address_1;
                $order->billing_address_2        = $order->shipping_address_2;
                $order->billing_city             = $order->shipping_city;
                $order->billing_state            = $order->shipping_state;
                $order->billing_postal_code      = $order->shipping_postal_code;
            } else {
                $order->billing_same_as_shipping = false;
                $order->billing_country_id       = $validated['billing_country'] ?? null;
                $order->billing_address_1        = $validated['billing_address_1'] ?? null;
                $order->billing_address_2        = $validated['billing_address_2'] ?? null;
                $order->billing_city             = $validated['billing_city'] ?? null;
                $order->billing_state            = $validated['billing_state'] ?? null;
                $order->billing_postal_code      = $validated['billing_postal_code'] ?? null;
            }

            $order->shipping_method = 'standard';
            $order->payment_method  = 'paypal';
            $order->order_notes     = $validated['order_notes'] ?? null;
            $order->promo_code      = $validated['promo_code'] ?? null;

            // REQUIRED totals
            $order->subtotal      = $shopSubtotal;
            $order->shipping_cost = $shopShipTotal;
            $order->total_amount  = $shopSubtotal + $shopShipTotal;

            $order->status        = 'pending';
            $order->save();

            // OrderItems
            foreach ($rows as $r) {
                $qty    = $r['quantity'];
                $price  = $r['price'];

                $profiles   = collect($r['profiles']);
                $selProfId  = $r['ship_prof'];
                $selProfile = $profiles->firstWhere('id', $selProfId);

                $unitShip = $selProfile['base_rate'] ?? 0;
                $lineShip = $unitShip * $qty;

                $orderItem = new \App\Models\OrderItem();
                $orderItem->order_id             = $order->id;
                $orderItem->product_id           = $r['product']->id;
                $orderItem->product_variation_id = $r['variation'];
                $orderItem->quantity             = $qty;
                $orderItem->price                = $price;
                $orderItem->shipping_profile_id  = $selProfId;
                $orderItem->shipping_cost        = $lineShip;
                $orderItem->save();
            }

            $orders[] = $order;
        }

        DB::commit();

        // Empty cart
        $request->session()->forget('cart');

        // Emails
        foreach ($orders as $order) {
            $order->load(['items.product', 'shop.user']);
            $buyer     = auth()->user();
            $shopOwner = $order->shop->user;

            \Mail::to($shopOwner->email)->send(new \App\Mail\OrderCreatedShopOwnerMail($order, $shopOwner, $buyer, $order->shop));
            \Mail::to($buyer->email)->send(new \App\Mail\OrderCreatedBuyerMail($order, $buyer, $order->shop));
        }

        return redirect()
            ->route('buyer.orders.show', $orders[0]->id)
            ->with('success', 'Your orders have been placed successfully! Please proceed to payment.');

    } catch (\Throwable $e) {
        DB::rollBack();

        \Log::error('Order creation failed: '.$e->getMessage(), [
            'user_id' => auth()->id(),
            'cart'    => $request->session()->get('cart'),
            'trace'   => $e->getTraceAsString(),
        ]);

        return back()
            ->withErrors(['error' => 'Failed to create order. Please try again or contact support.'])
            ->withInput();
    }
}






public function payNow($total){
        $order = Order::find($total);
        return view('account.pay_now', ['order' => $order]);
    }




    public function payNowInvoice($total){
        $order = Invoice::find($total);
        return view('invoices.pay_now', ['order' => $order]);
    }


public function successDeposit(Request $request, $id)
    {
        // Retrieve the order/invoice
        $order = Order::findOrFail($id);

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

        return redirect()
            ->route('account.orders')
            ->with('success', 'Your payment has been received. Your order is being processed; you will receive a call from our sales team shortly.');
}


   public function ship(Request $request, Order $order)
{
    
    

    // 2. validate
    $data = $request->validate([
        'courier'       => 'required|string|max:100',
        'tracking_no'   => 'required|string|max:120',
        'shipped_at' => 'nullable|date',
        'ship_notes'    => 'nullable|string|max:1000',
    ]);

    // 3. persist (transaction for safety)
    DB::transaction(function () use ($order, $data) {
        $order->update([
            'status'      => Order::STATUS_SHIPPED,
            'courier'     => $data['courier'],
            'tracking_no' => $data['tracking_no'],
            'shipped_at'  => $data['shipping_date'] ?? now(),
            'ship_notes'  => $data['ship_notes'] ?? null,
        ]);
    });

    // Send email notifications for shipped order
    try {
        // Load relationships for email
        $order->load(['items.product', 'shop.user', 'user']);
        
        // Get the buyer (order user)
        $buyer = $order->user;
        
        // Get the shop owner
        $shopOwner = $order->shop->user;
        
        // Prepare shipping data for email
        $shippingData = [
            'courier' => $data['courier'],
            'tracking_no' => $data['tracking_no'],
            'ship_notes' => $data['ship_notes'] ?? null,
        ];
        
        // Send email to shop owner
        \Mail::to($shopOwner->email)->send(new \App\Mail\OrderShippedShopOwnerMail(
            $order, 
            $shopOwner, 
            $buyer, 
            $order->shop,
            $shippingData
        ));
        
        // Send email to buyer
        \Mail::to($buyer->email)->send(new \App\Mail\OrderShippedBuyerMail(
            $order, 
            $buyer, 
            $order->shop,
            $shippingData
        ));
    } catch (\Exception $e) {
        // Log email sending error but don't fail the shipping process
        \Log::error('Failed to send shipping emails: ' . $e->getMessage(), [
            'order_id' => $order->id,
            'exception' => $e
        ]);
    }

    // 4. fire event / notification if you have it
    if (class_exists(\App\Events\OrderShipped::class)) {
        OrderShipped::dispatch($order);
    }

    return back()->with('success', 'Order marked as shipped.');
}


public function updateStatus(Request $request, Order $order)
{
    

    $request->validate([
        'action' => 'required|string|in:deliver,cancel', // add more if needed
    ]);

    match ($request->action) {
        'deliver' => $this->deliver($order),
        'cancel'  => $this->cancel($order),            // optional
    };

    dd("success after match");

    return back()->with('success', 'Order updated successfully.');
}


private function deliver(Order $order): void
{
  

    // only SHIPPED orders can transition to DELIVERED
    if ($order->status !== Order::STATUS_SHIPPED) {
        abort(422, 'Only shipped orders can be marked as delivered.');
    }

    DB::transaction(function () use ($order) {
        $order->update([
            'status'       => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    });

    // Send email notifications for delivered order
    try {
        // Load relationships for email
        $order->load(['items.product', 'shop.user', 'user']);
        
        // Get the buyer (order user)
        $buyer = $order->user;
        
        // Get the shop owner
        $shopOwner = $order->shop->user;
        
        // Send email to shop owner
        \Mail::to($shopOwner->email)->send(new \App\Mail\OrderDeliveredShopOwnerMail(
            $order, 
            $shopOwner, 
            $buyer, 
            $order->shop
        ));
        
        // Send email to buyer
        \Mail::to($buyer->email)->send(new \App\Mail\OrderDeliveredBuyerMail(
            $order, 
            $buyer, 
            $order->shop
        ));
        dd("success");
    } catch (\Exception $e) {
        
        // Log email sending error but don't fail the delivery process
        \Log::error('Failed to send delivery emails: ' . $e->getMessage(), [
            'order_id' => $order->id,
            'exception' => $e
        ]);
    }
}


public function process(Order $order)
{
    

    // Only "pending" orders may move to "processing"
    abort_unless(
        $order->status === Order::STATUS_PENDING,
        422,
        'Only pending orders can be processed.'
    );

    // Transaction ensures consistency
    DB::transaction(function () use ($order) {
        $order->update([
            'status'      => Order::STATUS_PROCESSING,
            'processed_at'=> now(),         // add this column if you want
        ]);
    });

    // Notify / broadcast (optional event)
    if (class_exists(\App\Events\OrderProcessed::class)) {
        OrderProcessed::dispatch($order);
    }

    return back()->with('success', 'Order marked as processing.');
}



    public function cancel(Request $request, Order $order)
    {
        // Allow cancellation only before the order is shipped
        if (! in_array($order->status, [
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
            ])) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $data = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'status' => Order::STATUS_REFUNDED,
                'cancel_reason' => $data['cancel_reason'],
            ]);

            Wallet::create([
                'user_id'   => $order->user_id,
                'credit'    => $order->total_amount,
                'debit'     => 0,
                'balance'   => 0, // Recalculated elsewhere if needed
                'reference' => 'refund_'.$order->id,
                'description' => 'Order refund',
            ]);
        });

        $order->load(['user', 'shop.user']);
        $buyer  = $order->user;
        $seller = $order->shop->user;

        Notification::send([$buyer, $seller], new \App\Notifications\OrderCancelledNotification($order));

        return back()->with('success', 'Your order was cancelled successfully.');
    }

}
