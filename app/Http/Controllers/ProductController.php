<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ListingFeeType;

class ProductController extends Controller
{
  

    public function index(Request $request)
    {
        $query = Product::with('category','media')
            ->where('shop_id', Auth::user()->shop->id)
            ->latest();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

 public function create()
{
    // Categories as before
    $categories = \App\Models\Category::orderBy('name')->get();

    // Load all shipping profiles for “choose existing”
    $shippingProfiles = \App\Models\ShippingProfile::whereShopId(shop_id())->orderBy('name')->get();

    // (Optional) if you want to pre-select a default:
    $defaultProfileId = old('default_shipping_profile', null);
    $processingTimes  = ProcessingTime::orderBy('days')->get();
  $countries = Country::orderBy('name')->get();
    return view('products.create', compact(
        'categories',
        'shippingProfiles',
        'defaultProfileId',
        'countries',
        'processingTimes'
    ));
}

public function store(Request $request)
{
    $user = Auth::user();

    if (! $user->shop) {
        return redirect()->route('shops.create')
                         ->with('warning', 'You must create a shop before listing products.');
    }

    $data = $request->validate([
        'name'                        => 'required|string|max:255',
        'type'                        => 'required|in:physical,digital,service',
        'description'                 => 'nullable|string',
        'category_id'                 => 'nullable|exists:categories,id',
        'price'                       => 'required|numeric|min:0',
        'discount_price'              => 'nullable|numeric|min:0|lt:price',
        'stock'                       => 'nullable|integer|min:0',
        'media.*'                     => 'nullable|image|max:5120',
        'digital_file'                => 'nullable|file|max:10240',
        'shipping_profiles'           => 'required_if:type,physical|array|min:1',
        'shipping_profiles.*'         => 'exists:shipping_profiles,id',
        'default_shipping_profile'    => 'required_if:type,physical|exists:shipping_profiles,id',
        'country_id'                  => 'nullable|exists:countries,id',

        // new fields
        'origin_postal_code'          => 'nullable|string|max:20',
        'processing_time_id'          => 'nullable|exists:processing_times,id',

        // variations
        'variations'                  => 'nullable|array',
        'variations.*.type'           => 'required_with:variations|string|max:255',
        'variations.*.variation_option' => 'required_with:variations|string|max:255',
        'variations.*.price'          => 'required_with:variations|numeric|min:0',
        'variations.*.stock'          => 'required_with:variations|integer|min:0',
    ]);

    // ensure default shipping profile is one of the selected
    if ($data['type']==='physical'
        && ! in_array($data['default_shipping_profile'], $data['shipping_profiles'] ?? [])) {
        return back()
            ->withInput()
            ->withErrors(['default_shipping_profile' => 'Default must be one of the selected shipping profiles.']);
    }

    // 1. Create the product
    $product = new Product();
    $product->shop_id                     = $user->shop->id;
    $product->name                        = $data['name'];
    $product->slug                        = Str::slug($data['name']).'-'.uniqid();
    $product->type                        = $data['type'];
    $product->category_id                 = $data['category_id'] ?? null;
    $product->country_id                  = $data['country_id'] ?? null;
    $product->origin_postal_code          = $data['origin_postal_code'] ?? null;
    $product->processing_time_id          = $data['processing_time_id'] ?? null;
    $product->description                 = $data['description'] ?? null;
    $product->price                       = $data['price'];
    $product->discount_price              = $data['discount_price'] ?? null;
    $product->stock                       = $data['type']==='physical' ? ($data['stock'] ?? 0) : null;
    $product->default_shipping_profile_id = $data['type']==='physical'
                                            ? $data['default_shipping_profile']
                                            : null;
    $product->is_active                   = false;
    $product->save();

    // 2. Sync shipping profiles
    if ($data['type']==='physical') {
        $sync = [];
        foreach ($data['shipping_profiles'] as $pid) {
            $sync[$pid] = [
                'is_default' => $pid == $data['default_shipping_profile'],
            ];
        }
        $product->shippingProfiles()->sync($sync);
    }

    // 3. Upload images
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $path = $file->store('products','public');
            $product->media()->create(['url'=>$path]);
        }
    }

    // 4. Handle digital file
    if ($data['type']==='digital' && $request->hasFile('digital_file')) {
        $disk = 'local';
        $file = $request->file('digital_file');
        $path = $file->store('digital-files',$disk);
        $product->digitalFiles()->create([
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
        ]);
    }

    // 5. Create variations if any
    if (! empty($data['variations'])) {
        foreach ($data['variations'] as $v) {
            $product->variations()->create([
                'type'             => $v['type'],
                'variation_option' => $v['variation_option'],
                'price'            => $v['price'],
                'stock'            => $v['stock'],
            ]);
        }
    }

    return redirect()
        ->route('products.edit',$product)
        ->with('success','Product created successfully! You can now add more details or activate it.');
}





 public function edit(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => "nullable|string|max:255|unique:products,slug,{$product->id}",
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'status'      => 'required|in:draft,active,archived',
            'images.*'    => 'nullable|image|max:2048',
        ]);

        // 5. Build JS payload for existing variations
        $existingVariationsForJs = $product->variations
            ->map(fn($v) => [
                'key'      => now()->timestamp . random_int(1000, 9999),
                'id'       => $v->id,
                'valueIds' => $v->values->pluck('id')->all(),
                'sku'      => $v->sku,
                'price'    => (float) $v->price,
                'stock'    => (int)   $v->stock,
            ])
            ->all();

    $processingTimes  = ProcessingTime::orderBy('days')->get();
        // 6. Render the edit view
        return view('products.edit', [
            'product'                 => $product,
            'shippingProfiles'        => $shippingProfiles,
            'assignedProfiles'        => $assignedProfiles,
            'defaultProfileId'        => $defaultProfileId,
            'categories'              => $categories,
            'countries'               => $countries,
            'existingVariationsForJs' => $existingVariationsForJs,
            'processingTimes'         => $processingTimes,
        ]);
    }



public function update(Request $request, Product $product)
{
    $data = $request->validate([
        'name'                      => 'required|string|max:255',
        'type'                      => 'required|in:physical,digital,service',
        'description'               => 'nullable|string',
        'category_id'               => 'required|exists:categories,id',
        'price'                     => 'required|numeric|min:0',
        'discount_price'            => 'nullable|numeric|min:0|lt:price',
        'stock'                     => 'nullable|integer|min:0',
        'media.*'                   => 'nullable|image|max:5120',
        'digital_file'              => 'nullable|file|max:10240',
        'shipping_profiles'         => 'required_if:type,physical|array|min:1',
        'shipping_profiles.*'       => 'exists:shipping_profiles,id',
        'default_shipping_profile'  => 'required_if:type,physical|exists:shipping_profiles,id',
        'country_id'                => 'nullable|exists:countries,id',

        // new origin & processing fields
        'origin_postal_code'        => 'nullable|string|max:20',
        'processing_time_id'        => 'nullable|exists:processing_times,id',

        // manual variations
        'variations'                     => 'nullable|array',
        'variations.*.id'                => 'nullable|integer|exists:product_variations,id',
        'variations.*.type'              => 'required_with:variations|string|max:255',
        'variations.*.variation_option'  => 'required_with:variations|string|max:255',
        'variations.*.price'             => 'required_with:variations|numeric|min:0',
        'variations.*.stock'             => 'required_with:variations|integer|min:0',
    ]);

    // 1) Update core product
    $product->update([
        'name'                        => $data['name'],
        'slug'                        => Str::slug($data['name']).'-'.uniqid(),
        'type'                        => $data['type'],
        'category_id'                 => $data['category_id'],
        'country_id'                  => $data['country_id'] ?? null,
        'origin_postal_code'          => $data['origin_postal_code'] ?? null,
        'processing_time_id'          => $data['processing_time_id'] ?? null,
        'description'                 => $data['description'] ?? null,
        'price'                       => $data['price'],
        'discount_price'              => $data['discount_price'] ?? null,
        'stock'                       => $data['type'] === 'physical'
                                            ? ($data['stock'] ?? 0)
                                            : null,
    ]);

    // 2) Sync shipping profiles
    if ($data['type'] === 'physical') {
        $sync = [];
        foreach ($data['shipping_profiles'] as $pid) {
            $sync[$pid] = ['is_default' => $pid == $data['default_shipping_profile']];
        }
        $product->shippingProfiles()->sync($sync);
    } else {
        $product->shippingProfiles()->detach();
    }

    // 3) Handle media uploads
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $path = $file->store('products','public');
            $product->media()->create(['url'=>$path]);
        }

    // 4) Digital files logic
    if ($data['type'] === 'digital' && $request->hasFile('digital_file')) {
        Storage::disk('local')
            ->delete($product->digitalFiles->pluck('filepath')->all());
        $product->digitalFiles()->delete();

        $file = $request->file('digital_file');
        $path = $file->store('digital-files','local');
        $product->digitalFiles()->create([
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
        ]);
    } elseif ($data['type'] !== 'digital') {
        Storage::disk('local')
            ->delete($product->digitalFiles->pluck('filepath')->all());
        $product->digitalFiles()->delete();
    }

    // 5) Sync manual variations
    $submittedIds = collect($data['variations'] ?? [])
        ->pluck('id')
        ->filter()
        ->map(fn($i) => (int)$i)
        ->all();

    // delete removed
    $product->variations()
            ->whereNotIn('id', $submittedIds)
            ->delete();

    // upsert remaining
    foreach ($data['variations'] ?? [] as $v) {
        if (!empty($v['id']) && $variation = $product->variations()->find($v['id'])) {
            $variation->update([
                'type'             => $v['type'],
                'variation_option' => $v['variation_option'],
                'price'            => $v['price'],
                'stock'            => $v['stock'],
            ]);
        } else {
            $product->variations()->create([
                'type'             => $v['type'],
                'variation_option' => $v['variation_option'],
                'price'            => $v['price'],
                'stock'            => $v['stock'],
            ]);
        }
    }

    return redirect()
        ->route('products.edit', $product)
        ->with('success', 'Product updated successfully!');
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
        $isFavorited = auth()->check() && auth()->user()->favorites()->where('product_id', $product->id)->exists();
        return view('products.show', compact('product', 'isFavorited'));
        
    }


    public function listings()
{
    $products = Product::with('media')->latest()->paginate(16);
    return view('theme.listings', compact('products'));
}


    public function toggleFeatured(Product $product): RedirectResponse
    {
        $product->is_featured = ! $product->is_featured;
        $product->save();

        return back()->with(
            'success',
            $product->is_featured
                ? 'Product has been marked as featured.'
                : 'Product has been un-featured.'
        );
    }

}