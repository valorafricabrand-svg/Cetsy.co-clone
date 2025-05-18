<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the current user's orders.
     */
    public function index()
    {
        // Only fetch this user's orders
        $orders = Order::where('user_id', Auth::id())
                       ->with('items.product')
                       ->latest()
                       ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Prevent viewing another user's order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This action is unauthorized.');
        }

        // Eager-load items and their products
        $order->load('items.product');

        return view('orders.show', compact('order'));
    }
}
