<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariant;

class ProductController extends Controller
{
  

    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure the user has a shop first
        if (! $user->shop) {
            return redirect()
                ->route('shops.create')
                ->with('error', 'You need to create a shop before adding products.');
        }

        $shopId = $user->shop->id;

        // Build the query
        $query = Product::with(['category', 'media'])
            ->where('shop_id', $shopId)
            ->latest();

        // Apply search if provided
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Paginate & preserve query string
        $products = $query->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            $rules = [
                'name'        => 'required|string|max:255',
                'slug'        => 'nullable|string|max:255|unique:products,slug',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'price'       => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
                'stock'       => 'required|integer|min:0',
                'low_stock'   => 'nullable|integer|min:0',
                'status'      => 'required|in:draft,active,archived',
                'product_type'=> 'required|in:physical,digital',
                'condition'   => 'required|in:new,refurbished,used',
                'images.*'    => 'nullable|image|max:2048',
                // Digital fields
                'download_file'   => 'nullable|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx',
                'download_limit'  => 'nullable|integer|min:1',
                'access_expiry'   => 'nullable|integer|min:1',
                // Variants
                'variants'        => 'nullable|array',
                'variants.*.size'     => 'nullable|string|max:50',
                'variants.*.color'    => 'nullable|string|max:50',
                'variants.*.material' => 'nullable|string|max:50',
                'variants.*.image'    => 'nullable|image|max:2048',
            ];

            // If digital, require digital fields
            if ($request->product_type === 'digital') {
                $rules['download_file'] = 'required|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx';
                $rules['download_limit'] = 'required|integer|min:1';
                $rules['access_expiry'] = 'required|integer|min:1';
            }

            $data = $request->validate($rules);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
                if (Product::where('slug', $data['slug'])->exists()) {
                    $data['slug'] .= '-' . time();
                }
            }

            $data['shop_id'] = Auth::user()->shop->id;

            // Handle digital file upload
            if ($request->hasFile('download_file')) {
                $data['download_file'] = $request->file('download_file')->store('products/digital', 'public');
            }

            $product = Product::create($data);

            // Handle images
            if ($files = $request->file('images')) {
                foreach ($files as $img) {
                    $path = $img->store('products/images','public');
                    $product->media()->create([
                        'type' => 'image',
                        'url'  => $path,
                    ]);
                }
            }

            // Handle variants (if you have a ProductVariant model/table)
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    // Generate SKU: e.g., PRODUCTID-SIZE-COLOR-MATERIAL or any format you prefer
                    $skuParts = [];
                    if (!empty($variant['size'])) $skuParts[] = strtoupper(substr($variant['size'], 0, 3));
                    if (!empty($variant['color'])) $skuParts[] = strtoupper(substr($variant['color'], 0, 3));
                    if (!empty($variant['material'])) $skuParts[] = strtoupper(substr($variant['material'], 0, 3));
                    $sku = 'P' . $product->id . '-' . implode('-', $skuParts);

                    $variantData = [
                        'product_id' => $product->id,
                        'size'       => $variant['size'] ?? null,
                        'color'      => $variant['color'] ?? null,
                        'material'   => $variant['material'] ?? null,
                        'sku'        => $sku,
                        'price'      => $variant['price'] ?? null,
                    ];
                    // Handle variant image
                    if (isset($variant['image']) && $variant['image']) {
                        $variantData['image'] = $variant['image']->store('products/variants', 'public');
                    }
                    ProductVariant::create($variantData);
                }
            }

            \DB::commit();
            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully!');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Product store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            return response()->view('errors.500', ['message' => 'An error occurred while creating the product. Please try again later.'], 500);
        }
    }

    public function edit(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $categories = Category::orderBy('name')->get();
        $variants = ProductVariant::where('product_id', $product->id)->get();
        return view('products.edit', compact('product','categories','variants'));
    }

    public function update(Request $request, Product $product)
    {
        \DB::beginTransaction();
        try {
            abort_if($product->shop_id !== Auth::user()->shop->id, 403);

            $rules = [
                'name'        => 'required|string|max:255',
                'slug'        => "nullable|string|max:255|unique:products,slug,{$product->id}",
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'price'       => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
                'stock'       => 'required|integer|min:0',
                'low_stock'   => 'nullable|integer|min:0',
                'status'      => 'required|in:draft,active,archived',
                'product_type'=> 'nullable|in:physical,digital',
                'condition'   => 'nullable|in:new,refurbished,used',
                'images.*'    => 'nullable|image|max:2048',
                // Digital fields
                'download_file'   => 'nullable|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx',
                'download_limit'  => 'nullable|integer|min:1',
                'access_expiry'   => 'nullable|integer|min:1',
                // Variants
                'variants'        => 'nullable|array',
                'variants.*.id'       => 'nullable|integer|exists:product_variants,id',
                'variants.*.size'     => 'nullable|string|max:50',
                'variants.*.color'    => 'nullable|string|max:50',
                'variants.*.material' => 'nullable|string|max:50',
                'variants.*.price'    => 'nullable|numeric|min:0',
                'variants.*.image'    => 'nullable|image|max:2048',
            ];

            $data = $request->validate($rules);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Handle digital file upload
            if ($request->hasFile('download_file')) {
                $data['download_file'] = $request->file('download_file')->store('products/digital', 'public');
            }

            $product->update($data);

            // Handle images
            if ($files = $request->file('images')) {
                foreach ($files as $img) {
                    $path = $img->store('products/images','public');
                    $product->media()->create([
                        'type' => 'image',
                        'url'  => $path,
                    ]);
                }
            }

            // Handle variants (update, create, delete)
            $existingVariantIds = $product->variants()->pluck('id')->toArray();
            $submittedVariantIds = [];
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    // If variant has ID, update; else, create new
                    if (!empty($variant['id'])) {
                        $variantModel = $product->variants()->find($variant['id']);
                        if ($variantModel) {
                            $variantData = [
                                'size'     => $variant['size'] ?? null,
                                'color'    => $variant['color'] ?? null,
                                'material' => $variant['material'] ?? null,
                                'price'    => $variant['price'] ?? null,
                            ];
                            // Handle variant image
                            if (isset($variant['image']) && $variant['image']) {
                                $variantData['image'] = $variant['image']->store('products/variants', 'public');
                            }
                            $variantModel->update($variantData);
                            $submittedVariantIds[] = $variantModel->id;
                        }
                    } else {
                        // Create new variant
                        $skuParts = [];
                        if (!empty($variant['size'])) $skuParts[] = strtoupper(substr($variant['size'], 0, 3));
                        if (!empty($variant['color'])) $skuParts[] = strtoupper(substr($variant['color'], 0, 3));
                        if (!empty($variant['material'])) $skuParts[] = strtoupper(substr($variant['material'], 0, 3));
                        $sku = 'P' . $product->id . '-' . implode('-', $skuParts);
                        $variantData = [
                            'product_id' => $product->id,
                            'size'       => $variant['size'] ?? null,
                            'color'      => $variant['color'] ?? null,
                            'material'   => $variant['material'] ?? null,
                            'sku'        => $sku,
                            'price'      => $variant['price'] ?? null,
                        ];
                        if (isset($variant['image']) && $variant['image']) {
                            $variantData['image'] = $variant['image']->store('products/variants', 'public');
                        }
                        $newVariant = $product->variants()->create($variantData);
                        $submittedVariantIds[] = $newVariant->id;
                    }
                }
            }
            // Delete variants that were removed
            $toDelete = array_diff($existingVariantIds, $submittedVariantIds);
            if (!empty($toDelete)) {
                $product->variants()->whereIn('id', $toDelete)->delete();
            }

            \DB::commit();
            return redirect()
                ->route('products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'product_id' => $product->id ?? null,
            ]);
            return response()->view('errors.500', ['message' => 'An error occurred while updating the product. Please try again later.'], 500);
        }
    }

    public function destroy(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        foreach ($product->media as $media) {
            Storage::disk('public')->delete($media->url);
            $media->delete();
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully!');
    }

    public function show(Product $product)
    {
        // Optional public view
        return view('products.show', compact('product'));
    }


        public function listing($slug)
    {

        $product = Product::whereSlug($slug)->first();
        return view('theme.listing_show', compact('product'));
        
    }


    public function listings()
{
    $products = Product::with('media')->latest()->paginate(16);
    return view('theme.listings', compact('products'));
}


  public function search(Request $request)
    {
        $q = $request->input('q');

        $products = Product::where('name', 'like', "%{$q}%")
                           ->orWhere('description', 'like', "%{$q}%")
                           ->paginate(12);

        return view('theme.listings', compact('products'))->with('q', $q);
        
    }

}
