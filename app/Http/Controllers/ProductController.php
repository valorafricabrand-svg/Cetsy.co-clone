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
use App\Services\Recommendation\ProductRecommendationService;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;




class ProductController extends Controller
{
    protected ProductRecommendationService $recommendations;

    public function __construct(ProductRecommendationService $recommendations)
    {
        $this->recommendations = $recommendations;
    }

    /* -------------------------------------------------------------
     | Activity helpers (product change logs)
     |-------------------------------------------------------------- */
    private function captureOnly(Product $product, array $fields): array
    {
        $out = [];
        foreach ($fields as $f) {
            $out[$f] = $product->getAttribute($f);
        }
        return $out;
    }

    private function computeChanges(array $before, array $after): array
    {
        $changes = [];
        foreach (array_keys($before + $after) as $key) {
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;
            // Normalize numeric strings
            if (is_numeric($old) && is_numeric($new)) {
                $old = (float) $old; $new = (float) $new;
            }
            if ($old !== $new) {
                $changes[$key] = ['from' => $old, 'to' => $new];
            }
        }
        return $changes;
    }

    private function recordProductActivity(Product $product, string $section, array $changes, array $extra = []): void
    {
        try {
            if (empty($changes) && empty($extra)) return; // nothing to log
            Activity::create([
                'user_id'      => auth()->id(),
                'is_read'      => false,
                'type'         => Activity::TYPE_PRODUCT,
                'description'  => 'Updated product: ' . ($product->name ?? ('#'.$product->id)),
                'related_id'   => $product->id,
                'related_type' => 'product',
                'properties'   => [
                    'section' => $section,
                    'product_id' => $product->id,
                    'changes' => $changes,
                ] + $extra,
            ]);
        } catch (\Throwable $e) {
            \Log::error('product.activity.log_failed', [
                'product_id' => $product->id,
                'section'    => $section,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public function index(Request $request)
    {
        // Resolve shop
        $shop = auth()->user()->shop;
        if (! $shop) {
            abort(403, 'No shop assigned to your account.');
        }
        $shopId = $shop->id;

        // Base query (include variations to show "From" price and variation badges on dashboard)
        $query = Product::with(['media','shop','variations.options'])
            ->where('shop_id', $shopId);

        $filters = [
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'type'      => $request->input('type'),
            'country_id'=> $request->input('country_id'),
        ];

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

        // Optional filter: show listings missing featured image (cleanup helper)
        if ($request->boolean('no_featured')) {
            $query->where(function($q){
                $q->whereNull('featured_image')->orWhere('featured_image','');
            });
        }

        $minPrice = $request->filled('price_min') ? (float) $request->input('price_min') : null;
        $maxPrice = $request->filled('price_max') ? (float) $request->input('price_max') : null;

        if (! is_null($minPrice) && ! is_null($maxPrice) && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        if (! is_null($minPrice)) {
            $query->where('price', '>=', $minPrice);
            $filters['price_min'] = $minPrice;
        }

        if (! is_null($maxPrice)) {
            $query->where('price', '<=', $maxPrice);
            $filters['price_max'] = $maxPrice;
        }

        // Type filter
        if ($request->filled('type')) {
            $type = $request->input('type');
            if (in_array($type, ['physical','digital','service'], true)) {
                $query->where('type', $type);
            } else {
                $filters['type'] = null;
            }
        }

        // Country filter
        if ($request->filled('country_id')) {
            $countryId = (int) $request->input('country_id');
            if ($countryId > 0) {
                $query->where('country_id', $countryId);
                $filters['country_id'] = $countryId;
            } else {
                $filters['country_id'] = null;
            }
        }


        // Fetch paginated products
        $products = $query
            ->latest()
            ->paginate(12)
            ->appends($request->only(['q','status','price_min','price_max','type','country_id','no_featured']));

        $groupedProducts = $products->getCollection()
            ->groupBy(function ($product) {
                return $product->type ?? 'other';
            });

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

        // Count of active listings missing featured image (for banner reminder)
        $missingFeaturedActive = Product::where('shop_id', $shopId)
            ->where('is_active', 1)
            ->where(function($q){ $q->whereNull('featured_image')->orWhere('featured_image',''); })
            ->count();

        $countryIds = Product::where('shop_id', $shopId)
            ->whereNotNull('country_id')
            ->distinct()
            ->pluck('country_id');

        $availableCountries = $countryIds->isNotEmpty()
            ? Country::whereIn('id', $countryIds)->orderBy('name')->get()
            : collect();

        $resetParams = array_filter([
            'q' => $request->input('q'),
            'status' => $request->input('status'),
        ], fn($value) => !is_null($value) && $value !== '');

        return view('products.index', compact(
            'products',
            'statusCounts',
            'groupedProducts',
            'availableCountries',
            'filters',
            'resetParams',
            'missingFeaturedActive'
        ));
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
        'description' => 'You created a new product',
        'type' => \App\Models\Activity::TYPE_PRODUCT,
        'related_id' => $product->id,
        'related_type' => 'product'
    ]);

    return redirect()
        ->route('products.show',$product)
        ->with('success','Listing created successfully! You can now add more details or activate it.');
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
    // Capture "before" snapshot
    $before = $this->captureOnly($product, [
        'name','type','category_id','country_id','origin_postal_code',
        'processing_time_id','description','price','discount_percent','stock'
    ]);
    $beforeMedia = $product->media()->count();
    $beforeVars  = $product->variations()->count();

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

    // Log changes
    $product->refresh();
    $after  = $this->captureOnly($product, array_keys($before));
    $changes = $this->computeChanges($before, $after);
    $afterMedia = $product->media()->count();
    $afterVars  = $product->variations()->count();
    $extra = [
        'counters' => [
            'media'      => ['from' => $beforeMedia, 'to' => $afterMedia],
            'variations' => ['from' => $beforeVars,  'to' => $afterVars],
        ]
    ];
    $this->recordProductActivity($product, 'full_update', $changes, $extra);

    return redirect()
        ->route('products.show', $product)
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

        // Eager-load modern variation graph so we can clone it
        $product->loadMissing(['media', 'digitalFiles', 'variants', 'variationTypes.options', 'variations.options']);

        $newProduct = null;
        DB::transaction(function () use ($product, &$newProduct) {
            // SKU duplication strategy (settings-driven with env fallback)
            $skuStrategy = function_exists('setting')
                ? (setting('duplicate_sku_strategy', env('DUPLICATE_SKU_STRATEGY', 'append')))
                : env('DUPLICATE_SKU_STRATEGY', 'append');   // append | clear | keep
            $skuSuffix   = function_exists('setting')
                ? (setting('duplicate_sku_suffix', env('DUPLICATE_SKU_SUFFIX', 'DUP')))
                : env('DUPLICATE_SKU_SUFFIX', 'DUP');        // used when append
            $skuRandLen  = (int) (function_exists('setting')
                ? (setting('duplicate_sku_random_len', env('DUPLICATE_SKU_RANDOM_LEN', 4)))
                : env('DUPLICATE_SKU_RANDOM_LEN', 4));
            $skuRandLen  = max(1, $skuRandLen);

            $makeSku = function (?string $sku, string $table) use ($skuStrategy, $skuSuffix, $skuRandLen) {
                if (empty($sku)) return null;
                if ($skuStrategy === 'clear') return null;
                if ($skuStrategy === 'keep') return $sku; // no guarantees of uniqueness

                // append
                $base = $sku;
                $candidate = $base . '-' . $skuSuffix . '-' . Str::upper(Str::random($skuRandLen));
                try {
                    while (DB::table($table)->where('sku', $candidate)->exists()) {
                        $candidate = $base . '-' . $skuSuffix . '-' . Str::upper(Str::random($skuRandLen));
                    }
                } catch (\Throwable $e) {
                    // ignore DB check failures; still return a suffixed SKU
                }
                return $candidate;
            };
            // 1) Clone the base product
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(6);
            // Reset listing-related fields so the copy doesn't inherit subscription/expiry
            $newProduct->is_active       = 0;
            $newProduct->listing_paid_at = null;
            $newProduct->next_due_date   = null;
            if (isset($newProduct->renewal_type)) {
                $newProduct->renewal_type = 'automatic'; // enum not-null
            }
            $newProduct->save();

            // 2) Media
            foreach ($product->media as $media) {
                $newProduct->media()->create($media->replicate()->toArray());
            }

            // 3) Legacy simple variants (ProductVariant model)
            foreach ($product->variants as $legacyVariant) {
                $clone = $legacyVariant->replicate();
                $clone->product_id = $newProduct->id;
                $clone->sku = $makeSku($clone->sku ?? null, $legacyVariant->getTable());
                $newProduct->variants()->create($clone->toArray());
            }

            // 4) Digital files
            foreach ($product->digitalFiles as $file) {
                $newProduct->digitalFiles()->create($file->replicate()->toArray());
            }

            // 5) Modern variations: clone VariationTypes + Options, then Variants + pivot options
            $typeIdMap = [];
            $optionIdMap = [];

            // 5a) Types and options
            foreach ($product->variationTypes as $oldType) {
                $newType = $oldType->replicate();
                $newType->product_id = $newProduct->id;
                $newType->save();
                $typeIdMap[$oldType->id] = $newType->id;

                foreach ($oldType->options as $oldOpt) {
                    $newOpt = $oldOpt->replicate();
                    $newOpt->variation_type_id = $newType->id;
                    $newOpt->save();
                    $optionIdMap[$oldOpt->id] = $newOpt->id;
                }
            }

            // 5b) Variants with option pivots
            foreach ($product->variations as $oldVariant) {
                $newVariant = $oldVariant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = $makeSku($newVariant->sku ?? null, $oldVariant->getTable());
                $newVariant->save();

                // Map old option ids to the new ones via $optionIdMap
                $oldOptionIds = optional($oldVariant->options)->pluck('id') ?? collect();
                $newOptionIds = $oldOptionIds->map(fn ($id) => $optionIdMap[$id] ?? null)
                                             ->filter()
                                             ->values()
                                             ->all();
                if (!empty($newOptionIds)) {
                    $newVariant->options()->attach($newOptionIds);
                }
            }

            // 6) Shipping profiles (per-product rows)
            try {
                $shippingRows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
                foreach ($shippingRows as $row) {
                    $newRow = $row->replicate();
                    $newRow->product_id = $newProduct->id;
                    $newRow->save();
                }
            } catch (\Throwable $e) {
                // Non-fatal: some installs may not use per-product rows
            }

            // 7) If your install uses product_shipping pivot (shop-level profiles), clone pivot rows too
            try {
                $pivotRows = DB::table('product_shipping')
                    ->where('product_id', $product->id)
                    ->get(['shipping_profile_id', 'is_default']);
                foreach ($pivotRows as $pv) {
                    DB::table('product_shipping')->updateOrInsert(
                        [
                            'product_id'         => $newProduct->id,
                            'shipping_profile_id'=> $pv->shipping_profile_id,
                        ],
                        [
                            'is_default' => (bool)$pv->is_default,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // ignore silently if table not present or schema differs
            }
        });

        return redirect()->route('products.show', $newProduct)
            ->with('success', 'Product duplicated successfully! Variations, options, and variant combinations were copied.');
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
            'shippingProfiles:id,name,base_rate,pickup_available',
            // Shop with rating aggregates and policies
            'shop' => function ($q) {
                $q->select('id','name','user_id','slug')
                  ->with('policies:shop_id,shipping,returns')
                  ->withCount('reviews')
                  ->withAvg('reviews', 'rating');
            },
            // + variation types & variants for picker:
            'variationTypes.options',
            'variations.options.variationType',
        ])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->whereSlug($slug)
        ->firstOrFail();

    // Public visibility: only active listings are publicly viewable.
    // Allow owner and admins to view paused/draft via direct link (preview).
    $isActive = (int)($product->is_active ?? 0) === 1;
    $viewerId = \Illuminate\Support\Facades\Auth::id();
    $ownerId  = optional($product->shop)->user_id;
    $isOwner  = $viewerId && ($viewerId === $ownerId);
    $isAdmin  = auth()->check() && (bool) (auth()->user()->is_admin ?? false);
    if (! $isActive && ! ($isOwner || $isAdmin)) {
        abort(404);
    }

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
    // Show shop-wide reviews (qualify user columns to avoid ambiguous select)
    $reviews = $product->shop
        ? $product->shop->reviews()
            ->with(['user' => function ($q) { $q->select('users.id', 'users.name'); }])
            ->latest()
            ->take(20)
            ->get()
        : collect();

    $faqs = $product->faqs()->latest()->get();

    $shopPolicies = $product->shop->policies
        ?? (object) ['shipping'=>null,'returns'=>null];

    $moreFromShop = $product->shop->products()
        ->where('id','!=',$product->id)
        ->where('is_active', 1)
        ->latest()
        ->take(8)
        ->get();

    $relatedProducts = $this->recommendations->relatedToProduct($product, Auth::user(), 8);

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

public function listings(Request $request)
{
    $query = Product::where('is_active', 1)->with([
        'media',
        'shop' => function ($q) {
            $q->withCount('reviews')->withAvg('reviews', 'rating');
        },
    ]);

    if ($request->filled('type')) {
        // Accept common aliases from UI/links: product(s) -> physical
        $map = [
            'product'  => 'physical',
            'products' => 'physical',
            'services' => 'service',
        ];
        $type = $map[$request->type] ?? $request->type;
        $query->where('type', $type);
    }

    $products = $query->latest()->paginate(16);
    $recommendedProducts = $this->recommendations->trendingForUser(Auth::user(), 8);

    return themed_view('listings', compact('products', 'recommendedProducts'));
}




    public function search(Request $request)
    {
        // Normalize query
        $q = trim((string) $request->input('q', ''));

        // Build searchable terms (split on whitespace)
        $terms = collect(preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values();

        $products = Product::where('is_active', 1)
            ->when($terms->isNotEmpty(), function ($query) use ($terms) {
                $query->where(function ($q2) use ($terms) {
                    // Match ANY term in name/description/tags
                    foreach ($terms as $t) {
                        $like = "%{$t}%";
                        $q2->orWhere('name', 'like', $like)
                           ->orWhere('description', 'like', $like)
                           ->orWhere('tags', 'like', $like);
                    }
                });
            })
            ->with([
                'media',
                'shop' => function ($q2) {
                    $q2->withCount('reviews')->withAvg('reviews', 'rating');
                },
            ])
            ->paginate(12)
            ->appends(['q' => $q]);

        $recommendedProducts = $this->recommendations->trendingForUser(Auth::user(), 6);

        return themed_view('listings', compact('products', 'recommendedProducts'))
            ->with('q', $q);
    }


 // in App\Http\Controllers\ProductController.php
// In your controller:
public function payFee(Request $request, Product $product)
{
    $freq = (int) ($product->category->listing_frequency ?? 4);
    $freq = in_array($freq, [1,4], true) ? $freq : 4;
    $baseFee = (float) ($product->category->listing_fee ?? 0);

    // Only allow the plan matching the category's frequency
    $allowedPlan = $freq === 1 ? 'monthly' : '4months';

    $validated = $request->validate([
        'plan' => ['required', Rule::in([$allowedPlan])],
    ]);

    $plans = [
        $allowedPlan => [
            'label'  => $freq === 1 ? 'Monthly' : '4-Month',
            'months' => $freq,
            'amount' => max($baseFee, 0),
        ],
    ];

    $selectedPlan = $validated['plan'] ?? $allowedPlan;

    return view('products.pay_fee', [
        'order'         => $product,
        'plan'          => $selectedPlan,
        'plans'         => $plans,
        'walletBalance' => auth()->check() ? wallet() : 0,
    ]);
}




          // In App\Http\Controllers\ProductController.php



public function successDeposit(Request $request, $id)
{
    // 1) Retrieve the product or 404
    $product = Product::findOrFail($id);

    // 2) Category-driven plan (1-month or 4-month cycle)
    $freq = (int) ($product->category->listing_frequency ?? 4);
    $freq = in_array($freq, [1,4], true) ? $freq : 4;
    $plan = $freq === 1 ? 'monthly' : '4months';
    $planLabels = [ 'monthly' => 'Monthly', '4months' => '4-Month' ];

    // 3) Fee is per-category cycle; due date is now + frequency months
    $fee     = max(0, (float) ($product->category->listing_fee ?? 0));
    $nextDue = now()->addMonths($freq);

    $fee = max($fee, 0);

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

    // 7) Record the payment (mark successful)
    Payment::create([
        'shop_id'              => $product->shop_id,
        'total_amount'         => $fee,
        'payment_method'       => $via,
        'paymentStatus'        => 3,
        'payment_status'       => 'successful',
        'currency'             => $product->currency ?? 'USD',
        'local_transaction_id' => $localTx,
        'payment_name'         => 'listing_fee',
    ]);

    // 8) Show a dedicated success page
    return view('products.success_deposit_fee', [
        'product'   => $product,
        'plan'      => $plan,
        'planLabel' => $planLabels[$plan] ?? ucfirst($plan),
        'amount'    => $fee,
        'nextDue'   => $nextDue,
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
        $___oldFeatured = $product->featured_image;

        // 3. Persist to DB.
        $product->update([
            'featured_image' => $fullUrl,
        ]);

        // Log activity
        try {
            if ($___oldFeatured !== $fullUrl) {
                Activity::create([
                    'user_id'      => auth()->id(),
                    'is_read'      => false,
                    'type'         => Activity::TYPE_PRODUCT,
                    'description'  => 'Changed primary product image',
                    'related_id'   => $product->id,
                    'related_type' => 'product',
                    'properties'   => [
                        'section' => 'media',
                        'action'  => 'set_primary',
                        'changes' => [
                            'featured_image' => ['from' => $___oldFeatured, 'to' => $fullUrl]
                        ],
                    ],
                ]);
            }
        } catch (\Throwable $e) { \Log::error('product.media.primary.log_failed', ['product_id' => $product->id, 'error' => $e->getMessage()]); }

        // 4. Bounce back with feedback.
        return back()->with('success', 'Featured image updated.');
    }


public function changeStatus(Request $request, Product $product)
{
    $data = $request->validate([
        'status' => ['required','in:1,2'],
    ]);

    // Require featured image before publishing new/paused listings
    if ($data['status'] == 1 && empty($product->featured_image)) {
        return back()
            ->with('warning', 'Add a featured image before publishing this listing.');
    }

    // If trying to publish (1) but no listing payment yet, block
    if ($data['status'] == 1 && empty($product->listing_paid_at)) {
        return back()
            ->with('warning', 'Please pay the listing fee before publishing this listing.');
    }

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
        $before = $this->captureOnly($product, ['price','discount_percent','stock','sku']);
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

        $product->refresh();
        $after   = $this->captureOnly($product, array_keys($before));
        $changes = $this->computeChanges($before, $after);
        $this->recordProductActivity($product, 'pricing', $changes);

        return back()->with('success', 'Price & inventory updated.');
    }

    public function updateVariations(Request $request, Product $product)
    {
        $beforeCount = $product->variations()->count();
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
        $afterCount = $product->variations()->count();
        $this->recordProductActivity($product, 'variations', [], [
            'counters' => [
                'variations' => ['from' => $beforeCount, 'to' => $afterCount]
            ]
        ]);
        return back()->with('success', 'Variations updated.');
    }

    public function updateDetails(Request $request, Product $product)
    {
        $before = $this->captureOnly($product, ['name','type','category_id','short_description','description']);
        $data = $request->validate([
            'name'              => ['required','string','max:190'],
            'type'              => ['required','string','in:physical,digital,service'],
            'category_id'       => ['required','integer','exists:categories,id'],
            'short_description' => ['nullable','string','max:500'],
            'description'       => ['nullable','string'],
            // 'digital_file'    => ['nullable','file','max:20480'], // 20MB – enable if you handle upload here
        ]);

        $product->update($data);
        $product->refresh();
        $after   = $this->captureOnly($product, array_keys($before));
        $changes = $this->computeChanges($before, $after);
        $this->recordProductActivity($product, 'details', $changes);

        // If you want to handle digital file upload here, uncomment and adapt:
    
        if ($request->hasFile('digital_file') && $request->file('digital_file')->isValid()) {
            $path = $request->file('digital_file')->store('digital-files');
            // Persist to your DigitalFile model / media library as needed
            $product->digitalFiles()->create([
                'filename' => $request->file('digital_file')->getClientOriginalName(),
                'filepath'     => $path,
                'size'     => $request->file('digital_file')->getSize(),
                'mime'     => $request->file('digital_file')->getClientMimeType(),
            ]);
        }
    

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
        $beforeProfiles = \DB::table('shipping_profiles')
            ->where('product_id', $product->id)
            ->count();
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

    // If no rules were provided, treat this as an "info-only" update and
    // do NOT reset existing shipping rows to free. Instead, update the
    // common origin/processing fields across the current profile rows.
    if ($rows->isEmpty()) {
        $existingRowsCount = \DB::table('shipping_profiles')
            ->where('shop_id', $shopId)
            ->where('product_id', $product->id)
            ->where('profile_name', $profileName)
            ->count();

        if ($existingRowsCount > 0) {
            \DB::table('shipping_profiles')
                ->where('shop_id', $shopId)
                ->where('product_id', $product->id)
                ->where('profile_name', $profileName)
                ->update([
                    'country_id'            => (int)$validated['country_id'],
                    'origin_postal_code'    => $validated['origin_postal_code'],
                    'processing_time_id'    => $processingTimeId ? (int)$processingTimeId : null,
                    'processing_custom_min' => $procMin,
                    'processing_custom_max' => $procMax,
                    'updated_at'            => now(),
                ]);

            return redirect()
                ->route('products.shipping', ['product' => $product, 'profile_name' => $profileName])
                ->with('success', 'Shipping profile info updated.');
        }

        // No existing rows yet: create one sane default rule
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
    $afterProfiles = \DB::table('shipping_profiles')
        ->where('product_id', $product->id)
        ->count();

    $this->recordProductActivity($product, 'shipping', [], [
        'counters' => [
            'profiles' => ['from' => $beforeProfiles, 'to' => $afterProfiles]
        ],
        'profile_name' => $profileName,
        'set_default'  => (bool)request()->boolean('set_default'),
    ]);
    return redirect()
        ->route('products.shipping', ['product' => $product, 'profile_name' => $profileName])
        ->with('success', 'Shipping profile saved successfully.');
    }









    public function updateSettings(Request $request, Product $product)
    {
        $before = $this->captureOnly($product, ['is_active','renewal_type','visibility','slug','tags']);
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

        // Enforce publish eligibility from settings as well
        if (isset($data['is_active']) && (int)$data['is_active'] === 1) {
            // Require a featured image
            if (empty($product->featured_image)) {
                return back()->with('warning', 'Add a featured image before publishing this listing.');
            }

            // Check payment + expiry with robust date handling
            $hasPaid = !empty($product->listing_paid_at);
            $notExpired = empty($product->next_due_date) || (function() use ($product) {
                try { return Carbon::parse($product->next_due_date)->isFuture(); }
                catch (\Exception $e) { return true; }
            })();

            if (!$hasPaid) {
                return back()->with('warning', 'Please pay the listing fee before activating this listing.');
            }
            if (!$notExpired) {
                return back()->with('warning', 'Your listing has expired — please renew before publishing.');
            }
        }

        $product->update($data);
        $product->refresh();
        $after   = $this->captureOnly($product, array_keys($before));
        $changes = $this->computeChanges($before, $after);
        $this->recordProductActivity($product, 'settings', $changes);

        return back()->with('success', 'Settings updated.');
    }


      public function media(Product $product)
    {
        $product->loadMissing('media');
        return view('products.media', compact('product'));
    }

}



