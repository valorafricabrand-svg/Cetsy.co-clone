<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\WalletTransaction;
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
        'shop'
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
