<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="currency-set-url" content="{{ \Illuminate\Support\Facades\Route::has('currency.set') ? route('currency.set') : url('/set-currency') }}">

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
    <link rel="apple-touch-icon" sizes="180x180" href="{{ setting('favicon_url') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ setting('favicon_url') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ setting('favicon_url') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ setting('favicon_url') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ setting('favicon_url') }}">
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
      <img src="{{ setting('logo_url') }}" style="height: 60px;">
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
          placeholder="Search..."
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

        {{-- Currency selector --}}
        @php
          try {
            $currentCurrency = get_currency();
            $navCurrencies = \App\Models\Currency::where('is_active', true)->orderBy('code')->get(['code','symbol']);
          } catch (\Throwable $e) {
            $currentCurrency = get_currency();
            $navCurrencies = collect([
              (object)['code' => 'USD','symbol' => '$'],
              (object)['code' => 'EUR','symbol' => '€'],
              (object)['code' => 'GBP','symbol' => '£'],
              (object)['code' => 'KES','symbol' => 'KES'],
            ]);
          }
        @endphp
        <li class="nav-item dropdown me-3">
          <a class="nav-link dropdown-toggle text-dark" href="#" id="currencyDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-coins me-1"></i>{{ $currentCurrency }}
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="currencyDropdown">
            <li class="px-2">
              @php $currencyGet = \Illuminate\Support\Facades\Route::has('currency.set.get') ? route('currency.set.get') : url('/set-currency'); @endphp
              <ul class="list-unstyled mb-0">
                @php $siteDefault = setting('default_currency', 'USD') ?: 'USD'; @endphp
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between {{ strtoupper($currentCurrency) === strtoupper($siteDefault) ? 'active' : '' }}" href="#" data-currency-reset="1">
                    <span>Use Site Default ({{ strtoupper($siteDefault) }})</span>
                    @if(strtoupper($currentCurrency) === strtoupper($siteDefault))
                      <i class="fas fa-check text-success"></i>
                    @endif
                  </a>
                </li>
                @foreach($navCurrencies as $c)
                  @php $code = strtoupper($c->code); $is = $code === strtoupper($currentCurrency); @endphp
                  <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between {{ $is ? 'active' : '' }}" href="#" data-currency-code="{{ $code }}">
                      <span>{{ $c->symbol ? $c->symbol.' ' : '' }}{{ $code }}</span>
                      @if($is)
                        <i class="fas fa-check text-success"></i>
                      @endif
                    </a>
                  </li>
                @endforeach
              </ul>
            </li>
          </ul>
        </li>
      

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

        {{-- Simple Notifications Bell --}}
        @auth
          @php
            // Get unread message count for current user
            $unreadMessages = 0;
            if (auth()->user()->isSeller()) {
              // For sellers: count messages for their products
              $shop = auth()->user()->shop;
              if ($shop) {
                $productIds = $shop->products()->pluck('id');
                $unreadMessages = \App\Models\Message::whereIn('product_id', $productIds)
                  ->where('receiver_id', auth()->id())
                  ->where('is_read', false)
                  ->count();
              }
            } else {
              // For buyers: count messages they received
              $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())
                ->where('is_read', false)
                ->count();
            }
          @endphp
          
          <li class="nav-item me-3">
            <a href="{{ auth()->user()->isSeller() ? route('seller.messages.index') : route('buyer.messages.index') }}" 
               class="nav-link position-relative text-dark" 
               title="Messages">
              <i class="fas fa-bell fa-lg"></i>
              @if($unreadMessages > 0)
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                  {{ $unreadMessages > 99 ? '99+' : $unreadMessages }}
                </span>
              @endif
            </a>
          </li>
        @endauth

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



{{-- ================ Responsive Multi-level Category Nav ================ --}}
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
          echo     e(html_entity_decode($cat->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
          if($has) echo '<i class="fas fa-chevron-right ms-2 rotate"></i>';
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
<nav class="bg-success">
  <div class="container">
    <ul class="nav flex-wrap">
      @foreach($mainCategories as $main)
        <li class="nav-item dropdown">
          <a class="nav-link text-white" href="#" id="catDD{{ $main->id }}" data-bs-toggle="dropdown">
            {{ html_entity_decode($main->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
            @if($main->childrenRecursive->isNotEmpty()) <i class="fas fa-chevron-down ms-1 rotate"></i>@endif
          </a>

          @if($main->childrenRecursive->isNotEmpty())
            <ul class="dropdown-menu">
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
/* ——— Layout ——— */
.dropdown-menu                { min-width:230px; border-radius:.5rem; box-shadow:0 .5rem 1rem rgba(0,0,0,.08); }
.dropdown-submenu>.dropdown-menu{
  top:-0.25rem;               /* 1 — tiny offset so corners don't overlap */
  left:100%;
  margin-left:.15rem;
}
.dropdown-submenu.no-children > a .rotate{display:none}

/* ——— Hover/active styles ——— */
.dropdown-item:hover,
.dropdown-item:focus         { background:#eaf7ef; color:#198754; }
.rotate                       { transition:.25s transform; }
.dropdown-submenu.show   > a .rotate,
.nav-item.dropdown.show  > a .rotate{ transform:rotate(90deg); }

/* ——— Ensure stacking & scrolling ——— */
.dropdown-menu               { max-height:72vh; overflow:auto; z-index:1055; }

/* Desktop hover open */
@media (min-width:992px){
  .nav-item.dropdown:hover   >.dropdown-menu { display:block; }
  .dropdown-submenu:hover    >.dropdown-menu { display:block; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{

  /* ---------- Toggle / flip logic ---------- */
  document.querySelectorAll('.dropdown-submenu > a').forEach(anchor=>{
    anchor.addEventListener('click',e=>{
      const sub = anchor.nextElementSibling;
      if(sub && sub.classList.contains('dropdown-menu')){
        e.preventDefault();
        const parentLi = anchor.parentElement;
        const already = parentLi.classList.toggle('show');
        sub.classList.toggle('show', already);

        // 2 — Flip left if overflowing viewport
        if(already){
          const rect = sub.getBoundingClientRect();
          if(rect.right > window.innerWidth){
            sub.style.left = 'auto';
            sub.style.right = '100%';
          }else{
            sub.style.left = '100%';
            sub.style.right = 'auto';
          }
        }

        // Hide open siblings
        parentLi.parentElement.querySelectorAll(':scope > .dropdown-submenu.show').forEach(li=>{
          if(li!==parentLi){
            li.classList.remove('show');
            li.querySelectorAll('.dropdown-menu.show').forEach(m=>m.classList.remove('show'));
          }
        });
      }
    });
  });
});
</script>
@endpush




    </div>

   

@yield('main')


<!-- Footer Section -->
@php
    $settings = \App\Models\Setting::first();
@endphp

<footer class="bg-dark text-white pt-5">
  <div class="container px-3 px-sm-5">
    <div class="row gx-4 gy-5">

      <!-- Sellers -->
      <div class="col-6 col-md-3">
        <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">
          Sellers
        </h4>
        <ul class="list-unstyled mb-0">
          @foreach([
            'Become a Seller'    => url('/become-seller'),
            'Privacy Policy'     => url('/privacy'),
            'Terms & Conditions' => url('/terms'),
            'Seller Forum'       => url('/seller-forum'),
            'Seller Tips'        => url('/seller-tips'),
          ] as $label => $link)
            <li class="mb-2">
              <a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">
                {{ $label }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <!-- Buyers -->
      <div class="col-6 col-md-3">
        <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">
          Buyers
        </h4>
        <ul class="list-unstyled mb-0">
          @foreach([
            'Buyer Tips'         => url('/buyer-tips'),
            'Privacy Policy'     => url('/privacy'),
            'Terms & Conditions' => url('/buyer-terms'),
          ] as $label => $link)
            <li class="mb-2">
              <a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">
                {{ $label }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <!-- About -->
      <div class="col-6 col-md-3">
        <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">
          About
        </h4>
        <ul class="list-unstyled mb-0">
          @foreach([
            'About ' . config('app.name') => url('/about'),
            'House Rules & Policy'       => url('/house-policy'),
          ] as $label => $link)
            <li class="mb-2">
              <a href="{{ $link }}" class="footer-link text-white-50 text-decoration-none">
                {{ $label }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <!-- Support -->
      <div class="col-6 col-md-3">
        <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2 footer-heading text-white">
          Support
        </h4>
        <ul class="list-unstyled mb-4">
          <li class="mb-2">
            <a href="{{ url('/contact') }}" class="footer-link text-white-50 text-decoration-none">
              Reach Us
            </a>
          </li>
          <li class="text-white-50 mb-1 footer-text">
            <strong>Email:</strong>
            <a href="mailto:{{ $settings->email }}" class="text-white">{{ $settings->email }}</a>
          </li>
          <li class="text-white-50 footer-text">
            <strong>Phone:</strong>
            <a href="tel:{{ $settings->phone }}" class="text-white">{{ $settings->phone }}</a>
          </li>
        </ul>

        <!-- Social Icons -->
        <div class="d-flex gap-4">
          @foreach([
            'facebook_url'  => 'fab fa-facebook-f',
            'instagram_url' => 'fab fa-instagram',
            'x_url'         => 'fab fa-twitter',
            'linkedin_url'  => 'fab fa-linkedin-in',
            'tiktok_url'    => 'fab fa-tiktok',
          ] as $key => $icon)
            @if(!empty($settings->{$key}))
              <a href="{{ $settings->{$key} }}" target="_blank" aria-label="{{ ucfirst(str_replace('_url','',$key)) }}"
                 class="footer-link social-icon">
                <i class="{{ $icon }}"></i>
              </a>
            @endif
          @endforeach
        </div>
      </div>

    </div>

    <div class="mt-5 pt-4 border-top border-secondary text-center">
      <p class="mb-0 text-white-50 footer-text">
        &copy; {{ date('Y') }} {{ config('app.name') }} — All rights reserved.
      </p>
    </div>
  </div>
</footer>

@push('styles')
<style>
  /* Base font size for all footer text and links */
  footer,
  .footer-text,
  .footer-link,
  .footer-heading {
    font-size: 15px !important;
  }

  .footer-heading {
    font-weight: 600;
  }

  .footer-link {
    display: inline-block;
    transition: color .2s ease-in-out;
  }
  .footer-link:hover {
    color: #fff !important;
    text-decoration: none;
  }

  /* Social icons slightly larger for visibility */
  .social-icon i {
    font-size: 18px;
  }
</style>
@endpush



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

<script>
  // Background currency switch (no URL params)
  (function(){
    function onReady(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
    onReady(function(){
      var els = document.querySelectorAll('[data-currency-code]');
      if(!els.length) return;
      var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      var action = document.querySelector('meta[name="currency-set-url"]')?.getAttribute('content') || '/set-currency';
      els.forEach(function(el){
        el.addEventListener('click', function(e){
          e.preventDefault();
          var reset = el.hasAttribute('data-currency-reset');
          var code = el.getAttribute('data-currency-code');
          if(!reset && !code) return;
          try {
            fetch(action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
              },
              credentials: 'same-origin',
              body: reset ? 'reset=1' : ('code=' + encodeURIComponent(code))
            }).then(function(){ location.reload(); })
              .catch(function(){ location.reload(); });
          } catch(_){ location.reload(); }
        });
      });
    });
  })();
</script>



</body>
</html>
