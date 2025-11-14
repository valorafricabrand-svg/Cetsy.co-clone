@extends('theme.'.theme().'.layouts.app')

@section('title', 'All Shops')

@section('main')
  <style>
    .shops-hero {
      background:
        radial-gradient(1200px 600px at -10% -10%, rgba(25,135,84,.10), transparent 60%),
        radial-gradient(1200px 600px at 110% 0%, rgba(25,135,84,.08), transparent 60%),
        linear-gradient(180deg, #0f5132, #198754);
    }
    .shops-toolbar {
      position: sticky;
      top: 0;
      z-index: 900;
      background: #fff;
      border-bottom: 1px solid rgba(0,0,0,.06);
    }
  </style>

  @php
    $q = request('q');
    $country = request('country');
  @endphp

  {{-- Hero (Argos-style band) --}}
  <section class="py-6 text-white shops-hero">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <p class="text-uppercase small fw-bold text-success-emphasis mb-1">
            <i class="fas fa-store me-1"></i> Shops
          </p>
          <h1 class="display-5 fw-bold mb-2">All Shops</h1>
          <p class="lead mb-0">Discover trusted Cetsy shops from around the world and explore their latest listings.</p>
        </div>
        <div class="col-lg-5">
          <form method="GET" action="{{ url()->current() }}" role="search" class="hero-search-form">
            <input type="hidden" name="country" value="{{ $country }}">
            <div class="hero-search-shell">
              <span class="hero-search-icon text-success-emphasis bg-white rounded-circle me-1" style="width:32px;height:32px;">
                <i class="fas fa-search"></i>
              </span>
              <label for="shopsSearch" class="visually-hidden">Search shops</label>
              <input
                id="shopsSearch"
                type="search"
                name="q"
                class="form-control hero-search-input"
                placeholder="Search shops, brands or owners"
                aria-label="Search shops"
                value="{{ $q }}"
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

  {{-- Filters toolbar --}}
  <section class="shops-toolbar py-3">
    <div class="container">
      <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-center">
        <div class="col-md-6 d-none d-lg-block">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-secondary"></i></span>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search shops…">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <select name="country" class="form-select" aria-label="Filter shops by country">
            <option value="">All countries</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}" {{ (string)$country === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-3">
          <button type="submit" class="btn btn-success w-100">Apply filters</button>
        </div>
      </form>
    </div>
  </section>

  <section class="py-4 bg-light">
    <div class="container">
      <div id="shopsList" class="row g-4">
        @forelse($shops as $shop)
          <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('shop.show', $shop->slug) }}" class="text-decoration-none">
              <div class="card h-100 text-center shadow-sm border-0">
                <div class="card-body d-flex flex-column align-items-center">
                  <img src="{{ $shop->logo ? ($shop->logo_url ?? asset('storage/' . $shop->logo)) : setting('favicon_url') }}"
                       alt="{{ $shop->name }} logo"
                       class="rounded-circle mb-3"
                       style="width:80px;height:80px;object-fit:cover;">
                  <h6 class="mb-1 text-dark">{{ $shop->name }}</h6>
                  <div class="small text-muted mb-1">
                    <span class="text-warning"><i class="fas fa-star"></i> {{ number_format($shop->reviews_avg_rating ?? 0, 1) }}</span>
                    <span>({{ $shop->reviews_count }})</span>
                  </div>
                  <p class="text-muted small mb-0">{{ $countries[$shop->country]->name ?? '' }}</p>
                </div>
              </div>
            </a>
          </div>
        @empty
          <div class="col-12 text-center text-muted">No shops found.</div>
        @endforelse
      </div>

      @if($shops->hasMorePages())
        <div class="mt-4 text-center">
          <button id="load-more" class="btn btn-success" data-next-page="{{ $shops->currentPage() + 1 }}">Load More</button>
        </div>
      @endif
    </div>
  </section>
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
          const newItems = doc.getElementById('shopsList').children;
          const container = document.getElementById('shopsList');
          Array.from(newItems).forEach(el => container.appendChild(el));

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

