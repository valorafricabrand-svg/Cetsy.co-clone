{{-- resources/views/listings/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Marketplace – Products, Services & Digital Goods')

@section('main')
  <style>
    .py-6 { padding-top: 4rem; padding-bottom: 4rem; }
    .hero-soft {
      background:
        radial-gradient(1200px 600px at -10% -10%, rgba(25,135,84,.10), transparent 60%),
        radial-gradient(1200px 600px at 110% 0%, rgba(25,135,84,.08), transparent 60%),
        linear-gradient(180deg, #0f5132, #198754);
    }

    /* ✅ Keep toolbar below nav dropdown/collapse */
    .toolbar { position: sticky; top: 0; z-index: 900; background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); }

    /* ✅ Ensure BOTH navbars (top + category) sit above the toolbar */
    nav { position: relative; z-index: 1100; }
    nav .dropdown-menu { z-index: 1101; }
    .navbar-collapse { position: relative; z-index: 1102; }

    .chip { display:inline-flex; align-items:center; gap:.5rem; padding:.35rem .6rem; border-radius:999px; border:1px solid rgba(0,0,0,.12); background:#fff; font-size:.875rem; }
    .view-toggle .btn { border-radius:.5rem; }
    .card-list { border:1px solid rgba(0,0,0,.06); border-radius:1rem; background:#fff; transition:transform .18s ease, box-shadow .18s ease; }
    .card-list:hover { transform:translateY(-3px); box-shadow:0 10px 22px rgba(16,24,40,.10); }
    .empty-spot { border:2px dashed rgba(25,135,84,.35); border-radius:1rem; background:rgba(25,135,84,.03); }
  </style>

  {{-- Hero (aligned with homepage visual language) --}}
  <section class="py-6 text-white hero-soft">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <p class="text-uppercase small fw-bold text-success-emphasis mb-1">
            <i class="fas fa-store me-1"></i> Marketplace
          </p>
          <h1 class="display-5 fw-bold mb-2">All Listings</h1>
          <p class="lead mb-0">Browse our global marketplace for physical products, professional services, and instant digital downloads.</p>
        </div>
        <div class="col-lg-5">
          <form class="hero-search-form" method="GET" action="{{ url()->current() }}" role="search">
            <div class="hero-search-shell">
              <span class="hero-search-icon text-success-emphasis bg-white rounded-circle me-1" style="width:32px;height:32px;">
                <i class="fas fa-search"></i>
              </span>
              <label for="listingsHeroSearch" class="visually-hidden">Search listings</label>
              <input
                id="listingsHeroSearch"
                type="search"
                name="q"
                class="form-control hero-search-input"
                placeholder="Search listings, brands or shops"
                aria-label="Search listings"
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

  @php
    $q        = request('q');
    $sort     = request('sort', 'latest');   // latest | price_asc | price_desc | popular
    $type     = request('type');             // product | service | digital
    $perPage  = (int) request('per_page', 24);
    $view     = request('view', 'grid');     // grid | list
  @endphp

  {{-- Toolbar --}}
  <section class="toolbar py-3">
    <div class="container">
      <form method="GET" action="{{ url()->current() }}" id="filtersForm">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-search text-secondary"></i></span>
              <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search listings…" aria-label="Search listings">
            </div>
          </div>

          <div class="col-6 col-md-2">
            <select class="form-select" name="type" aria-label="Filter by type">
              <option value="">All types</option>
              <option value="physical" {{ ($type==='physical' || $type==='product' || $type==='products')?'selected':'' }}>Products</option>
              <option value="service"  {{ ($type==='service'  || $type==='services')?'selected':'' }}>Services</option>
              <option value="digital"  {{ $type==='digital'?'selected':'' }}>Digital</option>
            </select>
          </div>

          <div class="col-6 col-md-2">
            <select class="form-select" name="sort" aria-label="Sort by">
              <option value="latest"     {{ $sort==='latest'?'selected':'' }}>Newest</option>
              <option value="popular"    {{ $sort==='popular'?'selected':'' }}>Popular</option>
              <option value="price_asc"  {{ $sort==='price_asc'?'selected':'' }}>Price: Low → High</option>
              <option value="price_desc" {{ $sort==='price_desc'?'selected':'' }}>Price: High → Low</option>
            </select>
          </div>

          <div class="col-6 col-md-2">
            <select class="form-select" name="per_page" aria-label="Items per page">
              @foreach([12,24,48] as $n)
                <option value="{{ $n }}" {{ $perPage===$n?'selected':'' }}>{{ $n }} / page</option>
              @endforeach
            </select>
          </div>

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
        @if($q)
          <span class="chip me-1"><i class="fas fa-search"></i> "{{ $q }}"
            <a href="{{ request()->fullUrlWithQuery(['q'=>null,'page'=>null]) }}" class="btn-close" aria-label="Clear search"></a>
          </span>
        @endif
        @if($type)
          <span class="chip me-1"><i class="fas fa-filter"></i> {{ ucfirst($type) }}
            <a href="{{ request()->fullUrlWithQuery(['type'=>null,'page'=>null]) }}" class="btn-close" aria-label="Clear type"></a>
          </span>
        @endif
        @if($sort && $sort!=='latest')
          <span class="chip me-1"><i class="fas fa-sort-amount-down"></i>
            @switch($sort)
              @case('popular') Popular @break
              @case('price_asc') Price: Low→High @break
              @case('price_desc') Price: High→Low @break
              @default Newest
            @endswitch
            <a href="{{ request()->fullUrlWithQuery(['sort'=>'latest','page'=>null]) }}" class="btn-close" aria-label="Reset sort"></a>
          </span>
        @endif

        @if($q || $type || ($sort && $sort!=='latest') || $perPage!==24 || $view!=='grid')
          <a href="{{ url()->current() }}" class="btn btn-sm btn-link text-decoration-none ms-1">
            <i class="fas fa-times-circle me-1"></i> Clear all
          </a>
        @endif
      </div>
    </div>
  </section>

  @push('scripts')
  <script>
  (function(){
    if (window.__videoThumbInitListings) return; window.__videoThumbInitListings = true;
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

  {{-- Results --}}
  <section class="py-4 bg-light">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0 text-muted">
    Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</strong>
    of <strong>{{ $products->total() }}</strong> listings
</p>
        @if($products->total() > 0)
          <a href="{{ route('listings') }}" class="btn btn-outline-success btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
        @endif
      </div>

      {{-- GRID VIEW (uses your partial as-is) --}}
      @if($view === 'grid')
        <div id="listing-items" class="row g-4">
          @forelse ($products as $item)
            <div class="col-6 col-md-4 col-lg-3">
              @include('theme.'.theme().'.partials.product-card', ['item' => $item])
              @if(($item->type ?? '') === 'physical' && (int)($item->stock ?? 0) === 1 && ($item->is_reserved ?? false))
                <div class="mt-2 small text-danger">Reserved in another pending order</div>
              @endif
            </div>
          @empty
            <div class="col-12 text-center text-muted py-5 empty-spot">
              <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
              <p class="mb-1">No listings match your filters.</p>
              <a href="{{ url()->current() }}" class="btn btn-success btn-sm mt-2">Clear Filters</a>
            </div>
          @endforelse
        </div>

      {{-- LIST VIEW (mirrors the partial’s concepts) --}}
      @else
        <div id="listing-items" class="vstack gap-3">
          @forelse ($products as $item)
            @php
              // --- Concept parity with product-card ---
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
              // Use the shop's overall rating on listings
              $shop = $item->shop ?? null;
              $avg  = round((float) ($shop?->reviews_avg_rating ?? ($shop ? $shop->reviews()->avg('rating') : 0)));
              $cnt  = (int) ($shop?->reviews_count ?? ($shop ? $shop->reviews()->count() : 0));
              $basePrice  = $item->price;
              $finalPrice = $item->discounted_price;
            @endphp

            <div class="card-list p-3">
              <div class="row g-3 align-items-center">
                <div class="col-4 col-md-3 col-lg-2">
                  <a href="{{ route('listing.show', $item->slug) }}" class="d-block rounded overflow-hidden">
                    <div class="ratio ratio-1x1 bg-white position-relative">
                      @php
                        $hasVideo = (isset($item->media)
                                     && method_exists($item->media,'firstWhere')
                                     && $item->media->firstWhere('type','video'));
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
                        class="img-fluid w-100 h-100"
                        loading="lazy" decoding="async"
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

                  {{-- Ratings (same style as partial) --}}
                  <div class="mb-1 small text-warning">
                    @for($i=1; $i<=5; $i++)
                      <i class="fa-star{{ $i <= $avg ? ' fa-solid' : ' fa-regular text-muted' }}"></i>
                    @endfor
                    @if($cnt) <span class="text-muted">({{ $cnt }})</span>@endif
                  </div>

                  {{-- Short description (optional) --}}
                  @if(!empty($item->short_description))
                    <div class="small text-muted">
                      {{ \Illuminate\Support\Str::limit(strip_tags($item->short_description), 120) }}
                    </div>
                  @endif
                </div>

                <div class="col-12 col-md-3 col-lg-3 text-md-end">
                  {{-- Price (exact parity with partial logic) --}}
                  @if(isset($finalPrice, $basePrice) && is_numeric($finalPrice) && is_numeric($basePrice) && $finalPrice < $basePrice)
                    <div class="d-flex align-items-baseline gap-2 justify-content-md-end mb-2">
                      <span class="fw-bold text-success">{{ money($finalPrice) }}</span>
                      <span class="text-muted text-decoration-line-through">{{ money($basePrice) }}</span>
                    </div>
                  @elseif(isset($basePrice))
                    <div class="h5 mb-2 text-success">{{ money($basePrice) }}</div>
                  @else
                    <div class="text-muted small mb-2">Contact for price</div>
                  @endif

                  <a href="{{ route('listing.show', $item->slug) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-eye me-1"></i> View
                  </a>
                </div>
              </div>
            </div>
          @empty
            <div class="text-center text-muted py-5 empty-spot">
              <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
              <p class="mb-1">No listings match your filters.</p>
              <a href="{{ url()->current() }}" class="btn btn-success btn-sm mt-2">Clear Filters</a>
            </div>
          @endforelse
        </div>
      @endif

      {{-- Load More --}}
      @if ($products->hasMorePages())
        <div class="mt-4 text-center">
          <button id="load-more" class="btn btn-success" data-next-page="{{ $products->currentPage() + 1 }}">
            Load More
          </button>
        </div>
      @endif
    </div>
  </section>

  {{-- View toggle + autosubmit --}}
@include('theme.'.theme().'.partials.product-carousel', [
    'items' => $recommendedProducts ?? collect(),
    'title' => 'Because you viewed similar items',
    'subtitle' => 'Hand-picked from categories and styles you\'ve been browsing.',
    'eyebrow' => 'Recommended',
    'eyebrowIcon' => 'fa-wand-magic-sparkles',
    'seeMoreUrl' => route('listings'),
    'seeMoreLabel' => 'Keep exploring'
])

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

      form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', () => {
          if (form.querySelector('input[name="page"]')) form.querySelector('input[name="page"]').value = 1;
          form.submit();
        });
      });

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
              const newItems = doc.getElementById('listing-items').children;
              const container = document.getElementById('listing-items');
              Array.from(newItems).forEach(el => container.appendChild(el));

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
@endsection
