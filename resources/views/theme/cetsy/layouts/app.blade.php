<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light">
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

  <!-- Social -->
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

  <!-- Config -->
  <script src="{{ asset('assets/js/config.js') }}" defer></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      if (window.config?.config?.phoenixIsRTL) {
        document.documentElement.setAttribute('dir', 'rtl');
      }
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
              <img src="{{ setting('logo_url') }}" alt="{{ config('app.name', 'Cetsy') }} logo" height="48" width="auto" loading="lazy">
            </a>
          </div>

          {{-- Desktop Nav (center grows) --}}
          <div class="collapse navbar-collapse" id="mainNavbar">
            {{-- Search (desktop) --}}
            <form class="d-none d-lg-flex ms-3 me-3 flex-grow-1" method="GET" action="{{ route('search') }}" role="search">
              <label for="navbarSearch" class="visually-hidden">Search</label>
              <input id="navbarSearch" class="form-control" type="search" name="q" placeholder="Search products, services, shops…" aria-label="Search" value="{{ request('q') }}" autocomplete="on">
              <button class="btn btn-outline-secondary ms-2" type="submit" aria-label="Submit search">
                <i class="fas fa-search"></i>
              </button>
            </form>
            {{-- Primary navigation links --}}
            <ul class="navbar-nav ms-3">
              <li class="nav-item"><a class="nav-link" href="{{ route('shops.index') }}">Shops</a></li>
            </ul>

            {{-- Cart + User (desktop) --}}
            <ul class="navbar-nav ms-auto align-items-center" x-data="cartDropdown()" x-init="fetchCart()">
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
                @if(auth()->user()->shop)
                  <li class="nav-item d-none d-lg-block">
                    <a class="nav-link" href="{{ route('seller.shops.show', auth()->user()->shop) }}">My Shop</a>
                  </li>
                @endif

                <li class="nav-item dropdown d-none d-lg-block">
                  <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
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
                     placeholder="Search products, services, shops…" aria-label="Search" value="{{ request('q') }}">
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
          @auth
            <div class="mb-3">
              <div class="fw-semibold mb-1">Hi, {{ auth()->user()->name }}</div>
              <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="btn btn-outline-danger btn-sm" type="submit">Log Out</button>
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
                ->whereNull('parent_id')->orderBy('id')->get();
          @endphp

          @if($mainCategories->isNotEmpty())
            <div class="mb-3">
              <div class="cat-scroll" aria-label="Top categories (scrollable)">
                @foreach($mainCategories as $main)
                  <a href="{{ $main->childrenRecursive->isNotEmpty() ? '#' : route('category.show',$main->slug) }}"
                     class="cat-chip text-decoration-none text-dark">
                    {{ $main->name }}
                    @if($main->childrenRecursive->isNotEmpty())
                      <i class="fas fa-chevron-right ms-1"></i>
                    @endif
                  </a>
                @endforeach
              </div>
            </div>

            {{-- Full tree (mobile) --}}
            <div class="border rounded-3 p-2" style="max-height: 55vh; overflow:auto;">
              @php
                $renderCats = function ($nodes) use (&$renderCats) {
                  echo '<ul class="list-group list-group-flush">';
                  foreach($nodes as $cat){
                    $kids = $cat->childrenRecursive;
                    $has  = $kids->isNotEmpty();
                    echo '<li class="list-group-item">';
                    echo  '<div class="d-flex align-items-center justify-content-between">';
                    echo    '<a class="text-decoration-none" href="'.($has?'#':route('category.show',$cat->slug)).'">'.e($cat->name).'</a>';
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
          @endif

          <div class="mt-auto pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} {{ config('app.name','Cetsy') }} — All rights reserved.
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
              echo     e($cat->name);
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
                    {{ $main->name }}
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
    <footer class="bg-dark text-white pt-5 mt-5">
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
                  <!-- <li class="text-white-50 footer-text">
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

        <div class="mt-5 pt-4 border-top border-secondary-subtle text-center">
          <p class="mb-0 text-white-50 footer-text">
            &copy; {{ date('Y') }} {{ config('app.name', 'Cetsy') }} — All rights reserved.
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
</body>
</html>
