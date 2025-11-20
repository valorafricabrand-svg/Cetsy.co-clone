@extends('theme.'.theme().'.layouts.app')

@section('main')
@php
use Illuminate\Support\Str;
@endphp

<!-- ====== Page Styles (scoped) ====== -->
<style>
/* Spacing helpers */
.py-6 {
    padding-top: 4rem;
    padding-bottom: 4rem;
}

.pt-6 {
    padding-top: 4rem;
}

.pb-6 {
    padding-bottom: 4rem;
}

/* Smooth fade/slide entrance */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.reveal {
    animation: fadeUp .6s ease forwards;
    opacity: 0;
}

.reveal-delay-1 {
    animation-delay: .08s;
}

.reveal-delay-2 {
    animation-delay: .16s;
}

.reveal-delay-3 {
    animation-delay: .24s;
}

.reveal-delay-4 {
    animation-delay: .32s;
}

/* Buttons */
.btn-pill {
    border-radius: 999px;
}

.btn-soft-success {
    background: rgba(25, 135, 84, .08);
    color: #198754;
    border: 1px solid rgba(25, 135, 84, .25);
}

.btn-soft-success:hover {
    background: rgba(25, 135, 84, .12);
    border-color: rgba(25, 135, 84, .35);
    color: #157347;
}

/* Hero styling (Argos-style promo card) */
#hero {
    background: linear-gradient(180deg, #f3f4f6, #ffffff);
}

.hero-promo-card {
    border-radius: 1.75rem;
    background: radial-gradient(140% 180% at 0% 0%, #ffffff 0, #f97373 35%, #e60012 70%);
    color: #ffffff;
    padding: 2rem 2.25rem;
    box-shadow: 0 30px 60px rgba(0, 0, 0, .18);
    position: relative;
    overflow: hidden;
}

.hero-promo-card::before {
    content: '';
    position: absolute;
    inset: 12px;
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, .18);
    pointer-events: none;
}

.hero-promo-copy {
    position: relative;
    z-index: 1;
}

.hero-promo-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .25rem .75rem;
    border-radius: .75rem;
    background: #ffffff;
    color: #e60012;
    font-weight: 800;
    text-transform: uppercase;
    font-size: .8rem;
    letter-spacing: .08em;
    margin-bottom: .5rem;
}

.hero-promo-heading {
    font-size: clamp(1.9rem, 2.6vw + 1.2rem, 2.7rem);
    font-weight: 800;
    line-height: 1.05;
    margin-bottom: .75rem;
}

.hero-promo-sub {
    font-size: 1.05rem;
    max-width: 26rem;
    color: rgba(255, 255, 255, .9);
}

.hero-promo-cta {
    margin-top: 1.25rem;
}

.hero-promo-cta .btn {
    border-radius: 999px;
}

.hero-promo-media {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-promo-media img {
    max-width: 100%;
    height: auto;
    border-radius: 1rem;
    box-shadow: 0 24px 48px rgba(15, 23, 42, .35);
}

.hero-slider {
    position: relative;
}

.hero-slide {
    display: none;
}

.hero-slide.is-active {
    display: block;
}

.hero-slider-dots {
    display: flex;
    justify-content: center;
    gap: .4rem;
    margin-top: .75rem;
}

.hero-slider-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    border: 0;
    padding: 0;
    background: rgba(255, 255, 255, .5);
    cursor: pointer;
}

.hero-slider-dot.is-active {
    width: 20px;
    background: #ffffff;
}

.hero-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .9);
    color: #e60012;
    box-shadow: 0 12px 24px rgba(0, 0, 0, .25);
    cursor: pointer;
}

.hero-slider-arrow-prev {
    left: 0.75rem;
}

.hero-slider-arrow-next {
    right: 0.75rem;
}

@media (min-width: 992px) {
    .hero-slider {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

@media (max-width: 991.98px) {
    .hero-slider-arrow {
        display: none;
    }
}

@media (max-width: 991.98px) {
    .hero-promo-card {
        border-radius: 1.5rem;
        padding: 1.5rem 1.25rem 1.75rem;
    }

    .hero-promo-heading {
        font-size: 1.9rem;
    }

    .hero-promo-media {
        margin-top: 1.25rem;
    }
}

/* Compact hero height (Argos-like) */
.hero-compact {
    padding-top: clamp(1.25rem, 3vw, 2.25rem) !important;
    padding-bottom: clamp(1.25rem, 3vw, 2.25rem) !important;
}

.hero-compact .hero-promo-card {
    padding: 1.5rem 1.75rem;
}

@media (min-width: 992px) {
    .hero-compact {
        padding-top: clamp(1.5rem, 2.5vw, 2.5rem) !important;
        padding-bottom: clamp(1.5rem, 2.5vw, 2.5rem) !important;
    }

    .hero-compact .hero-promo-card {
        padding: 1.75rem 2rem;
    }

    .hero-compact .hero-promo-media img {
        max-height: 380px;
        width: auto;
    }
}

@media (max-width: 991.98px) {
    .hero-compact .hero-promo-card {
        padding: 1.25rem 1.25rem 1.5rem;
    }
}

/* Hero search (Argos-style inspiration) */
.hero-search-form {
    max-width: 640px;
    margin-bottom: 0.75rem;
}

.hero-search-shell {
    display: flex;
    align-items: stretch;
    gap: .5rem;
    background: #fff;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, .12);
    box-shadow: 0 10px 30px rgba(15, 23, 42, .10);
    padding: .25rem .5rem .25rem .75rem;
}

.hero-search-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 1.1rem;
}

.hero-search-input.form-control {
    border: 0;
    box-shadow: none;
    padding-left: .5rem;
    padding-right: .5rem;
}

.hero-search-input.form-control:focus {
    outline: 0;
    box-shadow: none;
}

.hero-search-submit {
    border-radius: 999px;
    padding-inline: 1.5rem;
    white-space: nowrap;
}

.hero-quick-links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
}

.hero-quick-links-label {
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #6b7280;
    font-weight: 700;
}

.hero-category-chip {
    border-radius: 999px;
    border: 1px solid rgba(25, 135, 84, .25);
    background: rgba(25, 135, 84, .04);
    color: #065f46;
    font-size: .85rem;
    padding: .25rem .9rem;
    text-decoration: none;
}

.hero-category-chip:hover {
    background: rgba(25, 135, 84, .10);
    color: #064e3b;
}

@media (max-width: 575.98px) {
    .hero-search-shell {
        padding-inline: .5rem;
    }

    .hero-search-submit {
        padding-inline: 1.1rem;
        font-size: .9rem;
    }
}

/* Cards & grids */
.card-flat {
    border: 1px solid rgba(0, 0, 0, .06);
    border-radius: 1rem;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    background: #fff;
}

.card-flat:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 28px rgba(16, 24, 40, .10);
    border-color: rgba(25, 135, 84, .25);
}

.ratio-cover img {
    object-fit: cover;
}

/* Section headers */
.section-head .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem .75rem;
    border: 1px solid rgba(25, 135, 84, .25);
    color: #198754;
    background: rgba(25, 135, 84, .06);
    border-radius: 999px;
    font-weight: 600;
    font-size: .85rem;
}

/* Feature band (mini benefits) */
.feature-chip {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .8rem 1rem;
    border: 1px solid rgba(0, 0, 0, .06);
    border-radius: .85rem;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .04);
    font-weight: 600;
    color: #198754;
}

/* About section premium */
.about-hero {
    background: radial-gradient(1200px 600px at 10% -10%, rgba(25, 135, 84, .12), transparent 60%),
        radial-gradient(1200px 600px at 110% 0%, rgba(25, 135, 84, .08), transparent 60%),
        linear-gradient(180deg, #ffffff, #f8fafb);
    border-radius: 1.25rem;
    border: 1px solid rgba(0, 0, 0, .05);
}

.stat-chip {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .75rem;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, .06);
    box-shadow: 0 2px 10px rgba(0, 0, 0, .04);
    font-weight: 600;
    color: #198754;
    white-space: nowrap;
}

.feature-icon {
    width: 3rem;
    height: 3rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .75rem;
    background: rgba(25, 135, 84, .10);
    color: #198754;
    font-size: 1.2rem;
}

.link-arrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-weight: 600;
    color: #198754;
    text-decoration: none;
}

.link-arrow:hover {
    text-decoration: underline;
}

.cta-bar {
    border-radius: 1rem;
    background: linear-gradient(180deg, rgba(25, 135, 84, .08), rgba(25, 135, 84, .06));
    border: 1px solid rgba(25, 135, 84, .15);
}

/* Top sellers slider */
.top-sellers-section {
    background: #f8fafc;
}

.top-sellers-slider {
    position: relative;
    overflow: hidden;
}

.top-sellers-track {
    display: flex;
    gap: 1rem;
    scroll-behavior: smooth;
    overflow-x: auto;
    padding: .25rem;
    scrollbar-width: none;
}

.top-sellers-track::-webkit-scrollbar {
    display: none;
}

.top-seller-card {
    min-width: 260px;
    max-width: 320px;
    flex: 0 0 auto;
}

.top-seller-meta {
    font-size: .875rem;
}

.top-seller-actions {
    color: #198754;
    font-weight: 600;
}

.top-seller-nav .btn {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 767.98px) {
    .top-seller-card {
        min-width: 240px;
    }
}
</style>

<!-- ===================================== -->
<!-- Hero Section -->
<!-- ===================================== -->
<section id="hero" class="hero-compact py-4 py-lg-5 position-relative overflow-hidden">
    @php
    $topCategories = ($categories instanceof \Illuminate\Support\Collection)
    ? $categories->take(6)
    : collect($categories ?? [])->take(6);
    $heroImageFallback = asset('assets/images/illustrator.webp');
    $slides = isset($heroSlides) && $heroSlides instanceof \Illuminate\Support\Collection
    ? $heroSlides
    : collect();
    @endphp

    <div class="container position-relative">
        @if($slides->isNotEmpty())
        <div class="hero-slider" data-hero-slider>
            @foreach($slides as $index => $slide)
            @php
            $tag = $slide->tag ?: 'Save';
            $title = $slide->title;
            $sub = $slide->subtitle ?: 'Discover limited-time offers across the Cetsy marketplace.';
            $btnLabel = $slide->button_label ?: 'Shop deals';
            $btnUrl = $slide->resolved_button_url;
            $img = $slide->image_path ? asset('storage/'.$slide->image_path) : $heroImageFallback;
            @endphp
            <div class="hero-promo-card hero-slide reveal {{ $index === 0 ? 'is-active' : '' }}"
                data-slide-index="{{ $index }}">
                <div class="row g-4 align-items-center">
                    <!-- Left: copy -->
                    <div class="col-lg-5 col-md-6">
                        <div class="hero-promo-copy text-center text-md-start">
                            @if($tag)
                            <span class="hero-promo-tag">{{ $tag }}</span>
                            @endif

                            <h1 class="hero-promo-heading mb-2">{{ $title }}</h1>

                            <p class="hero-promo-sub mb-0">{{ $sub }}</p>

                            <div
                                class="hero-promo-cta d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-md-start">
                                <a href="{{ $btnUrl }}" class="btn btn-light text-danger fw-semibold">
                                    <i class="fas fa-tags me-1"></i> {{ $btnLabel }}
                                </a>
                                <a href="{{ route('listings') }}" class="btn btn-outline-light fw-semibold">
                                    Browse marketplace
                                </a>
                            </div>

                            {{-- Mobile search under promo (desktop uses header search) --}}
                            <form class="hero-search-form mx-auto mt-3 d-md-none" method="GET"
                                action="{{ route('search') }}" role="search">
                                <div class="hero-search-shell">
                                    <span class="hero-search-icon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <label for="heroSearch" class="visually-hidden">Search for products</label>
                                    <input id="heroSearch" type="search" name="q" class="form-control hero-search-input"
                                        placeholder="Search for products, brands and shops"
                                        aria-label="Search for products, brands and shops" value="{{ request('q') }}"
                                        autocomplete="on">
                                    <button class="btn btn-success hero-search-submit" type="submit">
                                        Search
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>

                    <!-- Right: promo artwork -->
                    <div class="col-lg-7 col-md-6">
                        <div class="hero-promo-media text-center">
                            <img src="{{ $img }}" alt="{{ $title }}" class="img-fluid"
                                onerror="this.onerror=null;this.src=@json($heroImageFallback);">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            @if($slides->count() > 1)
            <button class="hero-slider-arrow hero-slider-arrow-prev" type="button" data-hero-prev>
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hero-slider-arrow hero-slider-arrow-next" type="button" data-hero-next>
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="hero-slider-dots" data-hero-dots>
                @foreach($slides as $index => $slide)
                <button type="button" class="hero-slider-dot {{ $index === 0 ? 'is-active' : '' }}"
                    data-hero-dot="{{ $index }}">
                    <span class="visually-hidden">Go to slide {{ $index + 1 }}</span>
                </button>
                @endforeach
            </div>
            @endif
        </div>
        @else
        {{-- Fallback single hero when no slides are defined --}}
        <div class="hero-promo-card reveal">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5 col-md-6">
                    <div class="hero-promo-copy text-center text-md-start">
                        <span class="hero-promo-tag">Save</span>
                        <h1 class="hero-promo-heading mb-2">Shop our lowest prices on selected items</h1>
                        <p class="hero-promo-sub mb-0">
                            Discover limited-time offers across electronics, services, and more - all from trusted Cetsy
                            sellers.
                        </p>
                        <div
                            class="hero-promo-cta d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-md-start">
                            <a href="{{ route('listings', ['sort' => 'popular']) }}"
                                class="btn btn-light text-danger fw-semibold">
                                <i class="fas fa-tags me-1"></i> Shop deals
                            </a>
                            <a href="{{ route('listings') }}" class="btn btn-outline-light fw-semibold">
                                Browse marketplace
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-6">
                    <div class="hero-promo-media text-center">
                        <img src="{{ $heroImageFallback }}" alt="Featured Cetsy deals" class="img-fluid"
                            onerror="this.onerror=null;this.src=@json(asset('assets/images/default-og-image-cetsy.jpg'));">
                    </div>
                </div>
            </div>
        </div>
        @endif
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
<!-- Deals & Inspiration Strip (Argos-style, backed by Deal model) -->
<!-- ===================================== -->
<section class="py-4 py-md-5 bg-white border-top border-bottom">
    <div class="container">
        <div
            class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between g-3 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center"
                    style="width:52px;height:52px;">
                    <i class="fas fa-tags fa-lg"></i>
                </div>
                <div>
                    <div class="text-uppercase small fw-bold text-success mb-1">
                        @if(isset($activeDeals) && $activeDeals->count())
                        Today&rsquo;s highlighted deals
                        @else
                        Today&rsquo;s highlighted
                        @endif
                    </div>
                    <h2 class="h4 fw-bold mb-0">Deals &amp; Inspiration</h2>
                    <p class="mb-0 text-muted small">
                        @if(isset($activeDeals) && $activeDeals->count())
                        Save on limited‑time offers picked from our marketplace.
                        @else
                        Hand‑picked offers and ideas to get you started.
                        @endif
                    </p>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row flex-wrap gap-2">
                @if(isset($activeDeals) && $activeDeals->count())
                @foreach($activeDeals as $deal)
                <a href="{{ route('listings', ['deal' => $deal->id]) }}"
                    class="btn btn-outline-success btn-pill text-start text-md-center">
                    <span class="d-block fw-semibold">
                        <i class="fas fa-tag me-1"></i>{{ $deal->name }}
                    </span>
                    @if($deal->discount_percent)
                    <span class="small text-muted">{{ $deal->discount_percent }}% off</span>
                    @endif
                </a>
                @endforeach
                @else
                <a href="{{ route('listings', ['sort' => 'popular']) }}" class="btn btn-outline-success btn-pill">
                    <i class="fas fa-fire me-1"></i> Top picks
                </a>
                <a href="{{ route('listings', ['type' => 'digital']) }}" class="btn btn-outline-success btn-pill">
                    <i class="fas fa-download me-1"></i> Digital deals
                </a>
                <a href="{{ route('listings', ['type' => 'service']) }}" class="btn btn-outline-success btn-pill">
                    <i class="fas fa-briefcase me-1"></i> Service bundles
                </a>
                @endif
                <a href="{{ route('listings') }}" class="btn btn-success btn-pill">
                    <i class="fas fa-compass me-1"></i> View all deals
                </a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('[data-hero-slider]');
    if (!slider) return;
    const slides = Array.from(slider.querySelectorAll('.hero-slide'));
    if (!slides.length) return;
    const dotsContainer = slider.querySelector('[data-hero-dots]');
    const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll('[data-hero-dot]')) : [];
    const prevBtn = slider.querySelector('[data-hero-prev]');
    const nextBtn = slider.querySelector('[data-hero-next]');
    let current = 0;
    let timer = null;

    function show(index) {
        if (!slides[index]) return;
        slides[current].classList.remove('is-active');
        if (dots[current]) dots[current].classList.remove('is-active');
        current = index;
        slides[current].classList.add('is-active');
        if (dots[current]) dots[current].classList.add('is-active');
    }

    function next() {
        const idx = (current + 1) % slides.length;
        show(idx);
    }

    function prev() {
        const idx = (current - 1 + slides.length) % slides.length;
        show(idx);
    }

    function startAuto() {
        if (timer || slides.length < 2) return;
        timer = setInterval(next, 5000);
    }

    function stopAuto() {
        if (!timer) return;
        clearInterval(timer);
        timer = null;
    }

    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            const idx = parseInt(dot.getAttribute('data-hero-dot'), 10);
            if (!isNaN(idx)) {
                stopAuto();
                show(idx);
                startAuto();
            }
        });
    });

    if (prevBtn) prevBtn.addEventListener('click', function() {
        stopAuto();
        prev();
        startAuto();
    });
    if (nextBtn) nextBtn.addEventListener('click', function() {
        stopAuto();
        next();
        startAuto();
    });

    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    show(0);
    startAuto();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('[data-top-seller-slider]');
    const track = document.querySelector('[data-top-seller-track]');
    const prevBtn = document.querySelector('[data-top-seller-prev]');
    const nextBtn = document.querySelector('[data-top-seller-next]');
    if (!slider || !track) return;

    function slide(offset) {
        const step = Math.max(track.clientWidth * 0.8, 240);
        const tolerance = 6;
        const maxScroll = track.scrollWidth - track.clientWidth;
        const atEnd = track.scrollLeft >= maxScroll - tolerance;
        const atStart = track.scrollLeft <= tolerance;
        if (offset > 0 && atEnd) {
            track.scrollTo({
                left: 0,
                behavior: 'smooth'
            });
        } else if (offset < 0 && atStart) {
            track.scrollTo({
                left: maxScroll,
                behavior: 'smooth'
            });
        } else {
            track.scrollBy({
                left: offset * step,
                behavior: 'smooth'
            });
        }
    }

    if (prevBtn) prevBtn.addEventListener('click', () => {
        stopAuto();
        slide(-1);
        startAuto();
    });
    if (nextBtn) nextBtn.addEventListener('click', () => {
        stopAuto();
        slide(1);
        startAuto();
    });

    let autoTimer = null;
    const startAuto = () => {
        if (!autoTimer) autoTimer = setInterval(() => slide(1), 5000);
    };
    const stopAuto = () => {
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }
    };

    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);
    startAuto();
});
</script>
@endpush

<!-- ===================================== -->
<!-- Popular Items (slider) -->
<!-- ===================================== -->
@include('theme.'.theme().'.partials.product-carousel', [
'items' => $featuredProducts,
'title' => 'Popular Items',
'subtitle' => 'Trending picks from trusted sellers across the marketplace.',
'eyebrow' => 'Hot right now',
'eyebrowIcon' => 'fa-fire',
'seeMoreUrl' => route('listings', ['sort' => 'popular']),
'seeMoreLabel' => 'Browse all products'
])

<!-- ===================================== -->
<!-- Just for You -->
<!-- ===================================== -->
@include('theme.'.theme().'.partials.product-carousel', [
'items' => $featuredProducts,
'title' => 'Just for You',
'subtitle' => Auth::check()
? 'Curated from your favorites, orders, and recent views.'
: 'Sign in to personalize picks from your favorites and recent views.',
'eyebrow' => 'Recommended',
'eyebrowIcon' => 'fa-wand-magic-sparkles',
'seeMoreUrl' => route('listings', ['sort' => 'popular']),
'seeMoreLabel' => 'See more picks'
])

<!-- ===================================== -->
<!-- Top Sellers -->
<!-- ===================================== -->
@php
$topShops = ($shops instanceof \Illuminate\Support\Collection)
? $shops->take(6)
: collect($shops ?? [])->take(6);
@endphp
@if($topShops->isNotEmpty())
<section class="py-5 top-sellers-section">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <span class="eyebrow"><i class="fas fa-store"></i> Top Sellers</span>
                <h2 class="h3 fw-bold mb-1 mt-2">Featured Shops</h2>
                <p class="text-muted mb-0">Discover trusted sellers and explore their latest drops.</p>
            </div>
            <div class="top-seller-nav d-flex gap-2">
                <button class="btn btn-outline-success" type="button" aria-label="Previous shops" data-top-seller-prev>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-outline-success" type="button" aria-label="Next shops" data-top-seller-next>
                    <i class="fas fa-chevron-right"></i>
                </button>
                <a href="{{ route('shops.index') }}" class="btn btn-success btn-pill ms-1">View all shops</a>
            </div>
        </div>

        <div class="top-sellers-slider" data-top-seller-slider>
            <div class="top-sellers-track" data-top-seller-track>
                @foreach($topShops as $shop)
                <div class="top-seller-card card border-0 shadow-sm reveal">
                    <a href="{{ route('shop.show', $shop->slug) }}" class="text-decoration-none h-100 d-flex">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <img src="{{ $shop->logo ? ($shop->logo_url ?? asset('storage/' . ltrim($shop->logo, '/'))) : (setting('favicon_url') ?: asset('assets/images/default-og-image-cetsy.jpg')) }}"
                                        alt="{{ $shop->name }} logo" class="img-fluid rounded-circle"
                                        style="max-height:56px; max-width:56px;"
                                        onerror="this.onerror=null;this.src=@json(setting('favicon_url') ?: asset('assets/images/default-og-image-cetsy.jpg'));">
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark">{{ $shop->name }}</h5>
                                    <span class="text-muted small">{{ $shop->completed_orders_count ?? 0 }} completed
                                        orders</span>
                                </div>
                            </div>

                            <p class="text-muted small mb-3 flex-grow-1 top-seller-meta">
                                {{ Str::limit(strip_tags($shop->description ?? 'Explore curated items from this shop.'), 110) }}
                            </p>

                            <div class="d-flex align-items-center justify-content-between top-seller-actions mt-auto">
                                <span class="badge bg-success bg-opacity-10 text-success">Top rated</span>
                                <span>View shop <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

@if($topShops->isNotEmpty())
<section class="py-4">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <span class="eyebrow"><i class="fas fa-gem"></i> Featured Shops</span>
                <h3 class="h4 fw-bold mb-0 mt-2">Curated sellers to explore</h3>
            </div>
            <a href="{{ route('shops.index') }}" class="btn btn-outline-success btn-pill">Browse shops</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($topShops->take(4) as $shop)
            <div class="col">
                <a href="{{ route('shop.show', $shop->slug) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                    style="width:52px;height:52px;">
                                    <img src="{{ $shop->logo ? ($shop->logo_url ?? asset('storage/' . ltrim($shop->logo, '/'))) : (setting('favicon_url') ?: asset('assets/images/default-og-image-cetsy.jpg')) }}"
                                        alt="{{ $shop->name }} logo" class="img-fluid rounded-circle"
                                        style="max-height:52px; max-width:52px;"
                                        onerror="this.onerror=null;this.src=@json(setting('favicon_url') ?: asset('assets/images/default-og-image-cetsy.jpg'));">
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark">{{ $shop->name }}</h5>
                                    <span class="text-muted small">{{ $shop->completed_orders_count ?? 0 }} completed
                                        orders</span>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ Str::limit(strip_tags($shop->description ?? 'Discover unique finds from this shop.'), 90) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ===================================== -->
<!-- Most Trending Services (carousel) -->
@include('theme.'.theme().'.partials.product-carousel', [
'items' => $services,
'title' => 'Most Trending Services',
'subtitle' => 'Recently viewed and in-demand service providers.',
'eyebrow' => 'Services',
'eyebrowIcon' => 'fa-bolt',
'seeMoreUrl' => route('listings', ['type' => 'service']),
'seeMoreLabel' => 'View all services'
])

<!-- Featured Digital Downloads (carousel) -->
@include('theme.'.theme().'.partials.product-carousel', [
'items' => $featuredDigitals,
'title' => 'Featured Digital Downloads for You',
'subtitle' => 'Original music, e-books, templates, recipes, and more.',
'eyebrow' => 'Digital',
'eyebrowIcon' => 'fa-download',
'seeMoreUrl' => route('listings', ['type' => 'digital']),
'seeMoreLabel' => 'View all digitals'
])

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
                    Since 2021 &bull; Global Marketplace
                </span>

                <h2 class="display-6 fw-bold text-dark mb-2">Who is Cetsy?</h2>
                <p class="lead mb-3 text-secondary">
                    <span class="fw-semibold text-success">&ldquo;Cetsy&rdquo;</span> is a Malagasy word that means
                    <em>&ldquo;that&rsquo;s it&rdquo;</em>.
                </p>
                <p class="fs-5 text-muted mb-4">
                    Your global marketplace where anyone can find almost everything&mdash;from everyone, everywhere.
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
                        Cetsy is a global e-commerce marketplace, founded in 2021 to better connect world markets. As a
                        privately held company, we enable sellers to list nearly any item they can legally sell in their
                        region - safely and simply.
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
                        <a href="{{ url('/about') }}" class="fw-semibold text-success text-decoration-none">Read
                            more</a>.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill text-bg-light border"><i class="fas fa-shield-alt"></i>
                            Secure</span>
                        <span class="badge rounded-pill text-bg-light border"><i class="fas fa-dollar-sign"></i>
                            Multiple Payments</span>
                        <span class="badge rounded-pill text-bg-light border"><i class="fas fa-tachometer-alt"></i>
                            Scalable</span>
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
                        Become a Cetsy Seller in a few steps. Review the Seller Agreement to see what we expect and what
                        you can expect from us. Questions? Use our LIVE CHAT anytime.
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
                    <p class="text-muted mb-0">Discover unique items, support creators, and sell to a worldwide
                        audience.</p>
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