<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencySelectionController extends Controller
{
    /**
     * Update the visitor's selected currency in session + cookie.
     */
    public function set(Request $request)
    {
        // Allow either 'code' (3 letters) or 'reset=1' to clear override
        $reset = (bool) $request->boolean('reset');
        if ($reset) {
            session()->forget('currency_code');
            if (auth()->check()) {
                $u = auth()->user();
                $u->preferred_currency = null;
                $u->save();
            }
            $default = setting('default_currency', 'USD') ?: 'USD';
            $resp = response()->json(['message' => 'Currency reset', 'currency' => strtoupper($default)]);
            // Clear cookie
            $resp->headers->clearCookie('currency_code', '/', null, false, false);
            if ($request->wantsJson()) {
                return $resp;
            }
            return back()->with('status', 'Currency reset to site default')->withCookie(cookie('currency_code', null, -1));
        }

        $data = $request->validate([
            'code' => ['required','string','size:3'],
        ]);

        $code = strtoupper($data['code']);

        // Validate against DB active currencies if available; fallback to helper list
        $valid = false;
        try {
            if (class_exists(Currency::class)) {
                $valid = Currency::where('code', $code)->where('is_active', true)->exists();
            }
        } catch (\Throwable $e) {}

        if (!$valid && function_exists('currencies')) {
            $valid = array_key_exists($code, currencies());
        }

        if (!$valid) {
            return back()->with('error', 'Unsupported currency selection.');
        }

        // Persist: user preference if logged in, else session
        if (auth()->check()) {
            $user = auth()->user();
            $user->preferred_currency = $code;
            $user->save();
        } else {
            session(['currency_code' => $code]);
        }

        $resp = response()->json(['message' => 'Currency updated', 'currency' => $code]);
        // Set cookie as a resilient fallback (180 days)
        $resp->headers->setCookie(cookie('currency_code', $code, 60 * 24 * 180, '/'));
        if ($request->wantsJson()) {
            return $resp;
        }

        return back()->with('status', 'Currency updated to ' . $code)->withCookie(cookie('currency_code', $code, 60 * 24 * 180, '/'));
    }
}
