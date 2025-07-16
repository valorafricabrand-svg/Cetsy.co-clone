<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\WalletTransaction;
use App\Models\Product;

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
        // If there is a non-empty "cart" in session, redirect to the cart view
        if (session()->has('cart') && is_array(session('cart')) && count(session('cart')) > 0) {
            return redirect()->route('cart.view');
        }

        $ordersCount = Order::where('user_id', Auth::id())->count();
        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
        $accountBalance = wallet();
        $recentOrders = Order::where('user_id', Auth::id())->latest()->take(5)->get();
        $recommendedProducts = Product::take(8)->get(); // Replace with recommendation logic

        // Fetch favorites (wish list products)
        $favoriteProducts = Auth::user()->favorites()->with('media')->get();

        // Fetch offers made by the buyer
        $offers = \App\Models\Offer::where('buyer_id', Auth::id())->with('product')->latest()->get();

        
        return view('buyer.dashboard', compact(
            'ordersCount',
            'wishlistCount',
            'accountBalance',
            'recentOrders',
            'recommendedProducts',
            'favoriteProducts',
            'offers'
        ));
    }
}
