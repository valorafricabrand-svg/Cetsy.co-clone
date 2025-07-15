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
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;   

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
  $countries = Country::orderBy('name')->get();
    return view('products.create', compact(
        'categories',
        'shippingProfiles',
        'defaultProfileId',
        'countries'
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
            'name'                     => 'required|string|max:255',
            'type'                     => 'required|in:physical,digital,service',
            'description'              => 'nullable|string',
            'category_id'              => 'nullable|exists:categories,id',
            'price'                    => 'required|numeric|min:0',
            'discount_price'           => 'nullable|numeric|min:0|lt:price',
            'stock'                    => 'nullable|integer|min:0',
            'media.*'                  => 'nullable|image|max:5120',
            'digital_file'             => 'nullable|file|max:10240',
            'shipping_profiles'        => 'required_if:type,physical|array|min:1',
            'shipping_profiles.*'      => 'exists:shipping_profiles,id',
            'default_shipping_profile' => 'required_if:type,physical|exists:shipping_profiles,id',
            'country_id'               => 'nullable|exists:countries,id',

            // ←— NEW: handle variations block
            'variations'                  => 'nullable|array',
            'variations.*.sku'            => ['required_with:variations','string','max:255','distinct'],
            'variations.*.price'          => 'required_with:variations|numeric|min:0',
            'variations.*.stock'          => 'required_with:variations|integer|min:0',
            'variations.*.values'         => 'required_with:variations|array|min:1',
            'variations.*.values.*'       => [
                'required','integer',
                // ensure each value ID belongs to a CategoryAttribute for this category
                Rule::exists('category_attribute_values','id')->whereIn('category_attribute_id', function($q) use($request) {
                    $q->select('id')
                      ->from('category_attributes')
                      ->where('category_id', $request->input('category_id'));
                }),
            ],
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

        // ←— 5. Create variations if any were submitted
        if (! empty($data['variations'])) {
            foreach ($data['variations'] as $v) {
                $variation = $product->variations()->create([
                    'sku'   => $v['sku'],
                    'price' => $v['price'],
                    'stock' => $v['stock'],
                ]);
                $variation->values()->sync($v['values']);
            }
        }

        return redirect()
            ->route('products.edit',$product)
            ->with('success','Product created successfully! You can now add more details or activate it.');
    }


 public function edit(Product $product)
    {
        // 1. Get the current user’s shop and its shipping profiles
        $shop = Auth::user()->shop;
        $shippingProfiles = $shop->shippingProfiles;

        // 2. Determine which profiles are assigned and which is default
        $assignedProfiles = $product
            ->shippingProfiles()
            ->pluck('shipping_profile_id')
            ->toArray();

        $defaultProfileId = $product
            ->shippingProfiles()
            ->wherePivot('is_default', true)
            ->pluck('shipping_profile_id')
            ->first();

        // 3. Dropdown data for categories and countries
        $categories = Category::orderBy('name')->get();
        $countries  = Country::orderBy('name')->get();

        // 4. Eager-load variations → values, and category → attributes → values
        $product->load([
            // for each variation, load its CategoryAttributeValue rows
            'variations.values:id,category_attribute_id,value',
            // for the category’s attribute picker, load each attribute’s values
            'category.attributes.values:id,category_attribute_id,value',
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

        // 6. Render the edit view
        return view('products.edit', [
            'product'                 => $product,
            'shippingProfiles'        => $shippingProfiles,
            'assignedProfiles'        => $assignedProfiles,
            'defaultProfileId'        => $defaultProfileId,
            'categories'              => $categories,
            'countries'               => $countries,
            'existingVariationsForJs' => $existingVariationsForJs,
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

        // Relaxed variation rules
        'variations'                => 'nullable|array',
        'variations.*.id'           => 'nullable|integer',
        'variations.*.sku'          => 'required|string|max:255',
        'variations.*.price'        => 'required|numeric|min:0',
        'variations.*.stock'        => 'required|integer|min:0',
        'variations.*.values'       => 'required|array|min:1',
        'variations.*.values.*'     => 'required|integer',
    ]);

    // Update basic product fields
    $product->name             = $data['name'];
    $product->slug             = Str::slug($data['name']) . '-' . uniqid();
    $product->type             = $data['type'];
    $product->category_id      = $data['category_id'];
    $product->country_id       = $data['country_id'] ?? null;
    $product->description      = $data['description'] ?? null;
    $product->price            = $data['price'];
    $product->discount_price   = $data['discount_price'] ?? null;
    $product->stock            = $data['type'] === 'physical'
                                  ? ($data['stock'] ?? 0)
                                  : null;
    $product->save();

    // Media uploads
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $path = $file->store('products', 'public');
            $product->media()->create(['url' => $path]);
        }
    }

    // Digital files
    $disk = 'local';
    if ($data['type'] === 'digital' && $request->hasFile('digital_file')) {
        foreach ($product->digitalFiles as $oldFile) {
            Storage::disk($disk)->delete($oldFile->filepath);
            $oldFile->delete();
        }
        $file = $request->file('digital_file');
        $path = $file->store('digital-files', $disk);
        $product->digitalFiles()->create([
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
        ]);
    } elseif ($data['type'] !== 'digital') {
        foreach ($product->digitalFiles as $oldFile) {
            Storage::disk($disk)->delete($oldFile->filepath);
            $oldFile->delete();
        }
    }

    // Shipping profiles
    if ($data['type'] === 'physical') {
        $syncData = collect($data['shipping_profiles'])
            ->mapWithKeys(fn($pid) => [
                $pid => ['is_default' => $pid == $data['default_shipping_profile']],
            ])->toArray();
        $product->shippingProfiles()->sync($syncData);
    } else {
        $product->shippingProfiles()->detach();
    }

    // Sync Variations
    $submittedIds = collect($data['variations'] ?? [])
        ->pluck('id')
        ->filter()
        ->map(fn($id) => (int)$id)
        ->all();

    // Remove deleted variations
    $product->variations()
            ->whereNotIn('id', $submittedIds)
            ->each(fn($v) => $v->delete());

    // Upsert variations
    foreach ($data['variations'] ?? [] as $varData) {
        if (!empty($varData['id'])) {
            $variation = ProductVariation::find($varData['id']);
            $variation->update([
                'sku'   => $varData['sku'],
                'price' => $varData['price'],
                'stock' => $varData['stock'],
            ]);
        } else {
            $variation = $product->variations()->create([
                'sku'   => $varData['sku'],
                'price' => $varData['price'],
                'stock' => $varData['stock'],
            ]);
        }
        // Sync option-value links
        $variation->values()->sync($varData['values']);
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
        $product->load('media');
        return view('products.show', compact('product'));
    }

public function listing(string $slug)
{
    /* ------------------------------------------------------------
     | 1.  Fetch the product with everything the view needs
     |------------------------------------------------------------ */
    $product = Product::with([
            'media',
            'category:id,name,slug',
            'country:id,name',
            // ➜ keep only real columns that exist in shipping_profiles
            'shippingProfiles:id,name,base_rate',
            'shop:id,name,user_id,slug',
            'shop.policies:shop_id,shipping,returns',
        ])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->whereSlug($slug)
        ->firstOrFail();

    /* ------------------------------------------------------------
     | 2.  Record a view (logged-in users and guests)
     |------------------------------------------------------------ */
    $product->views()->create([
        'viewer_id' => Auth::id(),   // null for guests
        'ip'        => request()->ip(),
    ]);

    /* ------------------------------------------------------------
     | 3.  Per-viewer data
     |------------------------------------------------------------ */
    $isFavorited = Auth::check()
        && Auth::user()
               ->favorites()
               ->where('product_id', $product->id)
               ->exists();

    /* ------------------------------------------------------------
     | 4.  Extra data blocks for the Etsy-style page
     |------------------------------------------------------------ */
    $reviews = $product->reviews()
                       ->with('user:id,name')
                       ->latest()
                       ->take(20)
                       ->get();

    $faqs = $product->faqs()->latest()->get();

    $shopPolicies = $product->shop->policies
        ?? (object) ['shipping' => null, 'returns' => null];

    $moreFromShop = $product->shop->products()
        ->where('id', '!=', $product->id)
        ->latest()
        ->take(8)
        ->get();

    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->latest()
        ->take(8)
        ->get();

    /* ------------------------------------------------------------
     | 5.  Default shipping profile ID for the view (pivot field)
     |------------------------------------------------------------ */
    $defaultProfileId = optional(
        $product->shippingProfiles->firstWhere('pivot.is_default', true)
            ?? $product->shippingProfiles->first()      // fallback
    )->id;

    /* ------------------------------------------------------------
     | 6.  Render
     |------------------------------------------------------------ */
    return themed_view('listing_show', [
        'product'          => $product,
        'isFavorited'      => $isFavorited,
        'reviews'          => $reviews,
        'faqs'             => $faqs,
        'shopPolicies'     => $shopPolicies,
        'moreFromShop'     => $moreFromShop,
        'relatedProducts'  => $relatedProducts,
        'defaultProfileId' => $defaultProfileId,
    ]);
}

    public function wishlist()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())->get();
        return view('buyer.wishlist', compact('wishlistItems'));
    }

    public function favorites()
    {
        $favorites = auth()->user()->favorites()->get();
        return view('buyer.favorites', compact('favorites'));
    }

    public function offers()
    {
        $user = Auth::user();
        
        // Get all offers made by the buyer with related data
        $offers = \App\Models\Offer::where('buyer_id', $user->id)
            ->with([
                'product.media',
                'product.shop.user',
                'counterOffers' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('product_id')
            ->map(function($productOffers) {
                // Prefer accepted, then pending, then latest by created_at
                $latestOffer = $productOffers->where('status', 'accepted')->first()
                    ?? $productOffers->where('status', 'pending')->first()
                    ?? $productOffers->sortByDesc('created_at')->first();
                
                // Get offer history for this product
                $offerHistory = $productOffers->flatMap(function($offer) {
                    return $offer->getOfferHistory();
                })->sortBy('created_at');
                
                return [
                    'product' => $latestOffer->product,
                    'latest_offer' => $latestOffer,
                    'offer_history' => $offerHistory,
                    'total_offers' => $productOffers->count(),
                    'has_counter_offers' => $productOffers->where('is_counter_offer', true)->count() > 0,
                    'status_summary' => $this->getOfferStatusSummary($productOffers)
                ];
            });

        return view('buyer.offers', compact('offers'));
    }

    private function getOfferStatusSummary($offers)
    {
        $summary = [
            'pending' => 0,
            'accepted' => 0,
            'declined' => 0,
            'expired' => 0
        ];

        foreach ($offers as $offer) {
            $summary[$offer->status]++;
        }

        return $summary;
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


    public function setFeaturedImage(
        Request $request,
        Product $product
    ): RedirectResponse {
        // 1. Validate – must be a non-empty string, ≤255 chars.
        $validated = $request->validate([
            'featured_image' => ['required', 'string', 'max:255'],
        ]);

        // 2. Build a full URL if what you receive is a relative path.
        //    (If your form already posts the full URL, remove asset().)
        $fullUrl = asset('storage/' . ltrim($validated['featured_image'], '/'));

        // 3. Persist to DB.
        $product->update([
            'featured_image' => $fullUrl,
        ]);

        // 4. Bounce back with feedback.
        return back()->with('success', 'Featured image updated.');
    }


}
