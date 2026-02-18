@extends('theme.'.theme().'.layouts.app')

@section('main')
@php
  $__about = App\Models\PolicySection::where('slug','about-cetsy')->first();
@endphp

@if($__about && trim((string)$__about->content) !== '')
{!! $__about->content !!}
@else
  @push('styles')
  <style>
    @keyframes aboutFadeUp {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .about-reveal {
      opacity: 0;
      animation: aboutFadeUp .6s ease forwards;
    }

    .about-reveal.d1 { animation-delay: .08s; }
    .about-reveal.d2 { animation-delay: .16s; }
    .about-reveal.d3 { animation-delay: .24s; }

    .about-img-card {
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 10px 24px rgba(16, 24, 40, .12);
      transition: transform .25s ease, box-shadow .25s ease;
    }

    .about-img-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 36px rgba(16, 24, 40, .16);
    }
  </style>
  @endpush

  <section class="relative overflow-x-clip bg-white py-12">
    <div class="pointer-events-none absolute -right-24 -top-20 h-72 w-72 rounded-full bg-emerald-200/35 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-20 top-[24rem] h-72 w-72 rounded-full bg-cyan-200/25 blur-3xl"></div>

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
        <div class="hidden lg:block">
          <div class="grid grid-cols-2 gap-3">
            <div class="about-reveal">
              <div class="about-img-card">
                <img src="{{ asset('build/assets/images/cetsybout.jpg') }}" alt="Cetsy Boutique" class="h-full w-full object-cover">
              </div>
            </div>
            <div class="about-reveal d1">
              <div class="about-img-card">
                <img src="{{ asset('build/assets/images/cetsyabout3.jpg') }}" alt="About Cetsy" class="h-full w-full object-cover">
              </div>
            </div>

            <div class="about-reveal d2 relative col-span-2 mt-1">
              <div class="about-img-card">
                <img src="{{ asset('build/assets/images/cetsyabout2.jpg') }}" alt="Cetsy Community" class="h-64 w-full object-cover">
              </div>

              <div class="absolute -right-2 -bottom-2 opacity-15">
                <svg width="120" height="90" xmlns="http://www.w3.org/2000/svg">
                  @foreach(range(10, 110, 20) as $x)
                    <circle cx="{{ $x }}" cy="70" r="3" fill="#198754" />
                  @endforeach
                </svg>
              </div>

              <div class="absolute left-4 bottom-4 rounded-xl border border-slate-200 bg-white/90 px-3 py-2 text-sm font-semibold text-emerald-700 shadow-lg backdrop-blur">
                <i class="fas fa-globe mr-1"></i> Global since 2021
              </div>
            </div>
          </div>
        </div>

        <div class="about-reveal d3">
          <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
            <i class="fas fa-store"></i> Welcome to Cetsy
          </span>

          <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
            About {{ config('app.name','Cetsy') }}: an online marketplace
          </h1>

          <p class="mt-4 text-base text-slate-600">
            {{ config('app.name','Cetsy') }} is an online marketplace platform that connects independent sellers
            with buyers. We provide the website, listing tools, and checkout to help facilitate transactions,
            but sellers are responsible for their own products, fulfillment, and customer service.
          </p>

          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-emerald-700 shadow-sm">
              <i class="fas fa-shield-alt"></i> Buyer Protection
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-emerald-700 shadow-sm">
              <i class="fas fa-wallet"></i> Multiple Payment Options
            </div>
          </div>

          <h2 class="mt-5 text-lg font-semibold text-slate-900">What we do</h2>
          <p class="mt-2 text-slate-600">
            We help buyers discover unique products from independent sellers, and we help sellers run their
            shops with tools for listings, orders, and payments. {{ config('app.name','Cetsy') }} is operated
            from <strong>{{ operating_region() }}</strong>.
          </p>

          <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-emerald-700"><i class="fas fa-users"></i> 50k+ Buyers</span>
              <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-emerald-700"><i class="fas fa-store"></i> 10k+ Sellers</span>
              <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-emerald-700"><i class="fas fa-box-open"></i> Physical & Digital</span>
            </div>
          </div>

          <h3 class="mt-5 text-sm font-semibold italic text-slate-700">Sellers</h3>
          <p class="mt-1 text-slate-600">
            Sellers on {{ config('app.name','Cetsy') }} are independent businesses. They set prices, create
            listings, ship orders, and handle returns/refunds in line with our policies.
          </p>

          <h3 class="mt-4 text-sm font-semibold text-slate-900">Allowed product types (clear &amp; limited)</h3>
          <p class="mt-1 text-slate-600">Sellers may list products that fit within these categories and comply with our rules:</p>
          <ul class="mt-2 list-disc space-y-1 pl-5 text-slate-600">
            <li>Handmade goods and artisan products</li>
            <li>Craft supplies and materials</li>
            <li>Art, prints, and stationery</li>
            <li>Home decor and accessories</li>
            <li>Fashion accessories and jewelry</li>
            <li>Vintage items (where permitted)</li>
            <li>Digital downloads (where offered and permitted)</li>
          </ul>

          <p class="mt-3 text-slate-600">
            Prohibited and restricted items are not allowed. See our
            <a href="{{ url('/prohibited-items') }}" class="font-medium text-emerald-700 hover:text-emerald-600">Prohibited / Restricted Items</a> list.
          </p>

          <ul class="mt-3 space-y-2 text-slate-600">
            <li><i class="fas fa-check-circle mr-2 text-emerald-600"></i>Verify local legality before posting a listing.</li>
            <li><i class="fas fa-check-circle mr-2 text-emerald-600"></i>Follow our <a href="{{ url('/seller-policy') }}" class="font-medium text-emerald-700 hover:text-emerald-600">Seller Policy</a> for a smooth experience.</li>
            <li><i class="fas fa-check-circle mr-2 text-emerald-600"></i>Reach support via <a href="{{ url('/contact') }}" class="font-medium text-emerald-700 hover:text-emerald-600">Contact</a>.</li>
          </ul>

          <p class="mt-3 text-slate-600">
            To become a seller, review our <a href="{{ url('/seller-policy') }}" class="font-medium text-emerald-700 hover:text-emerald-600">Seller Policy</a> and our
            <a href="{{ url('/terms') }}" class="font-medium text-emerald-700 hover:text-emerald-600">Terms &amp; Conditions</a>. Questions? Visit our
            <a href="{{ url('/contact') }}" class="font-medium text-emerald-700 hover:text-emerald-600">Contact</a> page.
          </p>

          <div class="mt-5 flex flex-wrap gap-2">
            <a href="{{ url('/login') }}" class="inline-flex items-center rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
              <i class="fas fa-user-check mr-2"></i> Get Started as a Seller
            </a>
            <a href="{{ url('/listings') }}" class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-5 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
              <i class="fas fa-compass mr-2"></i> Explore Marketplace
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endif
@endsection