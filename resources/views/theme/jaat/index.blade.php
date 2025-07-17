@extends('theme.'.theme().'.layouts.app')

@section('main')
  {{-- ───────────────────────── Hero ───────────────────────── --}}
  <section id="hero" class="py-6 py-lg-7 position-relative overflow-hidden"
           style="background: linear-gradient(135deg, #f7fdf8 0%, #e7f9e9 100%);">
    {{-- Decorative blob --}}
    <svg class="position-absolute top-0 end-0 opacity-25 d-none d-md-block"
         width="460" height="510" viewBox="0 0 460 510" fill="none" xmlns="http://www.w3.org/2000/svg"
         style="transform: translate(50%, -20%);">
      <path d="M358 0C420 32 470 73 458 146 446 220 372 325 294 392 215 460 132 490 79 467 26 444 2 368 0 283 -3 198 17 102 87 54 158 7 295 -23 358 0Z"
            fill="#d1f5e0"/>
    </svg>

    <div class="container position-relative">
      <div class="row align-items-center gy-5">
        {{-- Hero text --}}
        <div class="col-lg-6 text-center text-lg-start" data-aos="fade-right">
          <h1 class="display-4 fw-bold text-success mb-3">Jaat – Kenya’s Marketplace</h1>
          <p class="lead text-muted mb-4">
            Discover, buy, and sell one-of-a-kind items from trusted Kenyans—right here on Jaat.
          </p>

          <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 mb-5">
            <a href="{{ route('listings') }}"
               class="btn btn-success btn-lg rounded-pill shadow px-4">
              <i class="fa-solid fa-shop me-2"></i> Shop Now
            </a>
            <a href="#features"
               class="btn btn-outline-success btn-lg rounded-pill shadow px-4">
              Learn More
            </a>
          </div>

          <div class="d-flex flex-wrap gap-4 justify-content-center justify-content-lg-start text-secondary small">
            <span class="d-inline-flex align-items-center">
              <i class="fa-solid fa-shield-halved me-2 text-success"></i> Secure &amp; Trusted
            </span>
            <span class="d-inline-flex align-items-center">
              <i class="fa-solid fa-mobile-screen-button me-2 text-success"></i> M-Pesa &amp; Cards
            </span>
          </div>
        </div>

        {{-- Hero image --}}
        <div class="col-lg-6 text-center" data-aos="fade-left">
          <img src="{{ asset('assets/images/illustrator.webp') }}"
               alt="Kenya map illustration with shopping icons"
               class="img-fluid rounded-4 shadow-lg"
               style="max-width: 540px;">
        </div>
      </div>
    </div>
  </section>

  {{-- ───────────────── Trending Categories ───────────────── --}}
  <section class="py-6">
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
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-success text-white fw-semibold">
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
        <h2 class="h3 fw-bold mb-0">Featured for You</h2>
        <a href="{{ route('listings') }}" class="text-success small fw-semibold">
          See All <i class="fa-solid fa-arrow-right-long ms-1"></i>
        </a>
      </div>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @forelse($featuredProducts as $product)
          <div class="col" data-aos="zoom-in">
            <div class="card h-100 border-0 shadow-sm product-card">
              <a href="{{ route('listing.show', $product) }}" class="d-block">
                @if($img = $product->media->first())
                  <img src="{{ asset('storage/'.$img->url) }}"
                       alt="{{ $product->name }}"
                       class="card-img-top"
                       style="height: 200px; object-fit: cover;"
                       loading="lazy">
                @else
                  <div class="bg-secondary h-100 d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                    No Image
                  </div>
                @endif
              </a>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate mb-1">
                  <a href="{{ route('listing.show', $product) }}" class="text-dark">{{ $product->name }}</a>
                </h5>
                <p class="text-success fw-bold mb-3">KES {{ number_format($product->price, 2) }}</p>
                <div class="mt-auto">
                  <a href="{{ route('listing.show', $product) }}"
                     class="btn btn-outline-success btn-sm">
                    <i class="fa-solid fa-eye me-1"></i> View Listing
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

  {{-- ───────────────── Trending Services ───────────────── --}}
  <section class="py-6">
    <div class="container">
      <header class="text-center mb-4">
        <h2 class="display-6 fw-bold">Most Trending Services</h2>
        <p class="text-muted">Recently viewed &amp; more</p>
      </header>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @forelse($services as $service)
          <div class="col" data-aos="zoom-in">
            <div class="card h-100 border-0 shadow-sm">
              <a href="{{ route('listing.show', $service) }}">
                @if($img = $service->media->first())
                  <img src="{{ asset('storage/'.$img->url) }}"
                       alt="{{ $service->name }}"
                       class="card-img-top"
                       style="height: 200px; object-fit: cover;"
                       loading="lazy">
                @else
                  <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                    No Image
                  </div>
                @endif
              </a>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate mb-1">{{ $service->name }}</h5>
                <p class="text-success fw-bold mb-3">KES {{ number_format($service->price, 2) }}</p>
                <div class="mt-auto">
                  <a href="{{ route('listing.show', $service) }}"
                     class="btn btn-outline-success btn-sm">
                    <i class="fa-solid fa-eye me-1"></i> View Listing
                  </a>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center text-muted fs-5 fw-medium">No services available.</div>
        @endforelse
      </div>
    </div>
  </section>

  {{-- ───────────────── About Jaat ───────────────── --}}
  <section class="py-6 bg-white">
    <div class="container">
      <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8" data-aos="fade-up">
          <h2 class="fw-bold">Who is Jaat?</h2>
          <p class="text-warning mb-2">
            “Jaat” is short for <em>Jua Kali Artisans’ Trade</em> – celebrating Kenyan creativity.
          </p>
          <p class="lead">
            We’re Kenya’s marketplace for finding almost anything from everyone, everywhere in the country.
          </p>
        </div>
      </div>
      <div class="row gy-4" data-aos="fade-up">
        <div class="col-md-6 col-lg-4">
          <div class="h-100 p-4 border rounded-4 shadow-sm">
            <h4><a href="{{ url('/about') }}" class="text-decoration-none">How We Started</a></h4>
            <p class="mb-0">
              Founded in Nairobi in 2024 as a passion project to empower local makers.
              Today we connect shoppers and sellers country-wide—from Kisumu to Mombasa.
            </p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="h-100 p-4 border rounded-4 shadow-sm">
            <h4><a href="#" class="text-decoration-none">What We Do</a></h4>
            <p class="mb-0">
              A secure online mall linking Kenyan buyers with verified sellers. Pay via M-Pesa,
              card or wallet in just a few taps.
              <a href="/about" class="fw-semibold text-warning">Read More</a>
            </p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="h-100 p-4 border rounded-4 shadow-sm">
            <h4><a href="/login" class="text-decoration-none">Start Selling</a></h4>
            <p class="mb-3">
              Open your Jaat shop in minutes. Review the Seller Guidelines, list your products or services,
              and reach customers across Kenya. Need help? Chat live or email us—we’re here 24/7.
            </p>
            <a href="/register" class="btn btn-success btn-sm rounded-pill">Join Free</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('styles')
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
        integrity="sha512-Ho+jjFzJFDN5XvPy6PAW9hqDfeaoCcZWagV2metM3y8FPCPlffpcajvQ1QHBYsBo81uDUfMcqbsVbWMH5b9YxA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <style>
    .product-card:hover .card-img-top { transform: scale(1.05); transition: .4s; }
    .category-card:hover img       { transform: scale(1.1);  transition: .4s; }
  </style>
@endpush

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
          integrity="sha512-QwrpYofeCvTR6rIhiuQr8y43X5YLkO3Ocdm+pNsoKmIV7xdZtc7N69n5xrF/PGG3v8tSWlKnOpE+UsWgkh7mXA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      AOS.init({ duration: 800, once: true });
    });
  </script>
@endpush
