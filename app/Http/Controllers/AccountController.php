<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use App\Mail\OrderCancelledShopOwnerMail;
use App\Mail\OrderCancelledBuyerMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use App\Services\Recommendation\ProductRecommendationService;

class AccountController extends Controller
{
    protected ProductRecommendationService $recommendations;

    public function __construct(ProductRecommendationService $recommendations)
    {
        $this->recommendations = $recommendations;
    }

    public function dashboard()
    {
        $ordersCount = Order::where('user_id', Auth::id())->count();
        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
        $accountBalance = WalletTransaction::where('user_id', Auth::id())->sum('balance');
        $recentOrders = Order::where('user_id', Auth::id())->latest()->take(5)->get();
        $recommendedProducts = $this->recommendations->trendingForUser(Auth::user(), 8);

        return view('account.dashboard', compact(
            'ordersCount',
            'wishlistCount',
            'accountBalance',
            'recentOrders',
            'recommendedProducts'
        ));
    }

    public function orders(Request $request)
    {
        // Start query scoped to current user
        $query = Order::where('user_id', Auth::id())
            ->with(['items.shippingProfile.processingTime']);

        // If a search term is provided, filter by order ID or status
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Get paginated results, newest first
        $orders = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString(); // keep search param on pagination links

        // Return the enhanced view
        return view('account.orders', compact('orders'));
    }

public function orderDetails(Order $order)
{
    abort_if(!Auth::check() || $order->user_id !== Auth::id(), 404);

    $order->loadMissing([
        'items.product',
        'items.shippingProfile.processingTime',
        'shop',
        'payments' => fn($query) => $query->orderBy('created_at'),
    ]);

    // Mark order notifications as read for the buyer
    try {
        \App\Models\Activity::where('user_id', Auth::id())
            ->where('type', \App\Models\Activity::TYPE_ORDER)
            ->where(function($q) use ($order) { $q->where('related_id', $order->id)->orWhereNull('related_id'); })
            ->where('is_read', false)
            ->update(['is_read' => true]);
    } catch (\Throwable $e) { /* non-fatal */ }

    return view('account.order_details', compact('order'));
}

    public function cancelOrder(Request $request, Order $order)
    {
        abort_if(!Auth::check() || $order->user_id !== Auth::id(), 404);

        $cancellable = [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING];
        if (!in_array($order->status, $cancellable, true)) {
            return back()->with('error', 'This order cannot be cancelled at its current status.');
        }

        $validated = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $validated['cancel_reason'] ?? null;

        DB::transaction(function () use ($order, $reason) {
            if ($order->status === Order::STATUS_PROCESSING) {
                // Paid order: mark as refunded, wallet entries, and restock
                $order->update([
                    'status'        => Order::STATUS_REFUNDED,
                    'cancel_reason' => $reason,
                ]);

                // Refund buyer
                Wallet::create([
                    'user_id'    => $order->user_id,
                    'credit'     => (float) $order->total_amount,
                    'debit'      => 0,
                    'balance'    => 0,
                    'reference'  => 'refund_'.$order->id,
                    'description'=> 'Order refund',
                ]);

                // Debit seller
                $shopUserId = optional($order->shop)->user_id;
                if ($shopUserId) {
                    Wallet::create([
                        'user_id'    => $shopUserId,
                        'credit'     => 0,
                        'debit'      => (float) $order->total_amount,
                        'balance'    => 0,
                        'reference'  => 'seller_debit_'.$order->id,
                        'description'=> 'Order cancellation - payment reversed',
                    ]);
                }

                // Restock inventory (physical items only)
                try {
                    $order->loadMissing('items.product');
                    foreach ($order->items as $item) {
                        $product = $item->product; if (!$product) continue;
                        if (strtolower((string)($product->type ?? 'physical')) !== 'physical') continue;
                        $qty = max(1, (int) ($item->quantity ?? 1));
                        if (!is_null($product->stock)) {
                            $product->update(['stock' => ((int)$product->stock) + $qty]);
                        }
                        $variantId = (int) ($item->getAttribute('product_variation_id') ?? 0);
                        if ($variantId > 0) {
                            try { $variant = \App\Models\Variant::find($variantId); if ($variant && !is_null($variant->stock)) { $variant->update(['stock' => ((int)$variant->stock) + $qty]); } } catch (\Throwable $e) { /* ignore */ }
                        }
                    }
                } catch (\Throwable $e) { Log::warning('order.inventory.restock_failed', ['order_id'=>$order->id, 'error'=>$e->getMessage()]); }
            } else {
                // Pending: simple cancel (no inventory decremented yet)
                $order->update([
                    'status'        => Order::STATUS_CANCELLED,
                    'cancel_reason' => $reason,
                ]);
            }
        });

        // Emails to buyer and shop owner (best-effort)
        try {
            $order->load(['items.product', 'shop.user', 'user']);
            $buyer     = $order->user;
            $shop      = $order->shop;
            $shopOwner = optional($shop)->user;

            if ($buyer && $buyer->email) {
                Mail::to($buyer->email)->send(
                    new OrderCancelledBuyerMail($order, $buyer, $shop, $reason)
                );
            }
            if ($shopOwner && $shopOwner->email) {
                Mail::to($shopOwner->email)->send(
                    new OrderCancelledShopOwnerMail($order, $shopOwner, $buyer, $shop, $reason)
                );
            }
        } catch (Throwable $e) {
            Log::error('order.cancel.email_failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('buyer.orders.show', $order)->with('success', 'Order cancelled successfully.');
    }

    public function payments()
    {
        $payments = Payment::where('user_id', Auth::id())->get();
        return view('account.payments', compact('payments'));
    }

    public function details()
    {
        return view('account.details');
    }

    public function updateDetails(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();
        
        return redirect()->route('account.details')->with('success', 'Account details updated.');
    }

    public function addresses()
    {
        $addresses = Address::where('user_id', Auth::id())->get();
        return view('account.addresses', compact('addresses'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
