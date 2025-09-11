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

        // Persist in session and cookie (180 days)
        session(['currency_code' => $code]);
        return back()->withCookie(cookie('currency_code', $code, 60 * 24 * 180))
                     ->with('status', 'Currency updated to ' . $code);
    }
}

