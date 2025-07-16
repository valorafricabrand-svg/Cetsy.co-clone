@extends('layouts.frontapp')

@section('main')

<!-- Hero Section -->
<section id="hero" class="py-5" style="background-color: #FDF4E4;">
  <div class="container d-flex flex-column flex-lg-row align-items-center">
    <!-- Hero Text -->
    <div class="me-lg-5 text-center text-lg-start">
      <h1 class="display-4 fw-bold text-success mb-3">
        Cetsy Your Global Marketplace
      </h1>
      
      <p class="lead text-muted mb-4" style="max-width: 480px;">
        Your global marketplace where you’ll find almost anything—from anyone, anywhere.
      </p>
      <div class="d-flex justify-content-center justify-content-lg-start gap-3 mb-4">
        <a href="{{ route('listings') }}"
        class="btn btn-success btn-lg rounded-pill shadow-sm px-4">
        Shop Now
      </a>
      <a href="#features"
      class="btn btn-outline-success btn-lg rounded-pill shadow-sm px-4">
      Learn More
    </a>
  </div>
  <div class="d-flex flex-wrap gap-4 align-items-center justify-content-center justify-content-lg-start small text-secondary">
    <div class="d-flex align-items-center">
      <i class="fas fa-shield-alt fs-5 me-2 text-success"></i>
      <span>Secure &amp; Trusted</span>
    </div>
    <div class="d-flex align-items-center">
      <i class="fas fa-cogs fs-5 me-2 text-success"></i>
      <span>Custom Orders Available</span>
    </div>
  </div>
</div>

<!-- Hero Image -->
<div class="mt-5 mt-lg-0 text-center flex-shrink-0">
  <img
  src="{{ asset('assets/images/illustrator.webp') }}"
  alt="World map with shopping icons"
  class="img-fluid rounded-lg shadow"
  style="max-width: 600px;"
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
      <h2 class="h3 fw-bold mb-0">Featured products for You</h2>
      <a href="{{ route('listings') }}" class="text-success text-decoration-none small">
        See All Products
      </a>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($featuredProducts as $item)
   <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
  @empty
  <div class="col-12 text-center text-muted">
    No featured products at this time.
  </div>
  @endforelse
</div>
</div>
</section>

<!-- Most Trending Services -->
<section class="py-5 bg-white">
  <div class="container max-w-7xl mx-auto px-4">
    <header class="mb-4 text-center">
      <h2 class="display-5 fw-bold text-dark">Most Trending Services</h2>
      <p class="text-muted mt-2">Recently viewed & more</p>
    </header>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($services as $item)
       <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
  @empty
  <p class="col-12 text-center text-muted fs-5 fw-medium">No services available.</p>
  @endforelse
</div>
</div>
</section>

<!-- Featured Digital Downloads -->
<section class="bg-light py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h3 fw-bold mb-0">Featured digital downloads for You</h2>
      <a href="{{ route('listings') }}" class="text-success text-decoration-none small">
        See All Products
      </a>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($featuredDigitals as $item)
       <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
  @empty
  <div class="col-12 text-center text-muted">
    No featured products at this time.
  </div>
  @endforelse
</div>
</div>
</section>


<!-- About the company section -->
<section class="about-home py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center text-center mb-5">
      <div class="col-10 col-lg-8">
        <h2>Who is Cetsy?</h2>
        <h5 class="text-warning fw-normal mb-3">Who is Cetsy? "Cetsy" is a Malagasy word which means, "that's it"</h5>
        <h4>Your global market place where one can find almost everything from everyone, everywhere.</h4>
      </div>
    </div>

    <div class="row gy-4">
      <div class="col-md-6 col-lg-4">
        <div class="h-100 p-3 border rounded shadow-sm">
          <h3><a href="{{ url('/about') }}" class="text-decoration-none">How we started</a></h3>
          <p>Cetsy is a global e-commerce Marketplace, founded in 2021 with the intent to better connect all global markets. It is a privately held company based in Ohio USA, which allows for the sale of nearly any item that a seller can legally sell in his /her geographical region/country or state.</p>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="h-100 p-3 border rounded shadow-sm">
          <h3><a href="#" class="text-decoration-none">What we do</a></h3>
          <p>We connect buyers and sellers globally within Cetsy Marketplace, while offering multiple secure payment solutions to cater to the many needs of our Buyers and we rarely limit the creativity of our Sellers. <a href="/about" class="text-warning fw-bold">Read More</a></p>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="h-100 p-3 border rounded shadow-sm">
          <h3><a href="/login" class="text-decoration-none">Start Now</a></h3>
          <p>To become a Cetsy Seller in just a few simple steps, please review the Cetsy Seller Agreement which outlines what we expect from our Sellers and what you as a Seller, can expect from Cetsy. Should you have further questions or require assistance, feel free to email us, or reach us on LIVE CHAT.</p>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
