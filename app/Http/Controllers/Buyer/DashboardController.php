<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the buyer dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // You can fetch any data a buyer needs, e.g.:
        // $ordersCount = auth()->user()->orders()->count();
        // $wishlistCount = auth()->user()->wishlistItems()->count();
        // return view('buyer.dashboard', compact('ordersCount','wishlistCount'));

        return view('buyer.dashboard');
    }
}
