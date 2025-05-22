<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel Admin') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Vite-built CSS/JS (Tailwind + Alpine) -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>

<body class="font-sans antialiased bg-gray-100">
  <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
    <!-- Sidebar backdrop (mobile) -->
    <div 
      x-show="sidebarOpen" 
      @click="sidebarOpen = false"
      class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"
      x-cloak
    ></div>

    <!-- Sidebar -->
    <aside 
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
      class="fixed inset-y-0 left-0 z-30 w-64 transition transform bg-white shadow-lg lg:translate-x-0 lg:static lg:shadow-none"
      x-cloak
    >
      <div class="flex items-center justify-between px-4 py-3 border-b">
        <h1 class="text-lg font-semibold">{{ config('app.name', 'Admin') }}</h1>
        <button @click="sidebarOpen = false" class="text-gray-600 lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <nav class="p-4 space-y-2">
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-3 py-2 text-gray-700 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-200 font-medium' : '' }}">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h18v18H3V3z"/>
          </svg>
          Dashboard
        </a>

        <div x-data="{ open: false }" class="space-y-1">
          <button @click="open = !open" 
                  class="flex items-center justify-between w-full px-3 py-2 text-gray-700 rounded hover:bg-gray-100">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18M3 17h18"/>
              </svg>
              Management
            </span>
            <svg :class="open ? 'transform rotate-90' : ''" class="w-4 h-4 transition-transform" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <div x-show="open" x-cloak class="pl-8 space-y-1">
            <a href="{{ route('users.index') }}"
               class="block px-3 py-2 text-gray-600 rounded hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-gray-200 font-medium' : '' }}">
              Users
            </a>
            <a href="{{ route('roles.index') }}"
               class="block px-3 py-2 text-gray-600 rounded hover:bg-gray-100 {{ request()->routeIs('roles.*') ? 'bg-gray-200 font-medium' : '' }}">
              Roles
            </a>
          </div>
        </div>

        <a href="{{ route('settings') }}" 
           class="flex items-center px-3 py-2 text-gray-700 rounded hover:bg-gray-100 {{ request()->routeIs('settings') ? 'bg-gray-200 font-medium' : '' }}">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2m0 4v4m0-8v2"/>
          </svg>
          Settings
        </a>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" 
                  class="flex items-center w-full px-3 py-2 mt-4 text-left text-red-600 rounded hover:bg-red-50">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7"/>
            </svg>
            Logout
          </button>
        </form>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <!-- Top bar -->
      <header class="flex items-center justify-between px-6 py-4 bg-white border-b">
        <button @click="sidebarOpen = true" class="text-gray-600 focus:outline-none lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h2 class="text-xl font-semibold">@yield('title', 'Dashboard')</h2>
        <div class="flex items-center space-x-4">
          <span class="hidden sm:inline text-gray-600">Hello, {{ Auth::user()->name }}</span>
          <img src="{{ Auth::user()->avatar_url ?? 'https://i.pravatar.cc/40' }}" 
               alt="Avatar" class="w-8 h-8 rounded-full"/>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto p-6">
        @yield('content')
      </main>
    </div>
  </div>

  <!-- Alpine.js -->
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  @stack('scripts')
</body>
</html>
