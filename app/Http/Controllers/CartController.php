<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VariationOption;
use App\Models\ShippingProfile;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Add a product (with selected options & optional profile) to the session cart.
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
     * “Buy Now”: add to cart & redirect to cart page.
     */
    public function addToBuy(Request $request): RedirectResponse
    {
        $data = $this->validateCartData($request);
        $this->addItemToSessionCart($data);

        return redirect()
            ->route('cart.view')
            ->with('success', 'Proceeding to checkout…');
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
     * Update quantity for a row (increase, decrease, set).
     */
    public function updateCart(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'row_id' => 'required|string',
            'action' => 'required|in:increase,decrease,set',
            'qty'    => 'nullable|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        abort_unless(isset($cart[$request->row_id]), 404, 'Item not in cart.');

        switch ($request->action) {
            case 'increase':
                $cart[$request->row_id]['quantity']++;
                break;
            case 'decrease':
                $this->decreaseOrRemove($cart, $request->row_id);
                break;
            case 'set':
                $cart[$request->row_id]['quantity'] = max(1, (int)$request->qty);
                break;
        }

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'cart' => $cart]);
        }

        return redirect()->route('cart.view')->with('success', 'Cart updated successfully!');
    }

    /**
     * Persist each item’s selected shipping-profile into session, then redirect or respond with JSON.
     */
    public function updateShippingSelection(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'shipping_profile_ids'   => 'required|array',
            'shipping_profile_ids.*' => 'integer|exists:shipping_profiles,id',
        ]);

        $cart = session()->get('cart', []);

        foreach ($cart as $rowId => &$item) {
            if (isset($data['shipping_profile_ids'][$rowId])) {
                $item['selected_shipping_profile_id'] = (int)$data['shipping_profile_ids'][$rowId];
            }
        }
        unset($item);

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shipping selections saved.',
                'cart'    => $cart,
            ]);
        }

        // 🔁 Works from both Cart and Checkout
        return back()->with('success', 'Shipping selections saved – ready for checkout.');
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
     * Show the checkout page, using the cart (with profiles already set).
     */
    public function checkout(): View
    {
        $cart = session()->get('cart', []);
        return view('checkout.index', compact('cart'));
    }

    /* -----------------------------------------------------------------
     | Internal helpers
     | ----------------------------------------------------------------- */

    /**
     * Validate posted cart data.
     * NOTE: `variant_id` is optional and NOT DB-validated (no variations table dependency).
     */
    private function validateCartData(Request $request): array
    {
        return $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'variant_id'           => 'nullable|integer',             // no `exists:` rule
            'variations'           => 'nullable|array',
            'variations.*'         => 'integer|exists:variation_options,id',
            'quantity'             => 'nullable|integer|min:1',
            'shipping_profile_id'  => 'nullable|integer|exists:shipping_profiles,id',
        ]);
    }

    /**
     * Add (or merge) an item to the session cart, resolving correct pricing without requiring a variations table.
     */
    private function addItemToSessionCart(array $data): void
    {
        // Load product with possible relationships used for pricing.
        $product = Product::with(['shippingProfiles', 'media', 'variations.options'])
            ->findOrFail($data['product_id']);

        $qty           = max(1, (int) ($data['quantity'] ?? 1));
        $defaultShipId = $product->shippingProfiles->firstWhere('is_default', true)?->id;
        $shipProfile   = $data['shipping_profile_id'] ?? $defaultShipId;

        // Normalize selected option IDs (sorted)
        $optionIds = collect($data['variations'] ?? [])
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Load selected option models (to show names/types in cart)
        $options = $optionIds->isEmpty()
            ? collect()
            : VariationOption::with('variationType')
                ->whereIn('id', $optionIds)->get()
                ->sortBy(fn($o) => $optionIds->search($o->id))
                ->values();

        $summary = $options
            ->map(fn($o) => "{$o->variationType->name}: {$o->value}")
            ->join(', ');

        // Try to resolve a variant-like combo from the product's variations relation (if present).
        $variantIdFromPost = $data['variant_id'] ?? null;
        $resolvedVariant   = null;

        if ($variantIdFromPost && $product->relationLoaded('variations')) {
            $resolvedVariant = $product->variations->firstWhere('id', (int)$variantIdFromPost);
            if (! $resolvedVariant) {
                $variantIdFromPost = null; // safety
            }
        }

        if (! $resolvedVariant && $optionIds->isNotEmpty() && $product->relationLoaded('variations')) {
            foreach ($product->variations as $v) {
                $ids = optional($v->options)->pluck('id')->sort()->values();
                if ($ids && $ids->count() === $optionIds->count() && $ids->implode('-') === $optionIds->implode('-')) {
                    $resolvedVariant = $v;
                    break;
                }
            }
        }

        $price = null;
        if ($resolvedVariant && isset($resolvedVariant->price) && $resolvedVariant->price !== null) {
            $price = (float) $resolvedVariant->price;
        } else {
            $price = (float) ($product->discounted_price ?? $product->price);
        }

        $optionsKey = $optionIds->implode('-');
        $rowId = ($resolvedVariant && isset($resolvedVariant->id))
            ? "p{$product->id}-v{$resolvedVariant->id}"
            : ("p{$product->id}" . ($optionsKey !== '' ? "-o{$optionsKey}" : ''));

        $cart = session()->get('cart', []);

        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] += $qty;
            $cart[$rowId]['selected_shipping_profile_id'] = $shipProfile;
        } else {
            $firstMedia = optional($product->media->first())->url;
            $photoUrl   = $firstMedia ? asset('storage/'.ltrim($firstMedia, '/')) : null;

            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'name'                         => $product->name,
                'variant_id'                   => ($resolvedVariant && isset($resolvedVariant->id)) ? (int)$resolvedVariant->id : null,
                'variations'                   => $options->map(fn($o)=>[
                                                    'type'  => $o->variationType->name,
                                                    'value' => $o->value,
                                                    'id'    => $o->id,
                                                ])->all(),
                'variation_summary'            => $summary,
                'quantity'                     => $qty,
                'price'                        => $price,
                'photo'                        => $photoUrl,
                'shipping_profiles'            => $product->shippingProfiles->map(fn($p)=>[
                                                    'id'         => $p->id,
                                                    'name'       => $p->name,
                                                    'base_rate'  => $p->base_rate,
                                                    'is_default' => $p->is_default,
                                                ])->all(),
                'selected_shipping_profile_id' => $shipProfile,
            ];
        }

        session()->put('cart', $cart);
    }

    private function decreaseOrRemove(array &$cart, string $rowId): void
    {
        if (! isset($cart[$rowId])) {
            return;
        }
        $cart[$rowId]['quantity']--;
        if ($cart[$rowId]['quantity'] < 1) {
            unset($cart[$rowId]);
        }
    }
}
