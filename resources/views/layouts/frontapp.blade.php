<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">

    <!-- Dynamic Title -->
    <title>@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')</title>
    
    <!-- Description -->
    <meta name="description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical_url', 'https://cetsy.com')">

    <!-- Social Meta Section -->
    @section('social-meta')
        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')">
        <meta property="og:description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
        <meta property="og:type" content="website">
        <meta property="og:url" content="@yield('canonical_url', 'https://cetsy.com')">
        <meta property="og:image" content="@yield('meta_image', asset('assets/images/default-og-image-cetsy.jpg'))">
        <meta property="og:image:alt" content="Cetsy — Handmade Products Marketplace">
        <meta property="og:locale" content="en_US">
        <meta property="og:site_name" content="Cetsy">

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('title', 'Cetsy | All-in-one Platform to Showcase Your Handmade Products Globally')">
        <meta name="twitter:description" content="@yield('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.')">
        <meta name="twitter:image" content="@yield('meta_image', asset('assets/images/default-twitter-image-cetsy.jpg'))">
        <meta name="twitter:image:alt" content="Cetsy — Handmade Products Marketplace">
    @show

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ favicon_url() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ favicon_url() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ favicon_url() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ favicon_url() }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ favicon_url() }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Stylesheets -->
    <link href="{{ asset('vendors/mapbox-gl/mapbox-gl.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/config.js') }}" defer></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var phoenixIsRTL = window.config.config.phoenixIsRTL;
            if (phoenixIsRTL) {
                document.querySelector('html').setAttribute('dir', 'rtl');
            }
        });
    </script>

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Cetsy",
        "url": "https://cetsy.com",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://cetsy.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- Inline Styles -->
    <style>
        .text-primary { color: #027333 !important; }
        .btn-link { color: #027333; }
        a.text-primary:hover, a.text-primary:focus { color: #025a1f !important; }
    </style>



    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body style="--phoenix-scroll-margin-top: 1.2rem;">
  <!-- ===============================================-->
  <!--    Main Content-->
  <!-- ===============================================-->
  <main class="main" id="top">
    <div class="bg-body-emphasis" data-navbar-shadow-on-scroll="true">
    {{-- resources/views/layouts/partials/navbar.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    {{-- Brand --}}
    <a class="navbar-brand" href="{{ url('/') }}">
      <img src="{{ asset('assets/img/logo.jpg') }}" style="height: 100px;">
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
      <form
        class="d-flex me-3 flex-grow-1"
        method="GET"
        action="{{ route('search') }}"
      >
        <input
          class="form-control flex-grow-1"
          type="search"
          name="q"
          placeholder="Search handmade, vintage, and more..."
          aria-label="Search"
          value="{{ request('q') }}"
        >
        <button class="btn btn-outline-secondary ms-2" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </form>

      {{-- Cart + User --}}
      <ul
        class="navbar-nav ms-auto align-items-center"
        x-data="cartDropdown()"
        x-init="fetchCart()"
      >
      

           {{-- Cart --}}
        @php
          $cartCount = count(session('cart', []));
        @endphp
        <li class="nav-item me-3">
          <a href="{{ route('cart.view') }}" class="nav-link position-relative text-dark">
            <i class="fas fa-shopping-cart fa-lg"></i>
            @if($cartCount)
              <span class="badge bg-success position-absolute top-0 start-100 translate-middle">
                {{ $cartCount }}
              </span>
            @endif
          </a>
        </li>

        {{-- Authentication Links --}}
        @guest
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}">Log In</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-success btn-sm" href="{{ route('register') }}">
              Sign Up
            </a>
          </li>
        @else
          @if(auth()->user()->shop)
            <li class="nav-item">
              <a
                class="nav-link"
                href="{{ route('seller.shops.show', auth()->user()->shop) }}"
              >
                My Shop
              </a>
            </li>
          @else
           
          @endif

          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              id="userMenu"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              {{ auth()->user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">

              <li>
                <a class="dropdown-item" href="{{ route('dashboard') }}">
                  Dashboard
                </a>
              </li>


              <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                  Profile
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="dropdown-item" type="submit">
                    Log Out
                  </button>
                </form>
              </li>
            </ul>
          </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>



@php
  $mainCategories = \App\Models\Category::whereNull('parent_id')->orderBy('id')->get();
@endphp

@if($mainCategories->count())
  <nav class="bg-success">
    <div class="container">
      <ul class="nav">
        @foreach($mainCategories as $main)
          @php
            $subs = \App\Models\Category::where('parent_id',$main->id)->orderBy('id')->get();
          @endphp
          <li class="nav-item dropdown">
            <a
              class="nav-link text-white"
              href="#"
              id="catDropdown{{ $main->id }}"
              data-bs-toggle="dropdown"
            >
              {{ $main->name }}
              @if($subs->count()) <i class="fas fa-chevron-down ms-1"></i> @endif
            </a>
            @if($subs->count())
              <ul class="dropdown-menu" aria-labelledby="catDropdown{{ $main->id }}">
                @foreach($subs as $sub)
                  @php
                    $subsubs = \App\Models\Category::where('parent_id',$sub->id)->get();
                  @endphp
                  <li class="dropdown-submenu">
                    <a
                      class="dropdown-item d-flex justify-content-between align-items-center"
                      href="{{ $subsubs->count() ? '#' : route('category.show', $sub->slug) }}"
                    >
                      {{ $sub->name }}
                      @if($subsubs->count())
                        <i class="fas fa-chevron-right"></i>
                      @endif
                    </a>
                    @if($subsubs->count())
                      <ul class="dropdown-menu ms-2">
                        @foreach($subsubs as $ss)
                          <li>
                            <a class="dropdown-item" href="{{ route('category.show', $sub->slug) }}">
                              {{ $ss->name }}
                            </a>
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>
    </div>
  </nav>
@endif



    </div>

   

@yield('main')


<!-- Footer Section -->
<footer class="bg-dark text-white py-5">
  <div class="container px-3 px-sm-5">
    <div class="row gx-4 gy-5">

      <!-- Sellers -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">Sellers</h5>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2">
            <a href="{{ url('/become_seller') }}" class="text-white text-decoration-none footer-link">
              Become a Seller
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/privacy') }}" class="text-white text-decoration-none footer-link">
              Privacy Policy
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/terms') }}" class="text-white text-decoration-none footer-link">
              Terms &amp; Conditions
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/seller_forum') }}" class="text-white text-decoration-none footer-link">
              Seller Forum
            </a>
          </li>
          <li>
            <a href="{{ url('/seller_tips') }}" class="text-white text-decoration-none footer-link">
              Seller Tips
            </a>
          </li>
        </ul>
      </div>

      <!-- Buyers -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">Buyers</h5>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2">
            <a href="{{ url('/buyer_tips') }}" class="text-white text-decoration-none footer-link">
              Buyer Tips
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/privacy') }}" class="text-white text-decoration-none footer-link">
              Privacy Policy
            </a>
          </li>
          <li>
            <a href="{{ url('/buyer_terms') }}" class="text-white text-decoration-none footer-link">
              Terms &amp; Conditions
            </a>
          </li>
        </ul>
      </div>

      <!-- About -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">About</h5>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2">
            <a href="{{ url('/about') }}" class="text-white text-decoration-none footer-link">
              About Cetsy
            </a>
          </li>
          <li>
            <a href="{{ url('/house_policy') }}" class="text-white text-decoration-none footer-link">
              House Rules &amp; Policy
            </a>
          </li>
        </ul>
      </div>

      <!-- Support -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">Support</h5>
        <ul class="list-unstyled small mb-4">
          <li class="mb-2">
            <a href="{{ url('/contact') }}" class="text-white text-decoration-none footer-link">
              Reach Us
            </a>
          </li>
          <li class="text-secondary small text-white">
            Email: support@cetsy.co
          </li>
        </ul>

        <!-- Social Icons -->
        <div class="d-flex gap-3">
          <a href="#!" aria-label="Facebook" class="text-white footer-link">
            <i class="fab fa-facebook-f fs-5"></i>
          </a>
          <a href="#!" aria-label="Instagram" class="text-white footer-link">
            <i class="fab fa-instagram fs-5"></i>
          </a>
          <a href="#!" aria-label="Twitter" class="text-white footer-link">
            <i class="fab fa-twitter fs-5"></i>
          </a>
          <a href="#!" aria-label="LinkedIn" class="text-white footer-link">
            <i class="fab fa-linkedin-in fs-5"></i>
          </a>
        </div>
      </div>

    </div>

    <div class="mt-5 pt-4 border-top border-secondary text-center text-secondary small text-white">
      &copy; {{ date('Y') }} cetsy.co — All rights reserved.
    </div>
  </div>
</footer>


</main>

<!-- ===============================================-->
<!--    End of Main Content-->
<!-- ===============================================-->

<!-- ===============================================-->
<!--    JavaScripts-->
<!-- ===============================================-->
<script src="{{ asset('') }}vendors/popper/popper.min.js"></script>
<script src="{{ asset('') }}vendors/bootstrap/bootstrap.min.js"></script>
<script src="{{ asset('') }}vendors/anchorjs/anchor.min.js"></script>
<script src="{{ asset('') }}vendors/is/is.min.js"></script>
<script src="{{ asset('') }}vendors/fontawesome/all.min.js"></script>
<script src="{{ asset('') }}vendors/lodash/lodash.min.js"></script>
<script src="{{ asset('') }}vendors/list.js/list.min.js"></script>
<script src="{{ asset('') }}vendors/feather-icons/feather.min.js"></script>
<script src="{{ asset('') }}vendors/dayjs/dayjs.min.js"></script>
<script src="{{ asset('') }}vendors/mapbox-gl/mapbox-gl.js"></script>
<script src="{{ asset('') }}assets/js/phoenix.js"></script>
<script src="{{ asset('') }}vendors/isotope-layout/isotope.pkgd.min.js"></script>
<script src="{{ asset('') }}vendors/imagesloaded/imagesloaded.pkgd.min.js"></script>
<script src="{{ asset('') }}vendors/isotope-packery/packery-mode.pkgd.min.js"></script>
<script src="{{ asset('') }}vendors/bigpicture/BigPicture.js"></script>
<script src="{{ asset('') }}vendors/countup/countUp.umd.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDbaQGvhe7Af-uOMJz68NWHnO34UjjE7Lo&amp;callback=initMap" async></script>
<script src="{{ asset('') }}/{{ asset('') }}/../smtpjs.com/v3/smtp.js"></script>


@yield('scripts')
 @stack('scripts')



</body>
</html>
