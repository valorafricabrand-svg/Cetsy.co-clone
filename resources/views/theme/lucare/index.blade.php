@extends('theme.'.theme().'.layouts.app')

@section('main')
{{-- ───────────────────────── Hero ───────────────────────── --}}
<section id="hero" class="py-6 py-lg-7 position-relative overflow-hidden"
         style="background:linear-gradient(135deg,#fce4ec 0%,#f8bbd0 100%);">
  {{-- Decorative blob --}}
  <svg class="position-absolute top-0 end-0 opacity-25 d-none d-md-block" width="460" height="510"
       viewBox="0 0 460 510" fill="none" xmlns="http://www.w3.org/2000/svg"
       style="transform:translate(50%,-20%);">
    <path d="M358 0C420 32 470 73 458 146 446 220 372 325 294 392 215 460 132 490 79 467 26 444 2 368 0 283 -3 198 17 102 87 54 158 7 295 -23 358 0Z"
          fill="#f8bbd0"/>
  </svg>

  <div class="container position-relative">
    <div class="row align-items-center gy-5">
      {{-- Hero​ text --}}
      <div class="col-lg-6 text-center text-lg-start" data-aos="fade-right">
        <h1 class="display-4 fw-bold text-primary mb-3">
          Lucare – Kenya’s Top Online Beauty Store
        </h1>
        <p class="lead text-muted mb-4">
          Shop skincare, cosmetics &amp; wellness essentials from trusted local &amp; global brands.
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 mb-5">
          <a href="{{ route('listings') }}"
             class="btn btn-primary btn-lg rounded-pill shadow px-4">
            <i class="fa-solid fa-shop me-2"></i>Shop Now
          </a>
          <a href="#features"
             class="btn btn-outline-primary btn-lg rounded-pill shadow px-4">
            Learn More
          </a>
        </div>

        <div class="d-flex flex-wrap gap-4 justify-content-center justify-content-lg-start text-secondary small">
          <span class="d-inline-flex align-items-center">
            <i class="fa-solid fa-truck-fast me-2 text-primary"></i> Free Nationwide Delivery
          </span>
          <span class="d-inline-flex align-items-center">
            <i class="fa-solid fa-heart me-2 text-primary"></i> 100+ Trusted Brands
          </span>
          <span class="d-inline-flex align-items-center">
            <i class="fa-solid fa-lock me-2 text-primary"></i> Secure Payments
          </span>
        </div>
      </div>

      {{-- Hero image --}}
      <div class="col-lg-6 text-center" data-aos="fade-left">
        <img src="{{ asset('assets/images/beauty-hero.webp') }}"
             alt="Beauty products display"
             class="img-fluid rounded-4 shadow-lg"
             style="max-width:540px">
      </div>
    </div>
  </div>
</section>

{{-- ───────────────── Trending Categories ───────────────── --}}
<section id="features" class="py-6">
  <div class="container">
    <h2 class="h3 fw-bold mb-4 text-center text-lg-start">Trending Categories</h2>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3" data-aos="fade-up">
      @foreach($categories as $cat)
        <div class="col">
          <a href="{{ route('category.show', $cat->slug) }}" class="text-decoration-none">
            <div class="ratio ratio-1x1 position-relative rounded-3 overflow-hidden category-card">
              @if($cat->image)
                <img src="{{ asset('storage/'.$cat->image) }}"
                     alt="{{ $cat->name }}"
                     class="w-100 h-100 object-fit-cover">
              @else
                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary text-white fw-semibold">
                  {{ $cat->name }}
                </div>
              @endif
              <div class="position-absolute bottom-0 start-0 end-0 py-1 bg-dark bg-opacity-50 text-white text-center small fw-medium">
                {{ $cat->name }}
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────────────── Featured Products ───────────────── --}}
<section class="bg-light py-6">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h3 fw-bold mb-0">Featured For You</h2>
      <a href="{{ route('products.index') }}" class="text-primary small fw-semibold">
        See All
        <i class="fa-solid fa-arrow-right-long ms-1"></i>
      </a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($featuredProducts as $product)
        <div class="col" data-aos="zoom-in">
          <div class="card h-100 border-0 shadow-sm product-card">
            <a href="{{ route('listing.show', $product) }}" class="d-block">
              @php($thumb = product_thumb_url($product))
              <img src="{{ $thumb }}"
                   alt="{{ $product->name }}"
                   class="card-img-top"
                   style="height:200px;object-fit:cover"
                   loading="lazy">
            </a>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-truncate mb-1">
                <a href="{{ route('listing.show', $product) }}" class="text-dark">{{ $product->name }}</a>
              </h5>
              <p class="text-primary fw-bold mb-3">KES {{ number_format($product->price,2) }}</p>
              <div class="mt-auto d-flex justify-content-between">
                <a href="{{ route('listing.show', $product) }}"
                   class="btn btn-outline-primary btn-sm">
                  <i class="fa-solid fa-eye me-1"></i> View
                </a>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center text-muted">No featured products yet.</div>
      @endforelse
    </div>
  </div>
</section>

{{-- ───────────────── About Lucare ───────────────── --}}
<section class="py-6 bg-white">
  <div class="container">
    <div class="row justify-content-center text-center mb-5">
      <div class="col-lg-8" data-aos="fade-up">
        <h2 class="fw-bold">About Lucare</h2>
        <p class="lead text-muted">
          Lucare is Kenya’s premier online beauty store, bringing you skincare, cosmetics, and wellness essentials from trusted local and international brands—delivered nationwide.
        </p>
      </div>
    </div>

    <div class="row gy-4" data-aos="fade-up">
      <div class="col-md-6 col-lg-4">
        <div class="h-100 p-4 border rounded-4 shadow-sm">
          <h4><a href="{{ url('/about') }}" class="text-decoration-none text-primary">Our Story</a></h4>
          <p class="mb-0">
            Founded in Nairobi to revolutionize beauty shopping in Kenya, Lucare connects you with over 100 top brands and products you love.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="h-100 p-4 border rounded-4 shadow-sm">
          <h4><a href="#" class="text-decoration-none text-primary">What We Offer</a></h4>
          <p class="mb-0">
            From daily skincare staples to the latest makeup innovations and wellness must-haves, shop confidently with secure payments and fast delivery.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="h-100 p-4 border rounded-4 shadow-sm">
          <h4><a href="{{ route('register') }}" class="text-decoration-none text-primary">Join Us</a></h4>
          <p class="mb-3">
            Sign up to save favorites, track orders, and enjoy exclusive offers. Experience beauty shopping made simple.
          </p>
          <a href="{{ route('register') }}" class="btn btn-primary btn-sm rounded-pill">
            Get Started
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
      integrity="sha512-Ho+jjFzJFDN5XvPy6PAW9hqDfeaoCcZWagV2metM3y8FPCPlffpcajvQ1QHBYsBo81uDUfMcqbsVbWMH5b9YxA=="
      crossorigin="anonymous" referrerpolicy="no-referrer"/>
<style>
  .product-card:hover .card-img-top { transform: scale(1.05); transition: .4s }
  .category-card:hover img { transform: scale(1.1); transition: .4s }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha512-QwrpYofeCvTR6rIhiuQr8y43X5YLkO3Ocdm+pNsoKmIV7xdZtc7N69n5xrF/PGG3v8tSWlKnOpE+UsWgkh7mXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => AOS.init({ duration: 800, once: true }));
</script>
@endpush
