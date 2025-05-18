<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display the authenticated user’s cart.
     */
    public function index()
    {
        // 1. Get or create the cart for this user
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        // 2. Pull all items (and eager-load the product)
        $items = $cart->items()->with('product')->get();

        // 3. Calculate subtotal
        $subtotal = $items->sum(fn($item) => 
            $item->product->price * $item->quantity
        );

        // 4. Render view
        return view('cart.index', compact('items', 'subtotal'));
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        // Guest: session-based
        if (Auth::guest()) {
            $cart = session()->get('cart', []);
            $pid  = $data['product_id'];
            $cart[$pid] = ($cart[$pid] ?? 0) + $data['quantity'];
            session(['cart' => $cart]);

            return redirect()->back()
                             ->with('info', 'Added to cart! Please log in to save your cart permanently.');
        }

        // Authenticated: database cart
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        CartItem::updateOrCreate(
            [
                'cart_id'    => $cart->id,
                'product_id' => $data['product_id'],
            ],
            [
                // increment existing quantity
                'quantity'   => DB::raw("quantity + {$data['quantity']}")
            ]
        );

        return redirect()->route('cart.index')
                         ->with('success', 'Product added to your cart!');
    }

    /**
     * Update an item’s quantity (authenticated only).
     */
    public function update(Request $request, $productId)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', Auth::id())->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $productId)
                        ->firstOrFail();

        $item->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart (authenticated only).
     */
    public function destroy($productId)
    {
        $cart = Cart::where('user_id', Auth::id())->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $productId)
                        ->firstOrFail();

        $item->delete();

        return back()->with('success', 'Item removed from cart.');
    }
}
