<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="index, follow">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')</title>
  <meta name="description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
  <link rel="canonical" href="@yield('canonical_url', url()->current())">

  <!-- Social Meta -->
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

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('vendors/fontawesome/css/all.min.css') }}">

  <!-- Page-level Styles -->
  @yield('styles')
  @stack('styles')

  <!-- Config -->
  <script src="{{ asset('assets/js/config.js') }}" defer></script>

  <style>
    :root { 
      --brand-success: #198754; 
      --brand-primary: #027333; 
      --brand-dark: #1a2526; 
      --transition: all 0.3s ease-in-out;
      --app-header-height: 56px;
      --app-bottom-nav-height: 60px;
    }
    
    /* Base Styles */
    body { 
      font-family: "Nunito Sans", system-ui, -apple-system, sans-serif; 
      background: #fff; 
      font-size: 16px;
      padding-top: var(--app-header-height);
      padding-bottom: var(--app-bottom-nav-height);
    }
    
    .text-primary { color: var(--brand-primary) !important; }
    .btn-link { color: var(--brand-primary); }
    a.text-primary:hover, a.text-primary:focus { color: #025a1f !important; }
    
    /* ===== Mobile App UI ===== */
    @media (max-width: 768px) {
      /* Fixed App Header */
      .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        height: var(--app-header-height);
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 0 16px;
        display: flex;
        align-items: center;
      }
      
      .app-bar {
        width: 100%;
        display: flex;
        align-items: center;
      }

      /* App Menu Button */
      .app-menu-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: transparent;
        border: none;
        color: var(--brand-dark);
        transition: var(--transition);
        margin-right: 8px;
      }

      .app-menu-btn:active {
        background: rgba(0,0,0,0.05);
      }

      .app-menu-btn i {
        font-size: 20px;
      }

      /* Brand Logo */
      .navbar-brand {
        flex: 1;
        display: flex;
        justify-content: center;
        position: static;
        transform: none;
        margin: 0;
      }

      .navbar-brand img {
        height: 32px;
        transition: var(--transition);
      }

      /* App Actions */
      .app-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: auto;
      }
      
      .app-actions a {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--brand-dark);
        border-radius: 50%;
        transition: var(--transition);
      }
      
      .app-actions a:active {
        background: rgba(0,0,0,0.05);
      }
      
      .app-actions .badge {
        position: absolute;
        top: -2px;
        right: -2px;
        font-size: 10px;
        padding: 3px 6px;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      /* Hide desktop elements on mobile */
      .navbar .desktop-only { display: none !important; }
      nav[aria-label="Category Navigation"] { display: none; }
      
      /* Bottom Navigation Bar */
      .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: var(--app-bottom-nav-height);
        background: #fff;
        border-top: 1px solid rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-around;
        align-items: center;
        z-index: 1000;
        padding-bottom: env(safe-area-inset-bottom);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
      }
      
      .mobile-bottom-nav a {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #666;
        font-size: 10px;
        font-weight: 500;
        flex: 1;
        height: 100%;
        transition: var(--transition);
        position: relative;
      }
      
      .mobile-bottom-nav a.active {
        color: var(--brand-primary);
      }
      
      .mobile-bottom-nav a.active::after {
        content: '';
        position: absolute;
        top: 4px;
        width: 5px;
        height: 5px;
        background: var(--brand-primary);
        border-radius: 50%;
      }
      
      .mobile-bottom-nav i {
        font-size: 20px;
        margin-bottom: 2px;
      }
      
      /* App Drawer */
      .offcanvas.app-drawer {
        width: 85vw;
        max-width: 320px;
        border-radius: 0 16px 16px 0;
        background: #fff;
      }
      
      .app-drawer .offcanvas-header {
        padding: 16px;
        border-bottom: 1px solid rgba(0,0,0,0.08);
      }
      
      .app-drawer .offcanvas-title {
        font-weight: 700;
        font-size: 18px;
      }
      
      .app-drawer .btn-close {
        background-size: 14px;
        padding: 8px;
      }
      
      .app-drawer .list-group-item {
        border: 0;
        padding: 12px 16px;
        font-size: 15px;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 12px;
      }
      
      .app-drawer .list-group-item i {
        width: 20px;
        text-align: center;
      }
      
      .app-drawer .list-group-item:hover {
        background: rgba(0,0,0,0.03);
      }
      
      .app-drawer .form-control {
        border-radius: 20px;
        padding: 10px 16px;
        font-size: 15px;
      }
      
      /* Categories in drawer */
      .app-drawer details {
        margin-bottom: 4px;
      }
      
      .app-drawer details summary {
        padding: 12px 16px;
        font-size: 15px;
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      
      .app-drawer details summary::-webkit-details-marker {
        display: none;
      }
      
      .app-drawer details summary::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 12px;
        transition: var(--transition);
      }
      
      .app-drawer details[open] summary::after {
        transform: rotate(180deg);
      }
      
      .app-drawer details ul {
        padding-left: 16px;
        margin-top: 4px;
      }
      
      .app-drawer details li {
        margin-bottom: 4px;
      }
      
      .app-drawer details a {
        display: block;
        padding: 8px 16px;
        color: #555;
        text-decoration: none;
        font-size: 14px;
        border-radius: 6px;
        transition: var(--transition);
      }
      
      .app-drawer details a:hover {
        background: rgba(0,0,0,0.03);
        color: var(--brand-primary);
      }
      
      /* Prevent content from being hidden behind nav bars */
      main {
        min-height: calc(100vh - var(--app-header-height) - var(--app-bottom-nav-height));
      }
      
      footer {
        padding-bottom: calc(var(--app-bottom-nav-height) + 20px);
      }
    }
    
    /* ===== Desktop UI ===== */
    @media (min-width: 769px) {
      body {
        padding-top: 0;
        padding-bottom: 0;
      }
      
      .mobile-bottom-nav {
        display: none !important;
      }
      
      /* Professional Navbar */
      .navbar {
        padding: 16px 24px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      }
      
      .app-bar {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        gap: 24px;
      }
      
      .navbar-brand img {
        height: 40px;
      }
      
      /* Search Bar */
      .navbar .desktop-only {
        display: flex;
        align-items: center;
        gap: 24px;
        flex: 1;
      }
      
      .navbar .form-control {
        flex: 1;
        max-width: 600px;
        border-radius: 24px;
        padding: 10px 20px;
        border: 1px solid #ddd;
        font-size: 15px;
        transition: var(--transition);
      }
      
      .navbar .form-control:focus {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(2,115,51,0.1);
      }
      
      .navbar .btn-outline-secondary {
        border-radius: 24px;
        padding: 10px 16px;
        font-weight: 500;
      }
      
      /* Navigation Links */
      .navbar-nav .nav-link {
        font-weight: 600;
        color: var(--brand-dark);
        padding: 8px 16px;
        border-radius: 6px;
        transition: var(--transition);
      }
      
      .navbar-nav .nav-link:hover {
        background: rgba(0,0,0,0.03);
        color: var(--brand-primary);
      }
      
      /* Buttons */
      .btn-success {
        background: var(--brand-primary);
        border: none;
        padding: 10px 20px;
        border-radius: 24px;
        font-weight: 600;
        transition: var(--transition);
      }
      
      .btn-success:hover {
        background: #025a1f;
        transform: translateY(-1px);
      }
      
      /* Category Navigation */
      nav[aria-label="Category Navigation"] {
        background: var(--brand-dark);
        padding: 12px 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      }
      
      nav[aria-label="Category Navigation"] .container {
        max-width: 1400px;
        margin: 0 auto;
      }
      
      nav[aria-label="Category Navigation"] .nav-link {
        color: #fff;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 6px;
        transition: var(--transition);
      }
      
      nav[aria-label="Category Navigation"] .nav-link:hover {
        background: rgba(255,255,255,0.1);
      }
    }
    
    /* ===== Common Components ===== */
    /* Dropdown Menus */
    .dropdown-menu {
      min-width: 240px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.12);
      border: none;
      padding: 8px 0;
      margin-top: 8px;
    }
    
    .dropdown-item {
      padding: 8px 16px;
      font-size: 14px;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .dropdown-item i {
      width: 20px;
      text-align: center;
    }
    
    .dropdown-item:hover, .dropdown-item:focus {
      background: rgba(2,115,51,0.08);
      color: var(--brand-primary);
    }
    
    /* Multi-level Dropdowns */
    .dropdown-submenu {
      position: relative;
    }
    
    .dropdown-submenu > .dropdown-menu {
      top: 0;
      left: 100%;
      margin-left: 4px;
    }
    
    .dropdown-submenu > a::after {
      content: '\f054';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      font-size: 12px;
      margin-left: auto;
      transition: var(--transition);
    }
    
    .dropdown-submenu.show > a::after {
      transform: rotate(90deg);
    }
    
    @media (min-width: 992px) {
      .dropdown-submenu:hover > .dropdown-menu {
        display: block;
      }
    }
    
    /* Footer */
    footer {
      background: linear-gradient(180deg, #1a2526 0%, #2d3a3b 100%);
    }
    
    .footer-heading {
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.5px;
      margin-bottom: 16px;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .footer-link {
      display: inline-block;
      color: rgba(255,255,255,0.7);
      margin-bottom: 8px;
      transition: var(--transition);
      text-decoration: none;
    }
    
    .footer-link:hover {
      color: #fff;
      transform: translateX(4px);
    }
    
    .social-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      color: #fff;
      transition: var(--transition);
    }
    
    .social-icon:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-2px);
    }
  </style>

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('cartDropdown', () => ({
        items: [],
        fetchCart() { /* TODO: AJAX fetch if needed */ }
      }));
    });
  </script>

  @yield('head_scripts')
</head>

<body style="--phoenix-scroll-margin-top: 1.5rem;">
  <main class="main" id="top">
    <div class="bg-body-emphasis" data-navbar-shadow-on-scroll="true">

      <!-- Navbar -->
      <nav class="navbar navbar-light bg-white shadow-sm" aria-label="Main Navigation">
        <div class="container app-bar">
          <!-- Mobile Menu Button -->
          <button
            class="app-menu-btn d-lg-none"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#appDrawer"
            aria-controls="appDrawer"
            aria-label="Open menu"
          >
            <i class="fas fa-bars-staggered"></i>
          </button>

          <!-- Brand Logo -->
          <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ setting('logo_url') }}" alt="{{ config('app.name', 'Cetsy') }} logo" height="40" width="auto" loading="lazy">
          </a>

          <!-- Desktop Content -->
          <div class="desktop-only">
            <!-- Search -->
            <form class="d-flex flex-grow-1 me-3" method="GET" action="{{ route('search') }}" role="search">
              <label for="navbarSearch" class="visually-hidden">Search</label>
              <input
                id="navbarSearch"
                class="form-control"
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

            <!-- Navigation -->
            @php $cartCount = (int) count(session('cart', [])); @endphp
            <ul class="navbar-nav ms-auto align-items-center" x-data="cartDropdown()" x-init="fetchCart()">
              <li class="nav-item me-2">
                <a href="{{ route('cart.view') }}" class="nav-link position-relative" aria-label="View cart">
                  <i class="fas fa-shopping-cart"></i>
                  @if($cartCount)
                    <span class="badge bg-success position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
                  @endif
                </a>
              </li>

              @guest
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('login') }}">Log In</a>
                </li>
                <li class="nav-item ms-2">
                  <a class="btn btn-success" href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-1"></i> Sign Up
                  </a>
                </li>
              @else
                @if(auth()->user()->shop)
                  <li class="nav-item">
                    <a class="nav-link" href="{{ route('seller.shows.show', auth()->user()->shop) }}">My Shop</a>
                  </li>
                @endif
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ auth()->user()->name }}
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt me-2"></i>Log Out</button>
                      </form>
                    </li>
                  </ul>
                </li>
              @endguest
            </ul>
          </div>

          <!-- Mobile Actions -->
          <div class="app-actions d-lg-none">
            <a href="{{ route('cart.view') }}" class="position-relative" aria-label="View cart">
              <i class="fas fa-shopping-cart"></i>
              @if($cartCount)
                <span class="badge bg-success position-absolute top-0 start-100">{{ $cartCount }}</span>
              @endif
            </a>
            @guest
              <a href="{{ route('login') }}" aria-label="Login"><i class="fas fa-user"></i></a>
            @else
              <a href="{{ route('dashboard') }}" aria-label="Profile"><i class="fas fa-user"></i></a>
            @endguest
          </div>
        </div>
      </nav>

      <!-- Mobile Drawer -->
      @php
        $mainCategories = \App\Models\Category::with('childrenRecursive')
            ->whereNull('parent_id')->orderBy('id')->get();
      @endphp
      <div class="offcanvas offcanvas-start app-drawer" tabindex="-1" id="appDrawer" aria-labelledby="appDrawerLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="appDrawerLabel">
            <i class="fas fa-bars me-2"></i> Menu
          </h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <!-- Search -->
          <form class="mb-4" method="GET" action="{{ route('search') }}" role="search">
            <label for="drawerSearch" class="visually-hidden">Search</label>
            <div class="input-group">
              <input id="drawerSearch" class="form-control" type="search" name="q" placeholder="Search products..." value="{{ request('q') }}">
              <button class="btn btn-outline-secondary" type="submit" aria-label="Search">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </form>

          <!-- Quick Links -->
          <div class="list-group mb-4">
            <a href="{{ url('/') }}" class="list-group-item list-group-item-action">
              <i class="fas fa-home me-2"></i> Home
            </a>
            <a href="{{ route('cart.view') }}" class="list-group-item list-group-item-action">
              <i class="fas fa-shopping-cart me-2"></i> Cart
              @if($cartCount)
                <span class="badge bg-success ms-auto">{{ $cartCount }}</span>
              @endif
            </a>
            @auth
              <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
              </a>
              <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                <i class="fas fa-user me-2"></i> Profile
              </a>
            @else
              <a href="{{ route('login') }}" class="list-group-item list-group-item-action">
                <i class="fas fa-sign-in-alt me-2"></i> Log In
              </a>
              <a href="{{ route('register') }}" class="list-group-item list-group-item-action">
                <i class="fas fa-user-plus me-2"></i> Sign Up
              </a>
            @endauth
          </div>

          <!-- Categories -->
          @if($mainCategories->isNotEmpty())
            <h6 class="text-uppercase text-muted mb-3">Categories</h6>
            <ul class="list-unstyled">
              @foreach($mainCategories as $main)
                <li class="mb-2">
                  @if($main->childrenRecursive->isEmpty())
                    <a class="text-decoration-none d-flex align-items-center gap-2" href="{{ route('category.show', $main->slug) }}">
                      <i class="fas fa-tag text-muted"></i> <span>{{ $main->name }}</span>
                    </a>
                  @else
                    <details>
                      <summary class="d-flex align-items-center gap-2">
                        <i class="fas fa-folder-open text-muted"></i> <span>{{ $main->name }}</span>
                      </summary>
                      <ul class="mt-2 ps-4">
                        @foreach($main->childrenRecursive as $child)
                          <li class="mb-1">
                            <a class="text-decoration-none" href="{{ route('category.show', $child->slug) }}">{{ $child->name }}</a>
                          </li>
                        @endforeach
                      </ul>
                    </details>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>

      <!-- Desktop Category Nav -->
      @php
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
        <nav class="bg-dark d-none d-lg-block" aria-label="Category Navigation">
          <div class="container">
            <ul class="nav flex-wrap justify-content-center">
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

      @push('scripts')
      <script>
      document.addEventListener('DOMContentLoaded', () => {
        // Desktop submenu behavior
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

        // Hover open for desktop
        if (window.matchMedia('(hover:hover)').matches) {
          document.querySelectorAll('.dropdown-submenu').forEach(li => {
            li.addEventListener('mouseenter', () => {
              const sub = li.querySelector(':scope > .dropdown-menu');
              if (sub) {
                sub.classList.add('show'); 
                li.classList.add('show');
                const rect = sub.getBoundingClientRect();
                if (rect.right > window.innerWidth) { 
                  sub.style.left = 'auto'; 
                  sub.style.right = '100%'; 
                }
              }
            });
            li.addEventListener('mouseleave', () => {
              li.classList.remove('show');
              li.querySelectorAll('.dropdown-menu').forEach(m => { 
                m.classList.remove('show'); 
                m.style.left=''; 
                m.style.right=''; 
              });
            });
          });
        }

        // Close on outside click or Esc
        const closeAll = () => document.querySelectorAll('.dropdown-menu.show,.dropdown-submenu.show')
          .forEach(el => { 
            el.classList.remove('show'); 
            el.style.left=''; 
            el.style.right=''; 
          });

        document.addEventListener('click', e => { 
          if (!e.target.closest('nav')) closeAll(); 
        });
        document.addEventListener('keydown', e => { 
          if (e.key === 'Escape') closeAll(); 
        });

        // Mobile touch enhancements
        if ('ontouchstart' in window) {
          document.querySelectorAll('.mobile-bottom-nav a').forEach(link => {
            link.addEventListener('touchstart', () => {
              link.style.transform = 'scale(1.1)';
            });
            link.addEventListener('touchend', () => {
              link.style.transform = 'scale(1)';
            });
          });
        }
      });
      </script>
      @endpush

    </div>

    <!-- Page Content -->
    @yield('main')

    <!-- Footer -->
    @php $settings = \App\Models\Setting::first(); @endphp
    <footer class="bg-dark text-white pt-5 pb-4">
      <div class="container px-4 px-sm-5">
        <div class="row gx-5 gy-4">
          <!-- Sellers -->
          <div class="col-6 col-md-3">
            <h4 class="footer-heading">Sellers</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'Become a Seller'    => url('/become-seller'),
                'Privacy Policy'     => url('/privacy'),
                'Terms & Conditions' => url('/terms'),
                'Seller Forum'       => url('/seller-forum'),
                'Seller Tips'        => url('/seller-tips'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- Buyers -->
          <div class="col-6 col-md-3">
            <h4 class="footer-heading">Buyers</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'Buyer Tips'         => url('/buyer-tips'),
                'Privacy Policy'     => url('/privacy'),
                'Terms & Conditions' => url('/buyer-terms'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- About -->
          <div class="col-6 col-md-3">
            <h4 class="footer-heading">About</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'About ' . config('app.name') => url('/about'),
                'House Rules & Policy'        => url('/house-policy'),
              ] as $label => $link)
                <li class="mb-2"><a href="{{ $link }}" class="footer-link">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>

          <!-- Support -->
          <div class="col-6 col-md-3">
            <h4 class="footer-heading">Support</h4>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><a href="{{ url('/contact') }}" class="footer-link">Reach Us</a></li>
              @if($settings)
                @if(!empty($settings->email))
                  <li class="mb-2">
                    <strong>Email:</strong> <a href="mailto:{{ $settings->email }}" class="footer-link">{{ $settings->email }}</a>
                  </li>
                @endif
                @if(!empty($settings->phone))
                  <li class="mb-2">
                    <strong>Phone:</strong> <a href="tel:{{ $settings->phone }}" class="footer-link">{{ $settings->phone }}</a>
                  </li>
                @endif
              @endif
            </ul>

            <!-- Social Icons -->
            @if($settings)
            <div class="d-flex gap-3">
              @foreach([
                'facebook_url'  => 'fab fa-facebook-f',
                'instagram_url' => 'fab fa-instagram',
                'x_url'         => 'fab fa-x-twitter',
                'linkedin_url'  => 'fab fa-linkedin-in',
                'tiktok_url'    => 'fab fa-tiktok',
              ] as $key => $icon)
                @if(!empty($settings->{$key}))
                  <a href="{{ $settings->{$key} }}" target="_blank" rel="noopener" aria-label="{{ ucfirst(str_replace('_url','',$key)) }}" class="social-icon">
                    <i class="{{ $icon }}"></i>
                  </a>
                @endif
              @endforeach
            </div>
            @endif
          </div>
        </div>

        <div class="mt-5 pt-4 border-top border-secondary text-center">
          <p class="mb-0">
            &copy; {{ date('Y') }} {{ config('app.name', 'Cetsy') }} — All rights reserved.
          </p>
        </div>
      </div>
    </footer>

    <!-- Mobile Bottom Nav -->
    @php
      $homeActive = request()->is('/');
      $searchActive = request()->routeIs('search') || request()->is('search*');
      $cartActive = request()->routeIs('cart.view') || request()->is('cart*');
      $profileActive = auth()->check()
        ? (request()->routeIs('dashboard') || request()->is('dashboard*') || request()->routeIs('profile.*'))
        : request()->routeIs('login') || request()->routeIs('register');
    @endphp
    <div class="mobile-bottom-nav" role="navigation" aria-label="Mobile bottom navigation">
      <a href="{{ url('/') }}" class="{{ $homeActive ? 'active' : '' }}" aria-current="{{ $homeActive ? 'page' : 'false' }}">
        <i class="fas fa-home"></i> Home
      </a>
      <a href="{{ route('search') }}" class="{{ $searchActive ? 'active' : '' }}" aria-current="{{ $searchActive ? 'page' : 'false' }}">
        <i class="fas fa-search"></i> Search
      </a>
      <a href="{{ route('cart.view') }}" class="{{ $cartActive ? 'active' : '' }}" aria-current="{{ $cartActive ? 'page' : 'false' }}">
        <i class="fas fa-shopping-cart"></i> Cart
      </a>
      @guest
        <a href="{{ route('login') }}" class="{{ $profileActive ? 'active' : '' }}" aria-current="{{ $profileActive ? 'page' : 'false' }}">
          <i class="fas fa-user"></i> Login
        </a>
      @else
        <a href="{{ route('dashboard') }}" class="{{ $profileActive ? 'active' : '' }}" aria-current="{{ $profileActive ? 'page' : 'false' }}">
          <i class="fas fa-user"></i> Profile
        </a>
      @endguest
    </div>

  </main>

  <!-- Scripts -->
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
  <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initMap" async></script>
  <script src="https://smtpjs.com/v3/smtp.js" defer></script>

  @yield('scripts')
  @stack('scripts')
</body>
</html>