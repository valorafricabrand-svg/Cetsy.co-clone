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

use Illuminate\Support\Facades\Auth;

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
        ->with(['customer', 'payment'])
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

    return view('seller.dashboard', compact('orders', 'products', 'total_orders', 'total_products', 'total_offers', 'accepted_offers', 'declined_offers'));
}





}
