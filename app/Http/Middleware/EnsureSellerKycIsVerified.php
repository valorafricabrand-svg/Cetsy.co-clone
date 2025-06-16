<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerKycIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // First check if user has active subscription
        if ($user && $user->isSeller() && !$user->hasActiveSubscription()) {
            return redirect()->route('seller.subscription')
                ->with('error', 'Please subscribe first to access KYC verification.');
        }

        // Then check KYC status
        if ($user && $user->isSeller() && (!$user->kyc || $user->kyc->status !== 'approved')) {
            return redirect()->route('seller.kyc')->with('error', 'Please complete KYC verification to access seller features.');
        }

        return $next($request);
    }
}
