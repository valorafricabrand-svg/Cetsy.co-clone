<?php

// app/Http/Controllers/VariationController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class VariationController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'values'   => ['required','array','min:1'],
            'values.*' => [
                'required','integer',
                Rule::exists('category_attribute_values','id')
                    ->whereIn('category_attribute_id', function($q) use($product) {
                        $q->select('id')
                          ->from('category_attributes')
                          ->where('category_id', $product->category_id);
                    }),
            ],
            // SKU no longer unique
            'sku'   => ['required','string','max:255'],
            'price' => ['required','numeric','min:0'],
            'stock' => ['required','integer','min:0'],
        ]);

        $variation = $product->variations()->create([
            'sku'   => $data['sku'],
            'price' => $data['price'],
            'stock' => $data['stock'],
        ]);
        $variation->values()->sync($data['values']);

        return back()->with('success','Variation added.');
    }

public function update(Request $request, ProductVariation $variation): RedirectResponse
{
    $data = $request->validate([
        'type'              => ['required','string','max:255'],
        'variation_option'  => ['required','string','max:255'],
        'price'             => ['required','numeric','min:0'],
        'stock'             => ['required','integer','min:0'],
    ]);

    $variation->update([
        'type'             => $data['type'],
        'variation_option' => $data['variation_option'],
        'price'            => $data['price'],
        'stock'            => $data['stock'],
    ]);

    return back()->with('success','Variation updated.');
}


    public function destroy(ProductVariation $variation): RedirectResponse
    {
        $variation->values()->detach();
        $variation->delete();

        return back()->with('success','Variation deleted.');
    }

    public function bulkStore(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'variations'                   => 'required|array|min:1',
            'variations.*.type'            => 'required|string|max:255',
            'variations.*.variation_option'=> 'required|string|max:255',
            // SKU no longer unique, but still distinct per submission,
            'variations.*.price'           => 'required|numeric|min:0',
            'variations.*.stock'           => 'required|integer|min:0',
        ]);

        foreach ($data['variations'] as $v) {
            $product->variations()->create([
                'type'             => $v['type'],
                'variation_option' => $v['variation_option'],
                'price'            => $v['price'],
                'stock'            => $v['stock'],
            ]);
        }

        return back()->with('success', 'Variations added successfully!');
    }
}
