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
        
        if ($user && $user->isSeller() && !$user->hasActiveSubscription()) {
            return redirect()->route('seller.subscription')
                ->with('error', 'Your subscription has expired. Please renew to continue using seller features.');
        }

        return $next($request);
    }
} 