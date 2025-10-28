<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\VariationOption;
use App\Models\ShippingProfile;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function addToCart(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $data = $this->validateCartData($request);
            $this->addItemToSessionCart($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'redirect'=> route('cart.view'),
                ]);
            }

            $link    = route('cart.view');
            $message = 'Product added to cart successfully! '
                     . '<a href="'. $link .'" class="text-decoration-underline">View Cart</a>';
            return back()->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to add to cart. Please try again.',
                ], 422);
            }
            return back()->withErrors(['cart' => 'Unable to add to cart. Please try again.'])->withInput();
        }
    }
    public function addToBuy(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $data = $this->validateCartData($request);
            $this->addItemToSessionCart($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart.',
                    'redirect'=> route('cart.view'),
                ]);
            }

            // Change behavior: after adding, take user to Cart (not Checkout)
            return redirect()
                ->route('cart.view')
                ->with('success', 'Product added to cart.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to add to cart. Please try again.',
                ], 422);
            }
            return back()->withErrors(['cart' => 'Unable to add to cart. Please try again.'])->withInput();
        }
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
        $rowId = $request->row_id;
        $cartItem = &$cart[$rowId];
        $product = Product::with('variations')->find($cartItem['product_id'] ?? null);
        if (! $product || ! $this->productIsPurchasable($product)) {
            unset($cart[$rowId]);
            return $this->stockErrorResponse($request, 'This product is no longer available.', $cart);
        }
        $variant = null;
        if (! empty($cartItem['variant_id'])) {
            $variant = optional($product->variations)->firstWhere('id', (int) $cartItem['variant_id']);
            if (! $variant) {
                unset($cart[$rowId]);
                return $this->stockErrorResponse($request, 'This product variation is no longer available.', $cart);
            }
        }
        $available = $this->availableStockFor($product, $variant);
        switch ($request->action) {
            case 'increase':
                $newQty = (int) ($cartItem['quantity'] ?? 0) + 1;
                if ($available !== null && $newQty > $available) {
                    unset($cartItem);
                    return $this->stockErrorResponse($request, $this->stockLimitMessage($product, $available), $cart);
                }
                $cartItem['quantity'] = $newQty;
                break;
            case 'decrease':
                unset($cartItem);
                $this->decreaseOrRemove($cart, $rowId);
                break;
            case 'set':
                $targetQty = max(1, (int) $request->qty);
                if ($available !== null && $targetQty > $available) {
                    unset($cartItem);
                    return $this->stockErrorResponse($request, $this->stockLimitMessage($product, $available), $cart);
                }
                $cartItem['quantity'] = $targetQty;
                break;
        }
        unset($cartItem);
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
        $profiles = ShippingProfile::with(['destCountry','processingTime'])
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
                // Processing days for ship-by hints
                'proc_min'           => $p->processing_custom_min ?? optional($p->processingTime)->start_day,
                'proc_max'           => $p->processing_custom_max ?? optional($p->processingTime)->end_day,
            ];
        })->values()->all();
    }
    private function addItemToSessionCart(array $data): void
    {
        $product = Product::with(['media', 'variations.options'])
            ->findOrFail($data['product_id']);
        if (! $this->productIsPurchasable($product)) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is no longer available.',
            ]);
        }
        // Reservation rule: for single-quantity physical items reserved by a pending unpaid order, block purchase
        if ((($product->type ?? null) === 'physical') && (int)($product->stock ?? 0) === 1 && ($product->is_reserved ?? false)) {
            throw ValidationException::withMessages([
                'quantity' => 'This item is currently reserved by another pending order.',
            ]);
        }
        $qty = max(1, (int) ($data['quantity'] ?? 1));
        $profiles   = $this->profilesForProduct($product->id); // ?o. strict per-product
        $defaultId  = collect($profiles)->firstWhere('is_default', true)['id']
                      ?? (collect($profiles)->first()['id'] ?? null);
        $shipProfile = $defaultId;
        if (! empty($data['shipping_profile_id'])) {
            $candidate = (int) $data['shipping_profile_id'];
            if (collect($profiles)->pluck('id')->contains($candidate)) {
                $shipProfile = $candidate;
            }
        }
        $optionIds = collect($data['variations'] ?? [])
            ->map(fn ($v) => (int) $v)->filter()->unique()->sort()->values();
        $options = $optionIds->isEmpty()
            ? collect()
            : VariationOption::with('variationType')
                ->whereIn('id', $optionIds)->get()
                ->sortBy(fn ($o) => $optionIds->search($o->id))
                ->values();
        $summary = $options->map(fn ($o) => "{$o->variationType->name}: {$o->value}")->join(', ');
        $resolvedVariant = null;
        if (! empty($data['variant_id'])) {
            $resolvedVariant = optional($product->variations)->firstWhere('id', (int) $data['variant_id']);
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
        $price = $resolvedVariant && isset($resolvedVariant->price) && $resolvedVariant->price !== null
            ? apply_discount((float) $resolvedVariant->price, $product->id)
            : apply_discount((float) $product->price, $product->id);
        $optionsKey = $optionIds->implode('-');
        $rowId = ($resolvedVariant && isset($resolvedVariant->id))
            ? "p{$product->id}-v{$resolvedVariant->id}"
            : ("p{$product->id}" . ($optionsKey !== '' ? "-o{$optionsKey}" : ''));
        $cart = session()->get('cart', []);
        $existingQty = isset($cart[$rowId]) ? (int) ($cart[$rowId]['quantity'] ?? 0) : 0;
        $this->assertStockAvailable($product, $resolvedVariant, $existingQty + $qty);
        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] += $qty;
            if ($shipProfile) {
                $cart[$rowId]['selected_shipping_profile_id'] = $shipProfile;
            }
        } else {
            $firstMedia = optional($product->media->first())->url;
            $photoUrl   = $firstMedia ? asset('storage/' . ltrim($firstMedia, '/')) : null;
            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'name'                         => $product->name,
                'variant_id'                   => $resolvedVariant->id ?? null,
                'variations'                   => $options->map(fn ($o) => [
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
    private function productIsPurchasable(Product $product): bool
    {
        $isActive = $product->getAttribute('is_active');
        if ($isActive !== null && ! (bool) $isActive) {
            return false;
        }
        return true;
    }
    private function availableStockFor(Product $product, ?Variant $variant): ?int
    {
        if ($variant && $variant->stock !== null) {
            return max(0, (int) $variant->stock);
        }
        if ($product->stock !== null) {
            return max(0, (int) $product->stock);
        }
        return null;
    }
    private function assertStockAvailable(Product $product, ?Variant $variant, int $desiredQty): void
    {
        $available = $this->availableStockFor($product, $variant);
        if ($available !== null && $desiredQty > $available) {
            throw ValidationException::withMessages([
                'quantity' => $this->stockLimitMessage($product, $available),
            ]);
        }
    }
    private function stockLimitMessage(Product $product, ?int $available): string
    {
        if ($available === null) {
            return 'This product is no longer available.';
        }
        if ($available < 1) {
            return 'This product is out of stock.';
        }
        return 'Only ' . $available . ' unit(s) of ' . $product->name . ' are available.';
    }
    private function stockErrorResponse(Request $request, string $message, array $cart): RedirectResponse|JsonResponse
    {
        session()->put('cart', $cart);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'cart'    => $cart,
            ], 422);
        }
        return redirect()->route('cart.view')->withErrors(['quantity' => $message]);
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
