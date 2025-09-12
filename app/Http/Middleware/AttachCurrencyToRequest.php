<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AttachCurrencyToRequest
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Only attach if not already provided by client
            if (!$request->has('currency')) {
                $code = get_currency();
                if (is_string($code) && strlen($code) === 3) {
                    $request->merge(['currency' => strtoupper($code)]);
                }
            }
        } catch (\Throwable $e) {}

        return $next($request);
    }
}

