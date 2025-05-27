<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="@section('description'){{ get_option('meta_description') }} @show">

    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ favicon_url() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ favicon_url() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ favicon_url() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ favicon_url() }}">
    <link rel="manifest" href="{{ favicon_url() }}">
    <meta name="msapplication-TileImage" content="{{ favicon_url() }}">
    <meta name="theme-color" content="#ffffff">
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap"
    rel="stylesheet">
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/line.css') }}">
    <link href="{{ asset('assets/css/theme-rtl.min.css') }}" type="text/css" rel="stylesheet" id="style-rtl">
    <link href="{{ asset('assets/css/theme.min.css') }}" type="text/css" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user-rtl.min.css') }}" type="text/css" rel="stylesheet"
    id="user-style-rtl">
    <link href="{{ asset('assets/css/user.min.css') }}" type="text/css" rel="stylesheet"
    id="user-style-default">
    <link href="{{ asset('vendors/prism/prism-okaidia.css') }}" rel="stylesheet">



    <!-- ===============================================-->
    <!--    Additional Scripts and Styles-->
    <!-- ===============================================-->
    <script>
        var phoenixIsRTL = window.config.config.phoenixIsRTL;
        if (phoenixIsRTL) {
            var linkDefault = document.getElementById('style-default');
            var userLinkDefault = document.getElementById('user-style-default');
            linkDefault.setAttribute('disabled', true);
            userLinkDefault.setAttribute('disabled', true);
            document.querySelector('html').setAttribute('dir', 'rtl');
        } else {
            var linkRTL = document.getElementById('style-rtl');
            var userLinkRTL = document.getElementById('user-style-rtl');
            linkRTL.setAttribute('disabled', true);
            userLinkRTL.setAttribute('disabled', true);
        }
    </script>

    <link href="{{ asset('vendors/leaflet/leaflet.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet.markercluster/MarkerCluster.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet.markercluster/MarkerCluster.Default.css') }}" rel="stylesheet">
 

    @yield('page-css')
      @yield('styles')

    @if(get_option('additional_css'))
    <style type="text/css">
        {{ get_option('additional_css') }}
    </style>
    @endif

    <style>
        .modal {
            z-index: 1050 !important;
            /* Ensure the modal is above other elements */
        }

        .modal-backdrop {
            z-index: 1040 !important;
            /* Ensure the backdrop is below the modal */
        }
    </style>
<!-- in your <head> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css" />

 <!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Hide x-cloak elements until Alpine is ready -->
<style>[x-cloak] { display: none !important; }</style>
   
</head>

<body x-data="cartComponent()" x-init="init(); fetchCart()">
  {{-- Toast --}}
  <div
    x-cloak
    x-show="flashMessage"
    class="position-fixed top-50 start-50 translate-middle zindex-tooltip"
  >
    <div class="toast show align-items-center text-white bg-success border-0">
      <div class="d-flex">
        <div class="toast-body">
          <span x-text="flashMessage"></span>
        </div>
      </div>
    </div>
  </div>

  {{-- Navbar --}}
  <nav class="navbar navbar-expand-sm navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="{{ route('home') }}">
        {{ config('app.name') }}
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        {{-- Search --}}
        <form class="d-flex me-auto my-2 my-sm-0" action="{{ route('products.index') }}" method="GET">
          <input
            class="form-control rounded-pill me-2"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search handmade, vintage & more…"
            aria-label="Search"
          >
          <button class="btn btn-outline-success rounded-pill" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </form>

        <ul class="navbar-nav align-items-center">
          <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categories</a></li>

          {{-- Cart --}}
          <li class="nav-item dropdown" x-data="{ open: false }">
            <a
              class="nav-link position-relative"
              href="#"
              @click.prevent="open = !open; fetchCart()"
              :class="{ show: open }"
            >
              <i class="fas fa-shopping-cart"></i>
              <span
                x-show="cartCount>0"
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                x-text="cartCount"
              ></span>
            </a>
            <div
              class="dropdown-menu dropdown-menu-end p-3"
              :class="{ show: open }"
              style="min-width:20rem"
              @click.away="open = false"
            >
              <template x-if="cartItems.length">
                <div>
                  <p class="fw-semibold mb-2">Cart Preview</p>
                  <ul class="list-unstyled mb-3" style="max-height:12rem;overflow:auto">
                    <template x-for="item in cartItems" :key="item.id">
                      <li class="d-flex align-items-center mb-2">
                        <img :src="`/storage/${item.image}`" class="rounded me-2" style="width:3rem;height:3rem;object-fit:cover">
                        <div class="flex-grow-1">
                          <p class="mb-0" x-text="item.name"></p>
                          <small class="text-muted d-block">Qty: <span x-text="item.qty"></span></small>
                        </div>
                        <span class="fw-semibold text-success" x-text="`KES ${item.total}`"></span>
                      </li>
                    </template>
                  </ul>
                  <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('cart.index') }}" class="small text-success">View Cart</a>
                    <span class="small">Subtotal: <span x-text="`KES ${cartSubtotal}`"></span></span>
                  </div>
                </div>
              </template>
              <div x-show="!cartItems.length" class="text-center text-muted">Your cart is empty.</div>
            </div>
          </li>

          @guest
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log In</a></li>
            <li class="nav-item"><a class="btn btn-success ms-2" href="{{ route('register') }}">Sign Up</a></li>
          @else
            @if(auth()->user()->shop)
              <li class="nav-item"><a class="nav-link" href="{{ route('shops.show', auth()->user()->shop) }}">My Shop</a></li>
            @else
              <li class="nav-item"><a class="nav-link" href="{{ route('shops.create') }}">Open Shop</a></li>
            @endif

            {{-- User Dropdown --}}
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                {{ auth()->user()->name }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Log Out</button>
                  </form>
                </li>
              </ul>
            </li>
          @endguest

        </ul>
      </div>
    </div>
  </nav>

  {{-- Main --}}
  <main class="py-4">@yield('content')</main>

  {{-- Footer --}}
  <footer class="bg-white border-top py-4 mt-auto">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-3">
          <h5>Cetsy</h5>
          <p class="small text-muted">Bringing handmade and vintage goods from independent shops to your door.</p>
        </div>
        <div class="col-md-4 mb-3">
          <h6>About</h6>
          <ul class="list-unstyled small">
            <li><a href="#" class="text-decoration-none">Our Story</a></li>
            <li><a href="#" class="text-decoration-none">Careers</a></li>
            <li><a href="#" class="text-decoration-none">Press</a></li>
          </ul>
        </div>
        <div class="col-md-4 mb-3">
          <h6>Support</h6>
          <ul class="list-unstyled small">
            <li><a href="#" class="text-decoration-none">Help Center</a></li>
            <li><a href="#" class="text-decoration-none">Contact Us</a></li>
            <li><a href="#" class="text-decoration-none">Policies</a></li>
          </ul>
        </div>
      </div>
      <div class="text-center small text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
    </div>
  </footer>



  <script>
    function cartComponent() {
      return {
        flashMessage: '{{ session("success") ?? session("info") ?? "" }}',
        cartCount: 0,
        cartItems: [],
        cartSubtotal: '0.00',
        init() { if (this.flashMessage) setTimeout(() => this.flashMessage = '', 3000); },
        fetchCart() {
          fetch('/cart',{headers:{'Accept':'application/json'}})
            .then(r=>r.json()).then(d=>{this.cartCount=d.count;this.cartItems=d.items;this.cartSubtotal=d.subtotal});
        }
      };
    }
  </script>


  <!-- ===============================================-->
<!--    JavaScripts-->
<!-- ===============================================-->
<script src="{{ asset('vendors/popper/popper.min.js') }}"></script>
<script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendors/anchorjs/anchor.min.js') }}"></script>
<script src="{{ asset('vendors/is/is.min.js') }}"></script>
<script src="{{ asset('vendors/fontawesome/all.min.js') }}"></script>


<script src="{{ asset('vendors/lodash/lodash.min.js') }}"></script>
<script src="{{ asset('vendors/list.js/list.min.js') }}"></script>
<script src="{{ asset('vendors/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('vendors/dayjs/dayjs.min.js') }}"></script>
<script src="{{ asset('vendors/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('vendors/leaflet.markercluster/leaflet.markercluster.js') }}"></script>
<script src="{{ asset('vendors/leaflet.tilelayer.colorfilter/leaflet-tilelayer-colorfilter.min.js') }}"></script>
<script src="{{ asset('assets/js/phoenix.js') }}"></script>
<script src="{{ asset('vendors/echarts/echarts.min.js') }}"></script>
<script src="{{ asset('assets/js/ecommerce-dashboard.js') }}"></script>
<script src="{{ asset('vendors/prism/prism.js') }}"></script>

<script>
    var toastr_options = {closeButton : true};
</script>
@yield('page-js')
@yield('scripts')
@include('chat_widget')
@stack('scripts')

@if(get_option('additional_js') && get_option('additional_js') !== 'additional_js' )
{!! get_option('additional_js') !!}}
@endif
<script>
    $(document).on('click', '.ghuranti', function(){
        $('.themeqx-demo-chooser-wrap').toggleClass('open');
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- before </body> -->
<script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
</body>
</html>
