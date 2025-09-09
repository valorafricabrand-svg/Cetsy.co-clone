<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     * Accepts: search|keyword, min_price, max_price
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Support both `search` and `keyword` query params
        $term = $request->get('search', $request->get('keyword'));
        if (!empty($term)) {
            $query->where('name', 'like', '%' . $term . '%');
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Store a newly created product in storage for the authenticated seller.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (method_exists($user, 'isSeller') && !$user->isSeller()) {
            return response()->json(['message' => 'Only sellers can create products.'], 403);
        }

        if (!$user->shop) {
            return response()->json(['message' => 'Seller must have a shop before creating products.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:price',
            'type'         => 'nullable|in:physical,digital,service',
            'stock'        => 'nullable|integer|min:0',
            'image'        => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['shop_id'] = $user->shop->id;
        $data['type'] = $data['type'] ?? 'physical';

        // Upload image if present; store only the filename so mobile URL builder works
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = basename($path);
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * List products for the authenticated seller.
     */
    public function myProducts(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if (!$user->shop) {
            return response()->json(['data' => []]);
        }

        $query = Product::where('shop_id', $user->shop->id)->latest();
        return $query->paginate(10);
    }

    /**
     * Show a product with relations needed by the mobile app.
     * Includes: shipping_profiles, variation_types.options, variants.options
     */
    public function show(Product $product)
    {
        $product->load([
            'shippingProfiles',
            'variationTypes.options',
            'variations.options',
        ]);

        $data = $product->toArray();

        // Normalize image: if stored filename, expose as filename and keep featured_image for legacy
        if (!empty($product->image)) {
            $data['image'] = $product->image; // filename; mobile builds URL
        }

        // Shape variations for mobile
        $data['variation_types'] = $product->variationTypes->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'options' => $type->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'value' => $opt->value,
                ])->values(),
            ];
        })->values();

        $data['variants'] = $product->variations->map(function ($variant) {
            return [
                'id' => $variant->id,
                'price' => (float) $variant->price,
                'stock' => (int) ($variant->stock ?? 0),
                'option_ids' => $variant->options->pluck('id')->values(),
                'label' => $variant->options->pluck('value')->implode(' / '),
            ];
        })->values();

        // Minimal shipping profiles for mobile
        $data['shipping_profiles'] = $product->shippingProfiles->map(function ($sp) {
            return [
                'id' => $sp->id,
                'name' => $sp->name,
                'base_rate' => (float) $sp->base_rate,
            ];
        })->values();

        return response()->json($data);
    }
}
