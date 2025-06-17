<?php

namespace App\Http\Controllers\Seller;

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
     $user = Auth::user();
        
        // Check if seller has active subscription
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('seller.subscription')
                ->with('warning', 'Please activate your subscription to access seller features.');
        }
        
        // Check if seller has completed KYC
        // if (!$user->kyc || $user->kyc->status !== 'approved') {
        //     return redirect()->route('seller.kyc.create')
        //         ->with('warning', 'Please complete your KYC verification to access seller features.');
        // }
        
        // If all checks pass, show the dashboard
        $orders = $user->orders()->orderBy('created_at', 'Desc')->with(['customer', 'payment'])->take(5)->get();
             $shopId = $user->shop->id;
        $products = Product::whereShopId($shopId)->orderBy('created_at', 'Desc')->with(['category'])->take(5)->get();
        $total_orders = $user->orders->count();
        $total_products = $products->count();

        // dd($products);

        return view('seller.dashboard', compact('orders', 'products', 'total_orders', 'total_products'));

   
}




}
