<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Address;
use App\Models\Country;
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
use App\Services\CommissionService;

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
            ->with(['items.shippingProfile.processingTime', 'shop']);

        // Text search (ID or status)
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Date range filter
        $from = $request->input('from');
        $to   = $request->input('to');
        try { if ($from) { $fromC = \Carbon\Carbon::createFromFormat('Y-m-d', $from)->startOfDay(); $query->where('created_at', '>=', $fromC); } } catch (\Throwable $e) {}
        try { if ($to)   { $toC   = \Carbon\Carbon::createFromFormat('Y-m-d', $to)->endOfDay();   $query->where('created_at', '<=', $toC);   } } catch (\Throwable $e) {}

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'amount_desc') {
            $query->orderByDesc('total_amount');
        } elseif ($sort === 'amount_asc') {
            $query->orderBy('total_amount');
        } else {
            $query->orderByDesc('id');
        }

        $orders = $query->paginate(10)->withQueryString();

        // Summary counters (quick filters)
        $base = Order::where('user_id', Auth::id());
        $summary = [
            'all'        => (clone $base)->count(),
            'pending'    => (clone $base)->where('status', Order::STATUS_PENDING)->count(),
            'processing' => (clone $base)->where('status', Order::STATUS_PROCESSING)->count(),
            'shipped'    => (clone $base)->where('status', Order::STATUS_SHIPPED)->count(),
            'delivered'  => (clone $base)->where('status', Order::STATUS_DELIVERED)->count(),
            'completed'  => (clone $base)->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled'  => (clone $base)->where('status', Order::STATUS_CANCELLED)->count(),
            'refunded'   => (clone $base)->where('status', Order::STATUS_REFUNDED)->count(),
        ];

        return view('account.orders', compact('orders', 'summary'));
    }

public function orderDetails(Order $order)
{
    abort_if(!Auth::check() || $order->user_id !== Auth::id(), 404);

    $order->loadMissing([
        'items.product',
        'items.product.digitalFiles',
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

                // Debit seller (on-hold if seller funds are still on hold for this order)
                $shopUserId = optional($order->shop)->user_id;
                if ($shopUserId) {
                    $hasOnHold = Wallet::where('user_id', $shopUserId)
                        ->where('status', 'on_hold')
                        ->where('meta->order_id', $order->id)
                        ->exists();

                    Wallet::create([
                        'user_id'    => $shopUserId,
                        'credit'     => 0,
                        'debit'      => (float) $order->total_amount,
                        'balance'    => 0,
                        'reference'  => 'seller_debit_'.$order->id,
                        'description'=> 'Order cancellation - payment reversed',
                        'status'     => $hasOnHold ? 'on_hold' : 'completed',
                        'meta'       => ['order_id' => $order->id],
                    ]);

                    CommissionService::refundCommission(
                        (int) $shopUserId,
                        (int) $order->id,
                        (float) $order->total_amount,
                        (float) $order->total_amount,
                        $hasOnHold ? 'on_hold' : 'completed'
                    );
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
        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->latest()
            ->get();
        return view('account.addresses', compact('addresses'));
    }

    public function createAddress()
    {
        $address = new Address([
            'type' => 'shipping',
            'is_default' => true,
        ]);

        $countries = Country::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('account.address_form', [
            'address' => $address,
            'countries' => $countries,
            'formMode' => 'create',
        ]);
    }

    public function storeAddress(Request $request)
    {
        $data = $this->validateAddressData($request);
        $data['user_id'] = Auth::id();
        $data['is_default'] = $request->boolean('is_default');

        $data = $this->normalizeAddressData($data);

        if ($data['is_default']) {
            Address::where('user_id', Auth::id())
                ->where('type', $data['type'])
                ->update(['is_default' => false]);
        }

        Address::create($data);

        return redirect()
            ->route('account.addresses')
            ->with('success', 'Address added successfully.');
    }

    public function editAddress(Address $address)
    {
        abort_if($address->user_id !== Auth::id(), 404);

        $countries = Country::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('account.address_form', [
            'address' => $address,
            'countries' => $countries,
            'formMode' => 'edit',
        ]);
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_if($address->user_id !== Auth::id(), 404);

        $data = $this->validateAddressData($request);
        $data['is_default'] = $request->boolean('is_default');
        $data = $this->normalizeAddressData($data);

        if ($data['is_default']) {
            Address::where('user_id', Auth::id())
                ->where('type', $data['type'])
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()
            ->route('account.addresses')
            ->with('success', 'Address updated successfully.');
    }

    public function destroyAddress(Address $address)
    {
        abort_if($address->user_id !== Auth::id(), 404);

        $address->delete();

        return redirect()
            ->route('account.addresses')
            ->with('success', 'Address deleted successfully.');
    }

    private function validateAddressData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:shipping,billing'],
            'label' => ['nullable', 'string', 'max:100'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'country' => ['nullable', 'string', 'max:120'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'zip' => ['nullable', 'string', 'max:40'],
        ]);
    }

    private function normalizeAddressData(array $data): array
    {
        $countryId = isset($data['country_id']) && $data['country_id'] !== ''
            ? (int) $data['country_id']
            : null;

        $countryName = trim((string) ($data['country'] ?? ''));
        if ($countryId) {
            $countryName = (string) (optional(Country::find($countryId))->name ?? $countryName);
        }

        $line1 = trim((string) ($data['address_1'] ?? ''));
        $line2 = trim((string) ($data['address_2'] ?? ''));
        $combinedAddress = trim($line1 . ' ' . $line2);

        $data['country_id'] = $countryId;
        $data['country'] = $countryName !== '' ? $countryName : null;
        $data['address_1'] = $line1 !== '' ? $line1 : null;
        $data['address_2'] = $line2 !== '' ? $line2 : null;
        $data['address'] = $combinedAddress !== '' ? $combinedAddress : null;
        $data['city'] = !empty($data['city']) ? trim((string) $data['city']) : null;
        $data['state'] = !empty($data['state']) ? trim((string) $data['state']) : null;
        $data['zip'] = !empty($data['zip']) ? trim((string) $data['zip']) : null;
        $data['label'] = !empty($data['label']) ? trim((string) $data['label']) : null;
        $data['full_name'] = !empty($data['full_name']) ? trim((string) $data['full_name']) : null;
        $data['email'] = !empty($data['email']) ? trim((string) $data['email']) : null;
        $data['phone'] = !empty($data['phone']) ? trim((string) $data['phone']) : null;

        return $data;
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
