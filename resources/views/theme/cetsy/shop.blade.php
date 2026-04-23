@extends('theme.'.theme().'.layouts.app')

@php
  use Illuminate\Support\Str;

  $shopImage = $shop->featured_image_url
    ?? $shop->logo_url
    ?? (setting('favicon_url') ?: asset('assets/images/cetsylogmain.png'));

  $localizedShopName = $shop->localized_name ?? $shop->name;
  $localizedShopBio = $shop->localized_bio ?? $shop->bio;
  $localizedShopAnnouncement = $shop->localized_announcement ?? $shop->announcement;
  $localizedShopPolicies = $shop->localized_policies ?? $shop->policies;

  $shopDescription = Str::limit(strip_tags($localizedShopBio ?? $localizedShopAnnouncement ?? ($localizedShopName . ' shop on Cetsy')), 155);
  $shopRouteParam = $shop->slug ?: $shop->id;

  $totalSales = $shop->orders()->whereIn('status', [
    \App\Models\Order::STATUS_COMPLETED,
    \App\Models\Order::STATUS_DELIVERED,
  ])->count();

  $totalProducts = $shop->products()->where('is_active', true)->count();
  $memberSince = $shop->created_at->diffForHumans();
  $averageRating = (float) ($shop->reviews()->avg('rating') ?? 0);
  $reviewCount = (int) $shop->reviews()->count();

  $reviews = $shop->reviews()
    ->with(['user', 'orderItem.product.media'])
    ->latest()
    ->take(10)
    ->get();

  $shopUrl = localized_route('shop.show', $shopRouteParam);
  $shopListItems = $products->getCollection()
    ->take(24)
    ->values()
    ->map(function ($product, $index) {
      $listingUrl = localized_route('listing.show', $product->slug);

      return [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => $listingUrl,
        'item' => [
          '@type' => 'Product',
          'name' => $product->localized_name ?? $product->name,
          'url' => $listingUrl,
          'image' => product_thumb_url($product),
        ],
      ];
    })
    ->all();

  $shopSchema = [
    '@type' => 'Store',
    '@id' => $shopUrl . '#shop',
    'name' => $localizedShopName,
    'url' => $shopUrl,
    'image' => $shopImage ?: asset('assets/images/cetsylogmain.png'),
    'description' => $shopDescription,
    'address' => [
      '@type' => 'PostalAddress',
      'addressCountry' => country_name($shop->country) ?: 'Global',
    ],
  ];

  if ($reviewCount > 0 && $averageRating > 0) {
    $shopSchema['aggregateRating'] = [
      '@type' => 'AggregateRating',
      'ratingValue' => round($averageRating, 1),
      'reviewCount' => $reviewCount,
      'bestRating' => '5',
      'worstRating' => '1',
    ];
  }

  $shopStructuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
      $shopSchema,
      [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
          [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => url('/'),
          ],
          [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Shops',
            'item' => localized_route('shops.index'),
          ],
          [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $localizedShopName,
            'item' => $shopUrl,
          ],
        ],
      ],
      [
        '@type' => 'ItemList',
        'name' => $localizedShopName . ' listings',
        'url' => $shopUrl,
        'numberOfItems' => $products->total(),
        'itemListElement' => $shopListItems,
      ],
    ],
  ];
@endphp

@section('title', $localizedShopName . ' | Shop on Cetsy')
@section('meta_description', $shopDescription)
@section('canonical_url', localized_route('shop.show', $shopRouteParam))
@section('meta_image', $shopImage)
@section('meta_robots', 'index, follow')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode($shopStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('styles')
<style>
  .shop-tab-btn.is-active {
    border-color: rgb(16 185 129);
    color: rgb(5 150 105);
    background: rgb(236 253 245);
  }

  .view-toggle-btn.is-active {
    background: rgb(5 150 105);
    color: #fff;
  }

  .shop-product-item {
    transition: transform .2s ease, box-shadow .2s ease;
  }

  .shop-product-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
  }
  @media (max-width: 430px) {
    .shop-grid-compact {
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 0.5rem;
    }
  }
</style>
@endpush

@section('main')
<div class="relative overflow-x-clip pb-10">
  <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full bg-emerald-200/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 top-[30rem] h-72 w-72 rounded-full bg-sky-200/35 blur-3xl"></div>

  <section class="relative border-b border-slate-200 bg-white py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-start">
        <div class="flex items-start gap-4">
          <img
            src="{{ $shop->logo ? ($shop->logo_url ?? asset('storage/' . $shop->logo)) : (setting('favicon_url') ?: asset('assets/images/cetsylogmain.png')) }}"
            alt="{{ $localizedShopName }} logo"
            class="h-20 w-20 rounded-full border border-slate-200 object-cover shadow-sm"
            onerror='this.onerror=null;this.src=@json(asset("assets/images/cetsylogmain.png"));'
          >

          <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $localizedShopName }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ country_name($shop->country) }}</p>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-600 sm:text-sm">
              <span class="inline-flex items-center gap-1.5"><i class="fas fa-shopping-bag text-emerald-600"></i> {{ $totalSales }} {{ Str::plural('sale', $totalSales) }}</span>
              <span class="inline-flex items-center gap-1.5"><i class="fas fa-box text-sky-600"></i> {{ $totalProducts }} {{ Str::plural('item', $totalProducts) }}</span>
              <span class="inline-flex items-center gap-1.5"><i class="fas fa-calendar text-indigo-600"></i> Since {{ $memberSince }}</span>
              @if($reviewCount)
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-star text-amber-500"></i> {{ number_format($averageRating, 1) }} ({{ $reviewCount }})</span>
              @endif
            </div>

            <a href="#reviews" class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600 hover:text-emerald-700">
              <span class="inline-flex items-center text-amber-500">
                @for($i=1; $i<=5; $i++)
                  @if($i <= floor($averageRating))
                    <i class="fas fa-star"></i>
                  @elseif($i - $averageRating < 1)
                    <i class="fas fa-star-half-alt"></i>
                  @else
                    <i class="far fa-star text-slate-300"></i>
                  @endif
                @endfor
              </span>
              <span class="font-semibold text-slate-900">{{ $reviewCount ? number_format($averageRating, 1) : 'No reviews yet' }}</span>
              @if($reviewCount)
                <span class="text-slate-500">({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
              @endif
            </a>
          </div>
        </div>

        <div class="flex items-center justify-start gap-2 lg:justify-end">
          @if(Auth::id() === $shop->user_id)
            <a href="{{ route('seller.shops.edit', $shop) }}" class="inline-flex items-center rounded-xl border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
              <i class="fas fa-edit mr-2"></i> Edit Shop
            </a>
          @else
            <button type="button" data-open-message class="inline-flex items-center rounded-xl border border-sky-300 px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50">
              <i class="fas fa-comment mr-2"></i> Message Seller
            </button>

            <details class="relative">
              <summary class="inline-flex cursor-pointer list-none items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fas fa-share-alt"></i>
              </summary>
              <div class="absolute right-0 z-20 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-lg">
                <button type="button" class="block w-full px-3 py-2 text-left text-slate-700 hover:bg-slate-50" onclick="shareOn('facebook')"><i class="fab fa-facebook mr-2"></i>Facebook</button>
                <button type="button" class="block w-full px-3 py-2 text-left text-slate-700 hover:bg-slate-50" onclick="shareOn('twitter')"><i class="fab fa-twitter mr-2"></i>Twitter</button>
                <button type="button" class="block w-full px-3 py-2 text-left text-slate-700 hover:bg-slate-50" onclick="shareOn('whatsapp')"><i class="fab fa-whatsapp mr-2"></i>WhatsApp</button>
                <hr class="my-1 border-slate-200">
                <button type="button" class="block w-full px-3 py-2 text-left text-slate-700 hover:bg-slate-50" onclick="copyShopUrl('{{ localized_route('shop.show', $shopRouteParam) }}')"><i class="fas fa-link mr-2"></i>Copy Link</button>
              </div>
            </details>
          @endif
        </div>
      </div>
    </div>
  </section>

  <section class="border-b border-slate-200 bg-white">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex flex-wrap gap-2 py-3">
        @foreach(['items' => 'Items', 'reviews' => 'Reviews', 'about' => 'About', 'policies' => 'Policies'] as $id => $label)
          <button
            type="button"
            class="shop-tab-btn {{ $loop->first ? 'is-active' : '' }} rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition"
            data-target="{{ $id }}"
          >
            {{ $label }}
          </button>
        @endforeach
      </div>
    </div>
  </section>

  @if($localizedShopAnnouncement)
    <section class="bg-slate-50 pt-4">
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
          <i class="fas fa-bullhorn mr-2"></i>{!! $localizedShopAnnouncement !!}
        </div>
      </div>
    </section>
  @endif

  @if(session('success'))
    <section class="bg-slate-50 pt-4">
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
      </div>
    </section>
  @endif

  @if($shop->featured_image)
    <section class="bg-slate-50 py-4">
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <img
          src="{{ $shop->featured_image_url ?? asset('storage/' . $shop->featured_image) }}"
          alt="Featured image for {{ $localizedShopName }}"
          class="h-48 w-full rounded-2xl border border-slate-200 object-cover shadow-sm md:h-72"
        >
      </div>
    </section>
  @endif

  <section class="bg-slate-50 py-5">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div id="items" class="shop-tab-panel space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="grid gap-2 sm:grid-cols-3">
              <label class="text-xs font-semibold text-slate-600">
                <span class="mb-1 block">Price</span>
                <select id="priceFilter" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none">
                  <option value="">All</option>
                  <option value="0-10">Under $10</option>
                  <option value="10-25">$10-$25</option>
                  <option value="25-50">$25-$50</option>
                  <option value="50-100">$50-$100</option>
                  <option value="100+">Over $100</option>
                </select>
              </label>

              <label class="text-xs font-semibold text-slate-600">
                <span class="mb-1 block">Type</span>
                <select id="typeFilter" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none">
                  <option value="">All Types</option>
                  <option value="physical">Products</option>
                  <option value="digital">Digital</option>
                  <option value="service">Services</option>
                </select>
              </label>

              <label class="text-xs font-semibold text-slate-600">
                <span class="mb-1 block">Sort By</span>
                <select id="sortFilter" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none">
                  <option value="newest">Newest</option>
                  <option value="price-low">Price: Low to High</option>
                  <option value="price-high">Price: High to Low</option>
                  <option value="rating">Top Rated</option>
                </select>
              </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 lg:justify-end">
              <span class="text-xs text-slate-500 sm:text-sm">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}</span>
              <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 bg-white p-1">
                <button type="button" class="view-toggle-btn is-active inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm" data-view="grid" aria-label="Grid view">
                  <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="view-toggle-btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm text-slate-700 hover:bg-slate-100" data-view="list" aria-label="List view">
                  <i class="fas fa-bars"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div id="gridView" class="shop-grid-compact grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-4">
          @forelse($products as $product)
            <div class="product-item shop-product-item" data-price="{{ (float) ($product->price ?? 0) }}" data-type="{{ $product->type }}" data-rating="{{ $shop->reviews_avg_rating ?? ($shop->average_rating ?? 0) }}">
              @include('theme.'.theme().'.partials.product-card', ['item' => $product])
            </div>
          @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">
              <i class="fas fa-info-circle mb-2 block text-2xl text-slate-400"></i>
              No products listed.
            </div>
          @endforelse
        </div>

        <div id="listView" class="hidden space-y-3">
          @foreach($products as $product)
            @php
              $thumbUrl = product_thumb_url($product);
              $isDigitalPreview = product_is_digital($product);
            @endphp

            <article class="product-item product-item-list shop-product-item flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3" data-price="{{ (float) ($product->price ?? 0) }}" data-type="{{ $product->type }}" data-rating="{{ $shop->reviews_avg_rating ?? ($shop->average_rating ?? 0) }}">
              <div class="relative overflow-hidden rounded-xl border border-slate-200 {{ $isDigitalPreview ? 'cetsy-preview-watermark' : '' }}"
                   @if($isDigitalPreview) data-watermark-label="Cetsy Preview" @endif>
                <img src="{{ $thumbUrl }}" alt="{{ $product->localized_name ?? $product->name }}" class="h-20 w-20 object-cover">
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="line-clamp-1 text-sm font-semibold text-slate-900">{{ $product->localized_name ?? $product->name }}</h3>
                <p class="mt-1 text-sm font-bold text-emerald-700">{{ money((float) $product->price, null) }}</p>
              </div>
              <button type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500" onclick="addToCart({{ $product->id }})">
                <i class="fas fa-cart-plus"></i>
              </button>
            </article>
          @endforeach
        </div>

        @if($products->hasMorePages())
          <div class="text-center">
            <button id="loadMore" class="rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" data-next-page-url="{{ $products->nextPageUrl() }}">
              Load More
            </button>
          </div>
        @endif
      </div>
      <div id="reviews" class="shop-tab-panel hidden">
        <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
          <div class="space-y-3">
            <h2 class="text-lg font-bold text-slate-900">Reviews</h2>
            @forelse($reviews as $review)
              <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                  <div class="text-amber-500">
                    @for($i=1; $i<=5; $i++)
                      <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star"></i>
                    @endfor
                  </div>
                  <span class="font-semibold text-slate-900">{{ $review->user->name }}</span>
                  <span class="text-slate-500">{{ $review->created_at->diffForHumans() }}</span>
                </div>

                @if($review->orderItem && $review->orderItem->product)
                  @php
                    $reviewProduct = $review->orderItem->product;
                    $reviewThumb = product_thumb_url($reviewProduct);
                  @endphp

                  <div class="mt-3 flex items-center gap-2">
                    <img src="{{ $reviewThumb }}" alt="{{ $reviewProduct->localized_name ?? $reviewProduct->name }} thumbnail" class="h-12 w-12 rounded-lg border border-slate-200 object-cover">
                    <a href="{{ localized_route('listing.show', $reviewProduct->slug ?? $reviewProduct->id) }}" class="text-sm font-medium text-slate-700 hover:text-emerald-700">{{ $reviewProduct->localized_name ?? $reviewProduct->name }}</a>
                  </div>
                @endif

                @if($review->comment)
                  <p class="mt-3 text-sm text-slate-600">{{ $review->comment }}</p>
                @endif

                @if(!empty($review->seller_response))
                  <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                      <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-700"><i class="fa fa-reply mr-1"></i>Seller reply</span>
                      @if($review->seller_responded_at)
                        <span class="text-slate-500">{{ $review->seller_responded_at->diffForHumans() }}</span>
                      @endif
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $review->seller_response }}</p>
                  </div>
                @endif

                @if(!empty($review->image_path))
                  <a href="{{ asset('storage/' . ltrim($review->image_path, '/')) }}" target="_blank" rel="noopener" class="mt-3 inline-block">
                    <img src="{{ asset('storage/' . ltrim($review->image_path, '/')) }}" alt="Review photo" class="h-32 w-32 rounded-xl border border-slate-200 object-cover">
                  </a>
                @endif
              </article>
            @empty
              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-6 text-sm text-slate-500">
                <i class="fas fa-info-circle mr-1"></i> No reviews yet.
              </div>
            @endforelse
          </div>

          <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
            <p class="text-4xl font-extrabold text-amber-500">{{ number_format($averageRating, 1) }}</p>
            <div class="mt-2 text-amber-500">
              @for($i=1; $i<=5; $i++)
                <i class="fa{{ $i <= floor($averageRating) ? 's' : 'r' }} fa-star"></i>
              @endfor
            </div>
            <p class="mt-2 text-sm text-slate-500">{{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</p>
          </aside>
        </div>
      </div>

      <div id="about" class="shop-tab-panel hidden">
        <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
          <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900">About This Shop</header>
            <div class="prose prose-sm max-w-none px-4 py-4 text-slate-700">
              {!! $localizedShopBio ?: 'No description provided.' !!}
            </div>
          </article>

          <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Shop Details</h3>
            <div class="mt-3 space-y-2 text-sm text-slate-600">
              <p><strong class="text-slate-900">Language:</strong> {{ $shop->language ?? 'N/A' }}</p>
              <p><strong class="text-slate-900">Country:</strong> {{ country_name($shop->country) }}</p>
              <p><strong class="text-slate-900">Currency:</strong> {{ $shop->currency ?? 'N/A' }}</p>
              <p class="flex items-center gap-2">
                <strong class="text-slate-900">Shop URL:</strong>
                <button type="button" class="rounded-lg border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" onclick="copyShopUrl('{{ localized_route('shop.show', $shopRouteParam) }}')" aria-label="Copy shop URL">
                  <i class="fas fa-link"></i>
                </button>
              </p>
            </div>
          </article>
        </div>

        <article class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
          <header class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900">Billing Address</header>
          <div class="px-4 py-4 text-sm text-slate-600">
            <p>{{ $shop->address ?? 'N/A' }}</p>
            <p>{{ $shop->city }}{{ $shop->postal ? ', ' . $shop->postal : '' }}</p>
          </div>
        </article>
      </div>

      <div id="policies" class="shop-tab-panel hidden">
        @if($localizedShopPolicies)
          <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900">Shop Policies</header>
            <div class="prose prose-sm max-w-none px-4 py-4 text-slate-700">
              {!! $localizedShopPolicies !!}
            </div>
          </article>
        @else
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-6 text-sm text-slate-500">
            <i class="fas fa-info-circle mr-1"></i> No policies available.
          </div>
        @endif
      </div>
    </div>
  </section>
</div>

@if(Auth::id() !== $shop->user_id)
<div id="messageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="messageModalLabel">
  <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="receiver_id" value="{{ $shop->user_id }}">
      <input type="hidden" name="product_id" value="">

      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 class="text-base font-semibold text-slate-900" id="messageModalLabel">Message Seller - {{ $localizedShopName }}</h2>
        <button type="button" data-close-message class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" aria-label="Close message dialog">Close</button>
      </div>

      <div class="space-y-3 px-4 py-4">
        <label for="messageBody" class="block text-sm font-semibold text-slate-700">Your message</label>
        <textarea id="messageBody" name="message" rows="4" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"></textarea>

        <div>
          <label for="messageAttachment" class="mb-1 block text-sm font-semibold text-slate-700">Attachment (optional)</label>
          <input type="file" name="attachment" id="messageAttachment" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700">
          <p class="mt-1 text-xs text-slate-500">Images or PDF, max 5MB.</p>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
        <button type="button" data-close-message class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Send Message</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = Array.from(document.querySelectorAll('.shop-tab-btn'));
    const tabPanels = Array.from(document.querySelectorAll('.shop-tab-panel'));

    function activateTab(targetId, pushHash = true) {
      tabButtons.forEach(btn => {
        const active = btn.dataset.target === targetId;
        btn.classList.toggle('is-active', active);
      });

      tabPanels.forEach(panel => {
        panel.classList.toggle('hidden', panel.id !== targetId);
      });

      if (pushHash) {
        history.replaceState(null, '', `#${targetId}`);
      }
    }

    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        activateTab(btn.dataset.target);
      });
    });

    const initialHash = window.location.hash ? window.location.hash.replace('#', '') : '';
    if (initialHash && tabPanels.some(panel => panel.id === initialHash)) {
      activateTab(initialHash, false);
    }

    const priceFilter = document.getElementById('priceFilter');
    const sortFilter = document.getElementById('sortFilter');
    const typeFilter = document.getElementById('typeFilter');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const viewButtons = Array.from(document.querySelectorAll('.view-toggle-btn'));

    function allItems() {
      return Array.from(document.querySelectorAll('.product-item'));
    }

    function matchesPriceRange(price, rangeValue) {
      if (!rangeValue) return true;
      if (rangeValue === '100+') return price >= 100;

      const parts = rangeValue.split('-').map(x => parseFloat(x));
      if (parts.length !== 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return true;
      return price >= parts[0] && price <= parts[1];
    }

    function applyFilters() {
      const priceRange = priceFilter ? priceFilter.value : '';
      const productType = typeFilter ? typeFilter.value : '';

      allItems().forEach(item => {
        const price = parseFloat(item.dataset.price || '0');
        const type = item.dataset.type || '';

        let show = true;
        if (!matchesPriceRange(price, priceRange)) show = false;
        if (productType && type !== productType) show = false;

        item.classList.toggle('hidden', !show);
      });
    }

    function sortContainer(container, sortBy) {
      const items = Array.from(container.querySelectorAll('.product-item'));
      items.sort((a, b) => {
        const priceA = parseFloat(a.dataset.price || '0');
        const priceB = parseFloat(b.dataset.price || '0');
        const ratingA = parseFloat(a.dataset.rating || '0');
        const ratingB = parseFloat(b.dataset.rating || '0');

        switch (sortBy) {
          case 'price-low':
            return priceA - priceB;
          case 'price-high':
            return priceB - priceA;
          case 'rating':
            return ratingB - ratingA;
          default:
            return 0;
        }
      });

      items.forEach(item => container.appendChild(item));
    }

    function applySort() {
      const sortBy = sortFilter ? sortFilter.value : 'newest';
      if (gridView) sortContainer(gridView, sortBy);
      if (listView) sortContainer(listView, sortBy);
    }

    function setView(mode) {
      if (!gridView || !listView) return;
      const showGrid = mode !== 'list';
      gridView.classList.toggle('hidden', !showGrid);
      listView.classList.toggle('hidden', showGrid);

      viewButtons.forEach(btn => {
        const active = btn.dataset.view === (showGrid ? 'grid' : 'list');
        btn.classList.toggle('is-active', active);
        btn.classList.toggle('text-slate-700', !active);
        btn.classList.toggle('hover:bg-slate-100', !active);
      });
    }

    if (priceFilter) {
      priceFilter.addEventListener('change', () => {
        applyFilters();
        applySort();
      });
    }

    if (typeFilter) {
      typeFilter.addEventListener('change', () => {
        applyFilters();
        applySort();
      });
    }

    if (sortFilter) {
      sortFilter.addEventListener('change', () => {
        applySort();
      });
    }

    viewButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        setView(btn.dataset.view);
      });
    });

    setView('grid');

    const loadMoreBtn = document.getElementById('loadMore');
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', () => {
        const nextUrl = loadMoreBtn.dataset.nextPageUrl;
        if (!nextUrl) return;

        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'Loading...';

        fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(res => res.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newGridItems = doc.querySelectorAll('#gridItems .product-item');
            const newListItems = doc.querySelectorAll('#listItems .product-item');

            newGridItems.forEach(item => gridView.appendChild(item));
            newListItems.forEach(item => listView.appendChild(item));

            const nextBtn = doc.getElementById('loadMore');
            if (nextBtn && nextBtn.dataset.nextPageUrl) {
              loadMoreBtn.dataset.nextPageUrl = nextBtn.dataset.nextPageUrl;
              loadMoreBtn.disabled = false;
              loadMoreBtn.textContent = 'Load More';
            } else {
              loadMoreBtn.remove();
            }

            applyFilters();
            applySort();
          })
          .catch(() => {
            loadMoreBtn.disabled = false;
            loadMoreBtn.textContent = 'Load More';
          });
      });
    }

    const messageModal = document.getElementById('messageModal');
    const openMessage = document.querySelector('[data-open-message]');
    const closeMessageButtons = Array.from(document.querySelectorAll('[data-close-message]'));

    function closeMessageModal() {
      if (!messageModal) return;
      messageModal.classList.add('hidden');
      messageModal.classList.remove('flex');
    }

    function openMessageModal() {
      if (!messageModal) return;
      messageModal.classList.remove('hidden');
      messageModal.classList.add('flex');
    }

    if (openMessage) {
      openMessage.addEventListener('click', openMessageModal);
    }

    closeMessageButtons.forEach(btn => btn.addEventListener('click', closeMessageModal));

    if (messageModal) {
      messageModal.addEventListener('click', event => {
        if (event.target === messageModal) {
          closeMessageModal();
        }
      });
    }

    window.shareOn = platform => {
      const url = encodeURIComponent(window.location.href);
      const text = encodeURIComponent('Check out this shop on Cetsy!');
      const routes = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
        twitter: `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
        whatsapp: `https://wa.me/?text=${text}%20${url}`,
      };

      if (routes[platform]) {
        window.open(routes[platform], '_blank', 'noopener');
      }
    };

    window.copyShopUrl = async url => {
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(url);
        } else {
          const input = document.createElement('input');
          input.value = url;
          document.body.appendChild(input);
          input.select();
          document.execCommand('copy');
          document.body.removeChild(input);
        }
        alert('Shop URL copied!');
      } catch (error) {
        alert('Copy failed, please copy manually.');
      }
    };

    window.addToCart = () => {
      alert('Add to cart functionality will be implemented here');
    };
  });
</script>
@endpush
