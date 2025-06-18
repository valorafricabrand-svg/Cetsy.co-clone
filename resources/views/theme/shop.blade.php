@extends('theme.layouts.main')

@section('main')

<!-- Shop Hero Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4">
    <div class="d-flex align-items-center gap-3">
      @if($shop->logo_url)
        <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }} logo"
             class="rounded-circle shadow-sm border"
             style="width: 80px; height: 80px; object-fit: cover;">
      @endif
      <div>
        <h1 class="h4 fw-bold mb-1">{{ $shop->name }}</h1>
        <span class="text-muted">Owned by {{ $shop->user->name }}</span>
      </div>
    </div>
    @if(Auth::id() === $shop->user_id)
      <a href="{{ route('shops.edit', $shop) }}" class="btn btn-outline-success rounded-pill">
        <i class="fas fa-edit me-1"></i> Edit Shop
      </a>
    @endif
  </div>
</section>

<!-- Flash Message -->
@if(session('success'))
  <div class="container mt-4">
    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
  </div>
@endif

<!-- Shop Overview -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <!-- About the Shop -->
      <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white fw-semibold border-bottom">About This Shop</div>
          <div class="card-body">
            @if($shop->bio)
              <p class="mb-0 text-secondary">{{ $shop->bio }}</p>
            @else
              <p class="text-muted mb-0">This shop has not provided a description yet.</p>
            @endif
          </div>
        </div>
      </div>

      <!-- Preferences -->
      <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white fw-semibold border-bottom">Shop Preferences</div>
          <div class="card-body row">
            <div class="col-sm-6 mb-3">
              <strong>Language:</strong><br>
              <span class="text-muted">{{ $shop->language ?? 'N/A' }}</span>
            </div>
            <div class="col-sm-6 mb-3">
              <strong>Country:</strong><br>
              <span class="text-muted">{{ $shop->country ?? 'N/A' }}</span>
            </div>
            <div class="col-sm-6 mb-3">
              <strong>Currency:</strong><br>
              <span class="text-muted">{{ $shop->currency ?? 'N/A' }}</span>
            </div>
            <div class="col-12">
              <strong>Shop URL:</strong><br>
              <a href="{{ route('shops.show', $shop) }}" class="text-success text-decoration-none">
                {{ url('shop/' . $shop->slug) }}
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Billing Address -->
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white fw-semibold border-bottom">Billing Address</div>
          <div class="card-body">
            <p class="mb-1 text-secondary">{{ $shop->address ?? 'N/A' }}</p>
            <p class="mb-0 text-secondary">{{ $shop->city ?? '' }}{{ $shop->postal ? ', ' . $shop->postal : '' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Shop Products -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h5 fw-bold text-dark">Products from {{ $shop->name }}</h2>
      <a href="{{ route('listings') }}" class="text-decoration-none text-success small">See All Listings</a>
    </div>

    @if($products->isEmpty())
      <div class="alert alert-info shadow-sm">
        This shop has not listed any products yet.
      </div>
    @else
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach($products as $product)
          <div class="col">
            <div class="card h-100 shadow-sm border-0 product-hover">
              <a href="{{ route('listing.show', $product) }}" class="text-decoration-none">
                @if($img = $product->media->first())
                  <img 
                    src="{{ asset('storage/'.$img->url) }}" 
                    alt="{{ $product->name }}" 
                    class="card-img-top"
                    style="height: 200px; object-fit: cover;"
                    loading="lazy">
                @else
                  <div class="bg-secondary d-flex justify-content-center align-items-center text-white" style="height: 200px;">
                    No Image
                  </div>
                @endif
              </a>
              <div class="card-body d-flex flex-column">
                <h6 class="card-title text-truncate">
                  <a href="{{ route('listing.show', $product) }}" class="text-dark text-decoration-none">
                    {{ $product->name }}
                  </a>
                </h6>
                <p class="text-success fw-bold mb-3">KES {{ number_format($product->price, 2) }}</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                  <a href="{{ route('listing.show', $product) }}" class="text-muted small">View</a>
                  <form method="POST" action="{{ route('cart.add') }}" x-data="{ busy: false }" @submit="busy = true">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-success btn-sm rounded-circle p-2" :disabled="busy">
                      <i class="fas fa-cart-plus" x-show="!busy"></i>
                      <span x-show="busy" class="spinner-border spinner-border-sm" role="status"></span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if($products->hasPages())
        <div class="mt-4 d-flex justify-content-center">
          {{ $products->links('pagination::bootstrap-5') }}
        </div>
      @endif
    @endif
  </div>
</section>

@endsection
