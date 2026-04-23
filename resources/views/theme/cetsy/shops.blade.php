@extends('theme.'.theme().'.layouts.app')

@section('title', 'All Shops')
@section('meta_description', 'Browse shops on Cetsy and discover creators offering handmade products, services, and digital goods.')
@section('canonical_url', route('shops.index'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', request()->query() ? 'noindex, follow' : 'index, follow')

@php
  $shopsUrl = route('shops.index');
  $shopListItems = $shops->getCollection()
    ->take(24)
    ->values()
    ->map(function ($shop, $index) use ($countries) {
      $shopRouteParam = $shop->slug ?: $shop->id;
      $shopUrl = route('shop.show', $shopRouteParam);
      $shopImage = $shop->logo
        ? ($shop->logo_url ?? asset('storage/' . ltrim($shop->logo, '/')))
        : (setting('favicon_url') ?: asset('assets/images/cetsylogmain.png'));

      return [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => $shopUrl,
        'item' => [
          '@type' => 'Store',
          'name' => $shop->localized_name ?? $shop->name,
          'url' => $shopUrl,
          'image' => $shopImage,
          'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => optional($countries->get($shop->country))->name ?: 'Global',
          ],
        ],
      ];
    })
    ->all();

  $shopsStructuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'CollectionPage',
        '@id' => $shopsUrl . '#webpage',
        'name' => 'All Shops',
        'description' => 'Browse shops on Cetsy and discover creators offering handmade products, services, and digital goods.',
        'url' => $shopsUrl,
      ],
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
            'item' => $shopsUrl,
          ],
        ],
      ],
      [
        '@type' => 'ItemList',
        'name' => 'Cetsy shops',
        'url' => $shopsUrl,
        'numberOfItems' => $shops->total(),
        'itemListElement' => $shopListItems,
      ],
    ],
  ];
@endphp

@push('structured-data')
<script type="application/ld+json">
{!! json_encode($shopsStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('styles')
<style>
  .shops-toolbar {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
  }

  .shops-empty {
    border: 2px dashed rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.04);
  }
  @media (max-width: 430px) {
    .shops-grid-compact {
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 0.5rem;
    }
  }
</style>
@endpush

@section('main')
@php
  $q = request('q');
  $country = request('country');
@endphp

<div class="relative overflow-x-clip pb-10">
  <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full bg-emerald-200/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 top-[26rem] h-72 w-72 rounded-full bg-cyan-200/35 blur-3xl"></div>

  <section class="relative bg-gradient-to-b from-emerald-900 to-emerald-700 py-12 text-white">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid items-center gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">
            <i class="fas fa-store mr-1"></i> Shops
          </p>
          <h1 class="mt-2 text-4xl font-extrabold leading-tight md:text-5xl">All Shops</h1>
          <p class="mt-3 max-w-2xl text-sm text-emerald-50/95 md:text-base">
            Discover trusted Cetsy shops from around the world and explore their latest listings.
          </p>
        </div>

        <form method="GET" action="{{ url()->current() }}" role="search" class="rounded-2xl bg-white/95 p-3 shadow-xl">
          <input type="hidden" name="country" value="{{ $country }}">
          <label for="shopsSearch" class="sr-only">Search shops</label>
          <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
              <i class="fas fa-search"></i>
            </span>
            <input
              id="shopsSearch"
              type="search"
              name="q"
              class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none"
              placeholder="Search shops, brands or owners"
              aria-label="Search shops"
              value="{{ $q }}"
              autocomplete="on"
            >
            <button class="rounded-full bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500" type="submit">
              Search
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <section class="shops-toolbar sticky top-0 z-20 border-b border-slate-200 py-3">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <form method="GET" action="{{ url()->current() }}" class="grid gap-2 md:grid-cols-12 md:items-center">
        <div class="hidden md:col-span-7 md:block">
          <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2">
            <i class="fas fa-search text-slate-400"></i>
            <input type="search" name="q" value="{{ $q }}" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none" placeholder="Search shops...">
          </div>
        </div>

        <div class="col-span-7 md:col-span-3">
          <select name="country" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none" aria-label="Filter shops by country">
            <option value="">All countries</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}" {{ (string) $country === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-span-5 md:col-span-2">
          <button type="submit" class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Apply</button>
        </div>
      </form>

      <div class="mt-2 flex flex-wrap items-center gap-2">
        @if($q)
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
            <i class="fas fa-search text-slate-400"></i> "{{ $q }}"
            <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}" class="text-slate-400 hover:text-slate-700" aria-label="Clear search">&times;</a>
          </span>
        @endif

        @if($country && $countries->has($country))
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
            <i class="fas fa-globe text-slate-400"></i> {{ $countries->get($country)->name }}
            <a href="{{ request()->fullUrlWithQuery(['country' => null, 'page' => null]) }}" class="text-slate-400 hover:text-slate-700" aria-label="Clear country">&times;</a>
          </span>
        @endif

        @if($q || $country)
          <a href="{{ url()->current() }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-600">Clear all</a>
        @endif
      </div>
    </div>
  </section>

  <section class="bg-slate-50 py-5">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="mb-3 text-sm text-slate-500">
        Showing <strong>{{ $shops->firstItem() ?? 0 }}-{{ $shops->lastItem() ?? 0 }}</strong>
        of <strong>{{ $shops->total() }}</strong> shops
      </p>

      <div id="shopsList" class="shops-grid-compact grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4">
        @forelse($shops as $shop)
          @php
            $shopLogo = $shop->logo ? ($shop->logo_url ?? asset('storage/' . ltrim($shop->logo, '/'))) : setting('favicon_url');
            $countryName = optional($countries->get($shop->country))->name;
          @endphp
          <article class="group rounded-xl border border-slate-200 bg-white p-2 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:rounded-2xl sm:p-3">
            <a href="{{ route('shop.show', $shop->slug) }}" class="block">
              <img
                src="{{ $shopLogo }}"
                alt="{{ $shop->localized_name ?? $shop->name }} logo"
                class="mx-auto h-14 w-14 rounded-full border border-slate-200 object-cover sm:h-20 sm:w-20"
                loading="lazy"
                decoding="async"
              >
              <h3 class="mt-2 line-clamp-1 text-[12px] font-semibold text-slate-900 group-hover:text-emerald-700 sm:mt-3 sm:text-sm">{{ $shop->localized_name ?? $shop->name }}</h3>
              <p class="mt-1 hidden text-xs text-slate-500 sm:block">
                <span class="font-semibold text-amber-500"><i class="fas fa-star"></i> {{ number_format($shop->reviews_avg_rating ?? 0, 1) }}</span>
                <span>({{ $shop->reviews_count }})</span>
              </p>
              <p class="mt-1 line-clamp-1 text-[11px] text-slate-500 sm:text-xs">{{ $countryName ?: 'Global' }}</p>
              <span class="mt-2 inline-flex rounded-full border border-slate-200 px-2.5 py-1 text-[10px] font-semibold text-slate-700 transition group-hover:border-emerald-300 group-hover:text-emerald-700 sm:mt-3 sm:px-3 sm:text-[11px]">
                Visit Shop
              </span>
            </a>
          </article>
        @empty
          <div class="shops-empty col-span-full rounded-2xl p-10 text-center text-slate-500">
            <i class="fas fa-store-slash mb-3 block text-3xl text-slate-400"></i>
            <p class="mb-1 text-sm">No shops found.</p>
            <a href="{{ url()->current() }}" class="mt-3 inline-flex rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Clear Filters</a>
          </div>
        @endforelse
      </div>

      @if($shops->hasMorePages())
        <div class="mt-5 text-center">
          <button id="load-more" class="rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500" data-next-page="{{ $shops->currentPage() + 1 }}">Load More</button>
        </div>
      @endif
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const loadMoreBtn = document.getElementById('load-more');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
      const nextPage = loadMoreBtn.dataset.nextPage;
      const url = new URL(window.location.href);
      url.searchParams.set('page', nextPage);
      loadMoreBtn.disabled = true;
      loadMoreBtn.textContent = 'Loading...';

      fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const nextContainer = doc.getElementById('shopsList');
          const container = document.getElementById('shopsList');
          if (!nextContainer || !container) {
            loadMoreBtn.remove();
            return;
          }

          Array.from(nextContainer.children).forEach(el => container.appendChild(el));

          const newBtn = doc.getElementById('load-more');
          if (newBtn) {
            loadMoreBtn.dataset.nextPage = newBtn.dataset.nextPage;
            loadMoreBtn.disabled = false;
            loadMoreBtn.textContent = 'Load More';
          } else {
            loadMoreBtn.remove();
          }
        })
        .catch(() => loadMoreBtn.remove());
    });
  }
});
</script>
@endpush
