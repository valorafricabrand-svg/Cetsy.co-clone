@extends('theme.'.theme().'.layouts.app')

@section('title', 'Become a Seller on Cetsy')
@section('meta_description', 'Open a Cetsy shop to sell handmade products, services, and digital downloads to buyers around the world.')
@section('canonical_url', localized_route('become-seller'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
<section class="relative overflow-hidden py-10 lg:py-14">
  <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-16 bottom-0 h-64 w-64 rounded-full bg-rose-200/30 blur-3xl"></div>

  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid items-center gap-6 lg:grid-cols-[1.15fr_0.85fr]">
      <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
          How to Become a Seller at Cetsy.co
        </h1>

        <p class="mt-4 text-base text-slate-600 sm:text-lg">
          Thank you for your interest in becoming a <strong>Seller</strong> on Cetsy, a global marketplace where you can offer nearly anything, to anyone, anywhere.
        </p>

        <div class="mt-5 space-y-4 text-[15px] leading-7 text-slate-700 sm:text-base">
          <p>
            If you can legally sell the item in your country, you can probably list it on Cetsy. Tangible goods include (but are not limited to): household items, collectibles, jewelry, artwork, vehicles, pharmaceuticals, handmade crafts, real estate, outdoor equipment, and more. Each listing can include photos, videos, and audio.
          </p>
          <p>
            Digital products ("intangible items") are also welcome: original music, e-books, recipes, and other downloads.
          </p>
          <p>
            Not sure if your item qualifies?
            <a href="{{ url('/contact') }}" class="font-semibold text-emerald-700 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-600">
              Contact us via the Contact Us form
            </a>
            and we'll help.
          </p>
          <p>
            Ready to get started?
            <a href="{{ url('/user-agreement#seller-tips') }}" class="font-semibold text-rose-700 hover:text-rose-600">
              Review our Seller Agreement
            </a>
            to see both our expectations and your benefits as a Seller.
          </p>
          <p>
            See our
            <a href="https://docs.google.com/spreadsheets/d/169dQz2z0zhB8eXZ_yqWiq4IhWFQxc8BQYmY-kcymqRY/edit?usp=sharing" class="font-semibold text-emerald-700 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-600" target="_blank" rel="noopener">
              Listing Fees
            </a>
            for pricing details.
          </p>
        </div>

        <a href="{{ url('/register') }}" class="mt-6 inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
          Get Started
          <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </a>
      </article>

      <aside class="rounded-3xl border border-slate-200 bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm sm:p-7">
        <img
          src="{{ asset('assets/img/blog/blog-1.png') }}"
          alt="Become a Seller"
          class="mx-auto h-auto w-full max-w-sm"
          onerror='this.onerror=null;this.src=@json(asset("assets/images/cetsylogmain.png"));'
        >

        <div class="mt-5 rounded-2xl border border-emerald-200 bg-white/90 p-4">
          <p class="text-xs font-semibold uppercase tracking-[0.1em] text-emerald-700">Seller Snapshot</p>
          <p class="mt-1 text-sm text-slate-600">List physical or digital products, share rich media, and sell globally from one storefront.</p>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
