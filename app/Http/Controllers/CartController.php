<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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

        return back()->with('success', $message);
    }

    /**
     * "Buy Now": add to cart then go to cart/checkout.
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
     * Update quantity of a cart row.
     */
    public function updateCart(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'row_id' => 'required|string',
            'action' => 'required|in:increase,decrease,set',
            'qty'    => 'nullable|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (! isset($cart[$request->row_id])) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Item not in cart.'], 404)
                : redirect()->route('cart.view')->withErrors('Item not in cart.');
        }

        switch ($request->action) {
            case 'increase':
                $cart[$request->row_id]['quantity']++;
                break;
            case 'decrease':
                $this->decreaseOrRemove($cart, $request->row_id);
                break;
            case 'set':
                $cart[$request->row_id]['quantity'] = max(1, (int) $request->qty);
                break;
        }

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'cart' => $cart]);
        }

        return redirect()->route('cart.view')->with('success', 'Cart updated successfully!');
    }

    /**
     * Update shipping profile selections for rows.
     */
    public function updateShippingSelection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping_profile_ids'   => 'required|array',
            'shipping_profile_ids.*' => 'integer|exists:shipping_profiles,id',
        ]);

        $cart = session()->get('cart', []);

        foreach ($cart as $rowId => &$item) {
            if (isset($data['shipping_profile_ids'][$rowId])) {
                $item['selected_shipping_profile_id'] = (int) $data['shipping_profile_ids'][$rowId];
            }
        }
        unset($item);

        session()->put('cart', $cart);

        return redirect()
            ->route('cart.checkout')
            ->with('success', 'Shipping selections updated – proceed to checkout.');
    }

    /**
     * Remove a row from the cart.
     */
    public function removeFromCart(Request $request): RedirectResponse
    {
        $request->validate(['row_id' => 'required|string']);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->row_id])) {
            unset($cart[$request->row_id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.view')->with('success', 'Product removed from cart successfully!');
    }

    /**
     * Checkout page.
     */
    public function checkout(): View
    {
        $cart = session()->get('cart', []);
        return view('checkout.index', compact('cart'));
    }

    /* -----------------------------------------------------------------
     | Internals
     | ----------------------------------------------------------------- */

    private function validateCartData(Request $request): array
    {
        return $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'product_variation_id' => 'nullable|integer|exists:product_variations,id',
            'quantity'             => 'nullable|integer|min:1',
            'shipping_profile_id'  => 'nullable|integer|exists:shipping_profiles,id',
        ]);
    }

    private function addItemToSessionCart(array $data): void
    {
        /** @var Product $product */
        $product = Product::with(['shippingProfiles', 'media'])->findOrFail($data['product_id']);

        /** @var ProductVariation|null $variation */
        $variation = null;
        if (!empty($data['product_variation_id'])) {
            $variation = ProductVariation::where('product_id', $product->id)
                                         ->findOrFail($data['product_variation_id']);
        }

        $qty              = $data['quantity'] ?? 1;
        $defaultProfileId = $product->shippingProfiles->firstWhere('is_default', true)?->id;
        $chosenProfile    = $data['shipping_profile_id'] ?? $defaultProfileId;

        // Unique row id (product-only or product-variation combo)
        $rowId = $product->id . ($variation ? '-'.$variation->id : '');

        $cart = session()->get('cart', []);

        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] += $qty;
            $cart[$rowId]['selected_shipping_profile_id'] = $chosenProfile;
        } else {
            $price = $this->resolvePrice($product, $variation); // use variation if available else product

            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'product_variation_id'         => $variation?->id,
                'name'                         => $product->name,
                'variation'                    => $variation?->name ?? $variation?->variation_option ?? null,
                'quantity'                     => $qty,
                'price'                        => $price,
                'photo'                        => $this->resolvePhoto($product, $variation),
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
     * Prefer variation price; else product discounted price, else product price.
     */
    private function resolvePrice(Product $product, ?ProductVariation $variation): float
    {
        if ($variation) {
            // common fields to check
            if (!is_null($variation->price)) {
                return (float) $variation->price;
            }
            if (!is_null($variation->price_override ?? null)) {
                return (float) $variation->price_override;
            }
            if (isset($variation->price_diff) && $variation->price_diff != 0) {
                return (float) ($product->discounted_price ?? $product->price) + (float) $variation->price_diff;
            }
        }

        return (float) ($product->discounted_price ?? $product->price);
    }

    /**
     * Choose best image: variation image > product first media > null.
     */
    private function resolvePhoto(Product $product, ?ProductVariation $variation): ?string
    {
        if ($variation && $variation->image) {
            return asset('storage/'.$variation->image);
        }

        $first = $product->media->first()?->url;
        return $first ? asset('storage/'.$first) : null;
    }

    private function decreaseOrRemove(array &$cart, string $rowId): void
    {
        $cart[$rowId]['quantity']--;

        if ($cart[$rowId]['quantity'] < 1) {
            unset($cart[$rowId]);
        }
    }
}
