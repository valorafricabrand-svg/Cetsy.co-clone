@extends('theme.layouts.main')

@section('main')

<section id="hero" class="py-5 bg-light border-bottom">
    <div class="container d-flex flex-column flex-lg-row align-items-center">
        <!-- Hero Text -->
        <div class="hero-text me-lg-5 text-center text-lg-start">
            <h1 class="display-3 fw-bold text-success mb-4">
                Welcome to Cetsy
            </h1>
            <h2 class="h4 text-primary fw-semibold mb-5">
                Discover Handmade, Vintage & Custom Products from Local Artists
            </h2>
            <p class="lead text-muted mb-4">
                Cetsy is the ultimate marketplace for unique and handcrafted treasures. 
                Explore personalized gifts, vintage home decor, and one-of-a-kind custom creations 
                from talented artisans across the globe.
            </p>
            <div class="d-flex justify-content-center justify-content-lg-start gap-3 mb-4">
                <a href="{{ route('register') }}" class="btn btn-success btn-lg rounded-pill px-5 py-3 shadow-lg transition-all hover:scale-105">
                    Get Started Free
                </a>
                <a href="#features" class="btn btn-outline-secondary btn-lg rounded-pill px-5 py-3 shadow-lg transition-all hover:scale-105">
                    Learn More
                </a>
            </div>
            <div class="d-flex flex-wrap gap-5 align-items-center justify-content-center justify-content-lg-start text-muted small">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt text-success me-2 fs-4"></i>
                    <span>Secure & Trusted</span>
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
        <div class="hero-img mt-5 mt-lg-0 text-center">
            <img 
                src="{{ asset('assets/img/cetsy-hero-image.jpg') }}" 
                alt="Handmade Products on Cetsy" 
                class="img-fluid shadow-lg rounded-3" 
                style="max-width: 650px; transition: transform 0.3s ease-in-out;"
                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
            >
        </div>
    </div>
</section>



    <!-- Trending Categories Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Trending Categories</h2>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-4">
                @foreach($categories as $cat)
                    <div class="col">
                        <a href="{{ route('category.show', $cat->slug) }}" class="d-block text-decoration-none">
                            <div class="h-32 bg-gray-100 rounded-lg overflow-hidden position-relative">
                                @if($cat->image)
                                    <img src="{{ asset('storage/'.$cat->image) }}" alt="{{ $cat->name }}" class="w-100 h-100 object-cover">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <span class="text-gray-700 font-medium">{{ $cat->name }}</span>
                                    </div>
                                @endif
                            </div>
                            <p class="mt-2 text-center text-sm font-medium text-gray-800">{{ $cat->name }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-3xl font-bold text-gray-800">Featured for You</h2>
                <a href="{{ route('products.index') }}" class="text-success hover-underline">
                    See All Products
                </a>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                @forelse($featuredProducts as $product)
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <a href="{{ route('products.show', $product) }}">
                                @if($img = $product->media->first())
                                    <img src="{{ asset('storage/'.$img->url) }}" alt="{{ $product->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary text-white d-flex justify-content-center align-items-center" style="height: 200px;">No Image</div>
                                @endif
                            </a>
                            <div class="card-body">
                                <h5 class="card-title text-truncate">{{ $product->name }}</h5>
                                <p class="card-text text-success font-weight-bold">KES {{ number_format($product->price, 2) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('listing.show', $product) }}" class="text-muted text-decoration-none">View Details</a>
                                    <button
                                      @click="addToCart({{ $product->id }}, 1)"
                                      class="btn btn-success btn-sm"
                                      aria-label="Add {{ $product->name }} to cart"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 5m5-5v5m4-5v5m1-10h2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-600 col-span-12">No featured products at this time.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
