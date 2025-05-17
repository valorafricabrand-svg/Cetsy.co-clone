<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    

    /**
     * Show the authenticated user’s cart.
     */
    public function index()
    {
        $items = CartItem::with('product')
                         ->where('user_id', Auth::id())
                         ->get();

        $subtotal = $items->sum(fn($item) => $item->product->price * $item->quantity);

        return view('cart.index', compact('items', 'subtotal'));
    }

    /**
     * Add a product to cart.
     * - Guests: stored in session.
     * - Authenticated: stored in database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        // Guest: session-based cart
        if (Auth::guest()) {
            $cart = session()->get('cart', []);
            $pid  = $data['product_id'];
            $cart[$pid] = ($cart[$pid] ?? 0) + $data['quantity'];
            session(['cart' => $cart]);

            return redirect()->back()
                             ->with('info', 'Added to cart! Please log in to save your cart permanently.');
        }

        // Authenticated: database cart
        CartItem::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $data['product_id']],
            ['quantity' => DB::raw("quantity + {$data['quantity']}")]
        );

        return redirect()->route('cart.index')
                         ->with('success', 'Product added to your cart!');
    }

    /**
     * Update an item’s quantity (authenticated users only).
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::where('user_id', Auth::id())
                        ->where('product_id', $id)
                        ->firstOrFail();

        $item->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart (authenticated users only).
     */
    public function destroy($id)
    {
        $item = CartItem::where('user_id', Auth::id())
                        ->where('product_id', $id)
                        ->firstOrFail();

        $item->delete();

        return back()->with('success', 'Item removed from cart.');
    }
}
