<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Payment;
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
    // Categories as before
    $categories = \App\Models\Category::orderBy('name')->get();

    // Load all shipping profiles for “choose existing”
    $shippingProfiles = \App\Models\ShippingProfile::whereShopId(shop_id())->orderBy('name')->get();

    // (Optional) if you want to pre-select a default:
    $defaultProfileId = old('default_shipping_profile', null);

    return view('products.create', compact(
        'categories',
        'shippingProfiles',
        'defaultProfileId'
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
        'name'                       => 'required|string|max:255',
        'type'                       => 'required|in:physical,digital,service',
        'description'                => 'nullable|string',
        'category_id'                => 'nullable|exists:categories,id',
        'price'                      => 'required|numeric|min:0',
        'discount_price'             => 'nullable|numeric|min:0|lt:price',
        'stock'                      => 'nullable|integer|min:0',
        'media.*'                    => 'nullable|image|max:5120',
        'digital_file'               => 'nullable|file|max:10240',
        'shipping_profiles'          => 'required_if:type,physical|array|min:1',
        'shipping_profiles.*'        => 'exists:shipping_profiles,id',
        'default_shipping_profile'   => 'required_if:type,physical|exists:shipping_profiles,id',
    ]);

    // Ensure default is among selected
    if ($data['type'] === 'physical'
        && ! in_array($data['default_shipping_profile'], $data['shipping_profiles'])) {
        return back()
            ->withInput()
            ->withErrors(['default_shipping_profile' => 'Default must be one of the selected shipping profiles.']);
    }

    // Create product
    $product = new Product();
    $product->shop_id                     = $user->shop->id;
    $product->name                        = $data['name'];
    $product->slug                        = Str::slug($data['name']) . '-' . uniqid();
    $product->type                        = $data['type'];
    $product->category_id                 = $data['category_id'] ?? null;
    $product->description                 = $data['description'] ?? null;
    $product->price                       = $data['price'];
    $product->discount_price              = $data['discount_price'] ?? null;
    $product->stock                       = $data['type'] === 'physical' ? ($data['stock'] ?? 0) : null;
    $product->default_shipping_profile_id = $data['type'] === 'physical'
                                            ? $data['default_shipping_profile']
                                            : null;
    $product->is_active                   = false;
    $product->save();

    // Sync shipping profiles if physical
    if ($data['type'] === 'physical') {
        $syncData = [];
        foreach ($data['shipping_profiles'] as $profileId) {
            $syncData[$profileId] = [
                'is_default' => $profileId == $data['default_shipping_profile'],
            ];
        }
        $product->shippingProfiles()->sync($syncData);
    }

    // Upload images
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $path = $file->store('products', 'public');
            $product->media()->create(['url' => $path]);
        }
    }

    // Handle digital file
    if ($data['type'] === 'digital' && $request->hasFile('digital_file')) {
        $disk = 'local';
        $file = $request->file('digital_file');

        // No old files to delete on create

        $path     = $file->store('digital-files', $disk);
        $filename = $file->getClientOriginalName();

        $product->digitalFiles()->create([
            'filename' => $filename,
            'filepath' => $path,
        ]);
    }

    return redirect()
        ->route('products.edit', $product)
        ->with('success', 'Product created successfully! You can now add more details or activate it.');
}

  public function edit(Product $product)
{
    $shop = auth()->user()->shop;

    $shippingProfiles = $shop->shippingProfiles()->get();

    // Get IDs of shipping profiles assigned to this product
    $assignedProfiles = $product->shippingProfiles()->pluck('shipping_profile_id')->toArray();

    // Get default profile ID if any
    $defaultProfileId = $product->shippingProfiles()->wherePivot('is_default', true)->pluck('shipping_profile_id')->first();

    $categories = \App\Models\Category::orderBy('name')->get();


    return view('products.edit', compact('product', 'shippingProfiles', 'assignedProfiles', 'defaultProfileId', 'categories'));
}


public function update(Request $request, Product $product)
{
    $user = Auth::user();

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:physical,digital,service',
        'description' => 'nullable|string',
     'category_id' => 'required|string|max:255',
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
     $product->category_id = $data['category_id'];
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

        return themed_view('listing_show', compact('product'));
    }

    public function wishlist()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())->get();
        return view('buyer.wishlist', compact('wishlistItems'));
    }

    public function listings()
    {
        $products = Product::with('media')->latest()->paginate(16);
        return themed_view('listings', compact('products'));
    }

    public function search(Request $request)
    {
        $q = $request->input('q');

        $products = Product::where('name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->paginate(12);

        return themed_view('listings', compact('products'))->with('q', $q);
    }


      public function payFee(Request $request, Product $product)
    {
         
        return view('products.pay_fee', ['order' => $product]);
    }


               public function successDeposit(Request $request, $id)
    {
        // Retrieve the order/invoice
        $product = Product::findOrFail($id);

          $product->update([
            'is_active'        => 1,
            'listing_paid_at'  => now(),     // add this column if desired
            'next_due_date'   => now()->addMonth(4), 
        ]);

        // Determine payment method: default to 'paypal'
        $method = $request->get('method', 'paypal');

        // Prepare a unique local transaction ID if not provided
        // (e.g., PayPal flow might not send one; MPESA flow might include its own)
        $localTxId = $request->get('transaction_id');
        if (!$localTxId) {
            do {
                $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
            } while (Payment::where('local_transaction_id', $localTxId)->exists());
        }

        // Determine currency sign dynamically
        // (assume order has a currency column; fallback to 'USD')
        $currency = $order->currency ?? 'USD';

        // Build the payment data array
        $paymentData = [
            
           
            'shop_id'              => $product->shop_id,
            'total_amount'         => $product->category?->listing_fee,
            'payment_method'       => $method,
            'status'               => '3',
            'currency'             => $currency,
            'local_transaction_id' => $localTxId,
            'payment_name' => 'listing_fee',
        ];


        // Create the payment record
        $payment = Payment::create($paymentData);

      
        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Your payment has been received.');
    }
}
