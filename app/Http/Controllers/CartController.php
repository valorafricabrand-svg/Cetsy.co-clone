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
     * Display the authenticated user’s cart, or return JSON for Alpine.
     */
    public function index(Request $request)
    {
        // Build the data
        $count    = 0;
        $subtotal = 0;
        $items    = [];

        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $col  = $cart->items()->with('product.media')->get();

            $count    = $col->sum('quantity');
            $subtotal = $col->sum(fn($i) => $i->product->price * $i->quantity);
            $items    = $col->map(fn($i) => [
                'id'    => $i->product->id,
                'name'  => $i->product->name,
                'qty'   => $i->quantity,
                'price' => number_format($i->product->price, 2),
                'total' => number_format($i->product->price * $i->quantity, 2),
                'image' => $i->product->media->first()->url ?? null,
            ])->toArray();
        } else {
            $session = session('cart', []);
            $count   = array_sum($session);
        }

        // If JS/Alpine asked for JSON, give it JSON
        if ($request->expectsJson()) {
            return response()->json([
                'count'    => $count,
                'subtotal' => number_format($subtotal, 2),
                'items'    => $items,
            ]);
        }

        // Otherwise render view
        $viewItems = Auth::check()
            ? $cart->items()->with('product.media')->get()
            : collect();

        return view('cart.index', [
            'items'    => $viewItems,
            'subtotal' => $subtotal,
            'count'    => $count,
        ]);
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

        if (Auth::guest()) {
            $sess = session('cart', []);
            $pid  = $data['product_id'];
            $sess[$pid] = ($sess[$pid] ?? 0) + $data['quantity'];
            session(['cart' => $sess]);
        } else {
            $this->mergeSessionCartIntoDatabase();
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'product_id' => $data['product_id']],
                ['quantity' => DB::raw("quantity + {$data['quantity']}")]
            );
        }

        return $this->respondWithCart($request);
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, $productId)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);

        if (Auth::guest()) {
            $sess = session('cart', []);
            $sess[$productId] = $data['quantity'];
            session(['cart' => $sess]);
        } else {
            $cart = Cart::firstWhere('user_id', Auth::id());
            CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->update(['quantity' => $data['quantity']]);
        }

        return $this->respondWithCart($request);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, $productId)
    {
        if (Auth::guest()) {
            $sess = session('cart', []);
            unset($sess[$productId]);
            session(['cart' => $sess]);
        } else {
            $cart = Cart::firstWhere('user_id', Auth::id());
            CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->delete();
        }

        return $this->respondWithCart($request);
    }

    /**
     * Merge session cart into database on login.
     */
    protected function mergeSessionCartIntoDatabase()
    {
        $session = session('cart', []);
        if (empty($session) || ! Auth::check()) {
            return;
        }
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        foreach ($session as $pid => $qty) {
            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'product_id' => $pid],
                ['quantity' => DB::raw("quantity + $qty")]
            );
        }
        session()->forget('cart');
    }

    /**
     * Return JSON for JS or redirect with flash.
     */
    protected function respondWithCart(Request $request)
    {
        if ($request->expectsJson()) {
            // same logic as above
            return $this->index($request);
        }

        return redirect()->back()->with('success', 'Cart updated.');
    }
}
