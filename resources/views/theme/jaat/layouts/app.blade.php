<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="index, follow">

  <!-- Dynamic Title -->
  <title>
    @yield('title', 'Jaat – Kenya’s Marketplace')
  </title>

  <!-- Description -->
  <meta name="description"
        content="@yield('meta_description', 'Discover, buy, and sell one-of-a-kind items from trusted Kenyans—right here on Jaat.')">

  <!-- Canonical URL -->
  <link rel="canonical" href="@yield('canonical_url', 'https://jaat.co.ke')">

  <!-- Social Meta Section -->
  @section('social-meta')
    <!-- Open Graph Meta Tags -->
    <meta property="og:title"
          content="@yield('title', 'Jaat – Kenya’s Marketplace')">
    <meta property="og:description"
          content="@yield('meta_description', 'Discover, buy, and sell one-of-a-kind items from trusted Kenyans—right here on Jaat.')">
    <meta property="og:type" content="website">
    <meta property="og:url"  content="@yield('canonical_url', 'https://jaat.co.ke')">
    <meta property="og:image"
          content="@yield('meta_image', asset('assets/images/default-og-image-jaat.jpg'))">
    <meta property="og:image:alt" content="Jaat — Kenya’s Marketplace">
    <meta property="og:locale" content="en_KE">
    <meta property="og:site_name" content="Jaat">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title"
          content="@yield('title', 'Jaat – Kenya’s Marketplace')">
    <meta name="twitter:description"
          content="@yield('meta_description', 'Discover, buy, and sell one-of-a-kind items from trusted Kenyans—right here on Jaat.')">
    <meta name="twitter:image"
          content="@yield('meta_image', asset('assets/images/default-twitter-image-jaat.jpg'))">
    <meta name="twitter:image:alt" content="Jaat — Kenya’s Marketplace">
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

  <!-- Config & RTL -->
  <script src="{{ asset('assets/js/config.js') }}" defer></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      if (window.config.config.phoenixIsRTL) {
        document.documentElement.setAttribute('dir', 'rtl');
      }
    });
  </script>

  <!-- Inline Styles -->
  <style>
    .text-primary { color: #027333 !important; }
    .btn-link     { color: #027333; }
    a.text-primary:hover,
    a.text-primary:focus { color: #025a1f !important; }
  </style>

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body style="--phoenix-scroll-margin-top: 1.2rem;">
  <main class="main" id="top">
    <div class="bg-body-emphasis" data-navbar-shadow-on-scroll="true">

      {{-- Top Contact Bar --}}
      <header class="bg-success border-bottom py-2">
        <div class="container d-flex justify-content-end align-items-center gap-4">
          {{-- Phone --}}
          <div class="d-flex align-items-center">
            <i class="fas fa-phone-alt text-white me-1"></i>
            <small class="text-white fw-semibold">{{ setting('phone') ?? 'N/A' }}</small>
          </div>
          {{-- Email --}}
          <div class="d-flex align-items-center">
            <i class="fas fa-envelope text-white me-1"></i>
            <a href="mailto:{{ setting('email') }}" class="text-white fw-semibold text-decoration-none">
              {{ setting('email') ?? 'info@example.com' }}
            </a>
          </div>
          {{-- Social Media --}}
          <div class="d-flex align-items-center gap-2">
            @foreach(['facebook_url','x_url','instagram_url','linkedin_url','tiktok_url'] as $key)
              @if(!empty(setting($key)))
                <a href="{{ setting($key) }}" target="_blank" class="text-white">
                  <i class="fab fa-{{ str_replace('_url','',$key) }}"></i>
                </a>
              @endif
            @endforeach
          </div>
        </div>
      </header>

      {{-- Navbar --}}
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
          <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ setting('logo_url') }}" style="height:60px" alt="Jaat">
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                  data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                  aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="mainNavbar">
            {{-- Search --}}
            <form class="d-flex me-3 flex-grow-1" method="GET" action="{{ route('search') }}">
              <input class="form-control flex-grow-1" type="search" name="q"
                     placeholder="Search…" aria-label="Search" value="{{ request('q') }}">
              <button class="btn btn-outline-secondary ms-2" type="submit">
                <i class="fas fa-search"></i>
              </button>
            </form>

            {{-- Cart & Auth --}}
            <ul class="navbar-nav ms-auto align-items-center" x-data="cartDropdown()" x-init="fetchCart()">
              @php $count = count(session('cart', [])); @endphp
              <li class="nav-item me-3">
                <a href="{{ route('cart.view') }}" class="nav-link position-relative text-dark">
                  <i class="fas fa-shopping-cart fa-lg"></i>
                  @if($count)
                    <span class="badge bg-success position-absolute top-0 start-100 translate-middle">
                      {{ $count }}
                    </span>
                  @endif
                </a>
              </li>
              @guest
                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log In</a></li>
                <li class="nav-item"><a class="btn btn-success btn-sm" href="{{ route('register') }}">Sign Up</a></li>
              @else
                @if(auth()->user()->shop)
                  <li class="nav-item"><a class="nav-link" href="{{ route('seller.shops.show', auth()->user()->shop) }}">My Shop</a></li>
                @endif
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button"
                     data-bs-toggle="dropdown" aria-expanded="false">
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

      {{-- Category Nav --}}
      @php
        $mainCategories = \App\Models\Category::with('childrenRecursive')
            ->whereNull('parent_id')->orderBy('id')->get();
        $renderCats = function($nodes) use (&$renderCats) {
          foreach ($nodes as $cat) {
            $kids = $cat->childrenRecursive;
            $has  = $kids->isNotEmpty();
            echo '<li class="dropdown-submenu'.($has?'':' no-children').'">';
            echo   '<a class="dropdown-item d-flex justify-content-between align-items-center"'
                 .' href="'.($has?'#':route('category.show',$cat->slug)).'">'
                 .e($cat->name).
                 ($has?'<i class="fas fa-chevron-right ms-2 rotate"></i>':'').
                 '</a>';
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
                    {{ $main->name }}
                    @if($main->childrenRecursive->isNotEmpty())
                      <i class="fas fa-chevron-down ms-1 rotate"></i>
                    @endif
                  </a>
                  @if($main->childrenRecursive->isNotEmpty())
                    <ul class="dropdown-menu">{!! $renderCats($main->childrenRecursive) !!}</ul>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        </nav>
      @endif

      @push('styles')
        <style>
          .dropdown-menu { min-width:230px; border-radius:.5rem; box-shadow:0 .5rem 1rem rgba(0,0,0,.08); }
          .dropdown-submenu>.dropdown-menu { top:-.25rem; left:100%; margin-left:.15rem; }
          .dropdown-submenu.no-children > a .rotate { display:none; }
          .dropdown-item:hover, .dropdown-item:focus { background:#eaf7ef; color:#198754; }
          .rotate { transition:.25s transform; }
          .dropdown-submenu.show > a .rotate,
          .nav-item.dropdown.show > a .rotate { transform:rotate(90deg); }
          .dropdown-menu { max-height:72vh; overflow:auto; z-index:1055; }
          @media (min-width:992px){
            .nav-item.dropdown:hover > .dropdown-menu,
            .dropdown-submenu:hover > .dropdown-menu { display:block; }
          }
        </style>
      @endpush

      @push('scripts')
        <script>
          document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.dropdown-submenu > a').forEach(anchor => {
              anchor.addEventListener('click', e => {
                const sub = anchor.nextElementSibling;
                if(sub && sub.classList.contains('dropdown-menu')){
                  e.preventDefault();
                  const parentLi = anchor.parentElement;
                  const open = parentLi.classList.toggle('show');
                  sub.classList.toggle('show', open);
                  if(open){
                    const rect = sub.getBoundingClientRect();
                    if(rect.right > window.innerWidth){
                      sub.style.left = 'auto'; sub.style.right = '100%';
                    }
                  }
                  parentLi.parentElement.querySelectorAll('.dropdown-submenu.show').forEach(li => {
                    if(li !== parentLi){
                      li.classList.remove('show');
                      li.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                    }
                  });
                }
              });
            });
            if(window.matchMedia('(hover:hover)').matches){
              document.querySelectorAll('.dropdown-submenu').forEach(li => {
                li.addEventListener('mouseenter', () => {
                  const sub = li.querySelector('.dropdown-menu');
                  if(sub){
                    sub.classList.add('show'); li.classList.add('show');
                    const rect = sub.getBoundingClientRect();
                    if(rect.right > window.innerWidth){
                      sub.style.left='auto'; sub.style.right='100%';
                    }
                  }
                });
                li.addEventListener('mouseleave', () => {
                  li.classList.remove('show');
                  li.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
                });
              });
            }
            document.addEventListener('click', e => {
              if(!e.target.closest('nav')){
                document.querySelectorAll('.dropdown-menu.show,.dropdown-submenu.show')
                  .forEach(el => el.classList.remove('show'));
              }
            });
            document.addEventListener('keydown', e => {
              if(e.key==='Escape'){
                document.querySelectorAll('.dropdown-menu.show,.dropdown-submenu.show')
                  .forEach(el => el.classList.remove('show'));
              }
            });
          });
        </script>
      @endpush

    </div>

    {{-- Main Content --}}
    @yield('main')

    {{-- Footer --}}
    @php $settings = \App\Models\Setting::first(); @endphp
    <footer class="bg-dark text-white pt-5">
      <div class="container px-3 px-sm-5">
        <div class="row gx-4 gy-5">
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2">Sellers</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'Become a Seller'    => url('/become-seller'),
                'Privacy Policy'     => url('/privacy'),
                'Terms & Conditions' => url('/terms'),
                'Seller Forum'       => url('/seller-forum'),
                'Seller Tips'        => url('/seller-tips'),
              ] as $label => $link)
                <li class="mb-2">
                  <a href="{{ $link }}" class="text-white-50 text-decoration-none">{{ $label }}</a>
                </li>
              @endforeach
            </ul>
          </div>
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2">Buyers</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'Buyer Tips'         => url('/buyer-tips'),
                'Privacy Policy'     => url('/privacy'),
                'Terms & Conditions' => url('/buyer-terms'),
              ] as $label => $link)
                <li class="mb-2">
                  <a href="{{ $link }}" class="text-white-50 text-decoration-none">{{ $label }}</a>
                </li>
              @endforeach
            </ul>
          </div>
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2">About</h4>
            <ul class="list-unstyled mb-0">
              @foreach([
                'About Jaat'             => url('/about'),
                'House Rules & Policy'   => url('/house-policy'),
              ] as $label => $link)
                <li class="mb-2">
                  <a href="{{ $link }}" class="text-white-50 text-decoration-none">{{ $label }}</a>
                </li>
              @endforeach
            </ul>
          </div>
          <div class="col-6 col-md-3">
            <h4 class="text-uppercase mb-3 border-bottom border-secondary pb-2">Support</h4>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><a href="{{ url('/contact') }}" class="text-white-50 text-decoration-none">Reach Us</a></li>
              <li class="text-white-50 mb-1"><strong>Email:</strong> <a href="mailto:{{ $settings->email }}" class="text-white">{{ $settings->email }}</a></li>
              <li class="text-white-50"><strong>Phone:</strong> <a href="tel:{{ $settings->phone }}" class="text-white">{{ $settings->phone }}</a></li>
            </ul>
            <div class="d-flex gap-4">
              @foreach(['facebook_url','instagram_url','x_url','linkedin_url','tiktok_url'] as $key)
                @if(!empty($settings->{$key}))
                  <a href="{{ $settings->{$key} }}" target="_blank" class="text-white"><i class="fab fa-{{ str_replace('_url','',$key) }}"></i></a>
                @endif
              @endforeach
            </div>
          </div>
        </div>
        <div class="mt-5 pt-4 border-top border-secondary text-center">
          <p class="mb-0">&copy; {{ date('Y') }} Jaat — All rights reserved.</p>
        </div>
      </div>
    </footer>

    @push('styles')
      <style>
        footer, .footer-text, .footer-link, .footer-heading { font-size:15px!important; }
        .footer-heading { font-weight:600; }
        .footer-link { display:inline-block; transition:color .2s; }
        .footer-link:hover { color:#fff!important; text-decoration:none; }
        .social-icon i { font-size:18px; }
      </style>
    @endpush

  </main>

  <!-- Scripts -->
  <script src="{{ asset('vendors/popper/popper.min.js') }}"></script>
  <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>
  <script src="{{ asset('vendors/fontawesome/all.min.js') }}"></script>
  <script src="{{ asset('assets/js/phoenix.js') }}"></script>
  <script src="{{ asset('vendors/countup/countUp.umd.js') }}"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDbaQGvhe7Af-uOMJz68NWHnO34UjjE7Lo&callback=initMap" async></script>
  <script src="https://smtpjs.com/v3/smtp.js" defer></script>

  @yield('scripts')
  @stack('styles')
  @stack('scripts')
</body>
</html>
