<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if ($user && $user->isSeller()) {
            $shop = $user->shop;
            $grace = function_exists('subscription_grace_days') ? (int) subscription_grace_days() : (int) setting('subscription_grace_days', 5);
            $active = $user->hasActiveShopSubscription();
            // Sync shop is_active with subscription state when possible
            if ($shop && $shop->is_active !== $active) {
                $shop->is_active = $active;
                $shop->save();
            }

            if (!$active) {
                return redirect()->route('seller.subscription')
                    ->with('error', 'Your shop has been deactivated due to an expired subscription. Please renew to continue using seller features.');
            }
        }

        return $next($request);
    }
} 
