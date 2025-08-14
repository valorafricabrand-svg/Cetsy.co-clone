<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Http\Request; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\ListingFeeType;
use App\Models\Country;
use App\Models\Wishlist;
use App\Models\ProcessingTime;
use App\Services\Shared\GetShippingService;
use App\Models\ShippingPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;   
use App\Models\Activity;
use App\Models\ShippingProfile;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;




class ProductController extends Controller
{
       public function index(Request $request)
    {
        // Resolve shop
        $shop = auth()->user()->shop;
        if (! $shop) {
            abort(403, 'No shop assigned to your account.');
        }
        $shopId = $shop->id;

        // Base query
        $query = Product::with('media')
            ->where('shop_id', $shopId);

        // Apply search
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (($status = $request->input('status')) !== null) {
            if ($status === 'closed') {
                $query->whereNotIn('is_active', [0,1,2,3]);
            } else {
                $query->where('is_active', (int) $status);
            }
        }

        // Fetch paginated products
        $products = $query
            ->latest()
            ->paginate(12)
            ->appends($request->only(['q','status']));

        // Build counts for each status
        $rawCounts = Product::where('shop_id', $shopId)
            ->select('is_active', DB::raw('count(*) as cnt'))
            ->groupBy('is_active')
            ->pluck('cnt', 'is_active')
            ->toArray();

        // Count “closed” (any status not 0–3)
        $closedCount = Product::where('shop_id', $shopId)
            ->whereNotIn('is_active', [0,1,2,3])
            ->count();

        $statusCounts = [
            0       => $rawCounts[0] ?? 0,
            1       => $rawCounts[1] ?? 0,
            2       => $rawCounts[2] ?? 0,
            3       => $rawCounts[3] ?? 0,
            'closed' => $closedCount,
        ];

        return view('products.index', compact('products','statusCounts'));
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
        'discount_percent'            => 'nullable|integer|between:1,100',
        'stock'                       => 'nullable|integer|min:0',
        'media.*'                     => 'nullable|image|max:5120',
        'digital_file'                => 'nullable|file|max:10240',
        'country_id'                  => 'nullable|exists:countries,id',

        // new fields
        'origin_postal_code'          => 'nullable|string|max:20',
        'processing_time_id'          => 'nullable|exists:processing_times,id',

    ]);

 

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
    $product->discount_percent              = $data['discount_percent'] ?? null;
    $product->stock                       = $data['type']==='physical' ? ($data['stock'] ?? 0) : null;
    $product->is_active                   = false;
    $product->save();








    // Create activity record for the seller
    Activity::create([
        'user_id' => Auth::id(),
        'is_read' => false,
        'description' => 'You created a new product'
    ]);

    return redirect()
        ->route('products.show',$product)
        ->with('success','Product created successfully! You can now add more details or activate it.');
}





public function edit(Product $product)
{


    // 1) Current user’s shop + shipping profiles
    $shop = Auth::user()->shop;
    $shippingProfiles = $shop?->shippingProfiles ?? collect();

    // 2) Profiles assigned to this product + default profile id
    $assignedProfiles = $product->shippingProfiles()
        ->pluck('shipping_profile_id')
        ->all();

    $defaultProfileId = $product->shippingProfiles()
        ->wherePivot('is_default', true)
        ->value('shipping_profile_id');

    // 3) Dropdown data
    $categories       = Category::orderBy('name')->get();
    $countries        = Country::orderBy('name')->get();
    $processingTimes  = ProcessingTime::orderBy('days')->get();

    // 4) Eager‑load ONLY what the edit view actually uses:
    //    - variation types + options for the "Manage variation types" UI
    //    - (optional) category attributes/values if your form needs them
    $product->load([
        'variationTypes.options',
        // 'category.attributes.values', // ⬅️ uncomment if your edit form renders these
    ]);

   

    // 5) Render (NO variations payload sent)
    return view('products.edit', [
        'product'          => $product,
        'shippingProfiles' => $shippingProfiles,
        'assignedProfiles' => $assignedProfiles,
        'defaultProfileId' => $defaultProfileId,
        'categories'       => $categories,
        'countries'        => $countries,
        'processingTimes'  => $processingTimes,
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
         'discount_percent' => 'nullable|integer|between:1,100',
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
        'discount_percent'              => $data['discount_percent'] ?? null,
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

    public function duplicate(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $newProduct = null;
        DB::transaction(function () use ($product, &$newProduct) {
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(6);
            $newProduct->is_active = 0;
            $newProduct->save();

            foreach ($product->media as $media) {
                $newProduct->media()->create($media->replicate()->toArray());
            }

            foreach ($product->variants as $variant) {
                $newProduct->variants()->create($variant->replicate()->toArray());
            }

            foreach ($product->digitalFiles as $file) {
                $newProduct->digitalFiles()->create($file->replicate()->toArray());
            }
        });

        return redirect()->route('products.edit', $newProduct)
            ->with('success', 'Product duplicated successfully!');
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
            'shippingProfiles:id,name,base_rate',
            'shop:id,name,user_id,slug',
            'shop.policies:shop_id,shipping,returns',
            // + variation types & variants for picker:
            'variationTypes.options',
            'variations.options.variationType',
        ])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->whereSlug($slug)
        ->firstOrFail();

    /* ------------------------------------------------------------
     | 2.  Record a view (logged-in users and guests)
     |------------------------------------------------------------ */
    $product->views()->create([
        'viewer_id' => Auth::id(),
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
        ?? (object) ['shipping'=>null,'returns'=>null];

    $moreFromShop = $product->shop->products()
        ->where('id','!=',$product->id)
        ->latest()
        ->take(8)
        ->get();

    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id','!=',$product->id)
        ->latest()
        ->take(8)
        ->get();

    /* ------------------------------------------------------------
     | 5.  Default shipping profile ID for the view (pivot field)
     |------------------------------------------------------------ */
    $defaultProfileId = optional(
        $product->shippingProfiles
                ->firstWhere('pivot.is_default', true)
            ?? $product->shippingProfiles->first()
    )->id;

    /* ------------------------------------------------------------
     | 6.  Variation‐picker data
     |------------------------------------------------------------ */
    // map each VariationType → [id, name, options:[{id,value},…]]
    $typesData = $product->variationTypes
        ->map(fn($t) => [
            'id'      => $t->id,
            'name'    => $t->name,
            'options' => $t->options
                            ->map(fn($o)=>['id'=>$o->id,'value'=>$o->value])
                            ->values(),
        ])->values();

    // map each Variant → [id, price, byType:{ typeId→optionId }]
    $variantsData = $product->variations
        ->map(fn($v) => [
            'id'     => $v->id,
            'price'  => (float)$v->price,
            'byType' => $v->options
                          ->mapWithKeys(fn($o)=>[$o->variation_type_id=>$o->id])
                          ->toArray(),
        ])->values();

    // base & final price
    $basePrice    = (float)($product->price ?? 0);
    $displayPrice = (float)($product->discounted_price ?? $basePrice);

    /* ------------------------------------------------------------
     | 7.  Render
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
        // new variation‐picker props:
        'typesData'        => $typesData,
        'variantsData'     => $variantsData,
        'basePrice'        => $basePrice,
        'displayPrice'     => $displayPrice,
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
                'order', // Include order relationship
                'counterOffers' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->orderBy('id', 'desc')
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
        $products = Product::where('is_active', 1)->with('media')->latest()->paginate(16);
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


 // in App\Http\Controllers\ProductController.php
// In your controller:
public function payFee(Request $request, Product $product)
{
    $request->validate([
        'plan' => ['required','in:monthly,4months'],
    ]);
    // … compute $fee, $label, $due …
    return view('products.pay_fee', [
        'order'        => $product,
        'plan'         => $request->plan,      // pass it here
        'fourMonthFee' => $product->category->listing_fee,
        'monthlyFee'   => $product->category->listing_fee / 3,
        'walletBalance'=> auth()->check() ? wallet() : 0,
    ]);
}




          // In App\Http\Controllers\ProductController.php



public function successDeposit(Request $request, $id)
{
    // 1) Retrieve the product or 404
    $product = Product::findOrFail($id);

    // 2) Validate the plan
    $plan = $request->input('plan', '4months');
    if (! in_array($plan, ['monthly','4months'])) {
        $plan = '4months';
    }

    // 3) Compute fee & next due date
    $fourMonthFee = (float) $product->category->listing_fee;
    if ($plan === 'monthly') {
        $fee     = $fourMonthFee / 4;
        $nextDue = now()->addMonth();
    } else {
        $fee     = $fourMonthFee;
        $nextDue = now()->addMonths(4);
    }

    // 4) Activate the product and set due date
    $product->update([
        'is_active'      => true,
        'listing_paid_at'=> now(),
        'next_due_date'  => $nextDue,
    ]);

    // 5) Determine payment method
    $via = $request->input('via', 'paypal');

    // 6) Generate a unique local transaction ID
    $localTx = $request->input('transaction_id');
    if (! $localTx) {
        do {
            $localTx = 'TRAN_' . time() . Str::upper(Str::random(6));
        } while (Payment::where('local_transaction_id', $localTx)->exists());
    }

    // 7) Record the payment
    Payment::create([
        'shop_id'              => $product->shop_id,
        'total_amount'         => $fee,
        'payment_method'       => $via,
        'status'               => '3',    // completed
        'currency'             => $product->currency ?? 'USD',
        'local_transaction_id' => $localTx,
        'payment_name'         => 'listing_fee',
    ]);

    // 8) Show a dedicated success page
    return view('products.success_deposit_fee', [
        'product' => $product,
        'plan'    => $plan,
        'amount'  => $fee,
        'nextDue' => $nextDue,
    ]);
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


public function changeStatus(Request $request, Product $product)
{
    $data = $request->validate([
        'status' => ['required','in:1,2'],
    ]);

    // If trying to publish (1) but next_due_date is past, block:
    if ($data['status']==1 && Carbon::now()->gt($product->next_due_date)) {
        return back()
             ->with('warning', 'Your listing has expired — please renew before publishing.');
    }

    $product->update(['is_active' => $data['status']]);

    $msg = $data['status']==1
         ? 'Listing has been published.'
         : 'Listing has been paused.';

    return redirect()
        ->route('products.show',$product)
        ->with('success',$msg);
}


// app/Http/Controllers/ProductController.php
public function updateRenewal(Request $request, Product $product)
{
    

    $data = $request->validate([
        'renewal_type' => 'required|in:automatic,manual',
    ]);

    $product->update($data);

    return back()->with('success', 'Renewal type updated.');
}


 // ───────────────────────────── PAGES ─────────────────────────────
    public function pricing(Product $product)
    {
        return view('products.pricing', compact('product'));
    }

    public function variations(Product $product)
    {
        // If you eager-load variations elsewhere, this is optional:
        $product->loadMissing('variations');
        return view('products.variations', compact('product'));
    }

    public function details(Product $product)
    {
        // These match what your old monolithic edit view expected
        $countries       = Country::orderBy('name')->get();
        $processingTimes = ProcessingTime::orderBy('days')->get();

        return view('products.details', compact('product', 'countries', 'processingTimes'));
    }




public function shipping(Product $product, Request $request)
{
    $shopId = $product->shop_id
            ?? optional(auth()->user())->shop_id;
    if (!$shopId) {
        abort(403, 'Shop not resolved for this product.');
    }

    $countries       = Country::orderBy('name')->get();
    $processingTimes = ProcessingTime::orderBy('days')->get();

    // 1) Pull ALL existing rows
    $allRows = ShippingProfile::query()
        ->where('shop_id',    $shopId)
        ->where('product_id', $product->id)
        ->orderBy('profile_name')
        ->orderByRaw("CASE WHEN dest_location_type='everywhere_else' THEN 1 ELSE 0 END")
        ->orderBy('dest_country_id')
        ->get();

    // 2) If none exist yet, create a default free-shipping profile
    if ($allRows->isEmpty()) {
        ShippingProfile::create([
            'shop_id'               => $shopId,
            'product_id'            => $product->id,
            'profile_name'          => 'Standard shipping',
            'name'                  => 'Standard shipping',
            'is_default'            => true,

            // ship-from / processing from product defaults
            'country_id'            => $product->country_id,
            'origin_postal_code'    => $product->origin_postal_code,
            'processing_time_id'    => $product->processing_time_id,
            'processing_custom_min' => $product->processing_custom_min,
            'processing_custom_max' => $product->processing_custom_max,

            // a single free, everywhere_else row
            'dest_location_type'    => 'everywhere_else',
            'dest_country_id'       => null,
            'service'               => 'Other',
            'days_min'              => null,
            'days_max'              => null,
            'charge_type'           => 'free',
            'base_rate'             => 0.00,
            'additional_rate'       => 0.00,
        ]);

        // reload
        $allRows = ShippingProfile::query()
            ->where('shop_id',    $shopId)
            ->where('product_id', $product->id)
            ->orderBy('profile_name')
            ->orderByRaw("CASE WHEN dest_location_type='everywhere_else' THEN 1 ELSE 0 END")
            ->orderBy('dest_country_id')
            ->get();
    }

    // Distinct profile names
    $profileNames = $allRows
        ->pluck('profile_name')
        ->unique()
        ->values();

    // Decide current profile name
    $reqName     = trim((string)$request->query('profile_name', ''));
    $currentName = null;

    if ($reqName !== '') {
        $currentName = $reqName;
    } elseif (Schema::hasColumn('shipping_profiles', 'is_default')) {
        $def = $allRows->firstWhere('is_default', true);
        if ($def) {
            $currentName = $def->profile_name;
        }
    }
    if (!$currentName && $profileNames->contains('Standard shipping')) {
        $currentName = 'Standard shipping';
    }
    if (!$currentName) {
        $currentName = $profileNames->first() ?: 'Standard shipping';
    }

    // Filter rows for the current profile
    $rowsForProfile = $allRows->where('profile_name', $currentName);

    // Build array for Alpine init
    $rulesArray = $rowsForProfile->map(function(ShippingProfile $r){
        return [
            'location_type'    => $r->dest_location_type,
            'country_id'       => $r->dest_location_type === 'country'
                                    ? (int)($r->dest_country_id ?? 0)
                                    : '',
            'service'          => $r->service ?? 'Other',
            'days_min'         => $r->days_min ?? '',
            'days_max'         => $r->days_max ?? '',
            'charge_type'      => $r->charge_type ?? 'fixed',
            'price_one'        => (float)($r->base_rate ?? 0),
            'price_additional' => (float)($r->additional_rate ?? 0),
        ];
    })->values()->all();

    // Meta for ship-from / processing
    $metaRow = $rowsForProfile->first();
    $currentProfileMeta = (object)[
        'profile_name'          => $currentName,
        'is_default'            => (bool)optional($metaRow)->is_default,
        'country_id'            => optional($metaRow)->country_id ?? $product->country_id,
        'origin_postal_code'    => optional($metaRow)->origin_postal_code ?? $product->origin_postal_code,
        'processing_time_id'    => optional($metaRow)->processing_time_id ?? $product->processing_time_id,
        'processing_custom_min' => optional($metaRow)->processing_custom_min ?? $product->processing_custom_min,
        'processing_custom_max' => optional($metaRow)->processing_custom_max ?? $product->processing_custom_max,
    ];

    // Render
    return view('products.shipping', [
        'product'          => $product,
        'countries'        => $countries,
        'processingTimes'  => $processingTimes,
        'shippingProfiles' => $allRows,
        'currentProfile'   => $currentProfileMeta,
        'rulesArray'       => $rulesArray,
    ]);
}



    public function settings(Product $product)
    {
        return view('products.settings', compact('product'));
    }

    // ──────────────────────────── UPDATES ────────────────────────────
    public function updatePricing(Request $request, Product $product)
    {
        // Your form posts: price, discount_percent, stock, sku
        $validated = $request->validate([
            'price'            => ['required','numeric','min:0'],
            'discount_percent' => ['nullable','numeric','min:0','max:100'],
            'stock'            => ['nullable','integer','min:0'],
            'sku'              => ['nullable','string','max:100', Rule::unique('products','sku')->ignore($product->id)],
        ]);

        // Normalize stock: blank => null (unlimited)
        if ($request->filled('stock') === false) {
            $validated['stock'] = null;
        }

        // Compute discounted_price from % (if provided), else null
        $price = (float)$validated['price'];
        $discountPercent = (float)($validated['discount_percent'] ?? 0);
        $discountedPrice = $discountPercent > 0 ? round($price * (1 - $discountPercent/100), 2) : null;

        // If your schema stores both columns, update both; if not, remove the one you don't use.
        $update = [
            'price'            => $price,
            'discount_percent' => $discountPercent ?: null,
            'discounted_price' => $discountedPrice,
            'stock'            => $validated['stock'],
            'sku'              => $validated['sku'] ?? null,
        ];

        $product->update($update);

        return back()->with('success', 'Price & inventory updated.');
    }

    public function updateVariations(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variations'                       => ['array'],
            'variations.*.id'                  => ['nullable','integer','exists:product_variations,id'],
            'variations.*.sku'                 => ['nullable','string','max:120'],
            'variations.*.attributes_label'    => ['nullable','string','max:255'],
            'variations.*.price'               => ['nullable','numeric','min:0'],
            'variations.*.stock'               => ['nullable','integer','min:0'],
            'variations.*._delete'             => ['nullable','boolean'],
        ]);

        foreach ($validated['variations'] ?? [] as $row) {
            $delete  = !empty($row['_delete']);
            $id      = $row['id'] ?? null;

            $payload = [
                'sku'              => $row['sku'] ?? null,
                'attributes_label' => $row['attributes_label'] ?? null,
                'price'            => $row['price'] ?? null,
                'stock'            => array_key_exists('stock', $row) ? $row['stock'] : null,
            ];

            if ($id) {
                $var = ProductVariation::where('product_id', $product->id)->findOrFail($id);
                $delete ? $var->delete() : $var->update($payload);
            } else {
                // Create only when not deleting and there is at least one filled field
                if (!$delete && collect($payload)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty()) {
                    $payload['product_id'] = $product->id;
                    ProductVariation::create($payload);
                }
            }
        }

        return back()->with('success', 'Variations updated.');
    }

    public function updateDetails(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'              => ['required','string','max:190'],
            'type'              => ['required','string','in:physical,digital,service'],
            'category_id'       => ['required','integer','exists:categories,id'],
            'short_description' => ['nullable','string','max:500'],
            'description'       => ['nullable','string'],
            // 'digital_file'    => ['nullable','file','max:20480'], // 20MB – enable if you handle upload here
        ]);

        $product->update($data);

        // If you want to handle digital file upload here, uncomment and adapt:
        /*
        if ($request->hasFile('digital_file') && $request->file('digital_file')->isValid()) {
            $path = $request->file('digital_file')->store('digital-files');
            // Persist to your DigitalFile model / media library as needed
            $product->digitalFiles()->create([
                'filename' => $request->file('digital_file')->getClientOriginalName(),
                'path'     => $path,
                'size'     => $request->file('digital_file')->getSize(),
                'mime'     => $request->file('digital_file')->getClientMimeType(),
            ]);
        }
        */

        return back()->with('success', 'Details updated.');
    }



/**
 * Update the product's shipping by persisting to a Shipping Profile (shop-scoped).
 * - If shipping_profile_id is sent, update that profile (must belong to the same shop).
 * - Else use the product's default shipping profile if any.
 * - Else create a new shipping profile for the product's shop and set it as default.
 *
 * Expects from the form:
 *  - country_id, origin_postal_code
 *  - processing_time_id OR custom (processing_custom_min, processing_custom_max)
 *  - weight, length, width, height, shipping_class, requires_shipping
 *  - shipping_rules_json (array json)
 *  - shipping_upgrades_json (array json)
 *  - shipping_profile_id (optional)
 */






public function updateShipping(Product $product, Request $request)
{
    $shopId = $product->shop_id ?? optional(auth()->user())->shop_id;
    if (!$shopId) abort(403, 'Shop not resolved for this product.');

    // Validate top fields
    $validated = $request->validate([
        'profile_name'           => ['nullable','string','max:100'],
        'set_default'            => ['nullable','boolean'],

        // Origin
        'country_id'             => ['required','integer','exists:countries,id'],
        'origin_postal_code'     => ['required','string','max:50'],

        // Processing
        'processing_time_id'     => ['nullable','in:,"",1,2,3,4,5,custom'],
        'processing_custom_min'  => ['nullable','integer','min:1'],
        'processing_custom_max'  => ['nullable','integer','min:1'],

        // Rows JSON from UI
        'shipping_rules_json'    => ['nullable','string'],
    ]);

    $profileName = trim($validated['profile_name'] ?? '') ?: 'Standard shipping';

    // Normalize processing
    $processingTimeId = $validated['processing_time_id'] ?? null;
    $procMin = null; $procMax = null;
    if ($processingTimeId === 'custom') {
        $processingTimeId = null;
        $procMin = $validated['processing_custom_min'] ?? null;
        $procMax = $validated['processing_custom_max'] ?? null;
        if (!$procMin || !$procMax) {
            return back()->withErrors(['processing_time_id' => 'Provide both min and max days for custom processing time.'])->withInput();
        }
    }

    // Money normalizer: handles "", " 7.50 ", "7,50"
    $money = static function ($v): float {
        if ($v === null) return 0.00;
        if (is_numeric($v)) return round((float)$v, 2);
        $s = trim((string)$v);
        if ($s === '') return 0.00;
        $s = str_replace([' ', ','], ['', '.'], $s);
        $s = preg_replace('/[^0-9.]/', '', $s);
        if ($s === '' || $s === '.') return 0.00;
        return round((float)$s, 2);
    };

    // Decode rules JSON
    $rawRules = [];
    if (!empty($validated['shipping_rules_json'])) {
        $tmp = json_decode($validated['shipping_rules_json'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
            $rawRules = array_values($tmp);
        } else {
            return back()->withErrors(['shipping_rules_json' => 'Invalid shipping rules JSON.'])->withInput();
        }
    }

    // Normalize rows → DB columns (ensure base_rate & additional_rate are set)
    $rows = collect($rawRules)->map(function($r) use ($money){
        $chargeType = ($r['charge_type'] ?? 'fixed') === 'free' ? 'free' : 'fixed';

        $baseRate = $money($r['price_one'] ?? 0);
        $addRate  = array_key_exists('price_additional', $r)
            ? $money($r['price_additional'])
            : $money($r['price_two'] ?? 0);

        if ($chargeType === 'free') { $baseRate = 0.00; $addRate = 0.00; }

        $locType = ($r['location_type'] ?? 'country') === 'everywhere_else' ? 'everywhere_else' : 'country';

        return [
            'dest_location_type' => $locType,
            'dest_country_id'    => $locType === 'country'
                                    ? (!empty($r['country_id']) ? (int)$r['country_id'] : null)
                                    : null,
            'service'            => $r['service'] ?? 'Other',
            'days_min'           => isset($r['days_min']) ? (int)$r['days_min'] : null,
            'days_max'           => isset($r['days_max']) ? (int)$r['days_max'] : null,
            'charge_type'        => $chargeType,
            'base_rate'          => $baseRate,       // <-- SAVED
            'additional_rate'    => $addRate,        // <-- SAVED
        ];
    });

    // Server-side day range check
    $badRange = $rows->contains(fn($r) => $r['days_min'] && $r['days_max'] && $r['days_max'] < $r['days_min']);
    if ($badRange) {
        return back()->withErrors(['shipping_rules_json' => 'Fix delivery time ranges where max < min.'])->withInput();
    }

    // Fallback: at least one row
    if ($rows->isEmpty()) {
        $rows = collect([[
            'dest_location_type' => 'everywhere_else',
            'dest_country_id'    => null,
            'service'            => 'Other',
            'days_min'           => null,
            'days_max'           => null,
            'charge_type'        => 'free',
            'base_rate'          => 0.00,
            'additional_rate'    => 0.00,
        ]]);
    }

    DB::transaction(function() use ($product, $shopId, $validated, $profileName, $processingTimeId, $procMin, $procMax, $rows) {

        // Remove existing rows for this profile group
        DB::table('shipping_profiles')
            ->where('shop_id', $shopId)
            ->where('product_id', $product->id)
            ->where('profile_name', $profileName)
            ->delete();

        $now = now();
        $common = [
            'shop_id'               => $shopId,
            'product_id'            => $product->id,

            // Set BOTH to avoid NOT NULL `name` errors
            'profile_name'          => $profileName,
            'name'                  => $profileName,

            'is_default'            => (bool)request()->boolean('set_default'),

            // origin & processing
            'country_id'            => (int)$validated['country_id'],
            'origin_postal_code'    => $validated['origin_postal_code'],
            'processing_time_id'    => $processingTimeId ? (int)$processingTimeId : null,
            'processing_custom_min' => $procMin,
            'processing_custom_max' => $procMax,

            'created_at'            => $now,
            'updated_at'            => $now,
        ];

        // Build rows with rates (explicitly present)
        $payload = $rows->map(fn($r) => array_merge($common, [
            'dest_location_type' => $r['dest_location_type'],
            'dest_country_id'    => $r['dest_country_id'],
            'service'            => $r['service'],
            'days_min'           => $r['days_min'],
            'days_max'           => $r['days_max'],
            'charge_type'        => $r['charge_type'],
            'base_rate'          => $r['base_rate'],        // <- persisted value
            'additional_rate'    => $r['additional_rate'],  // <- persisted value
        ]))->toArray();

        DB::table('shipping_profiles')->insert($payload);

        // Default handling (only if column exists)
        if (Schema::hasColumn('shipping_profiles','is_default') && request()->boolean('set_default')) {
            DB::table('shipping_profiles')
                ->where('shop_id', $shopId)
                ->where('product_id', $product->id)
                ->where('profile_name', '<>', $profileName)
                ->update(['is_default' => false, 'updated_at' => $now]);
        }
    });

    return redirect()
        ->route('products.shipping', ['product' => $product, 'profile_name' => $profileName])
        ->with('success', 'Shipping profile saved successfully.');
}









    public function updateSettings(Request $request, Product $product)
    {
        $data = $request->validate([
            'is_active'    => ['required','integer','in:0,1,2,3,4'],
            'renewal_type' => ['required', Rule::in(['automatic','manual'])],
            'visibility'   => ['nullable', Rule::in(['Public','Private','Unlisted'])],
            'slug'         => ['nullable','string','max:190', Rule::unique('products','slug')->ignore($product->id)],
            'tags'         => ['nullable','string','max:255'],
        ]);

        if (!empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Optional: normalize tags (trim spaces)
        if (isset($data['tags'])) {
            $data['tags'] = collect(explode(',', $data['tags']))
                ->map(fn($t) => trim($t))
                ->filter()
                ->implode(', ');
        }

        $product->update($data);

        return back()->with('success', 'Settings updated.');
    }


      public function media(Product $product)
    {
        $product->loadMissing('media');
        return view('products.media', compact('product'));
    }

}
