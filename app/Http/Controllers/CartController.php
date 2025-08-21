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
    public function addToCart(Request $request): RedirectResponse
    {
        $data = $this->validateCartData($request);
        $this->addItemToSessionCart($data);

        $link    = route('cart.view');
        $message = 'Product added to cart successfully! '
                 . '<a href="'. $link .'" class="text-decoration-underline">View Cart</a>';

        return back()->with('success', $message);
    }

    public function addToBuy(Request $request): RedirectResponse
    {
        $data = $this->validateCartData($request);
        $this->addItemToSessionCart($data);

        return redirect()
            ->route('cart.view')
            ->with('success', 'Proceeding to checkout…');
    }

    public function viewCart(): View
    {
        $this->hydrateShippingProfiles();
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

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

        return back()->with('success', 'Shipping selections saved – ready for checkout.');
    }

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

    public function checkout(): View
    {
        $this->hydrateShippingProfiles();
        $cart = session()->get('cart', []);
        return view('checkout.index', compact('cart'));
    }

    /* -----------------------------------------------------------------
     | Internal
     | ----------------------------------------------------------------- */

    private function validateCartData(Request $request): array
    {
        return $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'variant_id'           => 'nullable|integer',
            'variations'           => 'nullable|array',
            'variations.*'         => 'integer|exists:variation_options,id',
            'quantity'             => 'nullable|integer|min:1',
            'shipping_profile_id'  => 'nullable|integer|exists:shipping_profiles,id',
        ]);
    }

    /**
     * Build a session-ready profile snapshot for a product.
     * Includes fields needed to recreate your label:
     *  - dest_location_type
     *  - dest_country_name (derived from relation)
     */
    private function profilesForProduct(int $productId): array
    {
        $profiles = ShippingProfile::with('destCountry')
            ->where('product_id', $productId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return $profiles->map(function ($p) {
            return [
                'id'                 => (int)$p->id,
                'name'               => $p->name,
                'base_rate'          => (float)$p->base_rate,
                'is_default'         => (bool)$p->is_default,
                'dest_location_type' => $p->dest_location_type,               // e.g. 'everywhere_else'
                'dest_country_name'  => optional($p->destCountry)->name,      // safe string or null
            ];
        })->values()->all();
    }

    private function addItemToSessionCart(array $data): void
    {
        $product = Product::with(['media', 'variations.options'])
            ->findOrFail($data['product_id']);

        $qty = max(1, (int) ($data['quantity'] ?? 1));

        $profiles   = $this->profilesForProduct($product->id); // ✅ strict per-product
        $defaultId  = collect($profiles)->firstWhere('is_default', true)['id']
                      ?? (collect($profiles)->first()['id'] ?? null);
        $shipProfile = $defaultId;
        if (!empty($data['shipping_profile_id'])) {
            $candidate = (int)$data['shipping_profile_id'];
            if (collect($profiles)->pluck('id')->contains($candidate)) {
                $shipProfile = $candidate;
            }
        }

        $optionIds = collect($data['variations'] ?? [])
            ->map(fn($v) => (int)$v)->filter()->unique()->sort()->values();

        $options = $optionIds->isEmpty()
            ? collect()
            : VariationOption::with('variationType')
                ->whereIn('id', $optionIds)->get()
                ->sortBy(fn($o) => $optionIds->search($o->id))
                ->values();

        $summary = $options->map(fn($o) => "{$o->variationType->name}: {$o->value}")->join(', ');

        $resolvedVariant = null;
        if (!empty($data['variant_id'])) {
            $resolvedVariant = optional($product->variations)->firstWhere('id', (int)$data['variant_id']);
        }
        if (!$resolvedVariant && $optionIds->isNotEmpty() && $product->relationLoaded('variations')) {
            foreach ($product->variations as $v) {
                $ids = optional($v->options)->pluck('id')->sort()->values();
                if ($ids && $ids->count() === $optionIds->count() && $ids->implode('-') === $optionIds->implode('-')) {
                    $resolvedVariant = $v; break;
                }
            }
        }

        $price = $resolvedVariant && isset($resolvedVariant->price) && $resolvedVariant->price !== null
            ? (float)$resolvedVariant->price
            : (float) ($product->discounted_price ?? $product->price);

        $optionsKey = $optionIds->implode('-');
        $rowId = ($resolvedVariant && isset($resolvedVariant->id))
            ? "p{$product->id}-v{$resolvedVariant->id}"
            : ("p{$product->id}" . ($optionsKey !== '' ? "-o{$optionsKey}" : ''));

        $cart = session()->get('cart', []);

        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] += $qty;
            if ($shipProfile) {
                $cart[$rowId]['selected_shipping_profile_id'] = $shipProfile;
            }
        } else {
            $firstMedia = optional($product->media->first())->url;
            $photoUrl   = $firstMedia ? asset('storage/'.ltrim($firstMedia, '/')) : null;

            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'name'                         => $product->name,
                'variant_id'                   => $resolvedVariant->id ?? null,
                'variations'                   => $options->map(fn($o)=>[
                                                    'type'  => $o->variationType->name,
                                                    'value' => $o->value,
                                                    'id'    => $o->id,
                                                ])->all(),
                'variation_summary'            => $summary,
                'quantity'                     => $qty,
                'price'                        => $price,
                'photo'                        => $photoUrl,
                // snapshot contains fields needed for label rendering
                'shipping_profiles'            => $profiles,
                'selected_shipping_profile_id' => $shipProfile,
            ];
        }

        session()->put('cart', $cart);
    }

    private function hydrateShippingProfiles(): void
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return;

        foreach ($cart as &$item) {
            $profiles = $this->profilesForProduct((int)$item['product_id']);
            $item['shipping_profiles'] = $profiles;

            $validIds = collect($profiles)->pluck('id');
            $selected = (int)($item['selected_shipping_profile_id'] ?? 0);

            if (!$validIds->contains($selected)) {
                $fallback = collect($profiles)->firstWhere('is_default', true)['id']
                    ?? (collect($profiles)->first()['id'] ?? null);
                $item['selected_shipping_profile_id'] = $fallback;
            }
        }
        unset($item);

        session()->put('cart', $cart);
    }

    private function decreaseOrRemove(array &$cart, string $rowId): void
    {
        if (! isset($cart[$rowId])) return;
        $cart[$rowId]['quantity']--;
        if ($cart[$rowId]['quantity'] < 1) unset($cart[$rowId]);
    }
}
