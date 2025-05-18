<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the seller dashboard, or redirect to shop creation if none exists.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Ensure the seller has a shop
        $shop = $user->shop;
        if (! $shop) {
            return redirect()->route('shops.create')
                             ->with('info', 'Please create your shop first.');
        }

        // 2. Compute metrics
        $productsCount = $shop->products()->count();
        $salesCount    = $shop->orders()->count();
        $earnings      = $shop->orders()->sum('total');

        // 3. Pass everything to the view
        return view('seller.dashboard', [
            'shop'          => $shop,
            'productsCount' => $productsCount,
            'salesCount'    => $salesCount,
            'earnings'      => $earnings,
        ]);
    }
}
