<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class RequireKycAfterTwoSales
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->isSeller() || !$user->shop) {
            return $next($request); // Not a seller or no shop, let other middleware handle
        }

        // Count completed sales for this shop
        $completedSales = Order::where('shop_id', $user->shop->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->count();


        if ($completedSales < 2) {
            return $next($request); // Allow listing creation
        }

        // Check if KYC is approved
        if (!$user->kyc || $user->kyc->status !== 'approved') {
            return redirect()->route('seller.kyc')
                ->with('warning', 'Please submit and get your KYC approved to continue listing after 2 sales.');
        }


        return $next($request);
    }
} 