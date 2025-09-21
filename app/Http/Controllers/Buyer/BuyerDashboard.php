<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\WalletTransaction;
use App\Services\Recommendation\ProductRecommendationService;

use Illuminate\Support\Facades\Auth;

class BuyerDashboard extends Controller
{
    protected ProductRecommendationService $recommendations;

    public function __construct(ProductRecommendationService $recommendations)
    {
        $this->recommendations = $recommendations;
    }

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
        $recommendedProducts = $this->recommendations->trendingForUser(Auth::user(), 8);

        // Fetch favorites (wish list products)
        $favoriteProducts = Auth::user()->favorites()->with('media')->get();

        // Fetch offers made by the buyer
        $offers = \App\Models\Offer::where('buyer_id', Auth::id())->with('product')->latest()->get();

        // Offer stats for the buyer
        $total_offers = (clone $offers)->count();
        $accepted_offers = \App\Models\Offer::where('buyer_id', Auth::id())->where('status', 'accepted')->count();
        $declined_offers = \App\Models\Offer::where('buyer_id', Auth::id())->where('status', 'declined')->count();

        return view('buyer.dashboard', compact(
            'ordersCount',
            'wishlistCount',
            'accountBalance',
            'recentOrders',
            'recommendedProducts',
            'favoriteProducts',
            'offers',
            'total_offers',
            'accepted_offers',
            'declined_offers'
        ));
    }
}




