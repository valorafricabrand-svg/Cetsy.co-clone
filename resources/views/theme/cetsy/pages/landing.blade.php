@extends('theme.cetsy.layouts.landing')

@section('title', 'Cetsy | Products, Services & Digital Downloads from Across the Globe')
@section('meta_description', 'Discover products, services, and digital downloads from sellers across the globe on Cetsy.')
@section('canonical_url', route('home'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/default-og-image-cetsy.jpg'))

@section('main')
<div class="landing-shell pb-28 md:pb-10">
    <div class="landing-orb landing-orb-one" aria-hidden="true"></div>
    <div class="landing-orb landing-orb-two" aria-hidden="true"></div>

    <div class="mx-auto w-full max-w-6xl px-4 pt-4 md:px-6 md:pt-6">
        <header class="app-header" data-reveal>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ logo_url() }}" alt="{{ config('app.name', 'Cetsy') }}" class="h-10 w-10 rounded-xl object-contain"
                        onerror='this.onerror=null;this.src=@json(asset("assets/images/cetsylogmain.png"));'>
                    <span>
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.15em] text-emerald-700">Marketplace</span>
                        <span class="block text-lg font-extrabold text-slate-900">{{ config('app.name', 'Cetsy') }}</span>
                    </span>
                </a>
            </div>

            <form action="{{ route('search') }}" method="GET" class="hidden flex-1 md:block">
                <label for="landingSearch" class="sr-only">Search products</label>
                <div class="search-shell">
                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input id="landingSearch" name="q" value="{{ request('q') }}" type="search" placeholder="Search products, services, shops" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>

            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('listings') }}" class="nav-chip">Explore</a>
                <a href="{{ route('shops.index') }}" class="nav-chip">Shops</a>
                <a href="{{ route('become-seller') }}" class="nav-chip">Sell</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="nav-btn">Login</a>
                <a href="{{ route('register') }}" class="nav-btn nav-btn-primary">Create account</a>
            </div>
        </header>

        <section class="mt-4" data-reveal>
            <div class="feature-row">
                <a href="{{ url('/user-agreement#privacy') }}" class="feature-pill">Buyer/Seller Protection</a>
                <a href="{{ url('/user-agreement#buyer-tips') }}" class="feature-pill">Global Shipping</a>
                <a href="{{ route('listings', ['sort' => 'popular']) }}" class="feature-pill">Curated Trending Picks Daily</a>
            </div>
        </section>

        <section class="mt-4 grid gap-4 lg:grid-cols-[1.05fr_0.95fr]" data-reveal>
            <article class="hero-card">
                <span class="hero-tag">Save</span>
                <h1 class="mt-2 text-3xl font-extrabold leading-tight text-white md:text-5xl">
                    Shop our lowest prices on selected items
                </h1>
                <p class="mt-3 max-w-xl text-sm text-white/85 md:text-base">
                    Discover limited-time offers across electronics, services, and digital goods from trusted Cetsy sellers.
                </p>
                <div class="mt-4 flex flex-wrap gap-2.5">
                    <a href="{{ route('listings', ['sort' => 'popular']) }}" class="cta-white">Shop deals</a>
                    <a href="{{ route('listings') }}" class="cta-outline">Browse marketplace</a>
                </div>

                <form action="{{ route('search') }}" method="GET" class="mt-5 md:hidden">
                    <label for="landingSearchMobile" class="sr-only">Search products</label>
                    <div class="search-shell bg-white/95">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input id="landingSearchMobile" name="q" value="{{ request('q') }}" type="search" placeholder="Search products" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </form>
            </article>

            <aside class="panel-card">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-[0.12em] text-emerald-700">Deals & Inspiration</h2>
                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-semibold text-emerald-700">Today</span>
                </div>
                <div class="mt-3 space-y-2.5">
                    <a href="{{ route('listings', ['sort' => 'popular']) }}" class="quick-item">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Top picks</p>
                            <p class="text-xs text-slate-500">Trending products and bundles</p>
                        </div>
                        <span class="quick-badge">Explore</span>
                    </a>
                    <a href="{{ route('listings', ['type' => 'digital']) }}" class="quick-item">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Digital deals</p>
                            <p class="text-xs text-slate-500">Templates, e-books, guides</p>
                        </div>
                        <span class="quick-badge">Open</span>
                    </a>
                    <a href="{{ route('listings', ['type' => 'service']) }}" class="quick-item">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Service bundles</p>
                            <p class="text-xs text-slate-500">High-rated providers near you</p>
                        </div>
                        <span class="quick-badge">View</span>
                    </a>
                </div>
            </aside>
        </section>

        <section class="mt-5" data-reveal>
            <div class="section-head">
                <div>
                    <p class="section-kicker">Hot right now</p>
                    <h2 class="section-title">Popular Items</h2>
                </div>
                <a href="{{ route('listings', ['sort' => 'popular']) }}" class="section-link">Browse all</a>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <article class="product-card">
                    <div class="product-media media-1"></div>
                    <h3 class="mt-3 text-sm font-bold text-slate-900">Handmade wall basket set</h3>
                    <p class="mt-1 text-xs text-slate-500">By Nia Craft Studio</p>
                    <p class="mt-2 text-sm font-extrabold text-emerald-700">$28</p>
                </article>
                <article class="product-card">
                    <div class="product-media media-2"></div>
                    <h3 class="mt-3 text-sm font-bold text-slate-900">Natural scented candles</h3>
                    <p class="mt-1 text-xs text-slate-500">By Candle Bloom</p>
                    <p class="mt-2 text-sm font-extrabold text-emerald-700">$19</p>
                </article>
                <article class="product-card">
                    <div class="product-media media-3"></div>
                    <h3 class="mt-3 text-sm font-bold text-slate-900">Custom leather notebook</h3>
                    <p class="mt-1 text-xs text-slate-500">By Kivu Atelier</p>
                    <p class="mt-2 text-sm font-extrabold text-emerald-700">$36</p>
                </article>
                <article class="product-card">
                    <div class="product-media media-4"></div>
                    <h3 class="mt-3 text-sm font-bold text-slate-900">Woven cotton throw</h3>
                    <p class="mt-1 text-xs text-slate-500">By Loom House</p>
                    <p class="mt-2 text-sm font-extrabold text-emerald-700">$42</p>
                </article>
            </div>
        </section>

        <section class="mt-5 grid gap-3 lg:grid-cols-2" data-reveal>
            <article class="panel-card">
                <div class="section-head">
                    <div>
                        <p class="section-kicker">Services</p>
                        <h3 class="section-title">Most Trending Services</h3>
                    </div>
                    <a href="{{ route('listings', ['type' => 'service']) }}" class="section-link">View all</a>
                </div>
                <div class="mt-3 space-y-2.5">
                    <div class="quick-item"><p class="text-sm font-semibold text-slate-900">Brand Identity Package</p><span class="quick-badge">$120</span></div>
                    <div class="quick-item"><p class="text-sm font-semibold text-slate-900">Product Photography</p><span class="quick-badge">$90</span></div>
                    <div class="quick-item"><p class="text-sm font-semibold text-slate-900">Shop Setup Support</p><span class="quick-badge">$65</span></div>
                </div>
            </article>

            <article class="panel-card">
                <div class="section-head">
                    <div>
                        <p class="section-kicker">Digital</p>
                        <h3 class="section-title">Featured Digital Downloads</h3>
                    </div>
                    <a href="{{ route('listings', ['type' => 'digital']) }}" class="section-link">View all</a>
                </div>
                <div class="mt-3 space-y-2.5">
                    <div class="quick-item"><p class="text-sm font-semibold text-slate-900">Canva social bundle</p><span class="quick-badge">$14</span></div>
                    <div class="quick-item"><p class="text-sm font-semibold text-slate-900">Resume template pack</p><span class="quick-badge">$12</span></div>
                    <div class="quick-item"><p class="text-sm font-semibold text-slate-900">Monthly budget planner</p><span class="quick-badge">$8</span></div>
                </div>
            </article>
        </section>

        <section class="mt-5" data-reveal>
            <div class="section-head">
                <div>
                    <p class="section-kicker">Top sellers</p>
                    <h2 class="section-title">Featured Shops</h2>
                </div>
                <a href="{{ route('shops.index') }}" class="section-link">View all shops</a>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <article class="shop-card"><p class="text-sm font-bold text-slate-900">Ayo Design House</p><p class="mt-1 text-xs text-slate-500">132 completed orders</p></article>
                <article class="shop-card"><p class="text-sm font-bold text-slate-900">Mara Craft Market</p><p class="mt-1 text-xs text-slate-500">101 completed orders</p></article>
                <article class="shop-card"><p class="text-sm font-bold text-slate-900">Nomad Pottery</p><p class="mt-1 text-xs text-slate-500">88 completed orders</p></article>
                <article class="shop-card"><p class="text-sm font-bold text-slate-900">Sahara Studio</p><p class="mt-1 text-xs text-slate-500">75 completed orders</p></article>
            </div>
        </section>

        <section class="mt-5" data-reveal>
            <div class="about-panel">
                <p class="section-kicker">Who is {{ config('app.name', 'Cetsy') }}?</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900 md:text-3xl">Your global marketplace for handmade products, services, and digital goods.</h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600 md:text-base">Cetsy helps creators launch shops, reach global buyers, and grow with secure payments and trusted fulfillment flows.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="stat-chip">50k+ Buyers</span>
                    <span class="stat-chip">10k+ Sellers</span>
                    <span class="stat-chip">80+ Countries</span>
                </div>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    <a href="{{ url('/about') }}" class="nav-btn nav-btn-primary">About Cetsy</a>
                    <a href="{{ route('become-seller') }}" class="nav-btn">Become a Seller</a>
                </div>
            </div>
        </section>

        <section class="mt-5 rounded-3xl bg-slate-900 p-5 text-white md:p-8" data-reveal>
            <div class="md:flex md:items-center md:justify-between md:gap-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300">Ready to start?</p>
                    <h3 class="mt-2 text-2xl font-extrabold md:text-3xl">Join {{ config('app.name', 'Cetsy') }} and grow your shop globally.</h3>
                    <p class="mt-2 text-sm text-slate-300">Keep the familiar Cetsy experience while running on a modern Tailwind foundation.</p>
                </div>
                <div class="mt-4 flex flex-wrap gap-2.5 md:mt-0">
                    <a href="{{ route('register') }}" class="cta-emerald">Create account</a>
                    <a href="{{ route('contact') }}" class="cta-outline">Contact us</a>
                </div>
            </div>
        </section>
    </div>

    <nav class="mobile-dock md:hidden" aria-label="Mobile navigation">
        <a href="{{ route('home') }}" class="dock-link is-active"><span>Home</span></a>
        <a href="{{ route('listings') }}" class="dock-link"><span>Explore</span></a>
        <a href="{{ route('shops.index') }}" class="dock-link"><span>Shops</span></a>
        <a href="{{ route('become-seller') }}" class="dock-link"><span>Sell</span></a>
    </nav>
</div>
@endsection
