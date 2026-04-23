<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocaleSelectionController extends Controller
{
    /**
     * Persist the visitor's selected locale.
     */
    public function set(Request $request, ?string $locale = null): RedirectResponse|JsonResponse
    {
        $selectedLocale = normalize_locale($locale ?: $request->query('locale'));

        if (! $selectedLocale) {
            return back()->with('error', __('Unsupported language selection.'));
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $selectedLocale);
        }

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->preferred_locale !== $selectedLocale) {
                $user->preferred_locale = $selectedLocale;
                $user->save();
            }
        }

        app()->setLocale($selectedLocale);
        Carbon::setLocale($selectedLocale);

        $cookie = cookie((string) config('locales.cookie', 'locale'), $selectedLocale, 60 * 24 * 180, '/');

        if ($request->wantsJson()) {
            return response()
                ->json([
                    'message' => __('Language updated.'),
                    'locale' => $selectedLocale,
                ])
                ->withCookie($cookie);
        }

        return redirect()
            ->to($this->redirectTarget($request))
            ->with('success', __('Language updated.'))
            ->withCookie($cookie);
    }

    /**
     * Resolve a safe redirect target after switching language.
     */
    protected function redirectTarget(Request $request): string
    {
        $redirect = trim((string) $request->query('redirect', ''));

        if ($redirect === '') {
            return url()->previous() ?: url('/');
        }

        $redirectHost = parse_url($redirect, PHP_URL_HOST);
        $requestHost = $request->getHost();

        if ($redirectHost === null || strcasecmp((string) $redirectHost, $requestHost) === 0) {
            return $redirect;
        }

        return url('/');
    }
}
