<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="application-name" content="{{ config('app.name', 'Laravel') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Laravel') }}">

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="currency-set-url" content="{{ \Illuminate\Support\Facades\Route::has('currency.set') ? route('currency.set') : url('/set-currency') }}">
    <meta name="description" content="@section('description'){{ get_option('meta_description') }} @show">
   
    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ setting('favicon_url') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ setting('favicon_url') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ setting('favicon_url') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ setting('favicon_url') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileImage" content="{{ setting('favicon_url') }}">
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

    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

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
      @stack('styles')

    @if(get_option('additional_css'))
    <style type="text/css">
        {{ get_option('additional_css') }}
    </style>
    @endif

    <style>
        /* Responsive helpers */
        .content img, .card img, .modal img { max-width: 100%; height: auto; }
        .table-responsive { -webkit-overflow-scrolling: touch; }
        .table-responsive > table { width: 100%; }
        @media (max-width: 767.98px) { footer { display: none !important; } }
    </style>

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


<body>
  
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <nav class="navbar navbar-vertical navbar-expand-lg" style="display:none;">
            <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
                <!-- scrollbar removed-->
                <div class="navbar-vertical-content">
                    <ul class="navbar-nav flex-column" id="navbarVerticalNav">


                        <li class="nav-item">
                       





@include('layouts.buyer_submenu')
@include('layouts.admin_submenu')
@include('layouts.seller_submenu')




                            </li>



                        </ul>
                    </div>
                </div>
              

              
                </nav>



@include('layouts.topnav')




    <script>
        var navbarTopShape = window.config.config.phoenixNavbarTopShape;
        var navbarPosition = window.config.config.phoenixNavbarPosition;
        var body = document.querySelector('body');
        var navbarDefault = document.querySelector('#navbarDefault');
        var navbarTop = document.querySelector('#navbarTop');
        var topNavSlim = document.querySelector('#topNavSlim');
        var navbarTopSlim = document.querySelector('#navbarTopSlim');
        var navbarCombo = document.querySelector('#navbarCombo');
        var navbarComboSlim = document.querySelector('#navbarComboSlim');
        var dualNav = document.querySelector('#dualNav');

        var documentElement = document.documentElement;
        var navbarVertical = document.querySelector('.navbar-vertical');

        if (navbarPosition === 'dual-nav') {
            topNavSlim?.remove();
            navbarTop?.remove();
            navbarTopSlim?.remove();
            navbarCombo?.remove();
            navbarComboSlim?.remove();
            navbarDefault?.remove();
            navbarVertical?.remove();
            dualNav.removeAttribute('style');
            document.documentElement.setAttribute('data-navigation-type', 'dual');

        } else if (navbarTopShape === 'slim' && navbarPosition === 'vertical') {
            navbarDefault?.remove();
            navbarTop?.remove();
            navbarTopSlim?.remove();
            navbarCombo?.remove();
            navbarComboSlim?.remove();
            topNavSlim.style.display = 'block';
            navbarVertical.style.display = 'inline-block';
            document.documentElement.setAttribute('data-navbar-horizontal-shape', 'slim');

        } else if (navbarTopShape === 'slim' && navbarPosition === 'horizontal') {
            navbarDefault?.remove();
            navbarVertical?.remove();
            navbarTop?.remove();
            topNavSlim?.remove();
            navbarCombo?.remove();
            navbarComboSlim?.remove();
            dualNav?.remove();
            navbarTopSlim.removeAttribute('style');
            document.documentElement.setAttribute('data-navbar-horizontal-shape', 'slim');
        } else if (navbarTopShape === 'slim' && navbarPosition === 'combo') {
            navbarDefault?.remove();
            navbarTop?.remove();
            topNavSlim?.remove();
            navbarCombo?.remove();
            navbarTopSlim?.remove();
            dualNav?.remove();
            navbarComboSlim.removeAttribute('style');
            navbarVertical.removeAttribute('style');
            document.documentElement.setAttribute('data-navbar-horizontal-shape', 'slim');
        } else if (navbarTopShape === 'default' && navbarPosition === 'horizontal') {
            navbarDefault?.remove();
            topNavSlim?.remove();
            navbarVertical?.remove();
            navbarTopSlim?.remove();
            navbarCombo?.remove();
            navbarComboSlim?.remove();
            dualNav?.remove();
            navbarTop.removeAttribute('style');
            document.documentElement.setAttribute('data-navigation-type', 'horizontal');
        } else if (navbarTopShape === 'default' && navbarPosition === 'combo') {
            topNavSlim?.remove();
            navbarTop?.remove();
            navbarTopSlim?.remove();
            navbarDefault?.remove();
            navbarComboSlim?.remove();
            dualNav?.remove();
            navbarCombo.removeAttribute('style');
            navbarVertical.removeAttribute('style');
            document.documentElement.setAttribute('data-navigation-type', 'combo');
        } else {
            topNavSlim?.remove();
            navbarTop?.remove();
            navbarTopSlim?.remove();
            navbarCombo?.remove();
            navbarComboSlim?.remove();
            dualNav?.remove();
            navbarDefault.removeAttribute('style');
            navbarVertical.removeAttribute('style');
        }

        var navbarTopStyle = window.config.config.phoenixNavbarTopStyle;
        var navbarTop = document.querySelector('.navbar-top');
        if (navbarTopStyle === 'darker') {
            navbarTop.setAttribute('data-navbar-appearance', 'darker');
        }

        var navbarVerticalStyle = window.config.config.phoenixNavbarVerticalStyle;
        var navbarVertical = document.querySelector('.navbar-vertical');
        if (navbarVerticalStyle === 'darker') {
            navbarVertical.setAttribute('data-navbar-appearance', 'darker');
        }
    </script>



    @yield('content')


@include('layouts.toast')


</main><!-- ===============================================-->
<!--    End of Main Content-->
<!-- ===============================================-->


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
<script>
  // Register Service Worker for PWA support
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
</body>
</html>
