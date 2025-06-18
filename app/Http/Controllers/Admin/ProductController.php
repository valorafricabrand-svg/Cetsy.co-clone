<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of all products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['shop', 'category', 'media'])
            ->latest();

        // Apply search if provided
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by shop if provided
        if ($shopId = $request->input('shop_id')) {
            $query->where('shop_id', $shopId);
        }

        // Filter by type if provided
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $products = $query->paginate(20)->withQueryString();
        $shops = Shop::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'shops'));
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['shop', 'category', 'media', 'digitalFiles']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $product->load(['shop', 'category', 'media']);
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Delete associated media files
        foreach ($product->media as $media) {
            \Storage::disk('public')->delete($media->url);
            $media->delete();
        }

        // Delete digital files if any
        foreach ($product->digitalFiles as $file) {
            \Storage::disk('local')->delete($file->filepath);
            $file->delete();
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';
        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product {$status} successfully.");
    }
} 