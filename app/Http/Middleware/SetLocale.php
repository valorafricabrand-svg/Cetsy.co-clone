<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = (string) config('locales.cookie', 'locale');

        $locale = null;

        foreach ([
            $request->query('lang'),
            $request->route('locale'),
            $request->hasSession() ? $request->session()->get('locale') : null,
            $request->cookie($cookieName),
            auth()->check() ? auth()->user()->preferred_locale : null,
            config('app.locale'),
        ] as $candidate) {
            $normalized = normalize_locale(is_scalar($candidate) ? (string) $candidate : null);

            if ($normalized) {
                $locale = $normalized;
                break;
            }
        }

        $locale = $locale ?: default_locale();

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
