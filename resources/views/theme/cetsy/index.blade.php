@extends('theme.'.theme().'.layouts.app')

@section('main')

<!-- ====== Page Styles (scoped) ====== -->
<style>
  /* Spacing helpers */
  .py-6 { padding-top: 4rem; padding-bottom: 4rem; }
  .pt-6 { padding-top: 4rem; }
  .pb-6 { padding-bottom: 4rem; }

  /* Smooth fade/slide entrance */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .reveal { animation: fadeUp .6s ease forwards; opacity: 0; }
  .reveal-delay-1 { animation-delay: .08s; }
  .reveal-delay-2 { animation-delay: .16s; }
  .reveal-delay-3 { animation-delay: .24s; }
  .reveal-delay-4 { animation-delay: .32s; }

  /* Buttons */
  .btn-pill { border-radius: 999px; }
  .btn-soft-success {
    background: rgba(25,135,84,.08);
    color: #198754;
    border: 1px solid rgba(25,135,84,.25);
  }
  .btn-soft-success:hover {
    background: rgba(25,135,84,.12);
    border-color: rgba(25,135,84,.35);
    color: #157347;
  }

  /* Hero styling */
  #hero {
    background:
      radial-gradient(1200px 600px at -10% -10%, rgba(25,135,84,.10), transparent 60%),
      radial-gradient(1200px 600px at 110% 0%, rgba(25,135,84,.08), transparent 60%),
      linear-gradient(180deg, #ffffff, #f8fafb);
  }
  .hero-bubble {
    position: absolute; border-radius: 999px; filter: blur(10px); opacity: .55;
    animation: floatY 7s ease-in-out infinite;
  }
  .bubble-1 { width: 160px; height: 160px; background: rgba(25,135,84,.18); top: -30px; left: -30px; }
  .bubble-2 { width: 120px; height: 120px; background: rgba(25,135,84,.12); bottom: -20px; right: 10%; animation-delay: .8s; }
  .bubble-3 { width: 90px; height: 90px; background: rgba(25,135,84,.10); top: 10%; right: -30px; animation-delay: 1.4s; }

  @keyframes floatY {
    0%,100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
  }

  /* Cards & grids */
  .card-flat {
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 1rem;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    background: #fff;
  }
  .card-flat:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 28px rgba(16,24,40,.10);
    border-color: rgba(25,135,84,.25);
  }
  .ratio-cover img { object-fit: cover; }

  /* Section headers */
  .section-head .eyebrow {
    display: inline-flex; align-items: center; gap: .5rem; padding: .35rem .75rem;
    border: 1px solid rgba(25,135,84,.25); color: #198754; background: rgba(25,135,84,.06);
    border-radius: 999px; font-weight: 600; font-size: .85rem;
  }

  /* Feature band (mini benefits) */
  .feature-chip {
    display: flex; align-items: center; gap: .6rem; padding: .8rem 1rem;
    border: 1px solid rgba(0,0,0,.06); border-radius: .85rem; background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
    font-weight: 600; color: #198754;
  }

  /* About section premium */
  .about-hero {
    background: radial-gradient(1200px 600px at 10% -10%, rgba(25,135,84,.12), transparent 60%),
                radial-gradient(1200px 600px at 110% 0%, rgba(25,135,84,.08), transparent 60%),
                linear-gradient(180deg, #ffffff, #f8fafb);
    border-radius: 1.25rem;
    border: 1px solid rgba(0,0,0,.05);
  }
  .stat-chip {
    display: inline-flex; align-items: center; gap: .5rem; padding: .5rem .75rem;
    border-radius: 999px; background: #fff; border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 2px 10px rgba(0,0,0,.04); font-weight: 600; color: #198754; white-space: nowrap;
  }
  .feature-icon {
    width: 3rem; height: 3rem; display: inline-flex; align-items: center; justify-content: center;
    border-radius: .75rem; background: rgba(25,135,84,.10); color: #198754; font-size: 1.2rem;
  }
  .link-arrow { display: inline-flex; align-items: center; gap: .5rem; font-weight: 600; color: #198754; text-decoration: none; }
  .link-arrow:hover { text-decoration: underline; }

  .cta-bar {
    border-radius: 1rem;
    background: linear-gradient(180deg, rgba(25,135,84,.08), rgba(25,135,84,.06));
    border: 1px solid rgba(25,135,84,.15);
  }
</style>

<!-- ===================================== -->
<!-- Hero Section -->
<!-- ===================================== -->
<section id="hero" class="hero-compact py-4 py-lg-5 position-relative overflow-hidden bg-light bg-gradient">
  <style>
    /* Compact hero spacing */
    #hero.hero-compact { padding-top: 1.25rem; padding-bottom: 1.25rem; }
    @media (min-width: 992px) {
      #hero.hero-compact { padding-top: 2rem; padding-bottom: 2rem; }
    }

    /* Illustration: limit overall size & height */
    .hero-img {
      width: 100%;
      height: auto;
      max-width: 460px;   /* cap width on large screens */
      max-height: 320px;  /* cap height on large screens */
    }
    @media (max-width: 991.98px) {
      .hero-img {
        max-width: 360px;   /* smaller on tablets/phones */
        max-height: 260px;  /* shorter on tablets/phones */
      }
    }

    /* Tighten vertical spacing inside hero */
    #hero .display-4 { margin-bottom: .5rem !important; }
    #hero .lead { margin-bottom: 1rem !important; }
    #hero .cta-group { gap: .75rem !important; margin-bottom: 1rem !important; }
    #hero .trust-badges { gap: 1rem !important; }
  </style>

  <!-- Floating bubbles -->
  <span class="hero-bubble bubble-1"></span>
  <span class="hero-bubble bubble-2"></span>
  <span class="hero-bubble bubble-3"></span>

  <div class="container position-relative">
    <div class="row align-items-center g-4">
      <!-- Hero Text -->
      <div class="col-lg-6 text-center text-lg-start reveal">
        <h1 class="display-4 fw-bold text-success mb-2">
          Cetsy — Your Global Marketplace
        </h1>

        <p class="lead text-muted" style="max-width: 520px;">
          Your global marketplace where you’ll find almost anything—from anyone, anywhere.
        </p>

        <div class="cta-group d-flex justify-content-center justify-content-lg-start gap-3">
          <a href="{{ route('listings') }}" class="btn btn-success btn-lg btn-pill shadow px-4">
            <i class="fas fa-shopping-bag me-2"></i> Shop Now
          </a>
          <a href="#features" class="btn btn-outline-success btn-lg btn-pill shadow px-4">
            <i class="fas fa-info-circle me-2"></i> Learn More
          </a>
        </div>

        <div class="trust-badges d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start small text-secondary">
          <div class="d-flex align-items-center me-3 mb-2">
            <i class="fas fa-shield-alt fs-5 me-2 text-success"></i>
            <span>Secure &amp; Trusted</span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-cogs fs-5 me-2 text-success"></i>
            <span>Custom Orders Available</span>
          </div>
        </div>
      </div>

      <!-- Hero Image -->
      <div class="col-lg-6 text-center reveal reveal-delay-2">
        <img
          src="{{ asset('assets/images/illustrator.webp') }}"
          alt="World map with shopping icons"
          class="img-fluid hero-img rounded shadow">
      </div>
    </div>
  </div>
</section>


<!-- ===================================== -->
<!-- Mini Feature Band (target for Learn More) -->
<!-- ===================================== -->
<section id="features" class="py-4">
  <div class="container">
    <div class="row g-3 justify-content-center">
      <div class="col-12 col-md-6 col-lg-4 reveal">
        <div class="feature-chip">
          <i class="fas fa-lock"></i> Buyer Protection & Secure Payments
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4 reveal reveal-delay-1">
        <div class="feature-chip">
          <i class="fas fa-truck"></i> Global Shipping & Local Sellers
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4 reveal reveal-delay-2">
        <div class="feature-chip">
          <i class="fas fa-star"></i> Curated Trending Picks Daily
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================================== -->
<!-- Trending Categories -->
<!-- ===================================== -->
<section class="py-5">
  <div class="container">
    <div class="section-head d-flex align-items-center justify-content-between mb-4">
      <div>
        <span class="eyebrow"><i class="fas fa-th-large"></i> Categories</span>
        <h2 class="h3 fw-bold mb-0 mt-2">Trending Categories</h2>
      </div>
    </div>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3">
      @foreach($categories as $cat)
        <div class="col reveal">
          <a href="{{ route('category.show', $cat->slug) }}" class="text-decoration-none">
            <div class="card-flat h-100">
              <div class="ratio ratio-1x1 overflow-hidden rounded-top ratio-cover">
                @if($cat->image)
                  <img src="{{ asset('storage/'.$cat->image) }}" alt="{{ $cat->name }}" class="w-100 h-100">
                @else
                  <div class="d-flex align-items-center justify-content-center h-100 bg-secondary text-white fw-semibold">
                    {{ $cat->name }}
                  </div>
                @endif
              </div>
              <div class="card-body p-2">
                <p class="text-center text-dark small fw-semibold mb-0">{{ $cat->name }}</p>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ===================================== -->
<!-- Featured Products -->
<!-- ===================================== -->
@include('theme.'.theme().'.partials.product-carousel', [
    'items' => $featuredProducts,
    'title' => Auth::check() ? 'Just for You' : 'Discover What\'s Hot',
    'subtitle' => Auth::check() ? 'Curated from your orders, favorites, and recent views.' : 'Sign in to personalize your picks and get recommendations tailored to you.',
    'eyebrow' => Auth::check() ? 'Tailored Picks' : 'Trending Now',
    'eyebrowIcon' => Auth::check() ? 'fa-wand-magic-sparkles' : 'fa-fire',
    'seeMoreUrl' => route('listings', ['type' => 'physical']),
    'seeMoreLabel' => 'Browse all products'
])

<!-- ===================================== -->
<!-- Most Trending Services -->
<!-- ===================================== -->
<section class="py-5 bg-white">
  <div class="container max-w-7xl mx-auto px-4">
    <header class="mb-4 text-center">
      <span class="eyebrow"><i class="fas fa-bolt"></i> Services</span>
      <h2 class="display-6 fw-bold text-dark mt-2">Most Trending Services</h2>
      <p class="text-muted mt-2">Recently viewed &amp; more</p>
    </header>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($services as $item)
        <div class="col-6 col-md-3 col-lg-3 reveal">
          @include('theme.'.theme().'.partials.product-card', ['item' => $item])
        </div>
      @empty
        <p class="col-12 text-center text-muted fs-5 fw-medium">No services available.</p>
      @endforelse
    </div>

    @if($services->count() > 0)
      <div class="text-center mt-4 reveal reveal-delay-2">
        <a href="{{ route('listings', ['type' => 'service']) }}" class="btn btn-success btn-lg btn-pill px-4">
          <i class="fas fa-briefcase me-2"></i> View All Services
        </a>
      </div>
    @endif
  </div>
</section>

<!-- ===================================== -->
<!-- Featured Digital Downloads -->
<!-- ===================================== -->
<section class="bg-light py-5">
  <div class="container">
    <div class="section-head d-flex align-items-center justify-content-between mb-4">
      <div>
        <span class="eyebrow"><i class="fas fa-download"></i> Digital</span>
        <h2 class="h3 fw-bold mb-0 mt-2">Featured Digital Downloads for You</h2>
      </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      @forelse($featuredDigitals as $item)
        <div class="col-6 col-md-3 col-lg-3 reveal">
          @include('theme.'.theme().'.partials.product-card', ['item' => $item])
        </div>
      @empty
        <div class="col-12 text-center text-muted">
          No featured products at this time.
        </div>
      @endforelse
    </div>

    @if($featuredDigitals->count() > 0)
      <div class="text-center mt-4 reveal reveal-delay-2">
        <a href="{{ route('listings', ['type' => 'digital']) }}" class="btn btn-success btn-lg btn-pill px-4">
          <i class="fas fa-cloud-download-alt me-2"></i> View All Digital Downloads
        </a>
      </div>
    @endif
  </div>
</section>

<!-- ===================================== -->
<!-- About the company section (Enhanced with Font Awesome icons) -->
<!-- ===================================== -->
<section class="about-home position-relative py-6 bg-white">
  <div class="container">
    <!-- Hero / Intro -->
    <div class="about-hero p-4 p-md-5 mb-5 text-center reveal">
      <div class="mx-auto" style="max-width: 900px;">
        <span class="stat-chip mb-3">
          <i class="fas fa-star"></i>
          Since 2021 • Global Marketplace
        </span>

        <h2 class="display-6 fw-bold text-dark mb-2">Who is Cetsy?</h2>
        <p class="lead mb-3 text-secondary">
          <span class="fw-semibold text-success">“Cetsy”</span> is a Malagasy word that means <em>“that’s it”</em>.
        </p>
        <p class="fs-5 text-muted mb-4">
          Your global marketplace where anyone can find almost everything—from everyone, everywhere.
        </p>

        <!-- Quick stats -->
        <div class="d-flex flex-wrap justify-content-center gap-2">
          <span class="stat-chip"><i class="fas fa-users"></i> 50k+ Buyers</span>
          <span class="stat-chip"><i class="fas fa-store"></i> 10k+ Sellers</span>
          <span class="stat-chip"><i class="fas fa-globe"></i> 80+ Countries</span>
        </div>
      </div>
    </div>

    <!-- 3 Feature Cards -->
    <div class="row g-4">
      <!-- How we started -->
      <div class="col-md-6 col-lg-4 reveal">
        <article class="card-flat p-4 h-100">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon">
              <i class="fas fa-rocket"></i>
            </div>
            <h3 class="h4 mb-0">How we started</h3>
          </div>
          <p class="text-muted mb-3">
            Cetsy is a global e-commerce marketplace founded in 2021 to better connect world markets.
            Privately held and based in Ohio, USA, it enables sellers to list nearly any item they can legally sell
            in their region—safely and simply.
          </p>
          <a class="link-arrow" href="{{ url('/about') }}">
            Learn our story <i class="fas fa-arrow-right"></i>
          </a>
        </article>
      </div>

      <!-- What we do -->
      <div class="col-md-6 col-lg-4 reveal reveal-delay-1">
        <article class="card-flat p-4 h-100">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon">
              <i class="fas fa-project-diagram"></i>
            </div>
            <h3 class="h4 mb-0">What we do</h3>
          </div>
          <p class="text-muted mb-3">
            We connect buyers and sellers globally with secure, flexible payment options, while empowering
            creators to sell with minimal limits on creativity inside the Cetsy Marketplace.
            <a href="{{ url('/about') }}" class="fw-semibold text-success text-decoration-none">Read more</a>.
          </p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-light border"><i class="fas fa-shield-alt"></i> Secure</span>
            <span class="badge rounded-pill text-bg-light border"><i class="fas fa-dollar-sign"></i> Multiple Payments</span>
            <span class="badge rounded-pill text-bg-light border"><i class="fas fa-tachometer-alt"></i> Scalable</span>
          </div>
        </article>
      </div>

      <!-- Start Now -->
      <div class="col-md-6 col-lg-4 reveal reveal-delay-2">
        <article class="card-flat p-4 h-100">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <h3 class="h4 mb-0">Start now</h3>
          </div>
          <p class="text-muted mb-3">
            Become a Cetsy Seller in a few steps. Review the Seller Agreement to see what we expect—and what
            you can expect from us. Questions? Email us or use LIVE CHAT anytime.
          </p>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ url('/login') }}" class="btn btn-success btn-pill">
              <i class="fas fa-sign-in-alt"></i> Sign in
            </a>
            <a href="{{ url('/register') }}" class="btn btn-soft-success btn-pill">
              <i class="fas fa-user-plus"></i> Create account
            </a>
          </div>
        </article>
      </div>
    </div>

    <!-- CTA Bar -->
    <div class="cta-bar mt-5 p-4 p-md-5 text-center reveal reveal-delay-3">
      <div class="row align-items-center gy-3">
        <div class="col-lg-7 mx-auto">
          <h4 class="fw-bold mb-1 text-dark">Ready to explore the global marketplace?</h4>
          <p class="text-muted mb-0">Discover unique items, support creators, and sell to a worldwide audience.</p>
        </div>
        <div class="col-lg-8 mx-auto mt-3">
          <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ url('/about') }}" class="btn btn-outline-success btn-pill">
              <i class="fas fa-info-circle"></i> About Cetsy
            </a>
            <a href="{{ url('/listings') }}" class="btn btn-success btn-pill">
              <i class="fas fa-compass"></i> Explore Marketplace
            </a>
            <a href="{{ url('/register') }}" class="btn btn-success btn-pill">
              <i class="fas fa-store"></i> Become a Seller
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

@endsection
