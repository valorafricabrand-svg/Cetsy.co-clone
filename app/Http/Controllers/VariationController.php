<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use App\Models\VariationType;
use App\Models\VariationOption;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class VariationController extends Controller
{
    /* ============================================================
     |  VARIANTS (product-level)
     |============================================================ */

    /**
     * Store a single variant using pre-selected option IDs.
     * Request payload:
     *  - values[] : array of variation_option IDs (required)
     *  - price    : decimal (required)
     *  - stock    : integer >= 0 (optional, blank => unlimited)
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $tracksStock = $product->type === 'physical';

        if (! $tracksStock || ($request->has('stock') && $request->filled('stock') === false)) {
            $request->merge(['stock' => null]);
        }

        $data = $request->validate([
            'values'   => ['required', 'array', 'min:1'],
            'values.*' => ['integer', 'exists:variation_options,id'],
            'price'    => ['required', 'numeric', 'min:0'],
            'stock'    => ['nullable', 'integer', 'min:0'],
        ]);

        $variant = $product->variations()->create([
            'price' => $data['price'],
            // If your table has these columns and they are NOT NULL, keep the safe defaults:
            'sku'   => null,
            'stock' => $tracksStock && array_key_exists('stock', $data) ? $data['stock'] : null,
        ]);

        $variant->options()->sync($data['values']);

        return back()->with('success', 'Variant added.');
    }

    /**
     * Bulk-create variants.
     * Supports either:
     *  - option_ids[] (exact option ids for each variant), OR
     *  - type + variation_option (created if missing, 1 option per row).
     * Only 'price' is accepted per-variant; SKU/stock are defaulted.
     */
    public function bulkStore(Request $request, Product $product): RedirectResponse
    {
        $tracksStock = $product->type === 'physical';

        if ($request->has('variations') && is_array($request->input('variations'))) {
            $normalized = collect($request->input('variations'))->map(function ($row) {
                if (array_key_exists('stock', $row) && ($row['stock'] === '' || $row['stock'] === null)) {
                    $row['stock'] = null;
                }
                return $row;
            })->all();
            $request->merge(['variations' => $normalized]);
        }

        $payload = $request->validate([
            'variations'                    => ['required', 'array', 'min:1'],

            // Path 1: free-text (we’ll create/find type & option)
            'variations.*.type'             => ['sometimes', 'required', 'string', 'max:255'],
            'variations.*.variation_option' => ['sometimes', 'required', 'string', 'max:255'],

            // Path 2: explicit option IDs
            'variations.*.option_ids'       => ['sometimes', 'array', 'min:1'],
            'variations.*.option_ids.*'     => ['integer', 'exists:variation_options,id'],

            // Common field
            'variations.*.price'            => ['required', 'numeric', 'min:0'],
            'variations.*.stock'            => ['nullable', 'integer', 'min:0'],
        ]);

        foreach ($payload['variations'] as $row) {
            // Determine option IDs set for this row
            if (isset($row['option_ids'])) {
                $optionIds = $row['option_ids'];
            } else {
                // Create/find a single option under the named type
                $type   = $product->variationTypes()->firstOrCreate(['name' => $row['type']]);
                $option = $type->options()->firstOrCreate(['value' => $row['variation_option']]);
                $optionIds = [$option->id];
            }

            // Create the variant with price only; default SKU/stock
            $variant = $product->variations()->create([
                'price' => $row['price'],
                'sku'   => null,
                'stock' => $tracksStock && array_key_exists('stock', $row) ? ($row['stock'] ?? null) : null,
            ]);

            $variant->options()->sync($optionIds);
        }

        return back()->with('success', 'Variations added successfully!');
    }

    /**
     * Update a variant (price + optional stock).
     * Request payload:
     *  - price : decimal (required)
     *  - stock : integer >= 0 | null (optional)
     */
    public function update(Request $request, Variant $variation): RedirectResponse
    {
        $variation->loadMissing('product');
        $tracksStock = optional($variation->product)->type === 'physical';

        if (! $tracksStock || ($request->has('stock') && $request->filled('stock') === false)) {
            $request->merge(['stock' => null]);
        }

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $payload = [
            'price' => $data['price'],
        ];

        if ($tracksStock && array_key_exists('stock', $data)) {
            $payload['stock'] = $data['stock'];
        } else {
            $payload['stock'] = null;
        }

        $variation->update($payload);

        return back()->with('success', 'Variant updated.');
    }

    /** Delete a variant (detach option links first). */
    public function destroy(Variant $variation): RedirectResponse
    {
        $variation->options()->detach();
        $variation->delete();

        return back()->with('success', 'Variant deleted.');
    }

    /* ============================================================
     |  VARIATION TYPES
     |============================================================ */

    /**
     * Create a variation type under a product (with optional comma-separated options).
     * Request payload:
     *  - name    : string (unique per product)
     *  - options : string (comma-separated), optional
     */
    public function storeType(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name'    => [
                'required', 'string', 'max:255',
                Rule::unique('variation_types', 'name')
                    ->where(fn ($q) => $q->where('product_id', $product->id)),
            ],
            'options' => ['nullable', 'string'],
            'affects_price' => ['nullable','boolean'],
        ]);

        $type = $product->variationTypes()->create([
            'name' => $data['name'],
            'affects_price' => (bool) ($data['affects_price'] ?? false),
        ]);

        if (!empty($data['options'])) {
            collect(explode(',', $data['options']))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->unique()
                ->each(fn ($v) => $type->options()->firstOrCreate(['value' => $v]));
        }

        return back()->with('success', 'Variation type created.');
    }

    /** Rename a variation type (unique name within the product). */
    public function updateType(Request $request, VariationType $variationType): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('variation_types', 'name')
                    ->where(fn ($q) => $q->where('product_id', $variationType->product_id))
                    ->ignore($variationType->id),
            ],
            'affects_price' => ['nullable','boolean'],
        ]);

        $variationType->update([
            'name' => $data['name'],
            'affects_price' => (bool) ($data['affects_price'] ?? false),
        ]);

        return back()->with('success', 'Variation type updated.');
    }

    /** Delete a variation type and its options (detach from variants first). */
    public function destroyType(VariationType $variationType): RedirectResponse
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($variationType) {
                $variationType->loadMissing('options.variants');

                $affectedVariantIds = $variationType->options
                    ->flatMap(function (VariationOption $option) {
                        return $option->variants->pluck('id');
                    })
                    ->unique()
                    ->values();

                if ($affectedVariantIds->isNotEmpty()) {
                    $variants = Variant::whereIn('id', $affectedVariantIds)->get();
                    foreach ($variants as $variant) {
                        $variant->options()->detach();
                    }

                    Variant::whereIn('id', $affectedVariantIds)->delete();
                }

                foreach ($variationType->options as $option) {
                    $option->delete();
                }

                $variationType->delete();
            });

            return back()->with('success', 'Variation type deleted.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete variation type', [
                'variation_type_id' => $variationType->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete the variation type. Please try again.');
        }
    }

    /* ============================================================
     |  VARIATION OPTIONS
     |============================================================ */

    /** Add a single option to a type. */
    public function storeOption(Request $request, VariationType $variationType): RedirectResponse
    {
        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                Rule::unique('variation_options', 'value')
                    ->where(fn ($q) => $q->where('variation_type_id', $variationType->id)),
            ],
        ]);

        $variationType->options()->create(['value' => $data['value']]);

        return back()->with('success', 'Option added.');
    }

    /** Update an option’s value. */
    public function updateOption(Request $request, VariationOption $option): RedirectResponse
    {
        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                Rule::unique('variation_options', 'value')
                    ->where(fn ($q) => $q->where('variation_type_id', $option->variation_type_id))
                    ->ignore($option->id),
            ],
        ]);

        $option->update(['value' => $data['value']]);

        return back()->with('success', 'Option updated.');
    }

    /** Delete an option (detach from all variants first). */
    public function destroyOption(VariationOption $option): RedirectResponse
    {
        $type = $option->variationType()->with('product')->first();

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($option) {
                $option->loadMissing('variants');

                $affectedVariantIds = $option->variants
                    ->pluck('id')
                    ->unique()
                    ->values();

                if ($affectedVariantIds->isNotEmpty()) {
                    $variants = Variant::whereIn('id', $affectedVariantIds)->get();
                    foreach ($variants as $variant) {
                        $variant->options()->detach();
                    }

                    Variant::whereIn('id', $affectedVariantIds)->delete();
                }

                $option->delete();
            });

            if ($type && $type->product) {
                return redirect()
                    ->route('products.variations.manage', [$type->product, $type])
                    ->with('success', 'Option deleted.');
            }

            return back()->with('success', 'Option deleted.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete variation option', [
                'option_id' => $option->id ?? null,
                'message'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete the option. Please try again.');
        }
    }

    /** Quickly toggle whether a variation type affects price. */
    public function toggleAffectsPrice(Request $request, VariationType $variationType): RedirectResponse
    {
        $variationType->update([
            'affects_price' => $request->boolean('affects_price'),
        ]);
        return back()->with('success', 'Variation type updated.');
    }
}
