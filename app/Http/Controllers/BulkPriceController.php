<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BulkPriceLog;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BulkPriceController extends Controller
{
    /**
     * Show bulk pricing form (only this shop's products).
     */
    public function create(Request $request)
    {
        $shopId = shop_id(); // remove if you don't want shop scoping

        $products = Product::query()
            ->with(['media','shop'])
            ->where('shop_id', $shopId)
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('name')
            ->get();

        $history = BulkPriceLog::where('shop_id', $shopId)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('products.pricing.bulk', compact('products', 'shopId', 'history'));
    }

    /**
     * Apply percentage price change (no authorization).
     */
    public function store(Request $request)
    {
        // ---- Validate -------------------------------------------------------
        $data = $request->validate([
            'percent'       => ['required','numeric','min:0'],
            'direction'     => ['required','in:up,down'],
            'column'        => ['required','in:price,sale_price'],
            'round_to'      => ['nullable','integer','min:0','max:4'],
            'apply_to_all'  => ['required','boolean'],
            'product_ids'   => ['array','nullable'],
            'product_ids.*' => ['integer','exists:products,id'],
            'dry_run'       => ['sometimes','boolean'],
            // 'flat_amount' => ['nullable','numeric'] // enable if you want flat additions/subtractions
        ]);

        $shopId   = shop_id(); // drop this if you do NOT want scoping
        $column   = $data['column'];
        $percent  = (float) $data['percent'];
        $direction= $data['direction']; // 'up' or 'down'
        $roundTo  = (int) ($data['round_to'] ?? 2);
        $applyAll = (bool) $data['apply_to_all'];
        $ids      = $data['product_ids'] ?? [];
        $dryRun   = (bool) ($data['dry_run'] ?? false);

        // Percent factor
        $factor = $direction === 'down'
            ? (1 - ($percent / 100))
            : (1 + ($percent / 100));

        // Optional flat amount (if needed)
        // $flat  = isset($data['flat_amount']) ? (float) $data['flat_amount'] : 0;
        // $sign  = $direction === 'down' ? -1 : 1;
        // We'll just stick to percent update now.

        // ---- Build query ----------------------------------------------------
        $query = $this->baseShopQuery($shopId);

        if (! $applyAll && $ids) {
            // Re-scope ids to ensure they belong to this shop
            $ids = Product::where('shop_id', $shopId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            if (empty($ids)) {
                return back()->with('success', 'No matching products in this shop.')->withInput();
            }

            $query->whereIn('id', $ids);
        }

        // Determine affected product IDs up front (for logs + variants)
        $productIds = $query->pluck('id')->all();
        $productCount = count($productIds);

        // Dry run? ------------------------------------------------------------
        if ($dryRun) {
            return back()->with('success', "Dry run: {$productCount} product(s) would be updated. No changes made.")
                         ->withInput();
        }

        // ---- Update in one SQL ---------------------------------------------
        $variantCount = 0;
        DB::transaction(function () use ($query, $column, $factor, $roundTo, $productIds, &$variantCount) {
            $query->update([
                $column => DB::raw("ROUND($column * {$factor}, {$roundTo})"),
            ]);

            // Apply to variants when updating base price
            if ($column === 'price' && !empty($productIds)) {
                $variantQuery = Variant::whereIn('product_id', $productIds);
                $variantCount = (int) $variantQuery->count();
                if ($variantCount > 0) {
                    $variantQuery->update([
                        'price' => DB::raw("ROUND(price * {$factor}, {$roundTo})"),
                    ]);
                }
            }
        });

        // Log the change ------------------------------------------------------
        BulkPriceLog::create([
            'shop_id'           => $shopId,
            'user_id'           => Auth::id(),
            'direction'         => $direction,
            'percent'           => $percent,
            'column'            => $column,
            'round_to'          => $roundTo,
            'apply_all'         => $applyAll,
            'selection_count'   => $applyAll ? null : count($ids),
            'affected_products' => $productCount,
            'affected_variants' => $variantCount,
        ]);

        return back()->with('success', 'Prices updated successfully!');
    }

    /**
     * Base query for this shop.
     */
    private function baseShopQuery(int $shopId)
    {
        return Product::query()->where('shop_id', $shopId);
    }
}
