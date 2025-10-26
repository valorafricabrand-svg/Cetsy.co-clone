<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DealController extends Controller
{
    public function index()
    {
        // Only show this seller’s deals
        $deals = Deal::where('shop_id', shop_id())
                     ->latest()
                     ->paginate(20);

        return view('seller.deals.index', compact('deals'));
    }

    public function create()
    {
        // Only fetch products belonging to this shop with pagination
        // Show more per page to allow selecting far more than 20 at once
        $products = Product::where('shop_id', shop_id())
                           ->orderBy('name')
                           ->paginate(100);

        return view('seller.deals.create', compact('products'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $page = (int) $request->get('page', 1);
        // Allow larger page sizes so sellers can select more than 20
        $perPage = (int) $request->get('per_page', 100);
        $perPage = max(1, min($perPage, 500));
        $category = $request->get('category', '');
        $productType = $request->get('type', '');

        $products = Product::where('shop_id', shop_id())
                           ->when($query, function($q) use ($query) {
                               $q->where('name', 'like', '%' . $query . '%');
                           })
                           ->when($category, function($q) use ($category) {
                               $q->where('category_id', $category);
                           })
                           ->when($productType, function($q) use ($productType) {
                               $q->where('product_type', $productType);
                           })
                           ->orderBy('name')
                           ->paginate($perPage, ['*'], 'page', $page);

        // Normalize selected IDs (comma-separated string or array)
        $selectedParam = $request->input('selected', []);
        if (is_string($selectedParam)) {
            $selectedIds = array_values(array_filter(array_map('intval', explode(',', $selectedParam))));
        } elseif (is_array($selectedParam)) {
            $selectedIds = array_values(array_filter(array_map('intval', $selectedParam)));
        } else {
            $selectedIds = [];
        }

        return response()->json([
            'products' => $products->items(),
            'hasMore' => $products->hasMorePages(),
            'currentPage' => $products->currentPage(),
            'total' => $products->total(),
            'html' => view('seller.deals.partials.product-cards', [
                'products' => $products->items(),
                'selectedIds' => $selectedIds,
            ])->render()
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validate
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'discount_percent' => 'required|integer|between:1,100',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
            'product_ids'      => 'array',
            'product_ids.*'    => 'exists:products,id',
        ]);

        // Ensure applies_to_all is boolean
        $validated['applies_to_all'] = $request->boolean('applies_to_all');

        // Parse datetimes
        $validated['starts_at'] = Carbon::parse($validated['starts_at']);
        $validated['ends_at']   = Carbon::parse($validated['ends_at']);

        // Associate to current shop
        $validated['shop_id'] = shop_id();

        // 2. Create the deal
        $deal = Deal::create($validated);

        // 3. Determine affected products
        if ($validated['applies_to_all']) {
            // All this shop's products
            $affected = Product::where('shop_id', shop_id())->pluck('id')->toArray();
        } else {
            // Only selected ones, but ensure they actually belong to this shop
            $allowed = Product::where('shop_id', shop_id())->pluck('id')->toArray();
            $affected = array_intersect($request->input('product_ids', []), $allowed);
            // Sync the pivot so you can still use Deal->products()
            $deal->products()->sync($affected);
        }

        // 4. Mass‑update the products.discount_percent column
        if (count($affected)) {
            Product::whereIn('id', $affected)
                   ->update(['discount_percent' => $validated['discount_percent']]);
        }

        return redirect()
            ->route('seller.deals.index')
            ->with('success', 'Deal created and product discounts updated.');
    }

    public function edit(Deal $deal)
    {
        // Ensure the deal belongs to the current shop
        if ($deal->shop_id !== shop_id()) {
            abort(403, 'Unauthorized access to deal.');
        }

        // Only fetch products belonging to this shop with pagination
        $products = Product::where('shop_id', shop_id())
                           ->orderBy('name')
                           ->paginate(100);

        return view('seller.deals.edit', compact('deal', 'products'));
    }

    public function update(Request $request, Deal $deal)
    {
        // Ensure the deal belongs to the current shop
        if ($deal->shop_id !== shop_id()) {
            abort(403, 'Unauthorized access to deal.');
        }

        // 1. Validate
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'discount_percent' => 'required|integer|between:1,100',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
            'product_ids'      => 'array',
            'product_ids.*'    => 'exists:products,id',
        ]);

        // Additional validation for expired deals
        if ($deal->isExpired()) {
            return redirect()
                ->route('seller.deals.index')
                ->with('error', 'Cannot edit expired deals. Please create a new deal instead.');
        }

        // Ensure applies_to_all is boolean
        $validated['applies_to_all'] = $request->boolean('applies_to_all');

        // Parse datetimes
        $validated['starts_at'] = Carbon::parse($validated['starts_at']);
        $validated['ends_at']   = Carbon::parse($validated['ends_at']);

        // 2. Update the deal
        $deal->update($validated);

        // 3. Determine affected products
        if ($validated['applies_to_all']) {
            // All this shop's products
            $affected = Product::where('shop_id', shop_id())->pluck('id')->toArray();
            // Clear existing product associations
            $deal->products()->detach();
        } else {
            // Only selected ones, but ensure they actually belong to this shop
            $allowed = Product::where('shop_id', shop_id())->pluck('id')->toArray();
            $affected = array_intersect($request->input('product_ids', []), $allowed);
            // Sync the pivot
            $deal->products()->sync($affected);
        }

        // 4. Mass‑update the products.discount_percent column
        if (count($affected)) {
            Product::whereIn('id', $affected)
                   ->update(['discount_percent' => $validated['discount_percent']]);
        }

        return redirect()
            ->route('seller.deals.index')
            ->with('success', 'Deal updated successfully.');
    }

    public function stop(Deal $deal)
    {
        // Ensure the deal belongs to the current shop
        if ($deal->shop_id !== shop_id()) {
            abort(403, 'Unauthorized access to deal.');
        }

        // Check if deal is already stopped or expired
        if ($deal->isExpired()) {
            return redirect()
                ->route('seller.deals.index')
                ->with('error', 'This deal has already expired.');
        }

        // Stop the deal by setting end time to now
        $deal->update(['ends_at' => Carbon::now()]);

        // Remove discount from affected products
        if ($deal->applies_to_all) {
            $affected = Product::where('shop_id', shop_id())->pluck('id')->toArray();
        } else {
            $affected = $deal->products()->pluck('id')->toArray();
        }

        if (count($affected)) {
            Product::whereIn('id', $affected)
                   ->update(['discount_percent' => 0]);
        }

        return redirect()
            ->route('seller.deals.index')
            ->with('success', 'Deal stopped successfully.');
    }

    public function destroy(Deal $deal)
    {
        // Ensure the deal belongs to the current shop
        if ($deal->shop_id !== shop_id()) {
            abort(403, 'Unauthorized access to deal.');
        }

        // Remove discount from affected products before deleting
        if ($deal->applies_to_all) {
            $affected = Product::where('shop_id', shop_id())->pluck('id')->toArray();
        } else {
            $affected = $deal->products()->pluck('id')->toArray();
        }

        if (count($affected)) {
            Product::whereIn('id', $affected)
                   ->update(['discount_percent' => 0]);
        }

        $deal->delete();

        return redirect()
            ->route('seller.deals.index')
            ->with('success', 'Deal deleted successfully.');
    }
}
