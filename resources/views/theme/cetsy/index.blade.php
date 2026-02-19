@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy | Handmade products, services, and digital goods')
@section('meta_description', 'Discover handmade products, services, and digital goods from creators across Africa on Cetsy.')
@section('canonical_url', route('home'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/default-og-image-cetsy.jpg'))
@section('meta_robots', 'index, follow')

@push('styles')
<style>
    .home-hero-slide { display: none; }
    .home-hero-slide.is-active { display: block; }
    .hide-scrollbar { scrollbar-width: none; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
</style>
@endpush

@section('main')
@php
    use Illuminate\Support\Str;

    $topCategories = ($categories instanceof \Illuminate\Support\Collection)
        ? $categories->take(6)
        : collect($categories ?? [])->take(6);

    $heroImageFallback = asset('assets/images/illustrator.webp');
    $productThumbFallback = asset('assets/images/default-og-image-cetsy.jpg');
    $slides = isset($heroSlides) && $heroSlides instanceof \Illuminate\Support\Collection
        ? $heroSlides
        : collect();

    $topShops = ($shops instanceof \Illuminate\Support\Collection)
        ? $shops->take(8)
        : collect($shops ?? [])->take(8);

    $renderProductCard = function ($item) {
        $basePrice = (float) ($item->price ?? 0);
        $salePrice = (float) ($item->discounted_price ?? $basePrice);
        $isService = strtolower((string) ($item->type ?? '')) === 'service';
        $thumb = product_thumb_url($item);

        $shop = $item->shop;
        $shopAvg = $shop ? ($shop->reviews_avg_rating ?? null) : null;
        $shopCount = $shop ? ($shop->reviews_count ?? null) : null;
        $avg = max(0, min(5, (int) round((float) ($shopAvg ?? 0))));
        $reviewsCnt = (int) ($shopCount ?? 0);

        return compact('basePrice', 'salePrice', 'isService', 'thumb', 'avg', 'reviewsCnt');
    };
@endphp

<div class="relative overflow-x-clip pb-10">
    <div class="pointer-events-none absolute -right-32 -top-40 h-96 w-96 rounded-full bg-emerald-200/40 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-28 top-[28rem] h-80 w-80 rounded-full bg-rose-200/40 blur-3xl"></div>

    <section class="relative mx-auto w-full max-w-7xl px-4 pb-4 pt-5 sm:px-6 lg:px-8">
        @if($slides->isNotEmpty())
            <div class="relative" data-home-hero>
                @foreach($slides as $index => $slide)
                    @php
                        $tag = $slide->tag ?: 'Save';
                        $title = $slide->title;
                        $sub = $slide->subtitle ?: 'Discover limited-time offers across the Cetsy marketplace.';
                        $btnLabel = $slide->button_label ?: 'Shop deals';
                        $btnUrl = $slide->resolved_button_url;
                        $slideImagePath = (string) ($slide->image_path ?? '');
                        if ($slideImagePath !== '' && Str::startsWith($slideImagePath, ['http://', 'https://', '//', 'data:'])) {
                            $img = $slideImagePath;
                        } elseif ($slideImagePath !== '') {
                            $img = media_url($slideImagePath);
                        } else {
                            $img = $heroImageFallback;
                        }
                    @endphp
                    <article class="home-hero-slide {{ $index === 0 ? 'is-active' : '' }} rounded-3xl bg-gradient-to-br from-white via-rose-500 to-red-600 p-4 shadow-2xl sm:p-6 lg:p-8" data-home-hero-slide="{{ $index }}">
                        <div class="grid items-center gap-6 lg:grid-cols-2">
                            <div class="text-center lg:text-left">
                                <span class="inline-flex items-center rounded-xl bg-white px-3 py-1 text-xs font-extrabold uppercase tracking-[0.14em] text-red-600">{{ $tag }}</span>
                                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">{{ $title }}</h1>
                                <p class="mt-3 max-w-xl text-sm text-white/90 sm:text-base">{{ $sub }}</p>
                                <div class="mt-5 flex flex-wrap items-center justify-center gap-2 lg:justify-start">
                                    <a href="{{ $btnUrl }}" class="rounded-full bg-white px-5 py-2.5 text-sm font-bold text-red-600 hover:bg-slate-100">
                                        <i class="fas fa-tags mr-1"></i> {{ $btnLabel }}
                                    </a>
                                    <a href="{{ route('listings') }}" class="rounded-full border border-white/50 px-5 py-2.5 text-sm font-semibold text-white hover:border-white hover:bg-white/10">Browse marketplace</a>
                                </div>

                                <form class="mx-auto mt-4 max-w-xl lg:mx-0 lg:max-w-none lg:hidden" method="GET" action="{{ route('search') }}">
                                    <label for="heroSearchMobile" class="sr-only">Search products</label>
                                    <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2 shadow">
                                        <i class="fas fa-search text-slate-400"></i>
                                        <input id="heroSearchMobile" type="search" name="q" value="{{ request('q') }}" placeholder="Search products, brands and shops" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                                        <button class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500" type="submit">Search</button>
                                    </div>
                                </form>
                            </div>
                            <div class="text-center">
                                <img src="{{ $img }}" alt="{{ $title }}" class="mx-auto max-h-[360px] w-auto rounded-2xl shadow-2xl" onerror="this.onerror=null;this.src=@json($heroImageFallback);">
                            </div>
                        </div>
                    </article>
                @endforeach

                @if($slides->count() > 1)
                    <button class="absolute left-4 top-1/2 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white text-red-600 shadow hover:bg-slate-100 md:flex" data-home-hero-prev type="button" aria-label="Previous slide">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="absolute right-4 top-1/2 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white text-red-600 shadow hover:bg-slate-100 md:flex" data-home-hero-next type="button" aria-label="Next slide">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="mt-3 flex items-center justify-center gap-2" data-home-hero-dots>
                        @foreach($slides as $index => $slide)
                            <button type="button" class="h-2.5 rounded-full transition-all {{ $index === 0 ? 'w-7 bg-slate-900' : 'w-2.5 bg-slate-300' }}" data-home-hero-dot="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <article class="rounded-3xl bg-gradient-to-br from-white via-rose-500 to-red-600 p-4 shadow-2xl sm:p-6 lg:p-8">
                <div class="grid items-center gap-6 lg:grid-cols-2">
                    <div class="text-center lg:text-left">
                        <span class="inline-flex items-center rounded-xl bg-white px-3 py-1 text-xs font-extrabold uppercase tracking-[0.14em] text-red-600">Save</span>
                        <h1 class="mt-3 text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">Shop our lowest prices on selected items</h1>
                        <p class="mt-3 max-w-xl text-sm text-white/90 sm:text-base">Discover limited-time offers across electronics, services, and more from trusted Cetsy sellers.</p>
                        <div class="mt-5 flex flex-wrap items-center justify-center gap-2 lg:justify-start">
                            <a href="{{ route('listings', ['sort' => 'popular']) }}" class="rounded-full bg-white px-5 py-2.5 text-sm font-bold text-red-600 hover:bg-slate-100">
                                <i class="fas fa-tags mr-1"></i> Shop deals
                            </a>
                            <a href="{{ route('listings') }}" class="rounded-full border border-white/50 px-5 py-2.5 text-sm font-semibold text-white hover:border-white hover:bg-white/10">Browse marketplace</a>
                        </div>
                    </div>
                    <div class="text-center">
                        <img src="{{ $heroImageFallback }}" alt="Featured Cetsy deals" class="mx-auto max-h-[360px] w-auto rounded-2xl shadow-2xl" onerror="this.onerror=null;this.src=@json(asset('assets/images/default-og-image-cetsy.jpg'));">
                    </div>
                </div>
            </article>
        @endif
    </section>

    <section class="mx-auto w-full max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
        <div class="grid gap-3 md:grid-cols-3">
            <a href="{{ url('/user-agreement#privacy') }}" class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50">
                <i class="fas fa-lock"></i>
                Buyer/Seller Protection & Secure Payments
            </a>
            <a href="{{ url('/user-agreement#buyer-tips') }}" class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50">
                <i class="fas fa-truck"></i>
                Global Shipping & Local Sellers
            </a>
            <a href="{{ route('listings', ['sort' => 'popular']) }}" class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50">
                <i class="fas fa-star"></i>
                Curated Trending Picks Daily
            </a>
        </div>
    </section>

    <section class="mx-auto w-full max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Global Payments & Withdrawals</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-slate-900">How Cetsy Payments Flow</h2>
                    <p class="mt-2 max-w-3xl text-sm text-slate-600">
                        Buyer payments are collected via Paystack, seller earnings reflect in Cetsy Wallet, and withdrawals are processed
                        through approved channels such as SWIFT, Wise, PayPal, or local bank transfer depending on country availability.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-credit-card mr-1.5 text-emerald-700"></i>Paystack</span>
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-building-columns mr-1.5 text-emerald-700"></i>SWIFT</span>
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-globe mr-1.5 text-emerald-700"></i>Wise</span>
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fab fa-paypal mr-1.5 text-emerald-700"></i>PayPal</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('payment_policy') }}" class="inline-flex items-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        <i class="fas fa-circle-info mr-2"></i> View Payout Countries & Methods
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between md:p-6">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="fas fa-tags"></i>
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">{{ isset($activeDeals) && $activeDeals->count() ? 'Today\'s highlighted deals' : 'Today\'s highlighted' }}</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Deals & Inspiration</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ isset($activeDeals) && $activeDeals->count() ? 'Save on limited-time offers picked from our marketplace.' : 'Hand-picked offers and ideas to get you started.' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(isset($activeDeals) && $activeDeals->count())
                    @foreach($activeDeals as $deal)
                        <a href="{{ route('listings', ['deal' => $deal->id]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
                            <span>{{ $deal->name }}</span>
                            @if($deal->discount_percent)
                                <span class="text-slate-400">{{ $deal->discount_percent }}% off</span>
                            @endif
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('listings', ['sort' => 'popular']) }}" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">Top picks</a>
                    <a href="{{ route('listings', ['type' => 'digital']) }}" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">Digital deals</a>
                    <a href="{{ route('listings', ['type' => 'service']) }}" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">Service bundles</a>
                @endif
                <a href="{{ route('listings') }}" class="rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500">View all deals</a>
            </div>
        </div>
    </section>

    @php
        $sections = [
            [
                'title' => 'Popular Items',
                'subtitle' => 'Trending picks from trusted sellers across the marketplace.',
                'eyebrow' => 'Hot right now',
                'items' => $featuredProducts,
                'seeMoreUrl' => route('listings', ['sort' => 'popular']),
                'seeMoreLabel' => 'Browse all products',
                'autoRotate' => true,
            ],
            [
                'title' => 'Just for You',
                'subtitle' => auth()->check() ? 'Curated from your favorites, orders, and recent views.' : 'Sign in to personalize picks from your favorites and recent views.',
                'eyebrow' => 'Recommended',
                'items' => $justForYouProducts ?? $featuredProducts,
                'seeMoreUrl' => route('listings', ['sort' => 'popular']),
                'seeMoreLabel' => 'See more picks',
                'autoRotate' => true,
            ],
            [
                'title' => 'Most Trending Services',
                'subtitle' => 'Recently viewed and in-demand service providers.',
                'eyebrow' => 'Services',
                'items' => $services,
                'seeMoreUrl' => route('listings', ['type' => 'service']),
                'seeMoreLabel' => 'View all services',
                'autoRotate' => true,
            ],
            [
                'title' => 'Featured Digital Downloads for You',
                'subtitle' => 'Original music, e-books, templates, recipes, and more.',
                'eyebrow' => 'Digital',
                'items' => $featuredDigitals,
                'seeMoreUrl' => route('listings', ['type' => 'digital']),
                'seeMoreLabel' => 'View all digitals',
                'autoRotate' => true,
            ],
        ];
    @endphp

    @foreach($sections as $section)
        @php
            $itemsCollection = $section['items'] instanceof \Illuminate\Support\Collection
                ? $section['items']->values()
                : collect($section['items'] ?? [])->values();
            $autoRotate = (bool) ($section['autoRotate'] ?? false);
            $chunkSize = 8;
            $pages = ($autoRotate ? $itemsCollection : $itemsCollection->take($chunkSize))
                ->chunk($chunkSize)
                ->values();
        @endphp
        @if($pages->isNotEmpty())
            <section class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $section['eyebrow'] }}</span>
                        <h2 class="mt-2 text-2xl font-extrabold text-slate-900">{{ $section['title'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $section['subtitle'] }}</p>
                    </div>
                    <a href="{{ $section['seeMoreUrl'] }}" class="hidden rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700 md:inline-flex">{{ $section['seeMoreLabel'] }}</a>
                </div>

                <div class="relative" @if($autoRotate && $pages->count() > 1) data-home-listing-rotator data-interval="5000" @endif>
                    @foreach($pages as $pageIndex => $pageItems)
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 {{ $pageIndex === 0 ? '' : 'hidden' }}" data-rotator-page="{{ $pageIndex }}">
                            @foreach($pageItems as $item)
                                @php($card = $renderProductCard($item))
                                <a href="{{ route('listing.show', $item->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                    <div class="relative aspect-square overflow-hidden bg-slate-100">
                                        @if((($item->type ?? '') === 'physical') && (int)($item->stock ?? 0) === 1 && (($item->is_reserved ?? false)) )
                                            <span class="absolute right-2 top-2 z-10 rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-semibold text-white">Reserved</span>
                                        @endif
                                        <img src="{{ $card['thumb'] }}" alt="{{ $item->name }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.03]" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=@json($productThumbFallback);">
                                    </div>
                                    <div class="flex flex-1 flex-col p-3">
                                        <h3 class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $item->name }}</h3>

                                        <div class="mt-2 flex items-center gap-1 text-[11px] text-amber-500">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa-star {{ $i <= $card['avg'] ? 'fa-solid' : 'fa-regular text-slate-300' }}"></i>
                                            @endfor
                                            @if($card['reviewsCnt'])
                                                <span class="ml-1 text-slate-400">({{ $card['reviewsCnt'] }})</span>
                                            @endif
                                        </div>

                                        <div class="mt-3">
                                            @if($card['isService'])
                                                <p class="text-[11px] uppercase tracking-[0.12em] text-slate-400">Priced From</p>
                                                <p class="text-sm font-bold text-emerald-700">{{ money($card['salePrice']) }}</p>
                                            @else
                                                @if($card['salePrice'] < $card['basePrice'])
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-sm font-bold text-emerald-700">{{ money($card['salePrice']) }}</p>
                                                        <p class="text-xs text-slate-400 line-through">{{ money($card['basePrice']) }}</p>
                                                    </div>
                                                @else
                                                    <p class="text-sm font-bold text-emerald-700">{{ money($card['basePrice']) }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endforeach

                    @if($autoRotate && $pages->count() > 1)
                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-xs font-medium text-slate-500">
                                Rotating every 5s:
                                <span class="font-semibold text-emerald-700" data-rotator-counter>1 / {{ $pages->count() }}</span>
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:border-emerald-300 hover:text-emerald-700" data-rotator-prev aria-label="Show previous items">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:border-emerald-300 hover:text-emerald-700" data-rotator-next aria-label="Show next items">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4 md:hidden">
                    <a href="{{ $section['seeMoreUrl'] }}" class="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">{{ $section['seeMoreLabel'] }}</a>
                </div>
            </section>
        @endif
    @endforeach

    @if($topShops->isNotEmpty())
        <section class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Top Sellers</span>
                    <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Featured Shops</h2>
                    <p class="mt-1 text-sm text-slate-500">Discover trusted sellers and explore their latest drops.</p>
                </div>
                <a href="{{ route('shops.index') }}" class="hidden rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700 md:inline-flex">View all shops</a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($topShops as $shop)
                    <a href="{{ route('shop.show', $shop->slug) }}" class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100">
                                <img src="{{ !empty($shop->logo_url) ? $shop->logo_url : (!empty($shop->logo) ? (Str::startsWith((string) $shop->logo, ['http://', 'https://', '//']) ? $shop->logo : media_url($shop->logo)) : (setting('favicon_url') ?: $productThumbFallback)) }}"
                                     alt="{{ $shop->name }} logo"
                                     class="h-full w-full object-cover"
                                     onerror="this.onerror=null;this.src=@json(setting('favicon_url') ?: $productThumbFallback);">
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $shop->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $shop->completed_orders_count ?? 0 }} completed orders</p>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">{{ Str::limit(strip_tags($shop->description ?? 'Explore curated items from this shop.'), 95) }}</p>
                        <div class="mt-3 flex items-center justify-between text-xs font-semibold text-emerald-700">
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5">Top rated</span>
                            <span>View shop <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4 md:hidden">
                <a href="{{ route('shops.index') }}" class="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">View all shops</a>
            </div>
        </section>
    @endif

    <section class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-8">
            <div class="mx-auto max-w-3xl text-center">
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Since 2021 - Global Marketplace</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900">Who is Cetsy?</h2>
                <p class="mt-3 text-base text-slate-600"><span class="font-semibold text-emerald-700">"Cetsy"</span> is a Malagasy word that means <em>"that’s it"</em>.</p>
                <p class="mt-2 text-sm text-slate-500 md:text-base">Your global marketplace where anyone can find almost everything from everyone, everywhere.</p>

                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700"><i class="fas fa-users mr-1"></i> 50k+ Buyers</span>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700"><i class="fas fa-store mr-1"></i> 10k+ Sellers</span>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700"><i class="fas fa-globe mr-1"></i> 80+ Countries</span>
                </div>

                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <a href="{{ url('/about') }}" class="rounded-full border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">About Cetsy</a>
                    <a href="{{ route('listings') }}" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Explore Marketplace</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Become a Seller</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const initHero = () => {
        const slider = document.querySelector('[data-home-hero]');
        if (!slider) return;

        const slides = Array.from(slider.querySelectorAll('[data-home-hero-slide]'));
        if (!slides.length) return;

        const prevBtn = slider.querySelector('[data-home-hero-prev]');
        const nextBtn = slider.querySelector('[data-home-hero-next]');
        const dots = Array.from(slider.querySelectorAll('[data-home-hero-dot]'));

        let current = 0;
        let timer = null;

        const show = (index) => {
            if (!slides[index]) return;
            slides[current].classList.remove('is-active');
            if (dots[current]) {
                dots[current].classList.remove('w-7', 'bg-slate-900');
                dots[current].classList.add('w-2.5', 'bg-slate-300');
            }

            current = index;
            slides[current].classList.add('is-active');
            if (dots[current]) {
                dots[current].classList.remove('w-2.5', 'bg-slate-300');
                dots[current].classList.add('w-7', 'bg-slate-900');
            }
        };

        const next = () => show((current + 1) % slides.length);
        const prev = () => show((current - 1 + slides.length) % slides.length);

        const startAuto = () => {
            if (slides.length < 2 || timer) return;
            timer = setInterval(next, 5000);
        };

        const stopAuto = () => {
            if (!timer) return;
            clearInterval(timer);
            timer = null;
        };

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const idx = parseInt(dot.getAttribute('data-home-hero-dot'), 10);
                if (Number.isNaN(idx)) return;
                stopAuto();
                show(idx);
                startAuto();
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                stopAuto();
                prev();
                startAuto();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                stopAuto();
                next();
                startAuto();
            });
        }

        slider.addEventListener('mouseenter', stopAuto);
        slider.addEventListener('mouseleave', startAuto);

        show(0);
        startAuto();
    };

    const initListingRotators = () => {
        const rotators = Array.from(document.querySelectorAll('[data-home-listing-rotator]'));
        if (!rotators.length) return;

        rotators.forEach((rotator) => {
            const pages = Array.from(rotator.querySelectorAll('[data-rotator-page]'));
            if (pages.length < 2) return;

            const prevBtn = rotator.querySelector('[data-rotator-prev]');
            const nextBtn = rotator.querySelector('[data-rotator-next]');
            const counter = rotator.querySelector('[data-rotator-counter]');
            const intervalAttr = parseInt(rotator.getAttribute('data-interval') || '5000', 10);
            const interval = Number.isNaN(intervalAttr) ? 5000 : Math.max(intervalAttr, 1000);

            let current = 0;
            let timer = null;

            const updateCounter = () => {
                if (counter) counter.textContent = `${current + 1} / ${pages.length}`;
            };

            const show = (index) => {
                if (!pages[index]) return;
                pages[current].classList.add('hidden');
                current = index;
                pages[current].classList.remove('hidden');
                updateCounter();
            };

            const next = () => show((current + 1) % pages.length);
            const prev = () => show((current - 1 + pages.length) % pages.length);

            const startAuto = () => {
                if (timer) return;
                timer = setInterval(next, interval);
            };

            const stopAuto = () => {
                if (!timer) return;
                clearInterval(timer);
                timer = null;
            };

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    stopAuto();
                    prev();
                    startAuto();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    stopAuto();
                    next();
                    startAuto();
                });
            }

            rotator.addEventListener('mouseenter', stopAuto);
            rotator.addEventListener('mouseleave', startAuto);
            rotator.addEventListener('touchstart', stopAuto, { passive: true });
            rotator.addEventListener('touchend', startAuto, { passive: true });

            show(0);
            startAuto();
        });
    };

    initHero();
    initListingRotators();
});
</script>
@endpush

