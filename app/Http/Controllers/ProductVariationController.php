<?php
// app/Http/Controllers/ProductVariationController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VariationType;
use Illuminate\Http\Request;

class ProductVariationController extends Controller
{
    public function manage(Product $product, VariationType $type)
    {
        abort_unless((int) $type->product_id === (int) $product->getKey(), 404);

        $product->loadMissing('variationTypes.options');

        // eager-load to keep queries tight
        $type->load('options');

        $otherTypes = $product->variationTypes()
            ->where('id', '!=', $type->id)
            ->with('options')
            ->get();

        // all variants that include at least one option from this $type
        $variantsForType = $product->variations()
            ->whereHas('options', fn ($q) => $q->where('variation_type_id', $type->id))
            ->with(['options.variationType'])
            ->get();

        return view('products.variation_type', compact(
            'product', 'type', 'otherTypes', 'variantsForType'
        ));
    }
}
