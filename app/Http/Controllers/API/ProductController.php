<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationOption;
use App\Models\Variant;
use App\Models\Media;
use App\Models\DigitalFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private function resolveRequestedProductType(array $data): string
    {
        $resolved = Product::resolveTypeForCategory(
            $data['type'] ?? null,
            $data['category_id'] ?? null
        );

        return $resolved ?: Product::TYPE_PHYSICAL;
    }

    /**
     * Display a listing of products.
     * Accepts: search|keyword, min_price, max_price
     */
    public function index(Request $request)
    {
        // Only surface active listings to the public API
        $query = Product::with(['media', 'shop', 'category'])
            ->where('is_active', 1);

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by listing type to mirror web (physical, digital, service)
        if ($request->filled('type')) {
            $type = $request->get('type');
            if (in_array($type, ['physical', 'digital', 'service'], true)) {
                $query->whereDisplayType($type);
            }
        }

        // Support both `search` and `keyword` query params
        $term = $request->get('search', $request->get('keyword'));
        if (!empty($term)) {
            $query->where('name', 'like', '%' . $term . '%');
        }

        $paginator = $query->latest()->paginate(10);
        $paginator->getCollection()->transform(function (Product $product) {
            return $this->serializePublicProduct($product);
        });

        return $paginator;
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
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_price'   => 'nullable|numeric|min:0|lte:price',
            'type'             => 'nullable|in:physical,digital,service',
            'category_id'      => 'nullable|integer|exists:categories,id',
            'stock'            => 'nullable|integer|min:0',
            'image'            => 'nullable|image|max:8192',
            // Service fields
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:190',
            'location'         => 'nullable|string|max:190',
            'tags'             => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['shop_id'] = $user->shop->id;
        $data['type'] = $this->resolveRequestedProductType($data + [
            'type' => $data['type'] ?? Product::TYPE_PHYSICAL,
        ]);
        if ($data['type'] !== Product::TYPE_PHYSICAL) {
            $data['stock'] = null;
        }

        // Compute discounted_price if only percent given
        if (!isset($data['discount_price']) && isset($data['discount_percent'])) {
            $price = (float) $data['price'];
            $pct   = (float) $data['discount_percent'];
            if ($pct > 0) {
                $data['discounted_price'] = round($price * (1 - $pct / 100), 2);
            }
        }

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

    /** Update listing settings (status, renewal, visibility, slug, tags) */
    public function updateSettings(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);
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
        if (isset($data['tags'])) {
            $data['tags'] = collect(explode(',', $data['tags']))
                ->map(fn($t) => trim($t))
                ->filter()
                ->implode(', ');
        }
        $hasBillingHistory = !empty($product->listing_paid_at) || !empty($product->next_due_date);
        if ((int) ($data['is_active'] ?? 0) !== 1) {
            $data['is_active'] = $hasBillingHistory ? 2 : 0;
        }
        $product->update($data);
        return response()->json(['message' => 'Settings updated', 'product' => $product->fresh()]);
    }

    /** Update core details including type/category/descriptions */
    public function updateDetails(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);
        $data = $request->validate([
            'name'              => ['required','string','max:190'],
            'type'              => ['required','string','in:physical,digital,service'],
            'category_id'       => ['nullable','integer','exists:categories,id'],
            'description'       => ['nullable','string'],
        ]);
        $data['type'] = $this->resolveRequestedProductType($data);
        $product->update($data);
        return response()->json(['message' => 'Details updated', 'product' => $product->fresh()]);
    }

    /** Upload product media (image) */
    public function uploadMedia(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);
        $request->validate(['image' => 'required|image|max:8192']);
        $path = $request->file('image')->store('products', 'public');
        $maxPos = (int) ($product->media()->max('position') ?? 0);
        $media = $product->media()->create(['type' => 'image', 'url' => $path, 'position' => $maxPos + 1]);
        // Also set as featured if empty
        if (empty($product->image)) {
            $product->update(['image' => basename($path)]);
        }
        return response()->json(['message' => 'Media uploaded', 'media' => $media]);
    }

    /** Delete a media item from the product */
    public function destroyMedia(Request $request, Product $product, \App\Models\Media $media)
    {
        $this->authorizeProduct($request, $product);
        abort_if($media->product_id !== $product->id, 404);

        // Remove file if on public disk
        if ($media->url && \Illuminate\Support\Str::startsWith($media->url, 'products/')) {
            Storage::disk('public')->delete($media->url);
        }

        // If this media is currently featured, reset to another
        $wasFeatured = $product->image && basename($media->url) === $product->image;
        $media->delete();

        if ($wasFeatured) {
            $next = $product->media()->orderBy('position')->first();
            $product->update(['image' => $next ? basename($next->url) : null]);
        }

        return response()->json(['message' => 'Media deleted']);
    }

    /** Reorder media items */
    public function reorderMedia(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);
        $data = $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        $ids = $data['order'];
        $pos = 1;
        foreach ($ids as $id) {
            $m = $product->media()->where('id', $id)->first();
            if ($m) {
                $m->position = $pos++;
                $m->save();
            }
        }
        // Update featured image to the first in order
        $first = $product->media()->orderBy('position')->first();
        if ($first) {
            $product->update(['image' => basename($first->url)]);
        }
        return response()->json(['message' => 'Media reordered']);
    }

    /** Upload a digital file for digital listings */
    public function uploadDigitalFile(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);
        $data = $request->validate([
            'digital_file'      => 'nullable|file|max:51200',
            'external_url'      => 'nullable|url|max:2048',
            'filename'          => 'nullable|string|max:190',
            'delivery_method'   => ['nullable', Rule::in([
                DigitalFile::SOURCE_UPLOAD,
                DigitalFile::SOURCE_EXTERNAL_URL,
            ])],
        ]);

        $method = (string) ($data['delivery_method'] ?? ($request->hasFile('digital_file')
            ? DigitalFile::SOURCE_UPLOAD
            : DigitalFile::SOURCE_EXTERNAL_URL));

        if ($method === DigitalFile::SOURCE_EXTERNAL_URL) {
            $url = $this->normalizeDigitalExternalUrl($data['external_url'] ?? null);
            if (! $url) {
                return response()->json(['message' => 'Provide a valid external download URL.'], 422);
            }

            $df = $product->digitalFiles()->create([
                'filename'     => !empty($data['filename']) ? $data['filename'] : $this->defaultExternalDigitalFilename($product, $url),
                'source_type'  => DigitalFile::SOURCE_EXTERNAL_URL,
                'external_url' => $url,
            ]);

            return response()->json(['message' => 'Digital link saved', 'file' => $df]);
        }

        if (! $request->hasFile('digital_file')) {
            return response()->json(['message' => 'Provide a digital file or switch to an external link.'], 422);
        }

        $file = $request->file('digital_file');
        $path = $file->store('digital-files', 'private');
        $df = $product->digitalFiles()->create([
            'filename'    => $file->getClientOriginalName(),
            'filepath'    => $path,
            'disk'        => 'private',
            'filesize'    => (int) $file->getSize(),
            'filetype'    => $file->getClientMimeType(),
            'source_type' => DigitalFile::SOURCE_UPLOAD,
        ]);

        return response()->json(['message' => 'Digital file uploaded', 'file' => $df]);
    }

    /** Save variations: types/options and variants */
    public function saveVariations(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);
        $data = $request->validate([
            'types' => 'nullable|array',
            'types.*.id' => 'nullable|integer|exists:variation_types,id',
            'types.*.name' => 'required|string|max:120',
            'types.*.options' => 'nullable|array',
            'types.*.options.*.id' => 'nullable|integer|exists:variation_options,id',
            'types.*.options.*.value' => 'required_without:types.*.options.*.id|string|max:120',

            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:variants,id',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.option_ids' => 'required|array|min:1',
            'variants.*.option_ids.*' => 'integer|exists:variation_options,id',
        ]);

        // Upsert types/options
        $optionIdMap = [];
        foreach ($data['types'] ?? [] as $t) {
            $type = isset($t['id']) ? VariationType::where('product_id', $product->id)->findOrFail($t['id'])
                                    : VariationType::create(['product_id' => $product->id, 'name' => $t['name']]);
            if (!empty($t['name']) && $type->name !== $t['name']) {
                $type->name = $t['name'];
                $type->save();
            }
            foreach ($t['options'] ?? [] as $opt) {
                if (!empty($opt['id'])) {
                    $optionIdMap[$opt['id']] = $opt['id'];
                    continue;
                }
                $o = VariationOption::create(['variation_type_id' => $type->id, 'value' => $opt['value']]);
                $optionIdMap[$o->id] = $o->id;
            }
        }

        // Upsert variants
        foreach ($data['variants'] ?? [] as $v) {
            $variant = isset($v['id']) ? Variant::where('product_id', $product->id)->findOrFail($v['id'])
                                       : new Variant(['product_id' => $product->id]);
            $variant->price = $v['price'];
            $variant->stock = $v['stock'] ?? null;
            $variant->save();
            // Attach options
            $variant->options()->sync($v['option_ids']);
        }

        return response()->json(['message' => 'Variations saved']);
    }

    /** Update shipping profile via JSON rules */
    public function updateShipping(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);

        $shopId = $product->shop_id ?? optional($request->user())->shop_id;
        if (!$shopId) return response()->json(['message' => 'Shop not resolved for this product.'], 403);

        $validated = $request->validate([
            'profile_name'           => ['nullable','string','max:100'],
            'set_default'            => ['nullable','boolean'],
            'country_id'             => ['required','integer','exists:countries,id'],
            'origin_postal_code'     => ['required','string','max:50'],
            'processing_time_id'     => ['nullable','in:,"",1,2,3,4,5,custom'],
            'processing_custom_min'  => ['nullable','integer','min:1'],
            'processing_custom_max'  => ['nullable','integer','min:1'],
            'shipping_rules_json'    => ['nullable','string'],
        ]);

        $profileName = trim($validated['profile_name'] ?? '') ?: 'Standard shipping';

        $processingTimeId = $validated['processing_time_id'] ?? null;
        $procMin = null; $procMax = null;
        if ($processingTimeId === 'custom') {
            $processingTimeId = null;
            $procMin = $validated['processing_custom_min'] ?? null;
            $procMax = $validated['processing_custom_max'] ?? null;
            if (!$procMin || !$procMax) {
                return response()->json(['message' => 'Provide both min and max days for custom processing time.'], 422);
            }
        }

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

        $rawRules = [];
        if (!empty($validated['shipping_rules_json'])) {
            $tmp = json_decode($validated['shipping_rules_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                $rawRules = array_values($tmp);
            } else {
                return response()->json(['message' => 'Invalid shipping rules JSON.'], 422);
            }
        }

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
                'dest_country_id'    => $locType === 'country' ? (!empty($r['country_id']) ? (int)$r['country_id'] : null) : null,
                'service'            => $r['service'] ?? 'Other',
                'days_min'           => isset($r['days_min']) ? (int)$r['days_min'] : null,
                'days_max'           => isset($r['days_max']) ? (int)$r['days_max'] : null,
                'charge_type'        => $chargeType,
                'base_rate'          => $baseRate,
                'additional_rate'    => $addRate,
            ];
        });

        $badRange = $rows->contains(fn($r) => $r['days_min'] && $r['days_max'] && $r['days_max'] < $r['days_min']);
        if ($badRange) {
            return response()->json(['message' => 'Fix delivery time ranges where max < min.'], 422);
        }
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
            DB::table('shipping_profiles')
                ->where('shop_id', $shopId)
                ->where('product_id', $product->id)
                ->where('profile_name', $profileName)
                ->delete();

            $now = now();
            $common = [
                'shop_id'               => $shopId,
                'product_id'            => $product->id,
                'profile_name'          => $profileName,
                'name'                  => $profileName,
                'is_default'            => (bool)request()->boolean('set_default'),
                'country_id'            => (int)$validated['country_id'],
                'origin_postal_code'    => $validated['origin_postal_code'],
                'processing_time_id'    => $processingTimeId ? (int)$processingTimeId : null,
                'processing_custom_min' => $procMin,
                'processing_custom_max' => $procMax,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
            $payload = $rows->map(fn($r) => array_merge($common, [
                'dest_location_type' => $r['dest_location_type'],
                'dest_country_id'    => $r['dest_country_id'],
                'service'            => $r['service'],
                'days_min'           => $r['days_min'],
                'days_max'           => $r['days_max'],
                'charge_type'        => $r['charge_type'],
                'base_rate'          => $r['base_rate'],
                'additional_rate'    => $r['additional_rate'],
            ]))->toArray();
            DB::table('shipping_profiles')->insert($payload);

            if (\Illuminate\Support\Facades\Schema::hasColumn('shipping_profiles','is_default') && request()->boolean('set_default')) {
                DB::table('shipping_profiles')
                    ->where('shop_id', $shopId)
                    ->where('product_id', $product->id)
                    ->where('profile_name', '<>', $profileName)
                    ->update(['is_default' => false, 'updated_at' => $now]);
            }
        });

        return response()->json(['message' => 'Shipping profile saved']);
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        $user = $request->user();
        abort_if(!$user || !$user->shop || $user->shop->id !== $product->shop_id, 403, 'Unauthorized');
    }

    private function normalizeDigitalExternalUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'external_url' => 'Digital link must start with http:// or https://.',
            ]);
        }

        return $url;
    }

    private function defaultExternalDigitalFilename(Product $product, string $url): string
    {
        $host = preg_replace('/^www\./i', '', (string) parse_url($url, PHP_URL_HOST));
        $base = trim((string) ($product->name ?: 'Digital download'));

        return trim($base . ' link' . ($host ? ' (' . $host . ')' : ''));
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
            'media',
            'shop:id,user_id,name,logo',
            'category:id,listing_type',
        ]);

        $data = $this->serializePublicProduct($product, true);

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

    /**
     * @return array<string, mixed>
     */
    private function serializePublicProduct(Product $product, bool $includeMedia = false): array
    {
        $data = $product->toArray();
        $effectiveType = product_effective_type($product) ?? ($product->type ?: Product::TYPE_PHYSICAL);

        $data['type'] = $effectiveType;
        $data['effective_type'] = $effectiveType;
        $data['thumbnail_url'] = product_raw_thumb_url($product);
        $data['preview_thumbnail_url'] = product_thumb_url($product);
        $data['preview_image_url'] = product_preview_image_url($product);
        $data['is_digital_preview'] = $effectiveType === Product::TYPE_DIGITAL;

        if ($includeMedia) {
            $data['media'] = $product->media->map(function ($media) use ($product) {
                return [
                    'id' => $media->id,
                    'type' => $media->type,
                    'url' => $media->url,
                    'preview_url' => product_media_preview_url($product, $media, 'display'),
                    'thumbnail_url' => product_media_preview_url($product, $media, 'thumb'),
                ];
            })->values();
        }

        return $data;
    }
}
