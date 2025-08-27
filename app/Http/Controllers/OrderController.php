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
public function index(Request $request)
{
    $user   = auth()->user();
    $shopId = Shop::where('user_id', $user->id)->value('id');

    // Base query scoped to this shop
    $baseQuery = Order::where('shop_id', $shopId);

    // 1) Compute per-status counts
    $counts = (clone $baseQuery)
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

    // Ensure zeros for missing statuses
    $allStatuses = ['pending','processing','shipped','completed','cancelled'];
    foreach ($allStatuses as $st) {
        $counts[$st] = $counts[$st] ?? 0;
    }

    $total = array_sum($counts);
    $statusCounts = array_merge(['all' => $total], $counts);

    // 2) Apply status filter
    $currentStatus = $request->query('status', 'all');
    if ($currentStatus !== 'all' && isset($counts[$currentStatus])) {
        $baseQuery->where('status', $currentStatus);
    }

    // 3) Apply search by ID
    $searchId = $request->query('search');
    if (!empty($searchId) && is_numeric($searchId)) {
        $baseQuery->where('id', $searchId);
    }

    // 4) Paginate with items & product eager-load
    $orders = $baseQuery
        ->with(['items.product'])
        ->orderByDesc('id')
        ->paginate(15)
        ->withQueryString(); // preserves status & search

    return view('seller.orders.index', compact(
        'user',
        'orders',
        'statusCounts',
        'currentStatus',
        'searchId'
    ));
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
    // Normalize checkbox -> boolean "1"/"0" → true/false
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

        // If you want stricter billing validation when not same-as:
        // 'billing_country'          => 'required_if:billing_same_as_shipping,false|nullable|integer|exists:countries,id',
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

        // Group prepared rows per shop
        $itemsByShop = [];

        foreach ($cart as $rowId => $item) {
            $productId = (int)($item['product_id'] ?? 0);
            if (!$productId) {
                continue;
            }

            /** @var \App\Models\Product|null $product */
            $product = \App\Models\Product::with(['shop', 'variations']) // variations for price resolution
                        ->find($productId);

            if (!$product || !$product->shop_id) {
                // Product no longer exists or has no shop; skip this row
                continue;
            }

            // Build a friendly variation summary if missing
            $variationSummary = $item['variation_summary'] ?? null;
            if (!$variationSummary && !empty($item['variations']) && is_array($item['variations'])) {
                // variations: [ ['type'=>'Color','value'=>'Red'], ... ]
                $variationSummary = collect($item['variations'])
                    ->map(function ($v) {
                        $type  = $v['type']  ?? ($v['name'] ?? 'Choice');
                        $value = $v['value'] ?? ($v['option'] ?? '');
                        $type  = trim((string)$type);
                        $value = trim((string)$value);
                        return $type && $value ? ($type . ': ' . $value) : null;
                    })
                    ->filter()
                    ->join(', ');
            }

            $qty = max(1, (int)($item['quantity'] ?? 1));

            // ————————— Authoritative Unit Price —————————
            // Prefer variant price (if cart stored a variant_id AND it exists on this product),
            // fall back to product discounted/base price.
            $unitPrice = null;
            $variantId = $item['variant_id'] ?? null;
            if ($variantId && $product->relationLoaded('variations')) {
                $variant = $product->variations->firstWhere('id', (int)$variantId);
                if ($variant && $variant->price !== null) {
                    $unitPrice = $product->applyDiscount((float)$variant->price);
                }
            }
            if ($unitPrice === null) {
                $unitPrice = (float)($product->discounted_price ?? $product->price ?? 0);
            }

            // ————————— Selected Shipping Profile (from session snapshot) —————————
            // Snapshot is a flat array like:
            // ['id'=>.., 'name'=>.., 'base_rate'=>.., 'is_default'=>.., 'dest_location_type'=>.., 'dest_country_name'=>..]
            $profiles   = collect($item['shipping_profiles'] ?? []);
            $selProfId  = (int)($item['selected_shipping_profile_id'] ?? 0);

            // Ensure the selected is actually in the snapshot; else fallback to default/first
            $selected   = $profiles->firstWhere('id', $selProfId);
            if (!$selected) {
                $selected = $profiles->firstWhere('is_default', true) ?: $profiles->first();
            }
            // If still nothing (no profiles), treat as zero-rate
            $unitShip = (float)($selected['base_rate'] ?? 0);
            $selProfId = $selected['id'] ?? null;

            // Accumulate for this shop
            $itemsByShop[$product->shop_id][] = [
                'row_id'            => $rowId,
                'product'           => $product,
                'variation_summary' => $variationSummary,
                'quantity'          => $qty,
                'unit_price'        => $unitPrice,
                'profiles'          => $profiles->values()->all(), // keep snapshot for traceability
                'selected_profile'  => $selected,
                'selected_profile_id' => $selProfId,
                'unit_shipping'     => $unitShip,
            ];
        }

        if (empty($itemsByShop)) {
            DB::rollBack();
            return back()->withErrors(['cart' => 'Your cart items are invalid or products are unavailable.']);
        }

        $orders = [];

        foreach ($itemsByShop as $shopId => $rows) {
            // Totals
            $shopSubtotal  = 0.0;
            $shopShipTotal = 0.0;

            foreach ($rows as $r) {
                $lineSub  = $r['unit_price']   * $r['quantity'];
                $lineShip = $r['unit_shipping'] * $r['quantity'];

                $shopSubtotal  += $lineSub;
                $shopShipTotal += $lineShip;
            }

            // Create order
            $order = new \App\Models\Order();
            $order->user_id   = auth()->id();
            $order->shop_id   = (int)$shopId;

            $order->full_name = $validated['full_name'];
            $order->email     = $validated['email'];
            $order->phone     = $validated['phone'];

            $order->shipping_country_id  = (int)$validated['shipping_country'];
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

            // If you later support multiple choices, take from request; for now defaults:
            $order->shipping_method = 'standard';
            $order->payment_method  = 'paypal';

            $order->order_notes  = $validated['order_notes'] ?? null;
            $order->promo_code   = $validated['promo_code'] ?? null;

            $order->subtotal      = (float)$shopSubtotal;
            $order->shipping_cost = (float)$shopShipTotal;
            $order->total_amount  = (float)($shopSubtotal + $shopShipTotal);
            $order->status        = \App\Models\Order::STATUS_PENDING;
            $order->save();

            // Order items
            foreach ($rows as $r) {
                $orderItem = new \App\Models\OrderItem();
                $orderItem->order_id            = $order->id;
                $orderItem->product_id          = $r['product']->id;
                $orderItem->variation_summary   = $r['variation_summary']; // store plain text summary
                $orderItem->quantity            = (int)$r['quantity'];
                $orderItem->price               = (float)$r['unit_price'];
                $orderItem->shipping_profile_id = $r['selected_profile_id']; // can be null
                $orderItem->shipping_cost       = (float)($r['unit_shipping'] * $r['quantity']);
                $orderItem->save();

                // Optional: decrement stock here if you manage inventory on checkout
                // $r['product']->decrement('stock', $r['quantity']);
            }

            $orders[] = $order;
        }

        DB::commit();

        // Clear cart after orders are created
        $request->session()->forget('cart');

        // Notify (left commented as in your original)
        // foreach ($orders as $order) {
        //     $order->load(['items.product', 'shop.user']);
        //     $buyer     = auth()->user();
        //     $shopOwner = $order->shop->user;
        //     $shop      = $order->shop;
        //     \Mail::to($shopOwner->email)->send(new \App\Mail\OrderCreatedShopOwnerMail($order, $shopOwner, $buyer, $shop));
        //     \Mail::to($buyer->email)->send(new \App\Mail\OrderCreatedBuyerMail($order, $buyer, $shopOwner, $shop));
        // }

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

        if ($order->isPaid()) {
            return redirect()
                ->route('account.orders')
                ->with('error', 'This order has already been paid.');
        }

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

        if ($order->isPaid()) {
            return redirect()
                ->route('account.orders')
                ->with('error', 'This order has already been paid.');
        }

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
            // Check if buyer has paid (order is PROCESSING)
            if ($order->status === Order::STATUS_PROCESSING) {
                // Buyer has paid - full refund process
                $order->update([
                    'status'        => Order::STATUS_REFUNDED,
                    'cancel_reason' => $data['cancel_reason'],
                ]);

                // Refund buyer
                Wallet::create([
                    'user_id'    => $order->user_id,
                    'credit'     => $order->total_amount,
                    'debit'      => 0,
                    'balance'    => 0,
                    'reference'  => 'refund_'.$order->id,
                    'description'=> 'Order refund',
                ]);

                // Debit seller to reverse the payment
                Wallet::create([
                    'user_id'    => $order->shop->user_id,
                    'credit'     => 0,
                    'debit'      => $order->total_amount,
                    'balance'    => 0,
                    'reference'  => 'seller_debit_'.$order->id,
                    'description'=> 'Order cancellation - payment reversed',
                ]);
            } else {
                // Buyer hasn't paid (order is PENDING) - simple cancellation
                $order->update([
                    'status'        => Order::STATUS_CANCELLED,
                    'cancel_reason' => $data['cancel_reason'],
                ]);
                // No financial transactions needed for unpaid orders
            }
        });

        $order->load(['user', 'shop.user']);
        $buyer  = $order->user;
        $seller = $order->shop->user;

        Notification::send([$buyer, $seller], new \App\Notifications\OrderCancelledNotification($order));

        $statusMessage = $order->status === Order::STATUS_REFUNDED 
            ? 'Your order was cancelled successfully.'
            : 'Your order was cancelled successfully.';

        return back()->with('success', $statusMessage);
    }

    /**
     * Seller cancels an order.
     */
    public function sellerCancel(Request $request, Order $order)
    {
        // Verify the authenticated user owns the shop for this order
        $user = auth()->user();
        $shop = Shop::where('user_id', $user->id)->first();
        
        if (!$shop || $order->shop_id !== $shop->id) {
            return back()->withErrors('You are not authorized to cancel this order.');
        }

        // Only allow cancellation for pending and processing orders
        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
            return back()->withErrors('This order can no longer be cancelled.');
        }

        $data = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $data) {
            // Check if buyer has paid (order is PROCESSING)
            if ($order->status === Order::STATUS_PROCESSING) {
                // Buyer has paid - full refund process
                $order->update([
                    'status'        => Order::STATUS_REFUNDED,
                    'cancel_reason' => 'Seller cancelled: ' . $data['cancel_reason'],
                ]);

                // Refund buyer
                Wallet::create([
                    'user_id'    => $order->user_id,
                    'credit'     => $order->total_amount,
                    'debit'      => 0,
                    'balance'    => 0,
                    'reference'  => 'seller_refund_'.$order->id,
                    'description'=> 'Order cancelled by seller - refund',
                ]);

                // Debit seller to reverse the payment
                Wallet::create([
                    'user_id'    => $order->shop->user_id,
                    'credit'     => 0,
                    'debit'      => $order->total_amount,
                    'balance'    => 0,
                    'reference'  => 'seller_cancellation_debit_'.$order->id,
                    'description'=> 'Order cancelled by seller - payment reversed',
                ]);
            } else {
                // Buyer hasn't paid (order is PENDING) - simple cancellation
                $order->update([
                    'status'        => Order::STATUS_CANCELLED,
                    'cancel_reason' => 'Seller cancelled: ' . $data['cancel_reason'],
                ]);
                // No financial transactions needed for unpaid orders
            }
        });

        $order->load(['user', 'shop.user']);
        $buyer  = $order->user;
        $seller = $order->shop->user;

        Notification::send([$buyer, $seller], new \App\Notifications\OrderCancelledNotification($order));

        $statusMessage = $order->status === Order::STATUS_REFUNDED 
            ? 'Order cancelled and buyer refunded successfully.'
            : 'Order cancelled successfully.';

        return back()->with('success', $statusMessage);
    }


    public function process(Request $request, Order $order)
    {
        $this->authorizeSeller($order);

        if ($order->status !== Order::STATUS_PENDING) {
            return back()->withErrors('Only pending orders can be processed.');
        }

        // If you want to REQUIRE payment before processing, uncomment this block:
        /*
        $paid = (float) $order->payments()
            ->where(function ($q) {
                $q->whereIn('status', ['success', 'completed', 'paid', 3]);
            })
            ->sum('total_amount');

        if ($paid <= 0) {
            return back()->withErrors('This order has not been paid yet.');
        }
        */

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => Order::STATUS_PROCESSING,
            ]);
        });

        // Optional: notify buyer & seller (ignore if you don’t have this notification)
        try {
            Notification::send(
                [$order->user, $order->shop?->user],
                new \App\Notifications\OrderProcessingNotification($order)
            );
        } catch (\Throwable $e) {
            // swallow if notification class doesn’t exist
        }

        return back()->with('success', 'Order moved to processing.');
    }


        private function authorizeSeller(Order $order): void
    {
        if (!auth()->check() || optional($order->shop)->user_id !== auth()->id()) {
            abort(403, 'You are not allowed to manage this order.');
        }
    }
}
