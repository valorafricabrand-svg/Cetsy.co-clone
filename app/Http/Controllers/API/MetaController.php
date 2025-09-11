<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Currency;

class MetaController extends Controller
{
    /** Return countries list (id, name) for mobile forms. */
    public function countries(Request $request)
    {
        return Country::orderBy('name')->get(['id','name']);
    }

    /** Return available currencies (DB-backed, with name/symbol/rate). */
    public function currencies(Request $request)
    {
        $currencies = Currency::where('is_active', true)
            ->orderBy('code')
            ->get(['code','name','symbol','usd_rate','decimal_places']);

        return $currencies->map(function ($c) {
            return [
                'code' => $c->code,
                'name' => $c->name,
                'symbol' => $c->symbol,
                'usd_rate' => (float) $c->usd_rate,
                'decimals' => (int) $c->decimal_places,
            ];
        });
    }
}
