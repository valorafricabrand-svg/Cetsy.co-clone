<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="index, follow">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Dynamic Title -->
  <title>@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')</title>

  <!-- Primary Description -->
  <meta name="description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">

  <!-- Canonical -->
  <link rel="canonical" href="@yield('canonical_url', url()->current())">

  <!-- Open Graph / Twitter (override per-page via @section('social-meta')) -->
  @section('social-meta')
    <meta property="og:title" content="@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')">
    <meta property="og:description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:image" content="@yield('meta_image', asset('assets/images/default-og-image-cetsy.jpg'))">
    <meta property="og:image:alt" content="Cetsy — Handmade Products Marketplace">
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="{{ config('app.name', 'Cetsy') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')">
    <meta name="twitter:description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
    <meta name="twitter:image" content="@yield('meta_image', asset('assets/images/default-twitter-image-cetsy.jpg'))">
    <meta name="twitter:image:alt" content="Cetsy — Handmade Products Marketplace">
  @show

  <!-- Favicons -->
  @php
    $favicon = setting('favicon_url') ?: asset('assets/img/favicons/favicon-32x32.png');
  @endphp
  <link rel="apple-touch-icon" sizes="180x180" href="{{ $favicon }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ $favicon }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ $favicon }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ $favicon }}">
  <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="{{ $favicon }}">
  <meta name="theme-color" content="#ffffff">

  <!-- Performance: Preconnects -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Core Styles -->
  <link href="{{ asset('vendors/mapbox-gl/mapbox-gl.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">

  <!-- Font Awesome (CSS, for fas/fa icons) -->
  <link rel="stylesheet" href="{{ asset('vendors/fontawesome/css/all.min.css') }}">

  <!-- Page-level Styles (optional) -->
  @yield('styles')
  @stack('styles')

  <!-- Config (defines window.config) -->
  <script src="{{ asset('assets/js/config.js') }}" defer></script>

  <script>
    // Respect RTL setting from phoenix config (if present)
    document.addEventListener("DOMContentLoaded", function () {
      if (window.config && window.config.config && window.config.config.phoenixIsRTL) {
        document.documentElement.setAttribute('dir', 'rtl');
      }
    });
  </script>

  <!-- Structured Data (Website) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ config('app.name', 'Cetsy') }}",
    "url": "{{ url('/') }}",
    "potentialAction": {
      "@type": "SearchAction",
      "target": "{{ url('/search') }}?q={search_term_string}",
      "query-input": "required name=search_term_string"
    }
  }
  </script>

  <!-- Inline Theme Tweaks -->
  <style>
    :root { --brand-success: #198754; }
    .text-primary { color: #027333 !important; }
    .btn-link { color: #027333; }
    a.text-primary:hover, a.text-primary:focus { color: #025a1f !important; }
    body { font-family: "Nunito Sans", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif; }
  </style>

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('cartDropdown', () => ({
        items: [],
        fetchCart() {
          // TODO: Replace with AJAX logic if fetching cart dynamically
        }
      }));
    });
  </script>

  <!-- Allow pages to add stuff in head if needed -->
  @yield('head_scripts')
</head>

<body style="--phoenix-scroll-margin-top: 1.2rem;">
  <!-- ===============================================-->
  <!--    Main Content-->
  <!-- ===============================================-->
  <main class="main" id="top">
    <div class="bg-body-emphasis" data-navbar-shadow-on-scroll="true">

      {{-- NAVBAR --}}
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" aria-label="Main Navigation">
        <div class="container">
          {{-- Brand --}}
          <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img src="{{ setting('logo_url') }}" alt="{{ config('app.name', 'Cetsy') }} logo" height="60" width="auto" loading="lazy">
          </a>

          {{-- Mobile toggle --}}
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="mainNavbar">
            {{-- Search --}}
            <form class="d-flex me-3 flex-grow-1" method="GET" action="{{ route('search') }}" role="search">
              <label for="navbarSearch" class="visually-hidden">Search</label>
              <input
                id="navbarSearch"
                class="form-control flex-grow-1"
                type="search"
                name="q"
                placeholder="Search products, services, shops…"
                aria-label="Search"
                value="{{ request('q') }}"
                autocomplete="on"
              >
              <button class="btn btn-outline-secondary ms-2" type="submit" aria-label="Submit search">
                <i class="fas fa-search"></i>
              </button>
            </form>

            {{-- Cart + User --}}
            <ul class="navbar-nav ms-auto align-items-center" x-data="cartDropdown()" x-init="fetchCart()">
              @php $cartCount = (int) count(session('cart', [])); @endphp

              {{-- Cart --}}
              <li class="nav-item me-3">
                <a href="{{ route('cart.view') }}" class="nav-link position-relative text-dark" aria-label="View cart">
                  <i class="fas fa-shopping-cart fa-lg"></i>
                  @if($cartCount)
                    <span class="badge bg-success position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
                  @endif
                </a>
              </li>

              {{-- Auth --}}
              @guest
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('login') }}">Log In</a>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                  <a class="btn btn-success btn-sm" href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-1"></i> Sign Up
                  </a>
                </li>
              @else
                @if(auth()->user()->shop)
                  <li class="nav-item">
                    <a class="nav-link" href="{{ route('seller.shops.show', auth()->user()->shop) }}">My Shop</a>
                  </li>
                @endif

                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ auth()->user()->name }}
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Log Out</button>
                      </form>
                    </li>
                  </ul>
                </li>
              @endguest
            </ul>
          </div>
        </div>
      </nav>

      {{-- ======= Responsive Multi-level Category Nav ======= --}}
      @php
        $mainCategories = \App\Models\Category::with('childrenRecursive')
            ->whereNull('parent_id')->orderBy('id')->get();

        $renderCats = function ($nodes) use (&$renderCats){
          foreach($nodes as $cat){
            $kids = $cat->childrenRecursive;
            $has  = $kids->isNotEmpty();
            echo '<li class="dropdown-submenu'.($has?'':' no-children').'">';
            echo   '<a class="dropdown-item d-flex justify-content-between align-items-center"'.
                   ' href="'.($has?'#':route('category.show',$cat->slug)).'">';
            echo     e($cat->name);
            if($has) echo '<i class="fas fa-chevron-right ms-2 rotate" aria-hidden="true"></i>';
            echo   '</a>';
            if($has){
              echo '<ul class="dropdown-menu">'.PHP_EOL;
              $renderCats($kids);
              echo '</ul>'.PHP_EOL;
            }
            echo '</li>'.PHP_EOL;
          }
        };
      @endphp

      @if($mainCategories->isNotEmpty())
        <nav class="bg-success" aria-label="Category Navigation">
          <div class="container">
            <ul class="nav flex-wrap">
              @foreach($mainCategories as $main)
                <li class="nav-item dropdown">
                  <a class="nav-link text-white" href="#" id="catDD{{ $main->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $main->name }}
                    @if($main->childrenRecursive->isNotEmpty())
                      <i class="fas fa-chevron-down ms-1 rotate" aria-hidden="true"></i>
                    @endif
                  </a>

                  @if($main->childrenRecursive->isNotEmpty())
                    <ul class="dropdown-menu" aria-labelledby="catDD{{ $main->id }}">
                      {!! $renderCats($main->childrenRecursive) !!}
                    </ul>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        </nav>
      @endif

      @push('styles')
      <style>
        /* Multi-level dropdown polish */
        .dropdown-menu { min-width:230px; border-radius:.5rem; box-shadow:0 .5rem 1rem rgba(0,0,0,.08); max-height:72vh; overflow:auto; z-index:1055; }
        .dropdown-submenu > .dropdown-menu { top:-0.25rem; left:100%; margin-left:.15rem; }
        .dropdown-submenu.no-children > a .rotate { display:none; }

        .dropdown-item:hover, .dropdown-item:focus { background:#eaf7ef; color:#198754; }
        .rotate { transition:.25s transform; }
        .dropdown-submenu.show > a .rotate,
        .nav-item.dropdown.show > a .rotate { transform:rotate(90deg); }

        @media (min-width:992px) {
          .nav-item.dropdown:hover > .dropdown-menu { display:block; }
          .dropdown-submenu:hover  > .dropdown-menu { display:block; }
        }
      </style>
      @endpush

      @push('scripts')
      <script>
      document.addEventListener('DOMContentLoaded', () => {
        // Toggle/flip and overflow protection for submenus
        document.querySelectorAll('.dropdown-submenu > a').forEach(anchor => {
          anchor.addEventListener('click', e => {
            const sub = anchor.nextElementSibling;
            if (sub && sub.classList.contains('dropdown-menu')) {
              e.preventDefault();
              const parentLi = anchor.parentElement;
              const open = parentLi.classList.toggle('show');
              sub.classList.toggle('show', open);

              if (open) {
                const rect = sub.getBoundingClientRect();
                if (rect.right > window.innerWidth) {
                  sub.style.left = 'auto';
                  sub.style.right = '100%';
                } else {
                  sub.style.left = '100%';
                  sub.style.right = 'auto';
                }
              }

              // Close siblings
              parentLi.parentElement.querySelectorAll(':scope > .dropdown-submenu.show').forEach(li => {
                if (li !== parentLi) {
                  li.classList.remove('show');
                  li.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                }
              });
            }
          });
        });

        // Hover open keeps flip logic
        if (window.matchMedia('(hover:hover)').matches) {
          document.querySelectorAll('.dropdown-submenu').forEach(li => {
            li.addEventListener('mouseenter', () => {
              const sub = li.querySelector(':scope > .dropdown-menu');
              if (sub) {
                sub.classList.add('show'); li.classList.add('show');
                const rect = sub.getBoundingClientRect();
                if (rect.right > window.innerWidth) { sub.style.left = 'auto'; sub.style.right = '100%'; }
              }
            });
            li.addEventListener('mouseleave', () => {
              li.classList.remove('show');
              li.querySelectorAll('.dropdown-menu').forEach(m => { m.classList.remove('show'); m.style.left=''; m.style.right=''; });
            });
          });
        }

        // Close on outside click or Esc
        const closeAll = () => document.querySelectorAll('.dropdown-menu.show,.dropdown-submenu.show')
          .forEach(el => { el.classList.remove('show'); el.style.left=''; el.style.right=''; });

        document.addEventListener('click', e => { if (!e.target.closest('nav')) closeAll(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });
      });
      </script>
      @endpush

    </div>

    {{-- Page Content --}}
    @yield('main')

    {{-- Footer --}}
    @php $settings = \App\Models\Setting::first(); @endphp
    <footer class="bg-dark text-white pt-5">
      <div class="container px-3 px-sm-5">
        <div class="row gx-4 gy-5">
          <!-- Sellers -->
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">Sellers</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'Become a Seller'    => url('/become-seller'),
                'Privacy Policy'     => url('/privacy'),
                'Terms & Conditions' => url('/terms'),
                'Seller Forum'       => url('/seller-forum'),
                'Seller Tips'        => url('/seller-tips'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- Buyers -->
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">Buyers</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'Buyer Tips'         => url('/buyer-tips'),
                'Privacy Policy'     => url('/privacy'),
                'Terms & Conditions' => url('/buyer-terms'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- About -->
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">About</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'About ' . config('app.name') => url('/about'),
                'House Rules & Policy'        => url('/house-policy'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- Support -->
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">Support</h4>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><a href="{{ url('/contact') }}" class="footer-link text-white-50 text-decoration-none">Reach Us</a></li>
              @if($settings)
                @if(!empty($settings->email))
                  <li class="text-white-50 mb-1 footer-text">
                    <strong>Email:</strong> <a href="mailto:{{ $settings->email }}" class="text-white">{{ $settings->email }}</a>
                  </li>
                @endif
                @if(!empty($settings->phone))
                <!--   <li class="text-white-50 footer-text">
                    <strong>Phone:</strong> <a href="tel:{{ $settings->phone }}" class="text-white">{{ $settings->phone }}</a>
                  </li> -->
                @endif
              @endif
            </ul>

            <!-- Social Icons -->
            @if($settings)
            <div class="d-flex gap-4">
              @foreach([
                'facebook_url'  => 'fab fa-facebook-f',
                'instagram_url' => 'fab fa-instagram',
                'x_url'         => 'fab fa-x-twitter',
                'linkedin_url'  => 'fab fa-linkedin-in',
                'tiktok_url'    => 'fab fa-tiktok',
              ] as $key => $icon)
                @if(!empty($settings->{$key}))
                  <a href="{{ $settings->{$key} }}" target="_blank" rel="noopener" aria-label="{{ ucfirst(str_replace('_url','',$key)) }}" class="footer-link social-icon">
                    <i class="{{ $icon }}"></i>
                  </a>
                @endif
              @endforeach
            </div>
            @endif
          </div>
        </div>

        <div class="mt-5 pt-4 border-top border-secondary text-center">
          <p class="mb-0 text-white-50 footer-text">
            &copy; {{ date('Y') }} {{ config('app.name', 'Cetsy') }} — All rights reserved.
          </p>
        </div>
      </div>

      @push('styles')
      <style>
        footer, .footer-text, .footer-link, .footer-heading { font-size: 15px !important; }
        .footer-heading { font-weight: 600; }
        .footer-link { display: inline-block; transition: color .2s ease-in-out; }
        .footer-link:hover { color: #fff !important; text-decoration: none; }
        .social-icon i { font-size: 18px; }
      </style>
      @endpush
    </footer>

  </main>

  <!-- ===============================================-->
  <!--    Scripts (deferred) -->
  <!-- ===============================================-->
  <script src="{{ asset('vendors/popper/popper.min.js') }}" defer></script>
  <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}" defer></script>
  <script src="{{ asset('vendors/anchorjs/anchor.min.js') }}" defer></script>
  <script src="{{ asset('vendors/is/is.min.js') }}" defer></script>

  <!-- FontAwesome JS optional (only if you rely on JS features). CSS already loaded above. -->
  <script src="{{ asset('vendors/fontawesome/all.min.js') }}" defer></script>

  <script src="{{ asset('vendors/lodash/lodash.min.js') }}" defer></script>
  <script src="{{ asset('vendors/list.js/list.min.js') }}" defer></script>
  <script src="{{ asset('vendors/feather-icons/feather.min.js') }}" defer></script>
  <script src="{{ asset('vendors/dayjs/dayjs.min.js') }}" defer></script>
  <script src="{{ asset('vendors/mapbox-gl/mapbox-gl.js') }}" defer></script>

  <script src="{{ asset('assets/js/phoenix.js') }}" defer></script>

  <script src="{{ asset('vendors/isotope-layout/isotope.pkgd.min.js') }}" defer></script>
  <script src="{{ asset('vendors/imagesloaded/imagesloaded.pkgd.min.js') }}" defer></script>
  <script src="{{ asset('vendors/isotope-packery/packery-mode.pkgd.min.js') }}" defer></script>
  <script src="{{ asset('vendors/bigpicture/BigPicture.js') }}" defer></script>
  <script src="{{ asset('vendors/countup/countUp.umd.js') }}" defer></script>

  <!-- Google Maps (async) — replace key via env if needed -->
  <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initMap" async></script>

  <!-- SMTPJS (fixed path) — only include if you actually use it -->
  <script src="https://smtpjs.com/v3/smtp.js" defer></script>

  {{-- Page-level scripts --}}
  @yield('scripts')
  @stack('scripts')

</body>
</html>
