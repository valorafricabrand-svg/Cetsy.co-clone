@extends('theme.'.theme().'.layouts.app')

@section('title', 'All Shops')

@section('main')
<section class="py-6 bg-light">
  <div class="container">
    <h1 class="text-center mb-4">All Shops</h1>

    @php
      $q = request('q');
      $country = request('country');
    @endphp

    <form method="GET" action="{{ url()->current() }}" class="row g-2 mb-4">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fas fa-search text-secondary"></i></span>
          <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search shops…">
        </div>
      </div>
      <div class="col-md-4">
        <select name="country" class="form-select">
          <option value="">All countries</option>
          @foreach($countries as $c)
            <option value="{{ $c->id }}" {{ $country == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-success w-100">Filter</button>
      </div>
    </form>

    <div id="shopsList" class="row g-4">
      @forelse($shops as $shop)
        <div class="col-6 col-md-4 col-lg-3">
          <a href="{{ route('shop.show', $shop->slug) }}" class="text-decoration-none">
            <div class="card h-100 text-center shadow-sm border-0">
              <div class="card-body d-flex flex-column align-items-center">
                <img src="{{ $shop->logo ? asset('storage/' . $shop->logo) : setting('favicon_url') }}"
                     alt="{{ $shop->name }} logo"
                     class="rounded-circle mb-3"
                     style="width:80px;height:80px;object-fit:cover;">
                <h6 class="mb-0 text-dark">{{ $shop->name }}</h6>
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
