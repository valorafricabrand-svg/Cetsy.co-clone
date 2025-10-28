<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="application-name" content="{{ config('app.name', 'Cetsy') }}">
  <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Cetsy') }}">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="index, follow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="currency-set-url" content="{{ \Illuminate\Support\Facades\Route::has('currency.set') ? route('currency.set') : url('/set-currency') }}">
  <meta name="default-currency" content="{{ setting('default_currency','USD') }}">

  <!-- Dynamic Title -->
  <title>@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')</title>

  <!-- Primary Description -->
  <meta name="description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">

  <!-- Canonical -->
  <link rel="canonical" href="@yield('canonical_url', url()->current())">

  <!-- Social -->
  @section('social-meta')
    <meta property="og:title" content="@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')">
    <meta property="og:description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:image" content="@yield('meta_image', asset('assets/images/default-og-image-cetsy.jpg'))">
    <meta property="og:image:alt" content="Cetsy  Handmade Products Marketplace">
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="{{ config('app.name', 'Cetsy') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')">
    <meta name="twitter:description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
    <meta name="twitter:image" content="@yield('meta_image', asset('assets/images/default-twitter-image-cetsy.jpg'))">
    <meta name="twitter:image:alt" content="Cetsy  Handmade Products Marketplace">
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
  <meta name="theme-color" content="#ffffff">

  <!-- Performance -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="{{ asset('vendors/mapbox-gl/mapbox-gl.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">

  <!-- Your Theme CSS (built on Bootstrap 5) -->
  <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">

  <!-- Font Awesome (CSS) -->
  <link rel="stylesheet" href="{{ asset('vendors/fontawesome/css/all.min.css') }}">

  <!-- Page-level Styles -->
  @yield('styles')
  @stack('styles')

  <!-- Responsive helpers -->
  <style>
    .content img, .card img, .modal img { max-width: 100%; height: auto; }
    .table-responsive { -webkit-overflow-scrolling: touch; }
    .table-responsive > table { width: 100%; }
    /* Mobile bottom nav (modern, Bootstrap 5-friendly) */
    .mobile-bottom-nav.navbar { 
      position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
      padding: .5rem .5rem calc(.5rem + env(safe-area-inset-bottom));
      background-color: rgba(255,255,255,.92); backdrop-filter: saturate(180%) blur(10px);
      border-top: 1px solid rgba(0,0,0,.08); box-shadow: 0 -8px 24px rgba(0,0,0,.06);
    }
    .mobile-bottom-nav .nav { gap: .25rem; }
    .mobile-bottom-nav__item.nav-link { 
      position: relative; color: #475569; border-radius: .75rem; padding: .4rem .25rem; 
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .mobile-bottom-nav__item i { font-size: 1.15rem; line-height: 1; }
    .mobile-bottom-nav__item .label { font-size: .72rem; line-height: 1; margin-top: .15rem; }
    .mobile-bottom-nav__item.active, .mobile-bottom-nav__item:hover, .mobile-bottom-nav__item:focus {
      color: #027333; background: rgba(2,115,51,.06);
    }
    .mobile-bottom-nav__item.active i { color: #027333; }
    .mobile-bottom-nav__item.active::after { 
      content: ""; position: absolute; top: -6px; left: 25%; right: 25%; height: 3px; border-radius: 999px; background: #027333;
    }
    .mobile-bottom-nav__badge { 
      position: absolute; top: .2rem; right: 25%; transform: translate(50%, -40%);
      background: #dc3545; color: #fff; font-size: .6rem; line-height: 1; border-radius: 999px; padding: .2rem .4rem; min-width: 1.1rem; text-align: center;
    }
    /* Dark theme tweak */
    [data-bs-theme='dark'] .mobile-bottom-nav.navbar {
      background-color: rgba(17,17,17,.9);
      border-top-color: rgba(255,255,255,.08);
    }
    [data-bs-theme='dark'] .mobile-bottom-nav__item.nav-link { color: #cbd5e1; }
    [data-bs-theme='dark'] .mobile-bottom-nav__item.active,
    [data-bs-theme='dark'] .mobile-bottom-nav__item:hover,
    [data-bs-theme='dark'] .mobile-bottom-nav__item:focus { background: rgba(2,115,51,.12); color: #34d399; }
    @media (max-width: 767.98px) { body.has-mobile-nav { padding-bottom: 72px; } }
    @media (max-width: 767.98px) { footer { display: none !important; } }
  </style>

  <!-- Config -->
  <script src="{{ asset('assets/js/config.js') }}" defer></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      if (window.config?.config?.phoenixIsRTL) {
        document.documentElement.setAttribute('dir', 'rtl');
      }
    });
  </script>
  <script>
    // Instant decrement for notification badges on click
    (function(){
      function parseCount(el){
        if(!el) return 0; var t=(el.textContent||'').trim();
        if(t==='') return 0; if(t==='99+') return 99; var n=parseInt(t,10); return isNaN(n)?0:n;
      }
      function setBadge(id, n){
        var el=document.getElementById(id); if(!el) return; n=Math.max(0, n|0);
        if(n>0){ el.textContent = n>99 ? '99+' : String(n); el.style.display='inline-block'; }
        else { el.textContent=''; el.style.display='none'; }
      }
      function decNotif(){
        ['topNotifCount','navNotifCount'].forEach(function(id){ var el=document.getElementById(id); if(!el) return; setBadge(id, parseCount(el)-1); });
      }
      document.addEventListener('click', function(e){
        var a = e.target && e.target.closest && e.target.closest('a[data-notif-id]');
        if(!a) return;
        var unread = a.getAttribute('data-unread');
        if(unread && unread !== '0'){
          decNotif();
          a.setAttribute('data-unread','0');
          var item = a.closest('.dropdown-item, .notification-item');
          if(item){
            var nb = item.querySelector('.badge.bg-primary.rounded-pill, .new-badge');
            if(nb && nb.parentNode){ try{ nb.parentNode.removeChild(nb); }catch(_){} }
            item.classList.remove('unread');
          }
        }
      }, true);
    })();
  </script>
  <script>
    // Live-refresh navbar badges (notif/messages)
    document.addEventListener('DOMContentLoaded', function(){
      function setBadge(id, count){
        var el = document.getElementById(id); if(!el) return;
        if(count>0){ el.textContent = count>99 ? '99+' : String(count); el.style.display='inline-block'; }
        else { el.textContent=''; el.style.display='none'; }
      }
      function refreshCounts(){
        // Avoid triggering RouteNotFoundException at render time; use static path
        var url = @json(url('/nav/counts'));
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
          .then(function(r){ return r.ok ? r.json() : {notif:0,msg:0}; })
          .then(function(data){
            setBadge('navNotifCount', (data && data.notif) ? data.notif : 0);
            setBadge('navMsgCount', (data && data.msg) ? data.msg : 0);
            setBadge('topNotifCount', (data && data.notif) ? data.notif : 0);
          })
          .catch(function(){});
      }
      refreshCounts();
      setInterval(refreshCounts, 30000);
    });
  </script>

  <!-- Inline Theme Tweaks -->
  <style>
    :root {
      --brand-success: #198754;
      --brand-dark: #0b3320;
      --nav-height: 64px;
    }
    body {
      font-family: "Nunito Sans", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans",
                   "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji", sans-serif;
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
    }
    .text-primary { color: #027333 !important; }
    .btn-link { color: #027333; }
    a.text-primary:hover, a.text-primary:focus { color: #025a1f !important; }

    /* Navbar polish */
    .navbar-brand img { height: 48px; width: auto; }
    .navbar { min-height: var(--nav-height); }
    .navbar .form-control { min-width: 280px; }
    @media (max-width: 991.98px) { .navbar .form-control { min-width: 0; } }

    /* Dropdown menus */
    .dropdown-menu {
      --bs-dropdown-min-width: 230px;
      border-radius: .5rem;
      box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
      will-change: transform;
    }
    .dropdown-item:hover, .dropdown-item:focus { background: #eaf7ef; color: #198754; }
    .rotate { transition: transform .25s ease; }
    .nav-item.dropdown.show > a .rotate { transform: rotate(180deg); }
    .dropdown-submenu.show > a .rotate { transform: rotate(90deg); }

    /* Multi-level submenu */
    .dropdown-submenu { position: relative; } /* anchor child menu positioning */
    .dropdown-submenu > .dropdown-menu {
      top: 0;
      left: 100%;
      margin-top: -0.25rem; /* overlap for seamless hover */
      margin-left: .125rem;
    }
    .dropdown-submenu.no-children > a .rotate { display: none; }

    /* Mobile category chips */
    .cat-scroll {
      display: flex; gap: .5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; padding: .75rem 0;
    }
    .cat-chip {
      white-space: nowrap; border: 1px solid rgba(0,0,0,.08); border-radius: 999px; padding: .4rem .75rem; background: #fff;
    }

    /* Footer polish */
    footer, .footer-text, .footer-link, .footer-heading { font-size: 15px !important; }
    .footer-heading { font-weight: 600; }
    .footer-link { transition: color .2s ease-in-out; }
    .footer-link:hover { color: #fff !important; text-decoration: none; }
    .social-icon i { font-size: 18px; }

    @media (prefers-reduced-motion: reduce) { .rotate { transition: none; } }
  </style>

  <!-- Alpine.js (optional for cart badge etc.) -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('cartDropdown', () => ({
        items: [],
        fetchCart() { /* hook for AJAX cart */ }
      }));
    });
  </script>

  @yield('head_scripts')
</head>

<body style="--phoenix-scroll-margin-top: 1.2rem;">
  <main class="main" id="top">
    <div class="bg-body-emphasis" data-navbar-shadow-on-scroll="true">

      {{-- NAVBAR (menu toggle moved LEFT) --}}
      <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top" aria-label="Main Navigation">
        <div class="container align-items-stretch">

          {{-- Left: Hamburger + Brand --}}
          <div class="d-flex align-items-center gap-2 me-2">
            <button class="navbar-toggler d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#mainOffcanvas"
                    aria-controls="mainOffcanvas" aria-label="Open menu">
              <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
              @php
                $__logo = setting('logo_url') ?: setting('favicon_url') ?: asset('assets/images/default-og-image-cetsy.jpg');
              @endphp
              <img src="{{ $__logo }}" alt="{{ config('app.name', 'Cetsy') }} logo" height="48" width="auto" loading="lazy"
                   onerror="this.onerror=null;this.src=@json(asset('assets/images/default-og-image-cetsy.jpg'));">
            </a>
          </div>

          {{-- Desktop Nav (center grows) --}}
          <div class="collapse navbar-collapse" id="mainNavbar">
            {{-- Search (desktop) --}}
            <form class="d-none d-lg-flex ms-3 me-3 flex-grow-1" method="GET" action="{{ route('search') }}" role="search">
              <label for="navbarSearch" class="visually-hidden">Search</label>
              <input id="navbarSearch" class="form-control" type="search" name="q" placeholder="Search products, services, shops" aria-label="Search" value="{{ request('q') }}" autocomplete="on">
              <button class="btn btn-outline-secondary ms-2" type="submit" aria-label="Submit search">
                <i class="fas fa-search"></i>
              </button>
            </form>
            {{-- Primary navigation links --}}
            <ul class="navbar-nav ms-3">
              <li class="nav-item"><a class="nav-link" href="{{ route('shops.index') }}">Shops</a></li>
            </ul>

            {{-- Currency + Cart + User (desktop) --}}
            <ul class="navbar-nav ms-auto align-items-center" x-data="cartDropdown()" x-init="fetchCart()">
              {{-- Currency selector --}}
              @php
                try {
                  $currentCurrency = get_currency();
                  $navCurrencies = \App\Models\Currency::where('is_active', true)
                    ->orderBy('code')->get(['code','symbol','usd_rate','decimal_places']);
                } catch (\Throwable $e) {
                  $currentCurrency = get_currency();
                  $navCurrencies = collect([
                    (object)['code' => 'USD','symbol' => '$','usd_rate'=>1.0,'decimal_places'=>2],
                    (object)['code' => 'EUR','symbol' => 'â‚¬','usd_rate'=>0.92,'decimal_places'=>2],
                    (object)['code' => 'GBP','symbol' => 'Â£','usd_rate'=>0.78,'decimal_places'=>2],
                    (object)['code' => 'KES','symbol' => 'KES','usd_rate'=>(float) env('USD_TO_KES',130),'decimal_places'=>2],
                  ]);
                }
              @endphp
              <li class="nav-item dropdown me-2 d-none d-lg-block">
                <a class="nav-link dropdown-toggle" href="#" id="currencyMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-coins me-1"></i>{{ $currentCurrency }}
                </a>
                <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="currencyMenu" style="min-width: 220px;">
                  @php $currencyGet = \Illuminate\Support\Facades\Route::has('currency.set.get') ? route('currency.set.get') : url('/set-currency'); @endphp
                  <ul class="list-unstyled mb-0 d-none">
                    {{-- Use site default option (clears override) --}}
                    @php 
                      $siteDefault = setting('default_currency', 'USD') ?: 'USD'; 
                      $defaultRow = null;
                      try { $defaultRow = \App\Models\Currency::where('code',$siteDefault)->first(); } catch (\Throwable $e) {}
                      $defRate = $defaultRow ? (float) $defaultRow->usd_rate : 1.0;
                      $defDec  = $defaultRow && $defaultRow->decimal_places !== null ? (int) $defaultRow->decimal_places : 2;
                    @endphp
                    <li>
                      <a class="dropdown-item d-flex align-items-center justify-content-between {{ strtoupper($currentCurrency) === strtoupper($siteDefault) ? 'active' : '' }}" href="#" data-currency-reset="1" data-rate="{{ $defRate }}" data-decimals="{{ $defDec }}">
                        <span>Use Site Default ({{ strtoupper($siteDefault) }})</span>
                        @if(strtoupper($currentCurrency) === strtoupper($siteDefault))
                          <i class="fas fa-check text-success"></i>
                        @endif
                      </a>
                    </li>
                    @foreach($navCurrencies as $c)
                      @php $code = strtoupper($c->code); $is = $code === strtoupper($currentCurrency); @endphp
                      <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between {{ $is ? 'active' : '' }}" href="#" data-currency-code="{{ $code }}" data-rate="{{ (float) ($c->usd_rate ?? 0) }}" data-decimals="{{ (int) ($c->decimal_places ?? 2) }}">
                          <span>
                            {{ $c->symbol ? $c->symbol.' ' : '' }}{{ $code }}
                          </span>
                          @if($is)
                            <i class="fas fa-check text-success"></i>
                          @endif
                        </a>
                      </li>
                    @endforeach
                  </ul>
                  @php $siteDefault = setting('default_currency', 'USD') ?: 'USD'; @endphp
                  <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('currency.set') ? route('currency.set') : url('/set-currency') }}" class="mt-2">
                    @csrf
                    <div class="mb-2 small fw-semibold">Choose Currency</div>
                    <div class="list-group list-group-flush" style="max-height: 200px; overflow:auto;">
                      @foreach($navCurrencies as $c)
                        @php $code = strtoupper($c->code); @endphp
                        <label class="list-group-item d-flex align-items-center justify-content-between">
                          <span>{{ $c->symbol ? $c->symbol.' ' : '' }}{{ $code }}</span>
                          <input type="radio" name="code" value="{{ $code }}" @checked(strtoupper($currentCurrency)=== $code) />
                        </label>
                      @endforeach
                    </div>
                    <button type="submit" class="btn btn-sm btn-success mt-2 w-100">Update</button>
                  </form>
                  <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('currency.set') ? route('currency.set') : url('/set-currency') }}" class="mt-2">
                    @csrf
                    <input type="hidden" name="reset" value="1" />
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Use Site Default ({{ strtoupper($siteDefault) }})</button>
                  </form>
                </div>
              </li>
              @php $cartCount = (int) count(session('cart', [])); @endphp
              <li class="nav-item me-2 d-none d-lg-block">
                <a href="{{ route('cart.view') }}" class="nav-link position-relative text-dark" aria-label="View cart">
                  <i class="fas fa-shopping-cart fa-lg"></i>
                  @if($cartCount)
                    <span class="badge bg-success position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
                  @endif
                </a>
              </li>

              @guest
                <li class="nav-item d-none d-lg-block">
                  <a class="nav-link" href="{{ route('login') }}">Log In</a>
                </li>
                <li class="nav-item ms-lg-2 d-none d-lg-block">
                  <a class="btn btn-success btn-sm" href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-1"></i> Sign Up
                  </a>
                </li>
              @else
                @php
                  $authUser = auth()->user();
                  $navShop  = optional($authUser->shop)->name;
                @endphp
                @if($authUser->shop)
                  <li class="nav-item d-none d-lg-block">
                    <a class="nav-link" href="{{ route('seller.shops.show', $authUser->shop) }}">My Shop</a>
                  </li>
                @endif

                <li class="nav-item dropdown d-none d-lg-block">
                  <a class="nav-link dropdown-toggle d-flex flex-column align-items-start text-start" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    <span class="fw-semibold">{{ $navShop ?? $authUser->name }}</span>
                    @if($navShop)
                      <span class="small text-muted">{{ $authUser->name }}</span>
                    @endif
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

          {{-- Right (mobile): search + cart --}}
          <div class="d-flex d-lg-none align-items-center gap-2 ms-auto">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearch" aria-controls="mobileSearch" aria-expanded="false" aria-label="Toggle search">
              <i class="fas fa-search"></i>
            </button>
            @php $cartCount = (int) count(session('cart', [])); @endphp
            <a href="{{ route('cart.view') }}" class="btn btn-outline-secondary btn-sm position-relative" aria-label="View cart">
              <i class="fas fa-shopping-cart"></i>
              @if($cartCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $cartCount }}</span>
              @endif
            </a>
          </div>
        </div>

        {{-- Mobile Search (collapsible) --}}
        <div class="collapse border-top d-lg-none" id="mobileSearch">
          <div class="container py-3">
            <form class="d-flex" method="GET" action="{{ route('search') }}" role="search">
              <label for="navbarSearchMobile" class="visually-hidden">Search</label>
              <input id="navbarSearchMobile" class="form-control" type="search" name="q"
                     placeholder="Search products, services, shops" aria-label="Search" value="{{ request('q') }}">
              <button class="btn btn-outline-secondary ms-2" type="submit" aria-label="Submit search">
                <i class="fas fa-search"></i>
              </button>
            </form>
          </div>
        </div>
      </nav>

      {{-- OFFCANVAS (now opens from LEFT) --}}
      <div class="offcanvas offcanvas-start" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="mainOffcanvasLabel">{{ config('app.name','Cetsy') }}</h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
          {{-- Currency selector (mobile) --}}
          @php
            try {
              $currentCurrency = get_currency();
              $navCurrencies = \App\Models\Currency::where('is_active', true)->orderBy('code')->get(['code','symbol']);
            } catch (\Throwable $e) {
              $currentCurrency = get_currency();
              $navCurrencies = collect([
                (object)['code' => 'USD','symbol' => '$'],
                (object)['code' => 'EUR','symbol' => 'â‚¬'],
                (object)['code' => 'GBP','symbol' => 'Â£'],
                (object)['code' => 'KES','symbol' => 'KES'],
              ]);
            }
          @endphp
          <div class="mb-3">
            @php $currencyGet = \Illuminate\Support\Facades\Route::has('currency.set.get') ? route('currency.set.get') : url('/set-currency'); @endphp
            <label for="currencySelect" class="form-label mb-1"><i class="fas fa-coins me-1"></i> Currency</label>
            @php $siteDefault = setting('default_currency', 'USD') ?: 'USD'; @endphp
            <select id="currencySelect" class="form-select form-select-sm" aria-label="Select currency">
              <option value="" disabled>Select currency¦</option>
              <option value="__default__">System Default ({{ strtoupper($siteDefault) }})</option>
              @foreach($navCurrencies as $c)
                @php $code = strtoupper($c->code); @endphp
                <option value="{{ $code }}" @selected(strtoupper($currentCurrency)=== $code)>
                  {{ $c->symbol ? $c->symbol.' ' : '' }}{{ $code }}
                </option>
              @endforeach
            </select>
            <small class="text-muted">Changes apply instantly.</small>
          </div>
          @auth
            @php
              $u = auth()->user();
              $isSeller = method_exists($u,'isSeller') && $u->isSeller();
              $isBuyer  = method_exists($u,'isBuyer')  && $u->isBuyer();
              // Counts (best-effort)
              try {
                $notifCount = \App\Models\Activity::where('user_id',$u->id)->where('is_read',false)->count();
              } catch (\Throwable $e) { $notifCount = 0; }
              try {
                $msgCount = class_exists('App\\Models\\Message')
                  ? \App\Models\Message::where('receiver_id',$u->id)->where('is_read',false)->count()
                  : 0;
              } catch (\Throwable $e) { $msgCount = 0; }
            @endphp
            <div class="mb-3">
              <div class="fw-semibold mb-2">Hi, {{ $u->name }}</div>
              <div class="list-group list-group-flush">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                  <i class="fas fa-gauge"></i>
                  <span>{{ $isSeller ? 'Seller Dashboard' : 'Dashboard' }}</span>
                </a>
                <a href="{{ $isSeller ? route('seller.orders.index') : (\Illuminate\Support\Facades\Route::has('account.orders') ? route('account.orders') : route('orders.index')) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                  <i class="fas fa-box"></i>
                  <span>Orders</span>
                </a>
                @if(!$isSeller && session()->has('created_order_ids'))
                <a href="{{ route('buyer.orders.created') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                  <i class="fas fa-star"></i>
                  <span>New Orders</span>
                </a>
                @endif
                <a href="{{ $isSeller ? route('seller.messages.index') : route('buyer.messages.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                  <span class="d-inline-flex align-items-center gap-2"><i class="fas fa-comments"></i> Messages</span>
                  <span id="navMsgCount" class="badge bg-danger rounded-pill" style="display: {{ $msgCount>0 ? 'inline-block' : 'none' }};">{{ $msgCount>99 ? '99+' : $msgCount }}</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                  <span class="d-inline-flex align-items-center gap-2"><i class="fas fa-bell"></i> Notifications</span>
                  <span id="navNotifCount" class="badge bg-primary rounded-pill" style="display: {{ $notifCount>0 ? 'inline-block' : 'none' }};">{{ $notifCount>99 ? '99+' : $notifCount }}</span>
                </a>
                <a href="{{ route('wallet.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                  <i class="fas fa-wallet"></i>
                  <span>Wallet</span>
                </a>
                @if($isBuyer)
                  <a href="{{ route('wishlist') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                  </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                  <i class="fas fa-user"></i>
                  <span>Profile</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="list-group-item p-0 border-0">
                  @csrf
                  <button class="list-group-item list-group-item-action d-flex align-items-center gap-2 text-danger" type="submit">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                  </button>
                </form>
              </div>
            </div>
          @else
            <div class="d-flex gap-2 mb-3">
              <a class="btn btn-success btn-sm w-100" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i> Sign Up</a>
              <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('login') }}">Log In</a>
            </div>
          @endauth
          <div class="mb-3">
            <a href="{{ route('shops.index') }}" class="btn btn-outline-success w-100">
              <i class="fas fa-store me-1"></i> Browse Shops
            </a>
          </div>

          {{-- Quick Category Chips --}}
          @php
            $mainCategories = \App\Models\Category::with('childrenRecursive')
                ->whereNull('parent_id')->orderBy('name')->get();
          @endphp

          @if($mainCategories->isNotEmpty())
            <div class="mb-3 d-flex align-items-center justify-content-between">
              <div class="cat-scroll" aria-label="Top categories (scrollable)">
                @foreach($mainCategories as $main)
                  <a href="{{ route('category.show',$main->slug) }}"
                     class="cat-chip text-decoration-none text-dark"
                     data-bs-dismiss="offcanvas">
                    {{ $main->name }}
                    @if($main->childrenRecursive->isNotEmpty())
                      <i class="fas fa-chevron-right ms-1"></i>
                    @endif
                  </a>
                @endforeach
              </div>
              <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary ms-2" data-bs-dismiss="offcanvas">All</a>
            </div>

            {{-- Full tree (mobile) --}}
            <div class="mb-2">
              <label for="categoryFilter" class="form-label small text-muted">Filter categories</label>
              <input type="text" id="categoryFilter" class="form-control form-control-sm" placeholder="Type to filterâ¦" autocomplete="off">
            </div>
            <div class="border rounded-3 p-2" id="categoryTree" style="flex:1 1 auto; min-height:0; overflow:auto; -webkit-overflow-scrolling: touch;">
              @php
                $renderCats = function ($nodes) use (&$renderCats) {
                  echo '<ul class="list-group list-group-flush">';
                  foreach($nodes as $cat){
                    $kids = $cat->childrenRecursive;
                    $has  = $kids->isNotEmpty();
                    $dataName = strtolower($cat->name);
                    echo '<li class="list-group-item" data-category-name="'.e($dataName).'">';
                    echo  '<div class="d-flex align-items-center justify-content-between gap-2">';
                    // Left: title + optional view link for parents
                    echo    '<div class="me-2 text-truncate">';
                    echo      '<a class="text-decoration-none text-dark text-truncate" href="'.route('category.show',$cat->slug).'" data-bs-dismiss="offcanvas">'.e(html_entity_decode($cat->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')).'</a>';
                    if($has) {
                      echo    '<a class="badge rounded-pill bg-light text-dark ms-2" href="'.route('category.show',$cat->slug).'" data-bs-dismiss="offcanvas" title="View all">View</a>';
                    }
                    echo    '</div>';
                    // Right: toggle for children
                    if($has) echo '<button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mcat-'.$cat->id.'" aria-expanded="false" aria-controls="mcat-'.$cat->id.'"><i class="fas fa-chevron-down"></i></button>';
                    echo  '</div>';
                    if($has){
                      echo '<div class="collapse ms-3 mt-2" id="mcat-'.$cat->id.'">';
                      $renderCats($kids);
                      echo '</div>';
                    }
                    echo '</li>';
                  }
                  echo '</ul>';
                };
              @endphp
              {!! $renderCats($mainCategories) !!}
            </div>
            <script>
              // Client-side filter for category tree
              (function(){
                function onReady(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
                onReady(function(){
                  var input = document.getElementById('categoryFilter');
                  var tree  = document.getElementById('categoryTree');
                  if(!input || !tree) return;
                  var items = tree.querySelectorAll('[data-category-name]');
                  function normalize(s){ return (s||'').toLowerCase().trim(); }
                  input.addEventListener('input', function(){
                    var q = normalize(input.value);
                    if(!q){
                      // Reset: show all and collapse nodes
                      items.forEach(function(li){ li.style.display=''; });
                      tree.querySelectorAll('.collapse.show').forEach(function(c){ try{ bootstrap.Collapse.getOrCreateInstance(c).hide(); }catch(e){} });
                      return;
                    }
                    items.forEach(function(li){ li.style.display='none'; });
                    // Show matches and their ancestor containers
                    items.forEach(function(li){
                      var name = li.getAttribute('data-category-name');
                      if(name && name.indexOf(q) !== -1){
                        li.style.display='';
                        // Expand parent collapses
                        var parentCollapse = li.closest('.collapse');
                        while(parentCollapse){
                          try{ bootstrap.Collapse.getOrCreateInstance(parentCollapse).show(); }catch(e){}
                          parentCollapse = parentCollapse.parentElement ? parentCollapse.parentElement.closest('.collapse') : null;
                        }
                      }
                    });
                  });
                });
              })();
            </script>
          @endif

          <div class="mt-auto pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} {{ config('app.name','Cetsy') }}  All rights reserved.
          </div>
        </div>
      </div>

      {{-- ======= DESKTOP Multi-level Category Nav ======= --}}
      @if($mainCategories->isNotEmpty())
        @php
          $renderCatsDesktop = function ($nodes) use (&$renderCatsDesktop){
            foreach($nodes as $cat){
              $kids = $cat->childrenRecursive;
              $has  = $kids->isNotEmpty();
              echo '<li class="dropdown-submenu'.($has?'':' no-children').'">';
              echo   '<a class="dropdown-item d-flex justify-content-between align-items-center" href="'.($has?'#':route('category.show',$cat->slug)).'" role="menuitem" tabindex="-1">';
              echo     e(html_entity_decode($cat->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
              if($has) echo '<i class="fas fa-chevron-right ms-2 rotate" aria-hidden="true"></i>';
              echo   '</a>';
              if($has){
                echo '<ul class="dropdown-menu" role="menu">';
                $renderCatsDesktop($kids);
                echo '</ul>';
              }
              echo '</li>';
            }
          };
        @endphp

        <nav class="bg-success d-none d-lg-block" aria-label="Category Navigation">
          <div class="container">
            <ul class="nav">
              @foreach($mainCategories as $main)
                <li class="nav-item dropdown">
                  <a class="nav-link text-white py-2 dropdown-toggle"
                     href="#"
                     id="catDD{{ $main->id }}"
                     data-bs-toggle="dropdown"
                     data-bs-auto-close="outside"
                     aria-expanded="false"
                     role="button">
                    {{ html_entity_decode($main->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                    @if($main->childrenRecursive->isNotEmpty())
                      <i class="fas fa-chevron-down ms-1 rotate" aria-hidden="true"></i>
                    @endif
                  </a>

                  @if($main->childrenRecursive->isNotEmpty())
                    <ul class="dropdown-menu" aria-labelledby="catDD{{ $main->id }}" role="menu">
                      {!! $renderCatsDesktop($main->childrenRecursive) !!}
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
        /* Desktop hover open for all levels */
        @media (min-width:992px) {
          .nav-item.dropdown:hover > .dropdown-menu { display:block; }
          .dropdown-submenu:hover  > .dropdown-menu { display:block; }
        }
      </style>
      @endpush

      @push('scripts')
      <script>
      document.addEventListener('DOMContentLoaded', () => {
        const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;

        // Utility: flip submenu left if overflowing
        const positionSubmenu = (menu) => {
          if (!menu) return;
          // Reset to default
          menu.style.left = '100%';
          menu.style.right = 'auto';
          const rect = menu.getBoundingClientRect();
          const pad = 16;
          if (rect.right > window.innerWidth - pad) {
            menu.style.left = 'auto';
            menu.style.right = '100%';
          }
        };

        // Open/close helpers for submenus
        const openSub = (li) => {
          const sub = li.querySelector(':scope > .dropdown-menu');
          if (!sub) return;
          li.classList.add('show');
          sub.classList.add('show');
          positionSubmenu(sub);
        };
        const closeSub = (li) => {
          const sub = li.querySelector(':scope > .dropdown-menu');
          li.classList.remove('show');
          if (sub) {
            sub.classList.remove('show');
            sub.style.left = '';
            sub.style.right = '';
          }
          // Also close nested children
          li.querySelectorAll(':scope .dropdown-submenu.show').forEach(n => {
            n.classList.remove('show');
            const m = n.querySelector(':scope > .dropdown-menu');
            if (m) m.classList.remove('show');
          });
        };

        // Hover behavior for top-level dropdowns (desktop only)
        document.querySelectorAll('.nav-item.dropdown').forEach(item => {
          const menu = item.querySelector(':scope > .dropdown-menu');
          item.addEventListener('mouseenter', () => {
            if (!isDesktop() || !menu) return;
            clearTimeout(item._leaveTimer);
            item.classList.add('show');
            menu.classList.add('show');
            // keep stable placement (no popper jitter)
            menu.setAttribute('data-bs-popper', 'static');
          });
          item.addEventListener('mouseleave', () => {
            if (!menu) return;
            item._leaveTimer = setTimeout(() => {
              item.classList.remove('show');
              menu.classList.remove('show');
            }, 150);
          });
        });

        // Hover behavior for all nested submenus (desktop only)
        const bindSubmenus = () => {
          document.querySelectorAll('.dropdown-submenu').forEach(li => {
            li.removeEventListener('mouseenter', li._enterHandler || (()=>{}));
            li.removeEventListener('mouseleave', li._leaveHandler || (()=>{}));

            li._enterHandler = () => {
              if (isDesktop()) {
                clearTimeout(li._leaveTimer);
                openSub(li);
              }
            };
            li._leaveHandler = () => {
              if (isDesktop()) {
                li._leaveTimer = setTimeout(() => closeSub(li), 150);
              }
            };

            li.addEventListener('mouseenter', li._enterHandler);
            li.addEventListener('mouseleave', li._leaveHandler);

            // Keyboard: ArrowRight opens, Esc closes
            const trigger = li.querySelector(':scope > a.dropdown-item');
            if (trigger) {
              trigger.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') {
                  e.preventDefault();
                  openSub(li);
                  const firstChild = li.querySelector(':scope > .dropdown-menu .dropdown-item');
                  if (firstChild) firstChild.focus();
                } else if (e.key === 'Escape') {
                  e.preventDefault();
                  closeSub(li);
                  trigger.focus();
                }
              });
            }
          });
        };
        bindSubmenus();

        // Re-calc positions on resize/scroll
        window.addEventListener('resize', () => {
          document.querySelectorAll('.dropdown-menu.show').forEach(positionSubmenu);
        });
        window.addEventListener('scroll', () => {
          document.querySelectorAll('.dropdown-menu.show').forEach(positionSubmenu);
        });

        // Touch/mobile: keep click-to-open for accessibility
        // If user taps a parent (with children), prevent navigation and toggle
        document.querySelectorAll('.dropdown-submenu > a.dropdown-item').forEach(a => {
          a.addEventListener('click', (e) => {
            const li = a.parentElement;
            const hasChild = !!li.querySelector(':scope > .dropdown-menu');
            if (hasChild && !isDesktop()) {
              e.preventDefault();
              li.classList.toggle('show');
              const sub = li.querySelector(':scope > .dropdown-menu');
              if (sub) sub.classList.toggle('show');
            }
          });
        });

        // Close all on outside click or Esc (desktop)
        const closeAll = () => document.querySelectorAll('.dropdown-menu.show, .dropdown-submenu.show, .nav-item.dropdown.show')
          .forEach(el => {
            if (el.classList.contains('nav-item')) {
              el.classList.remove('show');
            } else if (el.classList.contains('dropdown-menu')) {
              el.classList.remove('show');
              el.style.left=''; el.style.right='';
            } else {
              el.classList.remove('show');
            }
          });

        document.addEventListener('click', e => {
          if (!e.target.closest('nav')) closeAll();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });
      });
      </script>
      @endpush

    </div>

    {{-- Page Content --}}
    @yield('main')

    {{-- Footer --}}
    @php $settings = \App\Models\Setting::first(); @endphp
    <footer class="site-footer bg-dark text-white py-5 mt-5">
      <div class="container px-3 px-sm-5">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 gx-4 gy-5">
          <!-- About (blurb) -->
          <div class="col">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">About</h4>
            <p class="text-white-50 footer-text mb-0">
              Cetsy is a global marketplace connecting buyers and sellers worldwide, empowering entrepreneurs to list legal products and services with payments, tools, and a community built on trust, discovery, and growth.
            </p>
          </div>

          <!-- Quick Links -->
          <div class="col">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">Quick Links</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'User Agreement'              => url('/user-agreement'),
                'About Cetsy'                 => url('/about'),
                'Blog'                        => route('blog.index'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- Accounts -->
          <div class="col">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">Accounts</h4>
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><a href="{{ route('shops.index') }}" class="footer-link text-white-50 text-decoration-none">Shops</a></li>
              <li class="mb-2"><a href="{{ route('login') }}" class="footer-link text-white-50 text-decoration-none">Login</a></li>
              <li class="mb-2"><a href="{{ route('register') }}" class="footer-link text-white-50 text-decoration-none">Register</a></li>
            </ul>
          </div>

          <!-- Support -->
          <div class="col">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">Support</h4>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><a href="{{ url('/contact') }}" class="footer-link text-white-50 text-decoration-none">Reach Us</a></li>
              <li class="text-white-50 mb-1 footer-text">
                <strong>Email:</strong> <a href="mailto:{{ support_email() }}" class="text-white">{{ support_email() }}</a>
              </li>
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

        <div class="mt-5 pt-4 border-top border-secondary-subtle text-center">
          <p class="mb-0 text-white-50 footer-text">
            &copy; {{ date('Y') }} {{ config('app.name', 'Cetsy') }} All rights reserved.
          </p>
        </div>
      </div>
    </footer>

  </main>

  <!-- Scripts (deferred) -->
  <script src="{{ asset('vendors/popper/popper.min.js') }}" defer></script>
  <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}" defer></script>
  <script src="{{ asset('vendors/anchorjs/anchor.min.js') }}" defer></script>
  <script src="{{ asset('vendors/is/is.min.js') }}" defer></script>
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

  <!-- Google Maps (async) -->
  <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initMap" async></script>

  <!-- SMTPJS (only if used) -->
  <script src="https://smtpjs.com/v3/smtp.js" defer></script>

  {{-- Page-level scripts --}}
  @yield('scripts')
  @stack('scripts')

  <!-- PWA: Service Worker Registration -->
  <script>
    (function(){
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function(){
          navigator.serviceWorker.register('/service-worker.js').catch(function(err){
            console.warn('SW registration failed:', err);
          });
        });
      }
    })();
  </script>

  {{-- Mobile bottom navigation (front theme) --}}
  @include('theme.'.theme().'.partials._mobile_nav')

  <!-- Background currency switch (no URL params) -->
  <script>
    (function(){
      function onReady(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
      onReady(function(){
        var els = document.querySelectorAll('[data-currency-code]');
        if(!els.length) return;
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        var action = document.querySelector('meta[name="currency-set-url"]')?.getAttribute('content') || '/set-currency';
        var defaultCode = (document.querySelector('meta[name="default-currency"]').getAttribute('content')||'USD').toUpperCase();
        els.forEach(function(el){
          el.addEventListener('click', function(e){
            e.preventDefault();
            var reset = el.hasAttribute('data-currency-reset');
            var code = el.getAttribute('data-currency-code');
            if(!reset && !code) return;
            try{
              fetch(action, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': token || '',
                  'Accept': 'application/json, text/plain, */*',
                  'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                credentials: 'same-origin',
                body: reset ? 'reset=1' : ('code=' + encodeURIComponent(code))
              }).then(function(r){ return r.json().catch(function(){ return {}; }); })
                .then(function(o){
                  // Update nav label immediately
                  var newCode = (o && o.currency) ? String(o.currency).toUpperCase() : (reset ? defaultCode : code.toUpperCase());
                  var toggle = document.getElementById('currencyMenu');
                  if (toggle) { toggle.innerHTML = '<i class="fas fa-coins me-1"></i>'+ newCode; }
                  // Update active markers in dropdown
                  document.querySelectorAll('[data-currency-code], [data-currency-reset]').forEach(function(a){ a.classList.remove('active'); });
                  if (reset) {
                    var defItem = document.querySelector('[data-currency-reset]');
                    if (defItem) defItem.classList.add('active');
                  } else {
                    var sel = document.querySelector('[data-currency-code="'+ newCode +'"]');
                    if (sel) sel.classList.add('active');
                  }
                  // Fallback: reload to update all prices
                  setTimeout(function(){ location.reload(); }, 50);
                })
                .catch(function(){ location.reload(); });
            }catch(_){ location.reload(); }
          });
        });
      });
    })();
  </script>

  <!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6760175aaf5bfec1dbdcc04c/1if7lmf47';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body>
</html>

