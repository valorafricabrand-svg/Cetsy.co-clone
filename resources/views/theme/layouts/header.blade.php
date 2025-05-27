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
    <div class="bg-body-emphasis sticky-top" data-navbar-shadow-on-scroll="true">
      @include('theme.layouts.nav')
    </div>

   
