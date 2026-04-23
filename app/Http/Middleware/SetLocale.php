<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
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

        if ($redirect = $this->canonicalRedirect($request)) {
            return $redirect;
        }

        return $next($request);
    }

    /**
     * Redirect default-locale prefixed URLs back to their canonical base routes.
     */
    protected function canonicalRedirect(Request $request): ?Response
    {
        if (! $request->isMethodCacheable()) {
            return null;
        }

        $route = $request->route();
        $routeLocale = normalize_locale(is_scalar($route?->parameter('locale')) ? (string) $route->parameter('locale') : null);

        if ($routeLocale !== default_locale()) {
            return null;
        }

        $routeName = base_route_name($route?->getName());

        if (! $routeName || ! route_has_localized_variant($routeName) || ! Route::has($routeName)) {
            return null;
        }

        $routeParameters = $route->parameters();
        unset($routeParameters['locale']);

        $target = route($routeName, route_parameters_for($routeName, $routeParameters), true);

        $query = $request->query();
        unset($query['lang']);

        if (! empty($query)) {
            $target .= '?' . Arr::query($query);
        }

        return redirect()->to($target, 301);
    }
}
