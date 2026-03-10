<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\WalletTransaction;
use App\Models\Product;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Activity;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the buyer dashboard.
     *
     * @return \Illuminate\View\View
     */
public function index()
{
   $user = Auth::user();

    if (!$user->shop) {
        return redirect()->route('seller.shop.create')
            ->with('warning', 'Please create a shop to continue.');
    }
    $shopId = $user->shop->id;

    // $orders = $user->orders()
    //     ->orderBy('id', 'desc')
    //     ->with(['customer', 'payment'])
    //     ->take(5)
    //     ->get();

    $orders = Order::where('shop_id', $shopId)
        ->orderBy('id', 'desc')
        ->with(['customer', 'payment', 'items.product', 'items.shippingProfile.processingTime'])
        ->take(5)
        ->get();



    $products = Product::where('shop_id', $shopId)
        ->orderBy('created_at', 'desc')
        ->with('category')
        ->get();

    $total_orders = $orders->count();
    $total_products = $products->count();

    // Offer counts for this seller's products
    $productIds = $products->pluck('id');
    $total_offers = Offer::whereIn('product_id', $productIds)->count();
    $accepted_offers = Offer::whereIn('product_id', $productIds)->where('status', 'accepted')->count();
    $declined_offers = Offer::whereIn('product_id', $productIds)->where('status', 'declined')->count();

    // Messages sent from Favorites (analytics)
    $favorites_messages_total = Activity::where('user_id', $user->id)
        ->where('type', Activity::TYPE_MESSAGE)
        ->where('properties->source', 'favorites')
        ->count();
    $favorites_messages_week = Activity::where('user_id', $user->id)
        ->where('type', Activity::TYPE_MESSAGE)
        ->where('properties->source', 'favorites')
        ->where('created_at', '>=', now()->subDays(7))
        ->count();

    // Check holiday mode status
    $activeProducts = Product::where('shop_id', $shopId)->where('is_active', 1)->count();
    $pausedProducts = Product::where('shop_id', $shopId)->where('is_active', 2)->count();
    $isHolidayMode = (bool) ($user->shop->is_holiday_mode ?? false);

    // Recent reviews for this shop (from delivered/completed orders)
    $recentReviews = Review::with(['order:id,user_id,status,created_at', 'orderItem.product:id,name'])
        ->where('shop_id', $shopId)
        ->whereHas('order', function($q){
            $q->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]);
        })
        ->latest('created_at')
        ->take(5)
        ->get();

    return view('seller.dashboard', compact(
        'orders', 'products', 'total_orders', 'total_products',
        'total_offers', 'accepted_offers', 'declined_offers',
        'isHolidayMode', 'activeProducts', 'pausedProducts',
        'recentReviews',
        'favorites_messages_total', 'favorites_messages_week'
    ));
}

/**
 * Enable holiday mode by pausing all active products
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function enableHolidayMode(Request $request)
{
    $user = Auth::user();
    
    if (!$user->shop) {
        return redirect()->route('seller.shop.create')
            ->with('warning', 'Please create a shop to continue.');
    }
    
    $shopId = $user->shop->id;
    
    // Update all active products (is_active = 1) to paused status (is_active = 2)
    $updatedCount = Product::where('shop_id', $shopId)
        ->where('is_active', 1)
        ->update(['is_active' => 2]);

    $user->shop->forceFill(['is_holiday_mode' => true])->save();
    
    return redirect()->route('seller.shops.show', $user->shop)
        ->with('success', "Holiday mode enabled! {$updatedCount} active products have been paused.");
}

/**
 * Disable holiday mode by reactivating all paused products
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function disableHolidayMode(Request $request)
{
    $user = Auth::user();
    
    if (!$user->shop) {
        return redirect()->route('seller.shop.create')
            ->with('warning', 'Please create a shop to continue.');
    }
    
    $shopId = $user->shop->id;
    
    // Update all paused products (is_active = 2) back to active status (is_active = 1)
    $updatedCount = Product::where('shop_id', $shopId)
        ->where('is_active', 2)
        ->update(['is_active' => 1]);

    $user->shop->forceFill(['is_holiday_mode' => false])->save();
    
    return redirect()->route('seller.shops.show', $user->shop)
        ->with('success', "Holiday mode disabled! {$updatedCount} paused products have been reactivated.");
}

}
