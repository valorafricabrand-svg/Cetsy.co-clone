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
            'sku'   => ['required','string','max:255','unique:product_variations,sku'],
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
        $product = $variation->product;

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
            'sku'   => [
                'required','string','max:255',
                Rule::unique('product_variations','sku')->ignore($variation->id),
            ],
            'price' => ['required','numeric','min:0'],
            'stock' => ['required','integer','min:0'],
        ]);

        $variation->update([
            'sku'   => $data['sku'],
            'price' => $data['price'],
            'stock' => $data['stock'],
        ]);
        $variation->values()->sync($data['values']);

        return back()->with('success','Variation updated.');
    }

    public function destroy(ProductVariation $variation): RedirectResponse
    {
        $productId = $variation->product_id;
        $variation->values()->detach();
        $variation->delete();

      return back()->with('success','Variation deleted.');
    }
}
