<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $methods = PaymentMethod::with(['paymentType', 'shop.user'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('shop.user', function ($u) use ($q) {
                    $u->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
                })->orWhereHas('paymentType', function ($t) use ($q) {
                    $t->where('name', 'like', "%$q%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.payment-methods.index', compact('methods', 'q'));
    }
}

