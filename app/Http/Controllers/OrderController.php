<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Activity;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    /**
     * Seller: list orders.
     */
    public function index()
    {
        $user   = auth()->user();
        $shopId = Shop::where('user_id', $user->id)->value('id');

        $orders = Order::with(['items.product'])
            ->where('shop_id', $shopId)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('seller.orders.index', compact('user', 'orders'));
    }

    /**
     * Seller: show a single order.
     */
    public function show(Order $order)
    {
        $order->load('items.product', 'shop.user', 'user');
        return view('seller.orders.show', compact('order'));
    }

    /**
     * Seller: payments for this shop.
     */
    public function orderPayments(Request $request)
    {
        $shop = Shop::firstWhere('user_id', Auth::id());
        if (! $shop) {
            return back()->withErrors('You don’t have a shop yet. Please create one first.');
        }

        $payments = Payment::query()
            ->where('shop_id', $shop->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return view('seller.orders.payments', [
            'payments' => $payments,
            'shop'     => $shop,
        ]);
    }

    
    /**
     * Buyer: place order from cart.
     */
    public function storeOrder(Request $request)
    {
        // Normalize checkbox
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

            // Group cart rows by shop
            $itemsByShop = [];
            foreach ($cart as $rowId => $item) {
                $productId = $item['product_id'] ?? null;
                /** @var \App\Models\Product|null $product */
                $product   = \App\Models\Product::with('shop')->find($productId);
                if (! $product) continue;

                // Build a friendly variation summary if missing
                $variationSummary = $item['variation_summary'] ?? null;
                if (! $variationSummary && !empty($item['variations']) && is_array($item['variations'])) {
                    // variations: [ ['type'=>'Color','value'=>'Red'], ... ]
                    $variationSummary = collect($item['variations'])
                        ->map(function ($v) {
                            $type  = $v['type']  ?? ($v['name'] ?? 'Choice');
                            $value = $v['value'] ?? ($v['option'] ?? '');
                            return trim($type . ': ' . $value);
                        })
                        ->filter()
                        ->join(', ');
                }

                $itemsByShop[$product->shop_id][] = [
                    'row_id'            => $rowId,
                    'product'           => $product,
                    'variation_summary' => $variationSummary,       // <-- store summary, not an ID
                    'quantity'          => (int) ($item['quantity'] ?? 1),
                    'price'             => (float) ($item['price'] ?? 0),
                    'profiles'          => $item['shipping_profiles'] ?? [],
                    'ship_prof'         => $item['selected_shipping_profile_id'] ?? null,
                ];
            }

            $orders = [];

            foreach ($itemsByShop as $shopId => $rows) {
                // Totals
                $shopSubtotal  = 0.0;
                $shopShipTotal = 0.0;

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

                // Create order
                $order = new \App\Models\Order();
                $order->user_id   = auth()->id();
                $order->shop_id   = $shopId;

                $order->full_name = $validated['full_name'];
                $order->email     = $validated['email'];
                $order->phone     = $validated['phone'];

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

                // Required (new)
                $order->shipping_method = 'standard';
                $order->payment_method  = 'paypal';

                $order->order_notes  = $validated['order_notes'] ?? null;
                $order->promo_code   = $validated['promo_code'] ?? null;

                $order->subtotal      = $shopSubtotal;
                $order->shipping_cost = $shopShipTotal;
                $order->total_amount  = $shopSubtotal + $shopShipTotal;
                $order->status        = Order::STATUS_PENDING;
                $order->save();

                // Order items
                foreach ($rows as $r) {
                    $qty      = $r['quantity'];
                    $price    = $r['price'];
                    $profiles = collect($r['profiles'] ?? []);

                    $selProfId  = $r['ship_prof'];
                    $selProfile = $profiles->firstWhere('id', $selProfId);
                    $unitShip   = $selProfile['base_rate'] ?? 0;

                    $orderItem = new \App\Models\OrderItem();
                    $orderItem->order_id            = $order->id;
                    $orderItem->product_id          = $r['product']->id;
                    $orderItem->variation_summary   = $r['variation_summary']; // <-- store text
                    $orderItem->quantity            = $qty;
                    $orderItem->price               = $price;
                    $orderItem->shipping_profile_id = $selProfId;              // can be null
                    $orderItem->shipping_cost       = $unitShip * $qty;
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
                $shop      = $order->shop;

                // Shop-owner notification (expects 4 params)
                // \Mail::to($shopOwner->email)->send(
                //     new \App\Mail\OrderCreatedShopOwnerMail($order, $shopOwner, $buyer, $shop)
                // );

                // // Buyer notification (expects 4 params)
                // \Mail::to($buyer->email)->send(
                //     new \App\Mail\OrderCreatedBuyerMail($order, $buyer, $shopOwner, $shop)
                // );
            }

            return redirect()
                ->route('buyer.orders.show', $orders[0]->id)
                ->with('success', 'Your orders have been placed successfully! Please proceed to payment.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Order creation failed: '.$e->getMessage(), [
                'user_id' => auth()->id(),
                'cart'    => $request->session()->get('cart'),
                'trace'   => $e->getTraceAsString(),
            ]);

            $msg = config('app.debug')
                ? ('Failed to create order: '.$e->getMessage())
                : 'Failed to create order. Please try again or contact support.';

            return back()->withErrors(['error' => $msg])->withInput();
        }
    }

    /**
     * Show the "Pay now" page for an order.
     */
    public function payNow($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('account.pay_now', ['order' => $order]);
    }

    /**
     * Show the "Pay now" page for an invoice (if used).
     */
    public function payNowInvoice($invoiceId)
    {
        $invoice = \App\Models\Invoice::findOrFail($invoiceId);
        return view('invoices.pay_now', ['order' => $invoice]);
    }

    /**
     * Payment success callback.
     */
    public function successDeposit(Request $request, $id)
    {
        $order  = Order::findOrFail($id);
        $method = $request->get('method', 'paypal');

        // Unique local transaction id
        $localTxId = $request->get('transaction_id');
        if (! $localTxId) {
            do {
                $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
            } while (Payment::where('local_transaction_id', $localTxId)->exists());
        }

        $currency = $order->currency ?? 'USD';

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

        if ($method === 'mpesa' && $request->filled('mpesa_receipt')) {
            $paymentData['mpesa_receipt'] = $request->input('mpesa_receipt');
        }

        $payment = Payment::create($paymentData);

        if ($payment) {
            $order->update(['status' => Order::STATUS_PROCESSING]);
        }

        $shop = $order->shop;

        Wallet::create([
            'user_id'    => $shop->user_id,
            'credit'     => $order->total_amount,
            'debit'      => 0,
            'balance'    => 0,
            'reference'  => $localTxId,
            'method'     => $method,
            'description'=> 'Order payment',
        ]);

        // Emails
        try {
            $order->load(['items.product', 'shop.user', 'user']);
            $buyer     = $order->user;
            $shopOwner = $shop->user;

            \Mail::to($shopOwner->email)->send(
                new \App\Mail\PaymentSuccessShopOwnerMail($order, $shopOwner, $buyer, $shop, $payment)
            );

            \Mail::to($buyer->email)->send(
                new \App\Mail\PaymentSuccessBuyerMail($order, $buyer, $shop, $payment)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send payment success emails: '.$e->getMessage(), [
                'order_id'  => $order->id,
                'payment_id'=> $payment->id ?? null,
            ]);
        }

        return redirect()
            ->route('account.orders')
            ->with('success', 'Your payment has been received. Your order is now processing.');
    }

    /**
     * Mark as shipped.
     */
    public function ship(Request $request, Order $order)
    {
        $data = $request->validate([
            'courier'     => 'required|string|max:100',
            'tracking_no' => 'required|string|max:120',
            'shipped_at'  => 'nullable|date',
            'ship_notes'  => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'status'      => Order::STATUS_SHIPPED,
                'courier'     => $data['courier'],
                'tracking_no' => $data['tracking_no'],
                'shipped_at'  => $data['shipped_at'] ?? now(),
                'ship_notes'  => $data['ship_notes'] ?? null,
            ]);
        });

        try {
            $order->load(['items.product', 'shop.user', 'user']);
            $buyer     = $order->user;
            $shopOwner = $order->shop->user;

            $shippingData = [
                'courier'     => $data['courier'],
                'tracking_no' => $data['tracking_no'],
                'ship_notes'  => $data['ship_notes'] ?? null,
            ];

            \Mail::to($shopOwner->email)->send(
                new \App\Mail\OrderShippedShopOwnerMail($order, $shopOwner, $buyer, $order->shop, $shippingData)
            );
            \Mail::to($buyer->email)->send(
                new \App\Mail\OrderShippedBuyerMail($order, $buyer, $order->shop, $shippingData)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send shipping emails: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }

        return back()->with('success', 'Order marked as shipped.');
    }

    /**
     * Update status via action param.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $action = $request->validate([
            'action' => 'required|string|in:deliver,cancel',
        ])['action'];

        return match ($action) {
            'deliver' => $this->deliver($order),
            'cancel'  => $this->cancel($request, $order),
        };
    }

    /**
     * Mark shipped order as delivered.
     */
    private function deliver(Order $order)
    {
        abort_unless($order->status === Order::STATUS_SHIPPED, 422, 'Only shipped orders can be marked as delivered.');

        DB::transaction(fn () => $order->update([
            'status'       => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]));

        try {
            $order->load(['items.product', 'shop.user', 'user']);
            $buyer     = $order->user;
            $shopOwner = $order->shop->user;

            \Mail::to($shopOwner->email)->send(
                new \App\Mail\OrderDeliveredShopOwnerMail($order, $shopOwner, $buyer, $order->shop)
            );
            \Mail::to($buyer->email)->send(
                new \App\Mail\OrderDeliveredBuyerMail($order, $buyer, $order->shop)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send delivered emails: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }

        return back()->with('success', 'Order marked as delivered.');
    }

    /**
     * Cancel a pending/processing order.
     */
    public function cancel(Request $request, Order $order)
    {
        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
            return back()->withErrors('This order can no longer be cancelled.');
        }

        $data = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'status'        => Order::STATUS_REFUNDED,
                'cancel_reason' => $data['cancel_reason'],
            ]);

            Wallet::create([
                'user_id'    => $order->user_id,
                'credit'     => $order->total_amount,
                'debit'      => 0,
                'balance'    => 0,
                'reference'  => 'refund_'.$order->id,
                'description'=> 'Order refund',
            ]);
        });

        $order->load(['user', 'shop.user']);
        $buyer  = $order->user;
        $seller = $order->shop->user;

        Notification::send([$buyer, $seller], new \App\Notifications\OrderCancelledNotification($order));

        return back()->with('success', 'Your order was cancelled successfully.');
    }
}
