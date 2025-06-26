<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Add product to cart with shipping profile selection.
     */
    public function addToCart(Request $request)
    {
        // validate incoming data
        $data = $request->validate([
            'product_id'          => 'required|exists:products,id',
            'quantity'            => 'nullable|integer|min:1',
            'size_id'             => 'nullable|integer',
            'shipping_profile_id' => 'nullable|exists:shipping_profiles,id',
        ]);

        $product       = Product::with('shippingProfiles', 'media')->findOrFail($data['product_id']);
        $quantity      = $data['quantity'] ?? 1;
        $sizeId        = $data['size_id'] ?? 0;
        $chosenProfile = $data['shipping_profile_id']
                           ?? $product->shippingProfiles->firstWhere('is_default', true)?->id
                           ?? null;

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            // if item exists, just bump quantity and update profile if changed
            $cart[$product->id]['quantity'] += $quantity;
            $cart[$product->id]['selected_shipping_profile_id'] = $chosenProfile;
        } else {
            // build new cart line
            $cart[$product->id] = [
                'id'                            => $product->id,
                'name'                          => $product->name,
                'quantity'                      => $quantity,
                'price'                         => $product->price,
                'size_id'                       => $sizeId,
                'photo'                         => $product->media->first()?->url,
                'shipping_profiles'             => $product->shippingProfiles->map(fn($p) => [
                                                       'id'         => $p->id,
                                                       'name'       => $p->name,
                                                       'base_rate'  => $p->base_rate,
                                                       'is_default' => $p->is_default,
                                                   ])->toArray(),
                'selected_shipping_profile_id'  => $chosenProfile,
            ];
        }

        session()->put('cart', $cart);

        $link    = route('cart.view');
        $message = 'Product added to cart successfully! <a href="'. $link .'" class="text-decoration-underline">View Cart</a>';

        return redirect()->back()->with('success', $message);
    }

    /**
     * "Buy Now": add to cart then redirect straight to checkout.
     */
    public function addToBuy(Request $request)
    {
        // same validation as addToCart
        $data = $request->validate([
            'product_id'          => 'required|exists:products,id',
            'quantity'            => 'nullable|integer|min:1',
            'size_id'             => 'nullable|integer',
            'shipping_profile_id' => 'nullable|exists:shipping_profiles,id',
        ]);

        $product       = Product::with('shippingProfiles', 'media')->findOrFail($data['product_id']);
        $quantity      = $data['quantity'] ?? 1;
        $sizeId        = $data['size_id'] ?? 0;
        $chosenProfile = $data['shipping_profile_id']
                           ?? $product->shippingProfiles->firstWhere('is_default', true)?->id
                           ?? null;

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
            $cart[$product->id]['selected_shipping_profile_id'] = $chosenProfile;
        } else {
            $cart[$product->id] = [
                'id'                            => $product->id,
                'name'                          => $product->name,
                'quantity'                      => $quantity,
                'price'                         => $product->price,
                'size_id'                       => $sizeId,
                'photo'                         => $product->media->first()?->url,
                'shipping_profiles'             => $product->shippingProfiles->map(fn($p) => [
                                                       'id'         => $p->id,
                                                       'name'       => $p->name,
                                                       'base_rate'  => $p->base_rate,
                                                       'is_default' => $p->is_default,
                                                   ])->toArray(),
                'selected_shipping_profile_id'  => $chosenProfile,
            ];
        }

        session()->put('cart', $cart);

        return redirect()
               ->route('checkout.show')  // or wherever your checkout route is
               ->with('success', 'Proceeding to checkout...');
    }

    /**
     * Display the cart page.
     */
    public function viewCart()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    /**
     * Update product quantity in cart (increase/decrease).
     */
    public function updateCart(Request $request)
    {
        $cart = session()->get('cart', []);

        if ($request->filled('id') && isset($cart[$request->id]) && $request->filled('action')) {
            if ($request->action === 'increase') {
                $cart[$request->id]['quantity']++;
            } elseif ($request->action === 'decrease') {
                $cart[$request->id]['quantity']--;
                if ($cart[$request->id]['quantity'] < 1) {
                    unset($cart[$request->id]);
                }
            }
        }

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'cart' => $cart]);
        }

        return redirect()->route('cart.view')->with('success', 'Cart updated successfully!');
    }

    /**
     * Update shipping profile selections for items in the cart.
     */
    public function updateShippingSelection(Request $request)
    {
        $cart = session('cart', []);

        $data = $request->validate([
            'product_ids'          => 'required|array',
            'shipping_profile_ids' => 'nullable|array',
            'return_to'            => 'nullable|string',
        ]);

        $chosen = $data['shipping_profile_ids'] ?? [];

        // iterate and assign
        foreach ($cart as $id => &$item) {
            // only update if user provided a profile for this product
            if (isset($chosen[$id]) && in_array((int)$chosen[$id], array_column($item['shipping_profiles'], 'id'))) {
                $item['selected_shipping_profile_id'] = (int)$chosen[$id];
            }
        }
        unset($item);

        session()->put('cart', $cart);

        return redirect()
               ->route($data['return_to'] ?? 'cart.checkout')
               ->with('success', 'Shipping selections updated – proceed to checkout.');
    }

    /**
     * Remove product from cart.
     */
    public function removeFromCart(Request $request)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$request->id])) {
            unset($cart[$request->id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.view')->with('success', 'Product removed from cart successfully!');
    }

    /**
     * Display the checkout page.
     */
    public function checkout()
    {
        $cart = session()->get('cart', []);
        return view('checkout.index', compact('cart'));
    }
}
