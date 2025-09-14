<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    /** Return current default currency code (ISO). */
    public function currency()
    {
        return response()->json(['currency' => setting('default_currency', 'USD') ?: 'USD']);
    }

    /** Update default currency code (requires auth). */
    public function updateCurrency(Request $request)
    {
        $request->validate(['currency' => ['required','string','size:3']]);
        $code = strtoupper($request->input('currency'));

        // Validate against known list
        $list = array_keys(currencies());
        if (! in_array($code, $list, true)) {
            return response()->json(['message' => 'Unsupported currency'], 422);
        }

        $row = Setting::first();
        if ($row) {
            $row->update(['default_currency' => $code]);
        } else {
            Setting::create(['default_currency' => $code]);
        }

        return response()->json(['message' => 'Currency updated', 'currency' => $code]);
    }
}

