<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Add product to cart with shipping profiles info.
     */
    public function addToCart(Request $request)
    {
        $product = Product::with('shippingProfiles')->findOrFail($request->product_id);
        $quantity = max(1, (int) ($request->quantity ?? 1));
        $size_id = $request->size_id ?? 0;

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $defaultShippingProfile = $product->shippingProfiles->firstWhere('is_default', true);

            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'size_id' => $size_id,
                'photo' => $product->media->first()->url ?? null,
                'shipping_profiles' => $product->shippingProfiles->map(function ($profile) {
                    return [
                        'id' => $profile->id,
                        'name' => $profile->name,
                        'base_rate' => $profile->base_rate,
                        'is_default' => $profile->is_default,
                    ];
                })->toArray(),
                'selected_shipping_profile_id' => $defaultShippingProfile->id ?? null,
            ];
        }

        session()->put('cart', $cart);

        $link = route('cart.view');
        $message = 'Product added to cart successfully! <a href="' . $link . '" class="text-decoration-underline">View Cart</a>';

        return redirect()->back()->with('success', $message);
    }



        public function addToBuy(Request $request)
    {
        $product = Product::with('shippingProfiles')->findOrFail($request->product_id);
        $quantity = max(1, (int) ($request->quantity ?? 1));
        $size_id = $request->size_id ?? 0;

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $defaultShippingProfile = $product->shippingProfiles->firstWhere('is_default', true);

            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'size_id' => $size_id,
                'photo' => $product->media->first()->url ?? null,
                'shipping_profiles' => $product->shippingProfiles->map(function ($profile) {
                    return [
                        'id' => $profile->id,
                        'name' => $profile->name,
                        'base_rate' => $profile->base_rate,
                        'is_default' => $profile->is_default,
                    ];
                })->toArray(),
                'selected_shipping_profile_id' => $defaultShippingProfile->id ?? null,
            ];
        }

        session()->put('cart', $cart);

        $link = route('cart.view');
        $message = 'Product added to cart successfully! <a href="' . $link . '" class="text-decoration-underline">View Cart</a>';

        return redirect()->back()->with('success', $message);
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
     * Update product quantity in cart (increase or decrease).
     */
 public function updateCart(Request $request)
{
    $cart = session()->get('cart', []);

    if ($request->has('id') && isset($cart[$request->id]) && $request->has('action')) {
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
     * Update shipping profile selections for products in the cart.
     */
    public function updateShippingSelection(Request $request)
    {
        $cart = session()->get('cart', []);

        $request->validate([
            'product_ids' => 'required|array',
            'shipping_profile_ids' => 'required|array',
            'shipping_profile_ids.*' => 'required|integer',
        ]);

        foreach ($request->shipping_profile_ids as $index => $profileId) {
            $productId = $request->product_ids[$index] ?? null;

            if (!$productId || !isset($cart[$productId])) {
                continue;
            }

            $productItem = $cart[$productId];
            $shippingProfiles = collect($productItem['shipping_profiles'] ?? []);

            if (!$shippingProfiles->pluck('id')->contains($profileId)) {
                return redirect()->route('cart.view')
                    ->withErrors('Invalid shipping profile selected for ' . $productItem['name']);
            }

            $cart[$productId]['selected_shipping_profile_id'] = $profileId;
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.checkout')->with('success', 'Shipping selections updated, proceed to checkout.');
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
     * Display the checkout page with the current cart.
     */
    public function checkout()
    {
        $cart = session()->get('cart', []);
        return view('checkout.index', compact('cart'));
    }
}
