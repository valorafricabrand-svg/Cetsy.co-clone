@extends('theme.'.theme().'.layouts.app')

@section('title', 'All Shops')

@section('main')
<section class="py-6 bg-light">
  <div class="container">
    <h1 class="text-center mb-5">All Shops</h1>
    <div class="row g-4">
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
    <div class="mt-4">
      {{ $shops->links() }}
    </div>
  </div>
</section>
@endsection
