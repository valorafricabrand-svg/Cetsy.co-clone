<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VariationOption;
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

        // Log activity
        Activity::create([
            'user_id'     => Auth::id(),
            'is_read'     => false,
            'description' => 'You added a product to your cart'
        ]);

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
     * Persist each item’s selected shipping-profile into session,
     * then redirect straight to the checkout page.
     */
/**
 * Persist each item’s selected shipping-profile into session,
 * then redirect or respond with JSON.
 */
public function updateShippingSelection(Request $request): RedirectResponse|JsonResponse
{
    // 1) Validate we got an array of integers that exist in the DB
    $data = $request->validate([
        'shipping_profile_ids'   => 'required|array',
        'shipping_profile_ids.*' => 'integer|exists:shipping_profiles,id',
    ]);

    // 2) Pull the cart array out of the session
    $cart = session()->get('cart', []);

    // 3) Loop through each row and overwrite its selected_shipping_profile_id
    foreach ($cart as $rowId => &$item) {
        if (isset($data['shipping_profile_ids'][$rowId])) {
            $item['selected_shipping_profile_id'] = (int)$data['shipping_profile_ids'][$rowId];
        }
    }
    unset($item); // break the reference

    // 4) Write the entire modified cart back into the session
    session()->put('cart', $cart);

    // 5a) If this was an AJAX/JS call, return JSON
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Shipping selections saved.',
            'cart'    => $cart,
        ]);
    }

    // 5b) Otherwise redirect to checkout with a flash
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

        Activity::create([
            'user_id'     => Auth::id(),
            'is_read'     => false,
            'description' => 'You removed a product from your cart'
        ]);

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

    private function addItemToSessionCart(array $data): void
    {
        $product = Product::with(['shippingProfiles', 'media'])->findOrFail($data['product_id']);

        $qty           = $data['quantity'] ?? 1;
        $defaultShipId = $product->shippingProfiles->firstWhere('is_default', true)?->id;
        $shipProfile   = $data['shipping_profile_id'] ?? $defaultShipId;

        $optionIds = collect($data['variations'] ?? [])
                     ->map(fn($v) => (int)$v)->sort()->values();

        $options = $optionIds->isEmpty()
            ? collect()
            : VariationOption::with('variationType')
                ->whereIn('id', $optionIds)->get()
                ->sortBy(fn($o) => $optionIds->search($o->id));

        $summary = $options
            ->map(fn($o) => "{$o->variationType->name}: {$o->value}")
            ->join(', ');

        $rowId = implode('-', array_merge([$product->id], $optionIds->all()));

        $cart = session()->get('cart', []);

        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] += $qty;
            $cart[$rowId]['selected_shipping_profile_id'] = $shipProfile;
        } else {
            $price = (float) ($product->discounted_price ?? $product->price);

            $cart[$rowId] = [
                'row_id'                       => $rowId,
                'product_id'                   => $product->id,
                'name'                         => $product->name,
                'variations'                   => $options->map(fn($o)=>[
                                                    'type'  => $o->variationType->name,
                                                    'value' => $o->value,
                                                ])->all(),
                'variation_summary'            => $summary,
                'quantity'                     => $qty,
                'price'                        => $price,
                'photo'                        => optional($product->media->first())->url
                                                    ? asset('storage/'.optional($product->media->first())->url)
                                                    : null,
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
