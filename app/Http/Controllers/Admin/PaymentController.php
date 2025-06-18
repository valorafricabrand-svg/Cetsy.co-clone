<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Payment::with('customer');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $payments = $query->orderByDesc('created_at')->paginate(15);
        return view('admin.payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = \App\Models\Payment::with('customer')->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }
} 