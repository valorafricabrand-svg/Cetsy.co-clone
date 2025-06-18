<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ListingFeeType;
use App\Models\Country;
use App\Models\Wishlist;
use App\Models\ProcessingTime;
use App\Services\Shared\GetShippingService;
use App\Models\ShippingPeriod;

class ProductController extends Controller
{
    public function index()
    {
        $shopId = auth()->user()->shop->id;

        $products = Product::with('media')
            ->where('shop_id', $shopId)
            ->latest()
            ->paginate(12);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!$user->shop) {
            session()->put(['intend_url' => 'shop/create']);
            return redirect(route('shops.create'))->with('message', 'To add product first create shop');
        }

        $shop = $user->shop;
        $countryOriginName = $shop ? Country::where('id', $shop->country)->value('name') : '';
        $categories = Category::orderBy('name')->get();
        $category_listFee_types = ListingFeeType::orderBy('id', 'asc')->get();
        $countries = Country::orderBy('id', 'asc')->get();
        $processing_times = ProcessingTime::all();
        $shippingService = (new GetShippingService())->handle();
        $shippingPeriods = ShippingPeriod::all();

        $shippingChargeType = [
            ['id' => 0, 'name' => 'Free Shipping'],
            ['id' => 1, 'name' => 'Fixed Price'],
        ];

        $returnDeliveryDays = [
            ['id' => 7, 'name' => '7 days from delivery'],
            ['id' => 14, 'name' => '14 days from delivery'],
            ['id' => 21, 'name' => '21 days from delivery'],
            ['id' => 30, 'name' => '30 days from delivery'],
            ['id' => 45, 'name' => '45 days from delivery'],
            ['id' => 60, 'name' => '60 days from delivery'],
            ['id' => 90, 'name' => '90 days from delivery'],
        ];

        return view('products.create', compact(
            'categories',
            'category_listFee_types',
            'countries',
            'shippingChargeType',
            'returnDeliveryDays',
            'processing_times',
            'countryOriginName',
            'shippingService',
            'shippingPeriods'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->shop) {
            return redirect()->route('shops.create')
                ->with('warning', 'You must create a shop before listing products.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:physical,digital,service',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'nullable|integer|min:0',
            'media.*' => 'nullable|image|max:5120', // 5MB max per image
            'digital_file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $product = new Product();
        $product->shop_id = $user->shop->id;
        $product->name = $data['name'];
        $product->slug = Str::slug($data['name']) . '-' . uniqid();
        $product->type = $data['type'];
        $product->description = $data['description'] ?? null;
        $product->price = $data['price'];
        $product->discount_price = $data['discount_price'] ?? null;
        $product->stock = in_array($data['type'], ['physical']) ? ($data['stock'] ?? 0) : null;
        $product->is_active = true;
        $product->save();

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('products', 'public');
                $product->media()->create(['url' => $path]);
            }
        }

        if ($data['type'] === 'digital' && $request->hasFile('digital_file')) {
            $file = $request->file('digital_file');
            $disk = 'local'; // Adjust if you have 'private' disk configured

            $path = $file->store('digital-files', $disk);
            $filename = $file->getClientOriginalName();

            $product->digitalFiles()->create([
                'filename' => $filename,
                'filepath' => $path,
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

  public function edit(Product $product)
{
    $shop = auth()->user()->shop;

    $shippingProfiles = $shop->shippingProfiles()->get();

    // Get IDs of shipping profiles assigned to this product
    $assignedProfiles = $product->shippingProfiles()->pluck('shipping_profile_id')->toArray();

    // Get default profile ID if any
    $defaultProfileId = $product->shippingProfiles()->wherePivot('is_default', true)->pluck('shipping_profile_id')->first();

    return view('products.edit', compact('product', 'shippingProfiles', 'assignedProfiles', 'defaultProfileId'));
}


public function update(Request $request, Product $product)
{
    $user = Auth::user();

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:physical,digital,service',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'discount_price' => 'nullable|numeric|min:0|lt:price',
        'stock' => 'nullable|integer|min:0',
        'media.*' => 'nullable|image|max:5120', // 5MB max per image
        'digital_file' => 'nullable|file|max:10240', // 10MB max
        'shipping_profiles' => 'required_if:type,physical|array|min:1',
        'shipping_profiles.*' => 'exists:shipping_profiles,id',
        'default_shipping_profile' => 'required_if:type,physical|exists:shipping_profiles,id',
    ]);

    abort_if($product->shop_id !== $user->shop->id, 403);

    // Update product basic details
    $product->name = $data['name'];
    $product->slug = Str::slug($data['name']) . '-' . uniqid();
    $product->type = $data['type'];
    $product->description = $data['description'] ?? null;
    $product->price = $data['price'];
    $product->discount_price = $data['discount_price'] ?? null;
    $product->stock = in_array($data['type'], ['physical']) ? ($data['stock'] ?? 0) : null;
    $product->save();

    // Handle media uploads
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $path = $file->store('products', 'public');
            $product->media()->create(['url' => $path]);
        }
    }

    // Handle digital files upload and cleanup
    if ($data['type'] === 'digital' && $request->hasFile('digital_file')) {
        $file = $request->file('digital_file');
        $disk = 'local';

        foreach ($product->digitalFiles as $oldFile) {
            if (Storage::disk($disk)->exists($oldFile->filepath)) {
                Storage::disk($disk)->delete($oldFile->filepath);
            }
            $oldFile->delete();
        }

        $path = $file->store('digital-files', $disk);
        $filename = $file->getClientOriginalName();

        $product->digitalFiles()->create([
            'filename' => $filename,
            'filepath' => $path,
        ]);
    } elseif ($data['type'] !== 'digital') {
        $disk = 'local';
        foreach ($product->digitalFiles as $oldFile) {
            if (Storage::disk($disk)->exists($oldFile->filepath)) {
                Storage::disk($disk)->delete($oldFile->filepath);
            }
            $oldFile->delete();
        }
    }

    // Sync shipping profiles only if product is physical
    if ($data['type'] === 'physical') {
        $shippingProfiles = collect($data['shipping_profiles']);

        $syncData = $shippingProfiles->mapWithKeys(function ($profileId) use ($data) {
            return [
                $profileId => [
                    'is_default' => $profileId == $data['default_shipping_profile'],
                ],
            ];
        })->toArray();

        $product->shippingProfiles()->sync($syncData);
    } else {
        // Detach shipping profiles if product is not physical
        $product->shippingProfiles()->detach();
    }

    return redirect()->route('products.edit', $product)->with('success', 'Product updated successfully!');
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
        $product->load('media');
        return view('products.show', compact('product'));
    }

    public function listing($slug)
    {
        $product = Product::whereSlug($slug)->first();

        if(auth()->check()){
            $product->views()->create([
                'viewer_id' => auth()->id(),
                'ip'        => request()->ip(),
            ]);
        }

        return view('theme.listing_show', compact('product'));
    }

    public function wishlist()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())->get();
        return view('buyer.wishlist', compact('wishlistItems'));
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
