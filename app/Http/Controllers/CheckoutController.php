<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page (order summary + form).
     */
    public function index()
    {
        // 1. Get or create the current user's cart
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        // 2. Load items + product relation
        $items = $cart->items()->with('product')->get();

        // 3. Calculate subtotal
        $subtotal = $items->sum(fn($item) =>
            $item->product->price * $item->quantity
        );

        // 4. Render the checkout view
        return view('checkout.index', compact('items', 'subtotal'));
    }

    /**
     * Handle the form post, create the Order, and clear the cart.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_address' => 'required|string',
        ]);

        // 1. Fetch the cart
        $cart = Cart::where('user_id', Auth::id())->firstOrFail();
        $items = $cart->items()->with('product')->get();

        // 2. Calculate total
        $total = $items->sum(fn($item) =>
            $item->product->price * $item->quantity
        );

        // 3. Create the order record
        $order = Order::create([
            'user_id'          => Auth::id(),
            'shipping_address' => $data['shipping_address'],
            'total'            => $total,
        ]);

        // 4. Attach each cart item to the order
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->product->price,
            ]);
        }

        // 5. Clear the cart
        $cart->items()->delete();

        // 6. Redirect to a thank-you page
        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Your order has been placed!');
    }

    /**
     * Show a simple order confirmation.
     */
    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }
}
