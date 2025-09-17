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
            apply_discount($item->product->price, $item->product_id) * $item->quantity
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
    $cart = Cart::where('user_id', Auth::id())->firstOrFail();
    $items = $cart->items()->with('product')->get();
    $total = $items->sum(fn($item) =>
        apply_discount($item->product->price, $item->product_id) * $item->quantity
    );
    $shopId = $items->first()->product->shop_id ?? null;
    if (!$shopId) {
        return back()->withErrors('Unable to determine shop for this order.');
    }
    $order = Order::create([
        'user_id'          => Auth::id(),
        'shipping_address' => $data['shipping_address'],
        'total'            => $total,
        'shop_id'          => $shopId,
    ]);
    foreach ($items as $item) {
        $order->items()->create([
            'product_id' => $item->product_id,
            'quantity'   => $item->quantity,
            'price'      => apply_discount($item->product->price, $item->product_id),
        ]);
    }
    $cart->items()->delete();
    return redirect()
        ->route('checkout.success', $order)
        ->with('success', 'Your order has been placed!');
}
    /**
     * Show a simple order confirmation.
     */
    public function success(Order $order)
    {
        abort_if(!Auth::check() || $order->user_id !== Auth::id(), 404);
        return view('checkout.success', compact('order'));
    }
}
