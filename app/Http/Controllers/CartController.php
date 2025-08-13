<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VariationOption;
// If your variant model is named differently (e.g., ProductVariation), update the import:
use App\Models\Variation;
use App\Models\ShippingProfile;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

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

        return redirect()
            ->route('cart.checkout')
            ->with('success', 'Shipping selections saved – ready for checkout.');
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
     * Accepts either a pre-resolved variant_id OR a set of variation option IDs.
     */
    private function validateCartData(Request $request): array
    {
        return $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'variant_id'           => 'nullable|integer|exists:variations,id', // adjust table/model name if different
            'variations'           => 'nullable|array',
            'variations.*'         => 'integer|exists:variation_options,id',
            'quantity'             => 'nullable|integer|min:1',
            'shipping_profile_id'  => 'nullable|integer|exists:shipping_profiles,id',
        ]);
    }

    /**
     * Add (or merge) an item to the session cart, resolving correct variant pricing.
     */
    private function addItemToSessionCart(array $data): void
    {
        $product = Product::with(['shippingProfiles', 'media', 'variations.options'])
            ->findOrFail($data['product_id']);

        $qty           = max(1, (int) ($data['quantity'] ?? 1));
        $defaultShipId = $product->shippingProfiles->firstWhere('is_default', true)?->id;
        $shipProfile   = $data['shipping_profile_id'] ?? $defaultShipId;

        // Normalize option IDs (sorted)
        $optionIds = collect($data['variations'] ?? [])
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Load the selected options (preserve original UI order by $optionIds)
        $options = $optionIds->isEmpty()
            ? collect()
            : VariationOption::with('variationType')
                ->whereIn('id', $optionIds)->get()
                ->sortBy(fn($o) => $optionIds->search($o->id))
                ->values();

        // Text summary for display
        $summary = $options
            ->map(fn($o) => "{$o->variationType->name}: {$o->value}")
            ->join(', ');

        // Resolve the variant:
        // 1) If variant_id was posted, trust it (but ensure it belongs to this product).
        // 2) Else, try to find one by matching the exact set of option IDs.
        $variantId = $data['variant_id'] ?? null;
        $variant   = null;

        if ($variantId) {
            $variant = $product->variations->firstWhere('id', (int)$variantId);
            // Safety: if not found under this product, ignore it
            if (! $variant) {
                $variantId = null;
            }
        }

        if (! $variant && $optionIds->isNotEmpty()) {
            $variant = $this->findVariantByOptions($product, $optionIds->all());
            $variantId = $variant?->id;
        }

        // Compute price:
        // - if we have a priced variant -> use that
        // - else -> fall back to product discounted/base price
        $resolvedPrice = null;
        if ($variant && $variant->price !== null) {
            $resolvedPrice = (float) $variant->price;
        } else {
            $resolvedPrice = (float) ($product->discounted_price ?? $product->price);
        }

        // Build a stable row id:
        // Prefer variant-based identity if available; else fallback to product + options key.
        $optionsKey = $optionIds->implode('-'); // e.g. "12-33-41"
        $rowId = $variantId
            ? "p{$product->id}-v{$variantId}"
            : ("p{$product->id}". ($optionsKey !== '' ? "-o{$optionsKey}" : ''));

        $cart = session()->get('cart', []);

        if (isset($cart[$rowId])) {
            // Merge quantities if same exact row (same variant/options)
            $cart[$rowId]['quantity'] += $qty;
            $cart[$rowId]['selected_shipping_profile_id'] = $shipProfile;
        } else {
            $firstMedia = optional($product->media->first())->url;
            $photoUrl   = $firstMedia ? asset('storage/'.ltrim($firstMedia, '/')) : null;

            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'name'                         => $product->name,

                // Variant info (if any)
                'variant_id'                   => $variantId ?: null,

                // Selected options (for display)
                'variations'                   => $options->map(fn($o)=>[
                                                    'type'  => $o->variationType->name,
                                                    'value' => $o->value,
                                                    'id'    => $o->id,
                                                ])->all(),
                'variation_summary'            => $summary,

                // Quantity & price
                'quantity'                     => $qty,
                'price'                        => $resolvedPrice, // <-- Correct price, resolved above

                // Media
                'photo'                        => $photoUrl,

                // Shipping choices
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

    /**
     * Find a variant by exact option set (all IDs must match, order-agnostic).
     */
    private function findVariantByOptions(Product $product, array $optionIds): ?Variation
    {
        if (empty($optionIds)) {
            return null;
        }

        sort($optionIds);
        $needle = implode('-', $optionIds);

        foreach ($product->variations as $v) {
            if (! $v->relationLoaded('options')) {
                $v->load('options');
            }
            if (! $v->options || $v->options->isEmpty()) {
                continue;
            }
            $ids = $v->options->pluck('id')->sort()->values()->implode('-');
            if ($ids === $needle) {
                return $v;
            }
        }

        return null;
        // (Optional) If no exact match, you could implement a closest-match strategy here.
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
