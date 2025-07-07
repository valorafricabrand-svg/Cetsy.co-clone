{{-- resources/views/shops/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $shop->name . ' – Lucare Store')

@section('main')

<!-- Store Hero Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4">
    <div class="d-flex align-items-center gap-3">
      @if($shop->logo_url)
        <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }} logo"
             class="rounded-circle shadow-sm border"
             style="width: 80px; height: 80px; object-fit: cover;">
      @endif
      <div>
        <h1 class="h4 fw-bold mb-1 text-primary">{{ $shop->name }}</h1>
        <span class="text-muted">Owned by {{ $shop->user->name }}</span>
      </div>
    </div>
    @if(Auth::id() === $shop->user_id)
      <a href="{{ route('shops.edit', $shop) }}" class="btn btn-outline-primary rounded-pill">
        <i class="fas fa-edit me-1"></i> Edit Store
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

<!-- Featured Image Section -->
@if($shop->featured_image)
<section class="py-4 bg-white">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <img 
          src="{{ asset('storage/' . $shop->featured_image) }}" 
          alt="{{ $shop->name }} featured image"
          class="w-100 rounded"
          style="height: 300px; object-fit: cover;"
        >
      </div>
    </div>
  </div>
</section>
@endif

<!-- Shop Overview -->
<!-- Store Overview -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <!-- About the Store -->
      <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white fw-semibold border-bottom">About Lucare</div>
          <div class="card-body">
            @if($shop->bio)
              <p class="mb-0 text-secondary">{{ $shop->bio }}</p>
            @else
              <p class="text-muted mb-0">Lucare brings you the best in beauty—skincare, cosmetics & wellness from trusted brands nationwide.</p>
            @endif
          </div>
        </div>
      </div>

      <!-- Preferences -->
      <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white fw-semibold border-bottom">Store Details</div>
          <div class="card-body row">
            <div class="col-sm-6 mb-3">
              <strong>Country:</strong><br>
              <span class="text-muted">{{ $shop->country ?? 'Kenya' }}</span>
            </div>
            <div class="col-sm-6 mb-3">
              <strong>Currency:</strong><br>
              <span class="text-muted">{{ $shop->currency ?? 'KES' }}</span>
            </div>
            <div class="col-12">
              <strong>Web Address:</strong><br>
              <a href="{{ route('shops.show', $shop) }}" class="text-primary text-decoration-none">
                {{ url('shop/' . $shop->slug) }}
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white fw-semibold border-bottom">Contact</div>
          <div class="card-body">
            @if($shop->email)
              <p class="mb-1"><i class="fas fa-envelope me-2 text-primary"></i><a href="mailto:{{ $shop->email }}" class="text-decoration-none">{{ $shop->email }}</a></p>
            @endif
            @if($shop->phone)
              <p class="mb-0"><i class="fas fa-phone me-2 text-primary"></i>{{ $shop->phone }}</p>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Store Products -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h5 fw-bold text-dark">Products from {{ $shop->name }}</h2>
      <a href="{{ route('products.index') }}" class="text-decoration-none text-primary small">See All Products</a>
    </div>

    @if($products->isEmpty())
      <div class="alert alert-info shadow-sm">
        No products have been listed yet. Check back soon!
      </div>
    @else
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach($products as $product)
          <div class="col">
            <div class="card h-100 shadow-sm border-0 product-card">
              <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
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
                  <a href="{{ route('products.show', $product) }}" class="text-dark text-decoration-none">
                    {{ $product->name }}
                  </a>
                </h6>
                <p class="text-primary fw-bold mb-3">KES {{ number_format($product->price, 2) }}</p>
                <div class="mt-auto d-flex justify-content-center">
                  <a href="{{ route('products.show', $product) }}"
                     class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-eye me-1"></i> View
                  </a>
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

@push('styles')
<style>
  .product-card:hover .card-img-top { transform: scale(1.05); transition: .4s; }
</style>
@endpush
