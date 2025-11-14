{{-- resources/views/categories/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

{{-- SEO-friendly title (overrideable) --}}
@section('title', ($category->seo_title ?? $category->name) . ' – Marketplace Category')

@section('main')
  <style>
    /* Scoped cosmetics */
    .py-6 { padding-top: 4rem; padding-bottom: 4rem; }
    .hero-mask { background: linear-gradient(180deg, rgba(15,81,50,.88), rgba(25,135,84,.9)); }

    .eyebrow{
      display:inline-flex; align-items:center; gap:.5rem; padding:.35rem .75rem;
      border-radius:999px; background: rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.25);
      font-weight:600; font-size:.85rem;
    }

    /* ✅ Keep toolbar below nav dropdown/collapse */
    .toolbar { position: sticky; top: 0; z-index: 900; background:#fff; border-bottom:1px solid rgba(0,0,0,.06); }

    /* ✅ Force navbar menus above everything on this page */
    nav.navbar { z-index: 1100; }
    .navbar .dropdown-menu { z-index: 1101; }
    .navbar-collapse { position: relative; z-index: 1102; }

    .chip{
      display:inline-flex; align-items:center; gap:.5rem; padding:.35rem .6rem;
      border-radius:999px; border:1px solid rgba(0,0,0,.12); background:#fff; font-size:.875rem;
    }
    .view-toggle .btn { border-radius:.5rem; }
    .empty-spot { border:2px dashed rgba(25,135,84,.35); border-radius:1rem; background:rgba(25,135,84,.03); }
  </style>

  {{-- =========== Category Banner =========== --}}
  @php
    $banner = $category->image
      ? asset('storage/' . $category->image)
      : asset('assets/img/default-category.jpg');

    // Decode once to avoid showing "&amp;" literally when names are stored HTML-encoded
    $catName = html_entity_decode($category->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $desc = $category->description
      ?: ('Explore a wide range of ' . $catName . ($category->listing_type ? ' ' . $category->listing_type : ' listings'));
  @endphp
  @section('title', ($category->seo_title ?? $catName) . ' – Marketplace Category')

  <section class="position-relative py-6">
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-cover bg-center" style="background-image:url('{{ $banner }}'); filter: brightness(.65);"></div>
    <div class="position-absolute top-0 start-0 w-100 h-100 hero-mask"></div>
    <div class="position-relative container text-white px-3">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <div class="mb-2">
            <span class="eyebrow"><i class="fas fa-folder-open"></i> Category</span>
          </div>
          <h1 class="display-5 fw-bold text-white mb-2">{{ $catName }}</h1>
          <p class="lead mb-3 text-white-50">{{ $desc }}</p>

          {{-- Breadcrumbs (optional) --}}
          <nav class="mt-2" aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-start">
              <li class="breadcrumb-item"><a class="link-light text-decoration-none" href="{{ url('/') }}">Home</a></li>
              <li class="breadcrumb-item"><a class="link-light text-decoration-none" href="{{ route('listings') }}">Listings</a></li>
              <li class="breadcrumb-item active text-white-50" aria-current="page">{{ $catName }}</li>
            </ol>
          </nav>
        </div>
        <div class="col-lg-5">
          {{-- Category-scoped quick search (Argos-style shell) --}}
          <form class="hero-search-form" method="GET" action="{{ url()->current() }}" role="search">
            <div class="hero-search-shell">
              <span class="hero-search-icon text-success-emphasis bg-white rounded-circle me-1" style="width:32px;height:32px;">
                <i class="fas fa-search"></i>
              </span>
              <label for="categoryHeroSearch" class="visually-hidden">Search in {{ $catName }}</label>
              <input
                id="categoryHeroSearch"
                type="search"
                name="q"
                class="form-control hero-search-input"
                placeholder="Search in {{ $catName }}"
                aria-label="Search in {{ $catName }}"
                value="{{ request('q') }}"
                autocomplete="on"
              >
              <button class="btn btn-light text-success hero-search-submit" type="submit">
                Search
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  {{-- =========== Filters / Toolbar =========== --}}
  @php
    $q        = request('q');
    $sort     = request('sort', 'latest');   // latest | price_asc | price_desc | popular
    $perPage  = (int) request('per_page', 24);
    $view     = request('view', 'grid');     // grid | list
    $priceMin = request('min');
    $priceMax = request('max');
  @endphp

  <section class="toolbar py-3">
    <div class="container">
      <form method="GET" action="{{ url()->current() }}" id="filtersForm">
        <div class="row g-2 align-items-center">
          {{-- Search in category --}}
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-search text-secondary"></i></span>
              <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search in {{ $category->name }}…">
            </div>
          </div>

          {{-- Sort --}}
          <div class="col-6 col-md-2">
            <select class="form-select" name="sort" aria-label="Sort by">
              <option value="latest"     {{ $sort==='latest'?'selected':'' }}>Newest</option>
              <option value="popular"    {{ $sort==='popular'?'selected':'' }}>Popular</option>
              <option value="price_asc"  {{ $sort==='price_asc'?'selected':'' }}>Price: Low → High</option>
              <option value="price_desc" {{ $sort==='price_desc'?'selected':'' }}>Price: High → Low</option>
            </select>
          </div>

          {{-- Price range --}}
          <div class="col-3 col-md-2">
            <input type="number" min="0" step="1" name="min" value="{{ $priceMin }}" class="form-control" placeholder="Min">
          </div>
          <div class="col-3 col-md-2">
            <input type="number" min="0" step="1" name="max" value="{{ $priceMax }}" class="form-control" placeholder="Max">
          </div>

          {{-- Per page --}}
          <div class="col-6 col-md-2">
            <select class="form-select" name="per_page" aria-label="Items per page">
              @foreach([12,24,48] as $n)
                <option value="{{ $n }}" {{ $perPage===$n?'selected':'' }}>{{ $n }} / page</option>
              @endforeach
            </select>
          </div>

          {{-- View toggle --}}
          <div class="col-6 col-md-2 text-md-end">
            <div class="btn-group view-toggle" role="group" aria-label="Toggle view">
              <button type="button" class="btn btn-outline-success {{ $view==='grid'?'active':'' }}" data-view="grid" title="Grid view">
                <i class="fas fa-th-large"></i>
              </button>
              <button type="button" class="btn btn-outline-success {{ $view==='list'?'active':'' }}" data-view="list" title="List view">
                <i class="fas fa-bars"></i>
              </button>
            </div>
            <input type="hidden" name="view" value="{{ $view }}">
          </div>
        </div>
      </form>

      {{-- Active chips --}}
      <div class="mt-2">
        <span class="chip me-1"><i class="fas fa-folder"></i> {{ $catName }}</span>

        @if($q)
          <span class="chip me-1">
            <i class="fas fa-search"></i> "{{ $q }}"
            <a href="{{ request()->fullUrlWithQuery(['q'=>null,'page'=>null]) }}" class="btn-close" aria-label="Clear search"></a>
          </span>
        @endif

        @if($priceMin !== null || $priceMax !== null)
          <span class="chip me-1">
            <i class="fas fa-dollar-sign"></i>
            {{ $priceMin !== null ? 'Min '.$priceMin : '' }}{{ ($priceMin !== null && $priceMax !== null) ? ' – ' : '' }}{{ $priceMax !== null ? 'Max '.$priceMax : '' }}
            <a href="{{ request()->fullUrlWithQuery(['min'=>null,'max'=>null,'page'=>null]) }}" class="btn-close" aria-label="Clear price"></a>
          </span>
        @endif

        @if($sort && $sort!=='latest')
          <span class="chip me-1">
            <i class="fas fa-sort-amount-down"></i>
            @switch($sort)
              @case('popular') Popular @break
              @case('price_asc') Price: Low→High @break
              @case('price_desc') Price: High→Low @break
              @default Newest
            @endswitch
            <a href="{{ request()->fullUrlWithQuery(['sort'=>'latest','page'=>null]) }}" class="btn-close" aria-label="Reset sort"></a>
          </span>
        @endif

        {{-- Clear all (but stay in the same category) --}}
        @if($q || $priceMin !== null || $priceMax !== null || ($sort && $sort!=='latest') || $perPage!==24 || $view!=='grid')
          <a href="{{ url()->current() }}" class="btn btn-sm btn-link text-decoration-none ms-1">
            <i class="fas fa-times-circle me-1"></i> Clear all
          </a>
        @endif

        {{-- Browse all link (to global listings) --}}
        <a href="{{ route('listings') }}" class="btn btn-sm btn-link text-decoration-none ms-2">
          <i class="fas fa-list-ul me-1"></i> Browse All Listings
        </a>
      </div>
    </div>
  </section>

  @push('scripts')
  <script>
  (function(){
    if (window.__videoThumbInitCat) return; window.__videoThumbInitCat = true;
    function toFirstFrame(img){
      var src = img.getAttribute('data-video-src');
      if(!src) return;
      try{
        var v = document.createElement('video');
        v.preload = 'metadata'; v.muted = true; v.playsInline = true; v.src = src + '#t=0.1';
        v.addEventListener('loadeddata', function(){
          try{
            var w=v.videoWidth||480, h=v.videoHeight||270;
            var c=document.createElement('canvas'); c.width=w; c.height=h;
            c.getContext('2d').drawImage(v,0,0,w,h);
            img.src=c.toDataURL('image/jpeg',0.8);
            img.style.opacity='1'; img.style.filter='none';
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
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
  })();
  </script>
  @endpush

  {{-- =========== Listings =========== --}}
  <section class="py-4 bg-light">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-bold mb-0">Listings in {{ $catName }}</h2>
        <span class="text-muted small">
          Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</strong>
          of <strong>{{ $products->total() }}</strong>
        </span>
      </div>

      {{-- GRID VIEW --}}
      @if($view === 'grid')
        @if ($products->count())
          <div class="row g-4">
            @foreach ($products as $item)
              <div class="col-6 col-md-4 col-lg-3">
                @include('theme.'.theme().'.partials.product-card', ['item' => $item])
              </div>
            @endforeach
          </div>

          {{-- Pagination (preserve filters) --}}
          @if ($products->hasPages())
            <div class="mt-4 d-flex justify-content-center">
              {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
            </div>
          @endif
        @else
          <div class="alert alert-info text-center empty-spot py-5">
            <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
            No listings found in this category.
          </div>
        @endif

      {{-- LIST VIEW --}}
      @else
        @if ($products->count())
          <div class="vstack gap-3">
            @foreach ($products as $item)
              @php
                // Prefer a real image for thumbnails; skip video files
                if (!empty($item->featured_image)) {
                  $thumb = str_starts_with($item->featured_image, 'http')
                           ? $item->featured_image
                           : asset('storage/'.ltrim($item->featured_image,'/'));
                } else {
                  $firstImage = (isset($item->media) && method_exists($item->media, 'firstWhere'))
                                ? $item->media->firstWhere('type','image')
                                : null;
                  if ($firstImage && !empty($firstImage->url)) {
                    $thumb = asset('storage/'.ltrim($firstImage->url,'/'));
                  } else {
                    $firstVideo = (isset($item->media) && method_exists($item->media,'firstWhere')) ? $item->media->firstWhere('type','video') : null;
                    $thumb = ($item->shop && $item->shop->logo)
                             ? asset('storage/'.ltrim($item->shop->logo,'/'))
                             : (setting('favicon_url') ?: asset('assets/img/placeholder.svg'));
                  }
                }
                // Use shop-wide rating in listings
                $shop = $item->shop ?? null;
                $avg  = round((float) ($shop?->reviews_avg_rating ?? ($shop ? $shop->reviews()->avg('rating') : 0)));
                $cnt  = (int) ($shop?->reviews_count ?? ($shop ? $shop->reviews()->count() : 0));
                $basePrice  = $item->price;
                $finalPrice = $item->discounted_price;
              @endphp

              <div class="border rounded-3 p-3 bg-white">
                <div class="row g-3 align-items-center">
                  <div class="col-4 col-md-3 col-lg-2">
                    <a href="{{ route('listing.show', $item->slug) }}" class="d-block rounded overflow-hidden">
                      <div class="ratio ratio-1x1 bg-white position-relative">
                        @php
                          $hasVideo = (isset($item->media)
                                       && method_exists($item->media,'firstWhere')
                                       && $item->media->firstWhere('type','video'));
                          // Precompute preview video URL for inline data attribute
                          $vid = null;
                          if (!isset($firstImage) || !$firstImage) {
                            $vid = (isset($firstVideo) && $firstVideo && !empty($firstVideo->url))
                              ? asset('storage/'.ltrim($firstVideo->url,'/'))
                              : null;
                          }
                          $styleExtra = (isset($firstVideo) && $firstVideo && (!isset($firstImage) || !$firstImage))
                            ? 'opacity:.01; filter:blur(8px); transition:opacity .35s ease, filter .35s ease;'
                            : '';
                        @endphp
                        @if($hasVideo)
                          <span class="position-absolute top-0 start-0 m-2 px-2 py-1 rounded text-white" style="background:rgba(0,0,0,.7); font-size:.72rem;"><i class="fas fa-play me-1"></i>Video</span>
                        @endif
                        <img
                          src="{{ $thumb }}"
                          alt="{{ $item->name }}"
                          class="w-100 h-100"
                          style="object-fit:cover; {{ $styleExtra }}"
                          @if($vid) data-video-src="{{ $vid }}" @endif>
                      </div>
                    </a>
                  </div>

                  <div class="col-8 col-md-6 col-lg-7">
                    <h5 class="mb-1">
                      <a class="text-decoration-none text-dark" href="{{ route('listing.show', $item->slug) }}">
                        {{ $item->name ?? 'Untitled item' }}
                      </a>
                    </h5>
                    <div class="mb-1 small text-warning">
                      @for($i=1; $i<=5; $i++)
                        <i class="fa-star{{ $i <= $avg ? ' fa-solid' : ' fa-regular text-muted' }}"></i>
                      @endfor
                      @if($cnt) <span class="text-muted">({{ $cnt }})</span>@endif
                    </div>
                    @if(!empty($item->short_description))
                      <div class="small text-muted">
                        {{ \Illuminate\Support\Str::limit(strip_tags($item->short_description), 120) }}
                      </div>
                    @endif
                  </div>

                  <div class="col-12 col-md-3 col-lg-3 text-md-end">
                    @php
                      $isService = (strtolower((string)($item->type ?? '')) === 'service');
                      $lowestVariantPrice = optional($item->variations)->whereNotNull('price')->min('price');
                    @endphp
                    @if($isService)
                      <div class="small text-muted">Priced From</div>
                      @php $from = $lowestVariantPrice ?? (is_numeric($finalPrice) && is_numeric($basePrice) && $finalPrice < $basePrice ? $finalPrice : $basePrice); @endphp
                      @if(isset($from) && is_numeric($from))
                        <div class="h5 mb-2 text-success">{{ get_currency() }} {{ number_format($from, 2) }}</div>
                      @else
                        <div class="text-muted small mb-2">Contact for price</div>
                      @endif
                    @else
                      @if(isset($finalPrice, $basePrice) && is_numeric($finalPrice) && is_numeric($basePrice) && $finalPrice < $basePrice)
                        <div class="d-flex align-items-baseline gap-2 justify-content-md-end mb-2">
                          <span class="fw-bold text-success">{{ get_currency() }} {{ number_format($finalPrice, 2) }}</span>
                          <span class="text-muted text-decoration-line-through">{{ get_currency() }} {{ number_format($basePrice, 2) }}</span>
                        </div>
                      @elseif(isset($basePrice))
                        <div class="h5 mb-2 text-success">{{ get_currency() }} {{ number_format($basePrice, 2) }}</div>
                      @else
                        <div class="text-muted small mb-2">Contact for price</div>
                      @endif
                    @endif

                    <a href="{{ route('listing.show', $item->slug) }}" class="btn btn-success btn-sm">
                      <i class="fas fa-eye me-1"></i> View
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Pagination (preserve filters) --}}
          @if ($products->hasPages())
            <div class="mt-4 d-flex justify-content-center">
              {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
            </div>
          @endif
        @else
          <div class="alert alert-info text-center empty-spot py-5">
            <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
            No listings found in this category.
          </div>
        @endif
      @endif
    </div>
  </section>

  {{-- View toggle & auto-submit selects --}}
  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('filtersForm');
      const viewInput = form.querySelector('input[name="view"]');
      document.querySelectorAll('.view-toggle [data-view]').forEach(btn => {
        btn.addEventListener('click', () => {
          viewInput.value = btn.getAttribute('data-view');
          if (form.querySelector('input[name="page"]')) form.querySelector('input[name="page"]').value = 1;
          form.submit();
        });
      });
      form.querySelectorAll('select, input[type="number"]').forEach(el => {
        el.addEventListener('change', () => {
          if (form.querySelector('input[name="page"]')) form.querySelector('input[name="page"]').value = 1;
          form.submit();
        });
      });
    });
  </script>
  @endpush
@endsection

