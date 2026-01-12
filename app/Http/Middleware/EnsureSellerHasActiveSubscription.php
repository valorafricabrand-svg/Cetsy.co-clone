<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionService;

class EnsureSellerHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if ($user && $user->isSeller()) {
            $shop   = $user->shop;
            $active = $user->hasActiveSubscription();
            if (!$active) {
                $trial = SubscriptionService::startTrialIfEligible($user);
                if ($trial) {
                    $active = true;
                }
            }

            // Keep shop state synced to overall subscription status when a shop exists
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
