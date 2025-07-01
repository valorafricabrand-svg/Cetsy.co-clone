<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Add a product to the session cart.
     */
    public function addToCart(Request $request): RedirectResponse
    {



        $data = $this->validateCartData($request);

        $this->addItemToSessionCart($data);

        $link    = route('cart.view');
        $message = 'Product added to cart successfully! '
                 . '<a href="'. $link .'" class="text-decoration-underline">View Cart</a>';

        return redirect()->back()->with('success', $message);
    }

    /**
     * "Buy Now": add to cart then redirect straight to checkout.
     */
    public function addToBuy(Request $request): RedirectResponse
    {
        $data = $this->validateCartData($request);

        $this->addItemToSessionCart($data);

        return redirect()
               ->route('cart.view')
               ->with('success', 'Proceeding to checkout...');
    }

    /**
     * Display the cart page.
     */
    public function viewCart(): View
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    /**
     * Update product quantity in cart (increase/decrease).
     */
    public function updateCart(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id'     => 'required|integer',
            'action' => 'required|in:increase,decrease',
        ]);

        $cart = session()->get('cart', []);

        if (! isset($cart[$request->id])) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Item not in cart.'], 404)
                : redirect()->route('cart.view')->withErrors('Item not in cart.');
        }

        match ($request->action) {
            'increase' => $cart[$request->id]['quantity']++,
            'decrease' => $this->decreaseOrRemove($cart, $request->id),
        };

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'cart' => $cart]);
        }

        return redirect()->route('cart.view')->with('success', 'Cart updated successfully!');
    }

    /**
     * Update shipping profile selections for items in the cart.
     */
    public function updateShippingSelection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping_profile_ids' => 'required|array',
            'shipping_profile_ids.*' => 'integer|exists:shipping_profiles,id',
        ]);

        $cart = session()->get('cart', []);

        foreach ($cart as $productId => &$item) {
            if (isset($data['shipping_profile_ids'][$productId])) {
                $item['selected_shipping_profile_id'] = (int) $data['shipping_profile_ids'][$productId];
            }
        }
        unset($item);

        session()->put('cart', $cart);

        return redirect()
               ->route('cart.checkout')
               ->with('success', 'Shipping selections updated – proceed to checkout.');
    }

    /**
     * Remove product from cart.
     */
    public function removeFromCart(Request $request): RedirectResponse
    {
        $request->validate(['id' => 'required|integer']);

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
    public function checkout(): View
    {
        $cart = session()->get('cart', []);
        return view('checkout.index', compact('cart'));
    }

    /**
     * Validate the request data for adding/updating cart items.
     */
    private function validateCartData(Request $request): array
    {
        return $request->validate([
            'product_id'          => 'required|integer|exists:products,id',
            'quantity'            => 'nullable|integer|min:1',
            'size_id'             => 'nullable|integer',
            'shipping_profile_id' => 'nullable|integer|exists:shipping_profiles,id',
        ]);
    }

    /**
     * Centralized logic for adding or updating an item in the session cart.
     */
    private function addItemToSessionCart(array $data): void
    {
        $product = Product::with('shippingProfiles', 'media')
                          ->findOrFail($data['product_id']);

        $quantity = $data['quantity'] ?? 1;
        $sizeId   = $data['size_id'] ?? null;
        $defaultProfileId = $product->shippingProfiles
                                    ->firstWhere('is_default', true)?->id;
        $chosenProfile = $data['shipping_profile_id'] 
                         ?? $defaultProfileId;

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            // Already in cart: bump quantity and update profile
            $cart[$product->id]['quantity'] += $quantity;
            $cart[$product->id]['selected_shipping_profile_id'] = $chosenProfile;
        } else {
            // New item in cart
            $cart[$product->id] = [
                'id'                           => $product->id,
                'name'                         => $product->name,
                'quantity'                     => $quantity,
                'price'                        => $product->price,
                'size_id'                      => $sizeId,
                'photo'                        => $product->media->first()?->url,
                'shipping_profiles'            => $product->shippingProfiles
                                                     ->map(fn($p) => [
                                                         'id'         => $p->id,
                                                         'name'       => $p->name,
                                                         'base_rate'  => $p->base_rate,
                                                         'is_default' => $p->is_default,
                                                     ])->toArray(),
                'selected_shipping_profile_id' => $chosenProfile,
            ];
        }

        session()->put('cart', $cart);
    }

    /**
     * Decrease quantity or remove item if it drops below 1.
     */
    private function decreaseOrRemove(array &$cart, int $id): void
    {
        $cart[$id]['quantity']--;

        if ($cart[$id]['quantity'] < 1) {
            unset($cart[$id]);
        }
    }
}
