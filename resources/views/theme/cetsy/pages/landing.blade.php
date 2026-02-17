@extends('theme.cetsy.layouts.landing')

@section('title', config('app.name', 'Cetsy') . ' | Mobile-First Marketplace')
@section('meta_description', 'Cetsy mobile-first landing experience built with Tailwind CSS.')

@section('main')
<div class="landing-shell pb-28 md:pb-14">
    <div class="landing-orb landing-orb-one" aria-hidden="true"></div>
    <div class="landing-orb landing-orb-two" aria-hidden="true"></div>

    <div class="mx-auto w-full max-w-md px-4 pt-5 md:max-w-6xl md:px-6 md:pt-8">
        <div class="app-shell" data-reveal>
            <header class="app-topbar">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                    <span>9:41</span>
                    <div class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        <span class="h-2.5 w-5 rounded-sm border border-slate-400"></span>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-600 text-sm font-extrabold text-white">C</span>
                        <span>
                            <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Cetsy</span>
                            <span class="block text-sm font-bold text-slate-900">Marketplace</span>
                        </span>
                    </a>
                    <div class="hidden items-center gap-3 md:flex">
                        <a href="{{ route('listings') }}" class="text-sm font-semibold text-slate-700 transition hover:text-slate-900">Explore</a>
                        <a href="{{ route('become-seller') }}" class="text-sm font-semibold text-slate-700 transition hover:text-slate-900">Sell</a>
                        <a href="{{ route('contact') }}" class="text-sm font-semibold text-slate-700 transition hover:text-slate-900">Contact</a>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:border-slate-300" aria-label="Login">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
                        </a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">Get Started</a>
                    </div>
                </div>
            </header>

            <section class="mt-4 grid gap-4 md:grid-cols-[1.15fr_0.85fr] md:gap-6" data-reveal>
                <article class="hero-card">
                    <span class="gradient-chip">Mobile first, conversion focused</span>
                    <h1 class="mt-3 text-3xl font-bold leading-tight text-slate-950 md:text-5xl">
                        Shop, chat, and track orders like a real app.
                    </h1>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 md:max-w-xl md:text-base">
                        We redesigned the landing for touch-first navigation so customers can browse listings, follow shops, and checkout with less friction from any phone.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <a href="{{ route('listings') }}" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">Browse Listings</a>
                        <a href="{{ route('become-seller') }}" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Open a Shop</a>
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-2.5">
                        <div class="stat-pill">
                            <p class="text-lg font-bold text-slate-900">2.4x</p>
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Session time</p>
                        </div>
                        <div class="stat-pill">
                            <p class="text-lg font-bold text-slate-900">94%</p>
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Mobile traffic</p>
                        </div>
                        <div class="stat-pill">
                            <p class="text-lg font-bold text-slate-900">18k+</p>
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Active listings</p>
                        </div>
                    </div>
                </article>

                <aside class="demo-phone" aria-label="Mobile app preview">
                    <div class="demo-screen">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">For you</p>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">Live deals</span>
                        </div>

                        <div class="preview-card">
                            <p class="text-xs font-semibold text-slate-500">Trending now</p>
                            <p class="mt-1 text-base font-bold text-slate-900">Handmade pendant lamps</p>
                            <p class="mt-1 text-xs text-slate-500">From 42 curated shops this week</p>
                        </div>

                        <div class="mt-3 space-y-2.5">
                            <article class="feed-item">
                                <div class="feed-thumb feed-thumb-1"></div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Natural ceramic vase set</p>
                                    <p class="text-xs text-slate-500">Ships in 2-4 days</p>
                                </div>
                                <p class="text-sm font-bold text-slate-900">$32</p>
                            </article>
                            <article class="feed-item">
                                <div class="feed-thumb feed-thumb-2"></div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Handwoven table runner</p>
                                    <p class="text-xs text-slate-500">Free shipping over $49</p>
                                </div>
                                <p class="text-sm font-bold text-slate-900">$18</p>
                            </article>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="mt-4 grid gap-3 md:mt-6 md:grid-cols-3" data-reveal>
                <article class="feature-card">
                    <p class="section-kicker">Smart discovery</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Category rails made for thumbs</h2>
                    <p class="mt-2 text-sm text-slate-600">Swipe-friendly content blocks reduce bounce and keep users exploring deep into catalog pages.</p>
                </article>
                <article class="feature-card">
                    <p class="section-kicker">Instant trust</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Ratings and delivery data up front</h2>
                    <p class="mt-2 text-sm text-slate-600">Customers see seller reliability before tapping product details, increasing checkout confidence.</p>
                </article>
                <article class="feature-card">
                    <p class="section-kicker">Faster checkout</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">One-flow purchase journey</h2>
                    <p class="mt-2 text-sm text-slate-600">The layout keeps payment progress visible so buyers finish orders without confusion.</p>
                </article>
            </section>

            <section class="mt-4 rounded-3xl bg-slate-900 p-5 text-white md:mt-6 md:p-7" data-reveal>
                <div class="md:flex md:items-start md:justify-between md:gap-8">
                    <div>
                        <p class="section-kicker text-emerald-300">Built for mobile storefronts</p>
                        <h3 class="mt-2 text-2xl font-bold md:text-3xl">Your landing now feels native on phones.</h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            This foundation is ready for your next blocks: testimonials, featured collections, seller stories, and campaign promos.
                        </p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2.5 md:mt-0">
                        <a href="{{ route('register') }}" class="rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">Create account</a>
                        <a href="{{ route('contact') }}" class="rounded-xl border border-slate-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:border-slate-400">Talk to team</a>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <nav class="mobile-dock md:hidden" aria-label="Mobile app dock">
        <a href="{{ route('landing') }}" class="dock-link is-active" data-dock-target>
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
            <span>Home</span>
        </a>
        <a href="{{ route('listings') }}" class="dock-link" data-dock-target>
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <span>Explore</span>
        </a>
        <a href="{{ route('become-seller') }}" class="dock-link" data-dock-target>
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M6 7V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/><path d="M5 7h14l-1 12H6z"/></svg>
            <span>Sell</span>
        </a>
        <a href="{{ route('contact') }}" class="dock-link" data-dock-target>
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Support</span>
        </a>
    </nav>
</div>
@endsection
