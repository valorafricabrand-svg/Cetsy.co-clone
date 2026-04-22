@extends('theme.'.theme().'.layouts.app')

@php
  $q       = request('q');
  $sort    = request('sort', 'latest');
  $type    = request('type');
  $perPage = (int) request('per_page', 24);
  $view    = request('view', 'grid');
  $isSearchRoute = request()->routeIs('search') || request()->is('search');
  $hasListingQuery = request()->query() !== [];
  $listingsCanonicalUrl = route('listings');
  $listingsMetaRobots = ($isSearchRoute || $hasListingQuery) ? 'noindex, follow' : 'index, follow';
@endphp

@section('title', 'Marketplace - Products, Services and Digital Goods | Cetsy')
@section('meta_description', 'Browse marketplace listings for handmade products, services, and digital goods on Cetsy.')
@section('canonical_url', $listingsCanonicalUrl)
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', $listingsMetaRobots)

@push('styles')
<style>
  .listings-toolbar {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
  }
  .listings-chip-close {
    font-size: 13px;
    line-height: 1;
  }
  .listings-empty {
    border: 2px dashed rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.04);
  }
  @media (max-width: 430px) {
    .listings-grid-compact {
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 0.5rem;
    }
  }
</style>
@endpush

@section('main')
<div class="relative overflow-x-clip pb-10">
  <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full bg-emerald-200/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 top-[24rem] h-72 w-72 rounded-full bg-rose-200/35 blur-3xl"></div>

  <section class="relative bg-gradient-to-b from-emerald-900 to-emerald-700 py-12 text-white">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid items-center gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">
            <i class="fas fa-store mr-1"></i> Marketplace
          </p>
          <h1 class="mt-2 text-4xl font-extrabold leading-tight md:text-5xl">All Listings</h1>
          <p class="mt-3 max-w-2xl text-sm text-emerald-50/95 md:text-base">
            Browse our global marketplace for physical products, professional services, and instant digital downloads.
          </p>
        </div>

        <form method="GET" action="{{ url()->current() }}" role="search" class="rounded-2xl bg-white/95 p-3 shadow-xl">
          <label for="listingsHeroSearch" class="sr-only">Search listings</label>
          <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
              <i class="fas fa-search"></i>
            </span>
            <input id="listingsHeroSearch" type="search" name="q" value="{{ $q }}" placeholder="Search listings, brands or shops" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none" autocomplete="on">
            <button class="rounded-full bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500" type="submit">Search</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <section class="listings-toolbar sticky top-0 z-20 border-b border-slate-200 py-3">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <form method="GET" action="{{ url()->current() }}" id="filtersForm" class="space-y-3">
        <div class="grid gap-2 md:grid-cols-12 md:items-center">
          <div class="md:col-span-4">
            <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2">
              <i class="fas fa-search text-slate-400"></i>
              <input type="search" name="q" value="{{ $q }}" placeholder="Search listings..." aria-label="Search listings" class="min-w-0 w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none" enterkeyhint="search">
              <button type="submit" class="shrink-0 rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500" aria-label="Search listings">
                Search
              </button>
            </div>
          </div>

          <div class="md:col-span-2">
            <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none" name="type" aria-label="Filter by type">
              <option value="">All types</option>
              <option value="physical" {{ ($type==='physical' || $type==='product' || $type==='products')?'selected':'' }}>Products</option>
              <option value="service"  {{ ($type==='service'  || $type==='services')?'selected':'' }}>Services</option>
              <option value="digital"  {{ $type==='digital'?'selected':'' }}>Digital</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none" name="sort" aria-label="Sort by">
              <option value="latest"     {{ $sort==='latest'?'selected':'' }}>Newest</option>
              <option value="popular"    {{ $sort==='popular'?'selected':'' }}>Popular</option>
              <option value="price_asc"  {{ $sort==='price_asc'?'selected':'' }}>Price: Low to High</option>
              <option value="price_desc" {{ $sort==='price_desc'?'selected':'' }}>Price: High to Low</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none" name="per_page" aria-label="Items per page">
              @foreach([12,24,48] as $n)
                <option value="{{ $n }}" {{ $perPage===$n?'selected':'' }}>{{ $n }} / page</option>
              @endforeach
            </select>
          </div>

          <div class="md:col-span-2 md:text-right">
            <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 bg-white p-1">
              <button type="button" class="view-toggle-btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm {{ $view==='grid' ? 'bg-emerald-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}" data-view="grid" title="Grid view" aria-label="Grid view">
                <i class="fas fa-th-large"></i>
              </button>
              <button type="button" class="view-toggle-btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm {{ $view==='list' ? 'bg-emerald-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}" data-view="list" title="List view" aria-label="List view">
                <i class="fas fa-bars"></i>
              </button>
            </div>
            <input type="hidden" name="view" value="{{ $view }}">
          </div>
        </div>
      </form>

      <div class="mt-2 flex flex-wrap items-center gap-2">
        @if($q)
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
            <i class="fas fa-search text-slate-400"></i> "{{ $q }}"
            <a href="{{ request()->fullUrlWithQuery(['q'=>null,'page'=>null]) }}" class="listings-chip-close text-slate-400 hover:text-slate-700" aria-label="Clear search">&times;</a>
          </span>
        @endif

        @if($type)
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
            <i class="fas fa-filter text-slate-400"></i> {{ ucfirst($type) }}
            <a href="{{ request()->fullUrlWithQuery(['type'=>null,'page'=>null]) }}" class="listings-chip-close text-slate-400 hover:text-slate-700" aria-label="Clear type">&times;</a>
          </span>
        @endif

        @if($sort && $sort!=='latest')
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
            <i class="fas fa-sort-amount-down text-slate-400"></i>
            @switch($sort)
              @case('popular') Popular @break
              @case('price_asc') Price: Low to High @break
              @case('price_desc') Price: High to Low @break
              @default Newest
            @endswitch
            <a href="{{ request()->fullUrlWithQuery(['sort'=>'latest','page'=>null]) }}" class="listings-chip-close text-slate-400 hover:text-slate-700" aria-label="Reset sort">&times;</a>
          </span>
        @endif

        @if($q || $type || ($sort && $sort!=='latest') || $perPage!==24 || $view!=='grid')
          <a href="{{ url()->current() }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-600">Clear all</a>
        @endif
      </div>
    </div>
  </section>

  <section class="bg-slate-50 py-5">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-3 flex items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
          Showing <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong>
          of <strong>{{ $products->total() }}</strong> listings
        </p>
        @if($products->total() > 0)
          <a href="{{ route('listings') }}" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
            <i class="fas fa-undo mr-1"></i> Reset
          </a>
        @endif
      </div>

      @if($view === 'grid')
        <div id="listing-items" class="listings-grid-compact grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-4">
          @forelse ($products as $item)
            <div>
              @include('theme.'.theme().'.partials.product-card', ['item' => $item])
              @if(($item->type ?? '') === 'physical' && (int)($item->stock ?? 0) === 1 && ($item->is_reserved ?? false))
                <div class="mt-2 text-xs text-red-600">Reserved in another pending order</div>
              @endif
            </div>
          @empty
            <div class="listings-empty col-span-full rounded-2xl p-10 text-center text-slate-500">
              <i class="fas fa-box-open mb-3 block text-3xl text-slate-400"></i>
              <p class="mb-1 text-sm">No listings match your filters.</p>
              <a href="{{ url()->current() }}" class="mt-3 inline-flex rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Clear Filters</a>
            </div>
          @endforelse
        </div>
      @else
        <div id="listing-items" class="space-y-3">
            @forelse ($products as $item)
              @php
                $thumb = product_thumb_url($item);
                $effectiveType = product_effective_type($item);
                $isDigitalPreview = ($effectiveType === 'digital');

                $mediaItems = $item->media ?? collect();
                $firstImage = optional($mediaItems)->first(function ($media) {
                $url = (string) ($media->url ?? '');
                if ($url === '') return false;
                $type = strtolower((string) ($media->type ?? ''));
                if ($type === 'image') return true;
                if ($type === 'video') return false;
                return function_exists('is_video_media_path') ? !is_video_media_path($url) : true;
              });
              $firstVideo = optional($mediaItems)->first(function ($media) {
                $url = (string) ($media->url ?? '');
                if ($url === '') return false;
                $type = strtolower((string) ($media->type ?? ''));
                if ($type === 'video') return true;
                return function_exists('is_video_media_path') ? is_video_media_path($url) : false;
              });

              $shop = $item->shop ?? null;
              $avg  = round((float) ($shop?->reviews_avg_rating ?? ($shop ? $shop->reviews()->avg('rating') : 0)));
              $cnt  = (int) ($shop?->reviews_count ?? ($shop ? $shop->reviews()->count() : 0));
              $basePrice  = $item->price;
              $finalPrice = $item->discounted_price;
              $lowestVariantPrice = collect($item->variations ?? [])
                ->filter(fn ($variant) => ($variant->options->count() ?? 0) > 0)
                ->whereNotNull('price')
                ->min('price');

              $featuredMedia = (string) ($item->featured_image ?? '');
              $featuredIsVideo = ($featuredMedia !== '')
                  && function_exists('is_video_media_path')
                  && is_video_media_path($featuredMedia);

              $hasVideo = (bool) $firstVideo || $featuredIsVideo;
              $vid = null;
              if ($firstVideo && !empty($firstVideo->url) && !$firstImage) {
                $vid = media_url($firstVideo->url);
              } elseif ($featuredIsVideo && !$firstImage) {
                $vid = media_url($featuredMedia);
              }
            @endphp

              <article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="grid items-center gap-3 sm:grid-cols-[120px_1fr_auto]">
                  <a href="{{ route('listing.show', $item->slug) }}"
                     class="relative block overflow-hidden rounded-xl bg-slate-100 {{ $isDigitalPreview ? 'cetsy-preview-watermark' : '' }}"
                     @if($isDigitalPreview) data-watermark-label="Cetsy Preview" @endif>
                    @if($hasVideo)
                      <span class="absolute left-2 top-2 z-10 rounded-md bg-slate-900/75 px-2 py-0.5 text-[10px] font-semibold text-white"><i class="fas fa-play mr-1 text-[9px]"></i>Video</span>
                    @endif
                  <img src="{{ $thumb }}"
                       alt="{{ $item->name }}"
                       class="aspect-square h-full w-full object-cover"
                       loading="lazy" decoding="async"
                       @if($vid) data-video-src="{{ $vid }}" style="opacity:.01;filter:blur(8px);transition:opacity .35s ease,filter .35s ease;" @endif>
                </a>

                <div>
                  <h3 class="text-sm font-semibold text-slate-900">
                    <a href="{{ route('listing.show', $item->slug) }}" class="hover:text-emerald-700">{{ $item->name ?? 'Untitled item' }}</a>
                  </h3>

                  <div class="mt-1 text-xs text-amber-500">
                    @for($i=1; $i<=5; $i++)
                      <i class="fa-star{{ $i <= $avg ? ' fa-solid' : ' fa-regular text-slate-300' }}"></i>
                    @endfor
                    @if($cnt) <span class="ml-1 text-slate-400">({{ $cnt }})</span>@endif
                  </div>

                  @if(!empty($item->short_description))
                    <p class="mt-2 text-sm text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags($item->short_description), 120) }}</p>
                  @endif
                </div>

                <div class="text-left sm:text-right">
                  @if(!is_null($lowestVariantPrice))
                    <div class="mb-2">
                      <div class="text-[11px] uppercase tracking-[0.12em] text-slate-400">From</div>
                      <span class="text-base font-bold text-emerald-700">{{ money($lowestVariantPrice) }}</span>
                    </div>
                  @elseif(isset($finalPrice, $basePrice) && is_numeric($finalPrice) && is_numeric($basePrice) && $finalPrice < $basePrice)
                    <div class="mb-2 flex items-baseline gap-2 sm:justify-end">
                      <span class="text-base font-bold text-emerald-700">{{ money($finalPrice) }}</span>
                      <span class="text-xs text-slate-400 line-through">{{ money($basePrice) }}</span>
                    </div>
                  @elseif(isset($basePrice))
                    <p class="mb-2 text-base font-bold text-emerald-700">{{ money($basePrice) }}</p>
                  @else
                    <p class="mb-2 text-xs text-slate-400">Contact for price</p>
                  @endif

                  <a href="{{ route('listing.show', $item->slug) }}" class="inline-flex rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">
                    <i class="fas fa-eye mr-1"></i> View
                  </a>
                </div>
              </div>
            </article>
          @empty
            <div class="listings-empty rounded-2xl p-10 text-center text-slate-500">
              <i class="fas fa-box-open mb-3 block text-3xl text-slate-400"></i>
              <p class="mb-1 text-sm">No listings match your filters.</p>
              <a href="{{ url()->current() }}" class="mt-3 inline-flex rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Clear Filters</a>
            </div>
          @endforelse
        </div>
      @endif

      @if ($products->hasMorePages())
        <div class="mt-5 text-center">
          <button id="load-more" class="rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500" data-next-page="{{ $products->currentPage() + 1 }}">
            Load More
          </button>
        </div>
      @endif
    </div>
  </section>

  @include('theme.'.theme().'.partials.product-carousel', [
      'items' => $recommendedProducts ?? collect(),
      'title' => 'Because you viewed similar items',
      'subtitle' => 'Hand-picked from categories and styles you\'ve been browsing.',
      'eyebrow' => 'Recommended',
      'eyebrowIcon' => 'fa-wand-magic-sparkles',
      'seeMoreUrl' => route('listings'),
      'seeMoreLabel' => 'Keep exploring'
  ])
</div>
@endsection

@push('scripts')
<script>
(function(){
  if (window.__videoThumbInitListings) return;
  window.__videoThumbInitListings = true;

  function toFirstFrame(img){
    var src = img.getAttribute('data-video-src');
    if(!src) return;
    try{
      var v = document.createElement('video');
      v.preload = 'metadata';
      v.muted = true;
      v.playsInline = true;
      v.src = src + '#t=0.1';
      v.addEventListener('loadeddata', function(){
        try{
          var w = v.videoWidth || 480, h = v.videoHeight || 270;
          var c = document.createElement('canvas'); c.width = w; c.height = h;
          c.getContext('2d').drawImage(v,0,0,w,h);
          img.src = c.toDataURL('image/jpeg',0.8);
          img.style.opacity='1';
          img.style.filter='none';
          img.removeAttribute('data-video-src');
        }catch(e){}
      }, {once:true});
    }catch(e){}
  }

  function init(){
    var imgs = document.querySelectorAll('img[data-video-src]');
    if (!('IntersectionObserver' in window)) { imgs.forEach(toFirstFrame); return; }
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){ toFirstFrame(entry.target); io.unobserve(entry.target); }
      });
    }, { rootMargin:'200px' });
    imgs.forEach(function(img){ io.observe(img); });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filtersForm');
  if (!form) return;

  const viewInput = form.querySelector('input[name="view"]');
  const searchInput = form.querySelector('input[type="search"][name="q"]');
  const submitFilters = () => {
    const pageInput = form.querySelector('input[name="page"]');
    if (pageInput) pageInput.value = 1;
    if (typeof form.requestSubmit === 'function') form.requestSubmit();
    else form.submit();
  };

  document.querySelectorAll('.view-toggle-btn[data-view]').forEach(btn => {
    btn.addEventListener('click', () => {
      viewInput.value = btn.getAttribute('data-view');
      submitFilters();
    });
  });

  form.querySelectorAll('select').forEach(sel => {
    sel.addEventListener('change', () => {
      submitFilters();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('keydown', event => {
      if (event.key !== 'Enter') return;
      event.preventDefault();
      submitFilters();
    });
  }

  const loadMoreBtn = document.getElementById('load-more');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
      const nextPage = loadMoreBtn.dataset.nextPage;
      const url = new URL(window.location.href);
      url.searchParams.set('page', nextPage);
      loadMoreBtn.disabled = true;

      fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newContainer = doc.getElementById('listing-items');
          const container = document.getElementById('listing-items');
          if (!newContainer || !container) {
            loadMoreBtn.remove();
            return;
          }

          Array.from(newContainer.children).forEach(el => container.appendChild(el));

          const newBtn = doc.getElementById('load-more');
          if (newBtn) {
            loadMoreBtn.dataset.nextPage = newBtn.dataset.nextPage;
            loadMoreBtn.disabled = false;
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
