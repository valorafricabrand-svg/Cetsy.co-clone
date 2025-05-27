{{-- resources/views/cart/index.blade.php --}}
@extends('theme.layouts.main')

@section('main')

<!-- Hero Section -->
<section id="hero" class="py-5 bg-light border-bottom">
  <div class="container d-flex flex-column flex-lg-row align-items-center">
    <!-- Hero Text -->
    <div class="me-lg-5 text-center text-lg-start">
      <h1 class="display-4 fw-bold text-success mb-4">
        Welcome to Cetsy
      </h1>
      <h2 class="h4 text-primary fw-bold mb-4">
        Discover Handmade, Vintage &amp; Custom Products from Local Artists
      </h2>
      <p class="lead text-muted mb-4">
        Cetsy is the ultimate marketplace for unique and handcrafted treasures. Explore personalized gifts, vintage home decor, and one-of-a-kind creations from talented artisans across the globe.
      </p>
      <div class="d-flex justify-content-center justify-content-lg-start gap-3 mb-4">
        <a href="{{ route('register') }}" class="btn btn-success btn-lg rounded-pill shadow-sm">
          Get Started Free
        </a>
        <a href="#features" class="btn btn-outline-secondary btn-lg rounded-pill shadow-sm">
          Learn More
        </a>
      </div>
      <div class="d-flex flex-wrap gap-4 align-items-center justify-content-center justify-content-lg-start small text-muted">
        <div class="d-flex align-items-center">
          <i class="fas fa-shield-alt text-success me-2 fs-4"></i>
          <span>Secure &amp; Trusted</span>
        </div>
        <div class="d-flex align-items-center">
          <i class="fas fa-handshake text-success me-2 fs-4"></i>
          <span>Support Local Artists</span>
        </div>
        <div class="d-flex align-items-center">
          <i class="fas fa-cogs text-success me-2 fs-4"></i>
          <span>Custom Orders Available</span>
        </div>
      </div>
    </div>

    <!-- Hero Image -->
    <div class="mt-4 mt-lg-0 text-center">
      <img 
        src="{{ asset('assets/img/cetsy-hero-image.png') }}" 
        alt="Handmade Products on Cetsy" 
        class="img-fluid rounded shadow-sm"
        style="max-width:650px;"
      >
    </div>
  </div>
</section>

<!-- Trending Categories -->
<section class="py-5">
  <div class="container">
    <h2 class="h3 fw-bold mb-4">Trending Categories</h2>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3">
      @foreach($categories as $cat)
        <div class="col">
          <a href="{{ route('category.show', $cat->slug) }}" class="text-decoration-none">
            <div class="ratio ratio-1x1 bg-secondary rounded overflow-hidden">
              @if($cat->image)
                <img 
                  src="{{ asset('storage/'.$cat->image) }}" 
                  alt="{{ $cat->name }}" 
                  class="w-100 h-100" 
                  style="object-fit:cover;"
                >
              @else
                <div class="d-flex align-items-center justify-content-center h-100 text-white fw-semibold">
                  {{ $cat->name }}
                </div>
              @endif
            </div>
            <p class="mt-2 text-center text-dark small mb-0">{{ $cat->name }}</p>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="bg-light py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h3 fw-bold mb-0">Featured for You</h2>
      <a href="{{ route('listings') }}" class="text-success text-decoration-none small">
        See All Products
      </a>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($featuredProducts as $product)
        <div class="col">
          <div class="card h-100 shadow-sm border-0">
            <a href="{{ route('listing.show', $product) }}">
              @if($img = $product->media->first())
                <img 
                  src="{{ asset('storage/'.$img->url) }}" 
                  alt="{{ $product->name }}" 
                  class="card-img-top" 
                  style="height:200px; object-fit:cover;"
                >
              @else
                <div 
                  class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                  style="height:200px;"
                >
                  No Image
                </div>
              @endif
            </a>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-truncate">{{ $product->name }}</h5>
              <p class="card-text text-success fw-bold mb-3">KES {{ number_format($product->price,2) }}</p>
              
              <div class="mt-auto d-flex justify-content-between align-items-center">
                <a href="{{ route('listing.show', $product) }}" class="small text-decoration-none text-muted">
                  View Details
                </a>
                
                <!-- Add to Cart Form -->
                <form 
                  method="POST" 
                  action="{{ route('cart.store') }}" 
                  x-data="{ busy: false }" 
                  @submit="busy = true"
                >
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" value="1">
                  <button 
                    type="submit" 
                    class="btn btn-success btn-sm" 
                    :disabled="busy"
                  >
                    <i class="fas fa-cart-plus"></i>
                    <span x-show="!busy">Add</span>
                    <span x-show="busy" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  </button>
                </form>
                
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center text-muted">
          No featured products at this time.
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection
