<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Vite-built CSS/JS -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- x-cloak helper: hide until Alpine initializes -->
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>

<body class="font-sans antialiased bg-gray-100">
  <div class="min-h-screen">
    @include('layouts.navigation')

    {{-- Page Heading --}}
    @hasSection('header')
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          @yield('header')
        </div>
      </header>
    @endif

    {{-- Page Content --}}
    <main>
      @yield('content')
    </main>
  </div>

  {{-- Alpine.js for wizard, deferred --}}
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  {{-- Optional scripts pushed from child views --}}
  @stack('scripts')
</body>
</html>
