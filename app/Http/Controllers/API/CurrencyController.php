<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return Currency::orderBy('code')->get();
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'code'           => ['required','string','size:3'],
            'name'           => ['nullable','string','max:64'],
            'symbol'         => ['nullable','string','max:8'],
            'decimal_places' => ['nullable','integer','min:0','max:6'],
            'usd_rate'       => ['required','numeric','min:0'],
            'is_active'      => ['nullable','boolean'],
        ]);

        $code = strtoupper($data['code']);
        $currency = Currency::firstOrNew(['code' => $code]);
        $currency->fill(array_merge($data, ['code' => $code]));
        $currency->save();
        return response()->json(['message' => 'Saved', 'currency' => $currency]);
    }
}

