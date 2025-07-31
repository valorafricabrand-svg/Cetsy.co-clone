<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VariationOption;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;    
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Add a product (with selected options) to the session cart.
     */
    public function addToCart(Request $request): RedirectResponse
    {
        $data = $this->validateCartData($request);
        $this->addItemToSessionCart($data);

        $link    = route('cart.view');
        $message = 'Product added to cart successfully! '
                 . '<a href="'. $link .'" class="text-decoration-underline">View Cart</a>';

        // Create activity record for the seller
        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You added a product to your cart'
        ]);

        return back()->with('success', $message);
    }

    /**
     * "Buy Now": add to cart then redirect to the cart page.
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

        // Create activity record for the seller
        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You removed a product from your cart'
        ]);

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

    /**
     * Validate incoming add-to-cart payload.
     */
    private function validateCartData(Request $request): array
    {
        return $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'variations'           => 'nullable|array',
            'variations.*'         => 'integer|exists:variation_options,id',
            'quantity'             => 'nullable|integer|min:1',
            'shipping_profile_id'  => 'nullable|integer|exists:shipping_profiles,id',
        ]);
    }

    /**
     * Core logic to add (or increment) an item in the session cart,
     * storing the selected VariationOptions rather than a ProductVariation.
     */
    private function addItemToSessionCart(array $data): void
    {
        // Load product with media & shipping profiles
        $product = Product::with(['shippingProfiles','media'])->findOrFail($data['product_id']);

        // Determine quantity & chosen shipping profile
        $qty           = $data['quantity'] ?? 1;
        $defaultShipId = $product->shippingProfiles->firstWhere('is_default', true)?->id;
        $shipProfile   = $data['shipping_profile_id'] ?? $defaultShipId;

        // Fetch selected options, sorted
        $optionIds = collect($data['variations'] ?? [])->map(fn($v) => (int)$v)->sort()->values();
        $options   = $optionIds->isEmpty()
            ? collect()
            : VariationOption::with('variationType')
                              ->whereIn('id', $optionIds)
                              ->get()
                              ->sortBy(fn($o) => $optionIds->search($o->id));

        // Build human-readable summary & unique row ID
        $summary    = $options->map(fn($o) => "{$o->variationType->name}: {$o->value}")
                              ->join(', ');
        $rowId      = implode('-', array_merge([$product->id], $optionIds->all()));

        // Retrieve existing cart
        $cart = session()->get('cart', []);

        if (isset($cart[$rowId])) {
            // Already in cart -> increment quantity & update shipping
            $cart[$rowId]['quantity'] += $qty;
            $cart[$rowId]['selected_shipping_profile_id'] = $shipProfile;
        } else {
            // New entry
            $price = (float) ($product->discounted_price ?? $product->price);

            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'name'                         => $product->name,
                'variations'                   => $options->map(fn($o) => [
                                                    'type'  => $o->variationType->name,
                                                    'value' => $o->value,
                                                ])->all(),
                'variation_summary'            => $summary,
                'quantity'                     => $qty,
                'price'                        => $price,
                'photo'                        => optional($product->media->first())->url
                                                    ? asset('storage/'.optional($product->media->first())->url)
                                                    : null,
                'shipping_profiles'            => $product->shippingProfiles->map(fn($p) => [
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
     * Decrease a cart row's quantity or remove it if it falls below 1.
     */
    private function decreaseOrRemove(array &$cart, string $rowId): void
    {
        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity']--;
            if ($cart[$rowId]['quantity'] < 1) {
                unset($cart[$rowId]);
            }
        }
    }
}
