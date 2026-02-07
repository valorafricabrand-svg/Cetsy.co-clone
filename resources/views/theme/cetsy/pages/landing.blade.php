@extends('theme.'.theme().'.layouts.landing')

@section('title', config('app.name', 'Cetsy') . ' | Tailwind Landing')
@section('meta_description', 'Tailwind-powered landing page starter for Cetsy theme.')

@section('main')
<div class="landing-shell relative">
    <section class="relative z-10 mx-auto grid max-w-6xl gap-12 px-6 pb-20 pt-10 md:grid-cols-2 md:pt-16">
        <div data-reveal>
            <p class="mb-5 inline-flex rounded-full border border-amber-200 bg-amber-100/70 px-4 py-1 text-xs font-semibold uppercase tracking-wider text-amber-900">
                Tailwind landing starter
            </p>

            <h1 class="text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">
                Launch your next
                <span class="text-teal-700">marketplace story</span>
                with a bold landing page.
            </h1>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                This page is now inside the Cetsy theme and uses Tailwind utilities through a dedicated landing entrypoint.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Create account
                </a>
                <a href="{{ route('become-seller') }}" class="rounded-full bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-600">
                    Become a seller
                </a>
                <a href="{{ route('listings') }}" class="rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500">
                    Explore listings
                </a>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="glass-card rounded-2xl p-4">
                    <p class="text-2xl font-bold text-slate-900">50k+</p>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Active buyers</p>
                </div>
                <div class="glass-card rounded-2xl p-4">
                    <p class="text-2xl font-bold text-slate-900">10k+</p>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Shops launched</p>
                </div>
                <div class="glass-card rounded-2xl p-4">
                    <p class="text-2xl font-bold text-slate-900">80+</p>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Countries reached</p>
                </div>
            </div>
        </div>

        <div class="hero-grid relative rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl md:p-8" data-reveal>
            <div class="hero-float rounded-2xl bg-slate-900 p-6 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Live campaign</p>
                <p class="mt-2 text-2xl font-semibold">Summer craft drop</p>
                <p class="mt-2 text-sm text-slate-300">New arrivals from handmade creators in 14 regions this week.</p>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-teal-700 p-4 text-white shadow-md">
                    <p class="text-xs uppercase tracking-wider text-teal-100">Conversion</p>
                    <p class="mt-2 text-3xl font-bold">+38%</p>
                    <p class="mt-1 text-xs text-teal-100">After launching focused story pages.</p>
                </div>
                <div class="rounded-2xl bg-amber-400 p-4 text-amber-950 shadow-md">
                    <p class="text-xs uppercase tracking-wider">Most viewed</p>
                    <p class="mt-2 text-lg font-bold">Artisan home decor</p>
                    <p class="mt-1 text-xs">Fresh handmade picks every day.</p>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm font-semibold text-slate-900">Today snapshot</p>
                <div class="mt-3 h-3 rounded-full bg-slate-100">
                    <div class="h-3 w-3/4 rounded-full bg-teal-700"></div>
                </div>
                <p class="mt-2 text-xs text-slate-500">74% of visitors reached product detail pages.</p>
            </div>
        </div>
    </section>

    <section class="relative z-10 mx-auto max-w-6xl px-6 pb-16">
        <div class="grid gap-4 md:grid-cols-3">
            <article class="glass-card rounded-2xl p-6" data-reveal>
                <p class="text-sm font-semibold text-teal-700">01. Fast setup</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">Component-first build</h2>
                <p class="mt-2 text-sm text-slate-600">Start with utility classes, then extract repeated patterns into reusable Blade components.</p>
            </article>
            <article class="glass-card rounded-2xl p-6" data-reveal>
                <p class="text-sm font-semibold text-teal-700">02. Safer rollout</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">No storefront breakage</h2>
                <p class="mt-2 text-sm text-slate-600">This page uses its own CSS entrypoint so existing theme pages stay untouched while you iterate.</p>
            </article>
            <article class="glass-card rounded-2xl p-6" data-reveal>
                <p class="text-sm font-semibold text-teal-700">03. Ready to scale</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">Mobile-first by default</h2>
                <p class="mt-2 text-sm text-slate-600">Responsive sections are already in place for hero, feature cards, and calls-to-action.</p>
            </article>
        </div>
    </section>

    <section class="relative z-10 mx-auto max-w-6xl px-6 pb-24">
        <div class="rounded-3xl bg-slate-900 px-8 py-10 text-white md:flex md:items-center md:justify-between">
            <div data-reveal>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-300">Next step</p>
                <h3 class="mt-2 text-3xl font-bold">Turn this into your full marketing landing page.</h3>
                <p class="mt-3 max-w-2xl text-sm text-slate-300">
                    Add your real sections next: testimonials, featured products, pricing, FAQ, and final conversion block.
                </p>
            </div>
            <div class="mt-6 flex flex-wrap gap-3 md:mt-0" data-reveal>
                <a href="{{ route('landing') }}" class="rounded-full bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-amber-300">
                    View landing
                </a>
                <a href="{{ url('/') }}" class="rounded-full border border-slate-600 px-5 py-3 text-sm font-semibold text-white hover:border-slate-400">
                    Back to home
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
