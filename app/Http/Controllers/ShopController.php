<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\PaymentMethod;
use App\Models\Country;
use App\Models\Activity;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

class ShopController extends Controller
{
    /**
     * Show the "Create Your Shop" form, or redirect if the user already has a shop.
     */
    public function create()
    {
        if (auth()->user()->shop) {
            return redirect()->route('seller.shops.show', auth()->user()->shop->slug)
                ->with('info', 'You already have a shop.');
        }

        $countries = Country::all();

        return view('shops.create', compact('countries'));
    }


    /**
     * Validate and store a new shop.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $requireLogoOnSignup = function_exists('setting_bool')
            ? setting_bool('seller_signup_require_logo', false)
            : false;

        $data = $request->validate([
            // 1) Shop preferences
            'language'         => 'required|string|in:English,Swahili',
            'country'          => 'required|string|exists:countries,id',
            'currency'         => 'required|string',

            // 2) Name & slug
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:shops,slug',
            'bio'              => 'nullable|string|max:1000',
            'translations'     => 'nullable|array',
            'translations.name' => 'nullable|array',
            'translations.name.*' => 'nullable|string|max:255',
            'translations.bio' => 'nullable|array',
            'translations.bio.*' => 'nullable|string',
            'translations.announcement' => 'nullable|array',
            'translations.announcement.*' => 'nullable|string',
            'translations.policies' => 'nullable|array',
            'translations.policies.*' => 'nullable|string',

            // 4) Billing info
            'address'          => 'required|string|max:255',
            'city'             => 'required|string|max:100',
            'postal'           => 'required|string|max:20',

            // Shop logo can be configured from Admin and added later by the seller.
            'logo'             => ($requireLogoOnSignup ? 'required' : 'nullable') . '|image|max:2048',
            'featured_image'   => 'nullable|image|max:2048',
        ], [
            'logo.required'    => 'Please upload your shop logo.',
        ]);

        // Auto-generate slug if blank
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (Shop::where('slug', $data['slug'])->exists()) {
                $data['slug'] .= '-' . time();
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('shops/logos', 'public');
        }

        // Handle featured image upload
        if ($request->file('featured_image')) {
            $path = $request->file('featured_image')->store('shops/featured_images', 'public');
            $data['featured_image'] = $path;
        }


        // We don't persist the password field
        unset($data['password']);
        $translations = normalize_translation_payload($data['translations'] ?? [], [
            'name',
            'bio',
            'announcement',
            'policies',
        ]);
        unset($data['translations']);

        // Ensure enable_2fa is boolean (checkbox may be absent)
        $data['enable_2fa'] = !empty($data['enable_2fa']);

        // Create the shop via the one-to-one relationship
        $shop = $user->shop()->create($data);
        $shop->fillTranslations($translations);
        $shop->save();

        // Optional auto-approval for seller onboarding (removes manual admin approval bottleneck).
        $autoApproveSellerSignups = function_exists('setting_bool')
            ? setting_bool('seller_signup_auto_approve', (bool) env('SELLER_SIGNUP_AUTO_APPROVE', true))
            : (bool) env('SELLER_SIGNUP_AUTO_APPROVE', true);
        if ($user->isSeller() && $autoApproveSellerSignups && !((bool) $user->is_active)) {
            $user->forceFill(['is_active' => true])->save();
        }

        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('end_date')
            ->first();
        if ($subscription && empty($subscription->shop_id)) {
            $subscription->shop_id = $shop->id;
            $subscription->save();
        }

        // Create activity record for the seller
        Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'You created a new shop',
            'type' => \App\Models\Activity::TYPE_SHOP,
            'related_id' => $shop->id,
            'related_type' => 'shop'
        ]);

        return redirect()
            ->route('seller.shops.show', $shop)
            ->with('success', 'Your shop has been created!');
    }

    /**
     * Display a single shop by slug.
     */
    public function show(Shop $shop)
    {
        $paymentMethods = PaymentMethod::where('shop_id', $shop->id)->get();
        $subscription = $shop->user->subscription;
        // Holiday mode logic
        $activeProducts = $shop->products()->where('is_active', 1)->count();
        $pausedProducts = $shop->products()->where('is_active', 2)->count();
        $isHolidayMode = (bool) ($shop->is_holiday_mode ?? false);
        return view('shops.show', compact('shop', 'paymentMethods', 'subscription', 'isHolidayMode', 'activeProducts', 'pausedProducts'));
    }

    /**
     * Display a paginated list of all shops on the marketplace.
     */
    public function publicIndex(Request $request)
    {
        $query = Shop::query()
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest();

        if ($request->filled('q')) {
            $search = $request->q;
            $terms = collect(preg_split('/\s+/', trim((string) $search), -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn ($term) => trim($term))
                ->filter()
                ->values();

            $searchTerms = $terms->isEmpty() ? collect([$search]) : $terms;

            $query->where(function (Builder $q) use ($searchTerms) {
                foreach ($searchTerms as $index => $term) {
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';

                    $q->{$method}(
                        searchable_content_expression([
                            'name',
                            'slug',
                            'bio',
                            'announcement',
                            'name_translations',
                            'bio_translations',
                            'announcement_translations',
                        ]) . ' LIKE ?',
                        [search_like_value((string) $term)]
                    );
                }
            });
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        $shops = $query->paginate(12);

        $countries = Country::orderBy('name')->get()->keyBy('id');

        return themed_view('shops', compact('shops', 'countries'));
    }


    public function showPublic(Request $request, $id)
    {
        // Accept either slug or numeric ID for backward compatibility
        $shop = Shop::whereSlug($id)->first();
        $foundByNumericId = false;

        if (!$shop && ctype_digit((string) $id)) {
            $shop = Shop::findOrFail((int) $id);
            $foundByNumericId = true;
        }

        abort_if(! $shop, 404);
        abort_if(! $shop->isActive(), 404);

        if ($foundByNumericId) {
            $canonicalSlug = trim((string) ($shop->slug ?? ''));
            abort_if($canonicalSlug === '', 404);

            return redirect()->to(localized_route('shop.show', $canonicalSlug), 301);
        }

        $products = $shop->products()
            ->where('is_active', 1)
            ->with([
                'media',
                'shop' => function ($q) {
                    $q->withCount('reviews')->withAvg('reviews', 'rating');
                },
            ])
            ->latest()
            ->paginate(12);

        if ($request->ajax()) {
            return view('theme.' . theme() . '.partials.shop-products', compact('products'))->render();
        }

        return themed_view('shop', compact('shop', 'products'));
    }






    /**
     * Show the edit form.
     */
    public function edit(Shop $shop)
    {
        $countries = Country::all();

        return view('shops.edit', compact('shop', 'countries'));
    }

    /**
     * Persist updates from the edit form.
     */
    public function update(Request $request, Shop $shop)
    {
        // Authorization - ensure user can only edit their own shop
        if (Auth::id() !== $shop->user_id) {
            abort(403, 'You can only edit your own shop.');
        }

        // Validate just the editable fields
        $data = $request->validate([
            'language'       => 'required|string|in:English,Swahili',
            'country'        => 'required|string|exists:countries,id',
            'currency'       => 'required|string',
            'name'           => 'required|string|max:255',
            'slug'           => 'required|string|max:255|unique:shops,slug,' . $shop->id,
            'bio'            => 'nullable|string|max:1000',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'postal'         => 'required|string|max:20',
            'password'       => ['required', 'current_password'],
            'enable_2fa'     => ['required', 'boolean'],
            'logo'           => ['nullable', 'image', 'max:2048'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'announcement'   => ['nullable', 'string'],
            'policies'       => ['nullable', 'string'],
            'translations'   => ['nullable', 'array'],
            'translations.name' => ['nullable', 'array'],
            'translations.name.*' => ['nullable', 'string', 'max:255'],
            'translations.bio' => ['nullable', 'array'],
            'translations.bio.*' => ['nullable', 'string'],
            'translations.announcement' => ['nullable', 'array'],
            'translations.announcement.*' => ['nullable', 'string'],
            'translations.policies' => ['nullable', 'array'],
            'translations.policies.*' => ['nullable', 'string'],
        ], [
            'logo.required'  => 'Please upload your shop logo.',
        ]);

        // Slug uniqueness fallback if someone cleared it
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (Shop::where('slug', $data['slug'])->where('id', '!=', $shop->id)->exists()) {
                $data['slug'] .= '-' . time();
            }
        }

        // Logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('shops/logos', 'public');
        }

        // Featured image upload
        if ($request->file('featured_image')) {
            $path = $request->file('featured_image')->store('shops/featured_images', 'public');
            $data['featured_image'] = $path;
        }

        // Remove password from data
        unset($data['password']);
        $translations = normalize_translation_payload($data['translations'] ?? [], [
            'name',
            'bio',
            'announcement',
            'policies',
        ]);
        unset($data['translations']);

        // Update the model
        $shop->update($data);
        $shop->fillTranslations($translations);
        $shop->save();

        return redirect()
            ->route('seller.shops.show', $shop)
            ->with('success', 'Shop updated successfully!');
    }
}
