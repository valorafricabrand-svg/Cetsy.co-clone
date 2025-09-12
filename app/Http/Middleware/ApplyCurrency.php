<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class ApplyCurrency
{
    public function handle(Request $request, Closure $next)
    {
        // Accept currency via query or header and persist to session
        $code = $request->query('currency') ?: $request->header('X-Currency');

        // If no explicit code provided and session missing, hydrate session from cookie as a resilient fallback
        if (!$code) {
            try {
                if (!session()->has('currency_code')) {
                    $cookieCode = $request->cookies->get('currency_code');
                    if (is_string($cookieCode) && strlen($cookieCode) === 3) {
                        session(['currency_code' => strtoupper($cookieCode)]);
                    }
                }
            } catch (\Throwable $e) {}
        }

        if (is_string($code)) {
            $code = strtoupper(trim($code));
            if (strlen($code) === 3) {
                $isValid = false;
                try {
                    if (class_exists('App\\Models\\Currency')) {
                        $isValid = \App\Models\Currency::where('code', $code)->where('is_active', true)->exists();
                    }
                } catch (\Throwable $e) {}
                if (!$isValid && function_exists('currencies')) {
                    $isValid = array_key_exists($code, currencies());
                }
                if ($isValid) {
                    // Persist to user if authenticated; else session; also set cookie fallback
                    try {
                        if (function_exists('auth') && auth()->check()) {
                            $u = auth()->user();
                            $u->preferred_currency = $code;
                            $u->save();
                        } else {
                            session(['currency_code' => $code]);
                        }
                    } catch (\Throwable $e) {}
                    $response = $next($request);
                    return $response->withCookie(cookie('currency_code', $code, 60 * 24 * 180, '/'));
                }
            }
        }

        return $next($request);
    }
}
