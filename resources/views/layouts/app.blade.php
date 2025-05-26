{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  {{-- Fonts & Icons --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  {{-- Vite-built Tailwind + Alpine --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
  <a href="#main-content" class="sr-only focus:not-sr-only p-2 bg-indigo-600 text-white rounded">
    {{ __('Skip to content') }}
  </a>

  <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

    {{-- Mobile overlay --}}
    <div
      x-show="sidebarOpen"
      x-transition.opacity
      @click="sidebarOpen = false"
      class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"
      aria-hidden="true"
      x-cloak
    ></div>

    {{-- Sidebar --}}
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      x-transition.translate
      class="fixed inset-y-0 left-0 z-30 w-72 overflow-y-auto transition transform bg-gray-800 shadow-lg lg:static lg:translate-x-0"
      role="navigation"
      aria-label="{{ __('Main sidebar') }}"
    >
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
        <a href="{{ url('/') }}" class="text-2xl font-bold text-white">
          {{ config('app.name', 'Laravel') }}
        </a>
        <button
          @click="sidebarOpen = false"
          class="text-gray-400 lg:hidden focus:outline-none focus:ring-2 focus:ring-white rounded"
          aria-label="{{ __('Close sidebar') }}"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {{-- Search box --}}
      <div class="px-6 py-3">
        <div class="relative">
          <label for="sidebar-search" class="sr-only">{{ __('Search') }}</label>
          <input
            id="sidebar-search"
            type="text"
            placeholder="{{ __('Search…') }}"
            class="w-full pl-10 pr-4 py-2 rounded-lg bg-gray-700 text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
               fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      {{-- Navigation --}}
      <nav class="px-2 py-4 space-y-1">
        @auth
          @if(auth()->user()->isAdmin())
            <x-nav-menu
              href="{{ route('admin.dashboard') }}"
              :active="request()->routeIs('admin.dashboard')"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h18v18H3V3z" />
                </svg>
              </x-slot>
              {{ __('Dashboard') }}
            </x-nav-menu>

            <div
              x-data="{ open: request()->routeIs('admin.users.*') || request()->routeIs('admin.reports') }"
              class="space-y-1"
            >
              <button
                @click="open = !open"
                class="flex items-center w-full px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none transition-colors"
                :aria-expanded="open.toString()"
                aria-controls="admin-management-menu"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="ml-3 flex-1">{{ __('Management') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg"
                     :class="open ? 'transform rotate-90' : ''"
                     class="w-4 h-4 text-gray-300 transition-transform"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
              </button>
              <div
                x-show="open"
                x-cloak
                id="admin-management-menu"
                class="space-y-1 pl-12"
              >
                <x-nav-menu
                  href="{{ route('admin.users.index') }}"
                  :active="request()->routeIs('admin.users.*')"
                >
                  {{ __('Users') }}
                </x-nav-menu>
                <x-nav-menu
                  href="{{ route('admin.reports') }}"
                  :active="request()->routeIs('admin.reports')"
                >
                  {{ __('Reports') }}
                </x-nav-menu>
                <x-nav-menu
                  href="{{ route('admin.kyc.index') }}"
                  :active="request()->routeIs('admin.kyc.*')"
                >
                  {{ __('KYC Management') }}
                </x-nav-menu>
              </div>
            </div>

            <form method="POST" action="{{ route('admin.subscriptions.deactivate-expired') }}">
                @csrf
                <button type="submit" class="w-full px-4 py-2 mt-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                    Update Subscriptions
                </button>
            </form>

          @elseif(auth()->user()->isSeller())
            <x-nav-menu
              href="{{ route('seller.dashboard') }}"
              :active="request()->routeIs('seller.dashboard')"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
              </x-slot>
              {{ __('Seller Dashboard') }}
            </x-nav-menu>

            <x-nav-menu
              href="{{ route('seller.kyc') }}"
              :active="request()->routeIs('seller.kyc')"
            >
              <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4"/>
                </svg>
              </x-slot>
              {{ __('KYC Verification') }}
            </x-nav-menu>

            <div x-data="{ open: request()->routeIs('shops.*') }" class="space-y-1">
              <button
                @click="open = !open"
                class="flex items-center w-full px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none transition-colors"
                :aria-expanded="open.toString()"
                aria-controls="seller-store-menu"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                <span class="ml-3 flex-1">{{ __('My Store') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg"
                     :class="open ? 'transform rotate-90' : ''"
                     class="w-4 h-4 text-gray-300 transition-transform"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
              </button>
              <div
                x-show="open"
                x-cloak
                id="seller-store-menu"
                class="space-y-1 pl-12"
              >
                @if(auth()->user()->shop)
                  <x-nav-menu
                    href="{{ route('shops.show', auth()->user()->shop) }}"
                    :active="request()->routeIs('shops.show')"
                  >
                    {{ __('My Shop') }}
                  </x-nav-menu>
                @else
                  <x-nav-menu
                    href="{{ route('shops.create') }}"
                    :active="request()->routeIs('shops.create')"
                  >
                    {{ __('Open Shop') }}
                  </x-nav-menu>
                @endif
                <x-nav-menu
                  href="{{ route('products.index') }}"
                  :active="request()->routeIs('products.*')"
                >
                  {{ __('Products') }}
                </x-nav-menu>
                <x-nav-menu
                  href="{{ route('orders.index') }}"
                  :active="request()->routeIs('orders.*')"
                >
                  {{ __('Orders') }}
                </x-nav-menu>
                <!-- <x-nav-menu
                  href="#"
                  :active="request()->routeIs('orders.*')"
                >
                  {{ __('Messages') }}
                </x-nav-menu>
                <x-nav-menu
                  href="#"
                  :active="request()->routeIs('orders.*')"
                >
                  {{ __('Payouts') }}
                </x-nav-menu>
                

                <x-nav-menu
                  href="#"
                  :active="request()->routeIs('statistics.*')"
                >
                  {{ __('Statistics') }}
                </x-nav-menu> -->
                
              </div>
            </div>

          @else {{-- Buyer --}}
            <x-nav-menu
              href="{{ route('buyer.dashboard') }}"
              :active="request()->routeIs('buyer.dashboard')"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h18v18H3V3z" />
                </svg>
              </x-slot>
              {{ __('Dashboard') }}
            </x-nav-menu>
            <x-nav-menu
              href="{{ route('listings') }}"
              :active="request()->routeIs('listings')"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
              </x-slot>
              {{ __('Browse Products') }}
            </x-nav-menu>
            <x-nav-menu
              href="{{ route('cart.index') }}"
              :active="request()->routeIs('cart.*')"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h18v18H3V3z" />
                </svg>
              </x-slot>
              {{ __('Cart') }}
            </x-nav-menu>
            <x-nav-menu
              href="{{ route('orders.index') }}"
              :active="request()->routeIs('orders.*')"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
              </x-slot>
              {{ __('My Orders') }}
            </x-nav-menu>
          @endif

          {{-- Profile & Logout --}}
          <div class="mt-6 border-t border-gray-700 pt-4 px-4">
            <x-nav-menu
              href="{{ route('profile.edit') }}"
              :active="request()->routeIs('profile.edit')"
              class="text-gray-300 hover:bg-gray-700 hover:text-white"
            >
              <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A13.937 13.937 0 0112 15c3.07 0 5.914.998 8.879 2.69M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </x-slot>
              {{ __('Profile') }}
            </x-nav-menu>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button
                type="submit"
                class="flex items-center w-full px-4 py-2 mt-2 text-red-400 hover:bg-gray-700 hover:text-white rounded-md transition-colors focus:outline-none"
                aria-label="{{ __('Log out') }}"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-3" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7" />
                </svg>
                {{ __('Log Out') }}
              </button>
            </form>
          </div>
        @endauth
      </nav>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
      {{-- Top bar --}}
      <header class="flex items-center justify-between px-8 py-4 bg-white border-b shadow-sm">
        <button
          @click="sidebarOpen = true"
          class="text-gray-600 lg:hidden focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
          aria-label="{{ __('Open sidebar') }}"
          :aria-expanded="sidebarOpen.toString()"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <h1 class="text-2xl font-semibold text-gray-800">@yield('title', __('Dashboard'))</h1>
        <div class="flex items-center space-x-4">
          <span class="hidden text-gray-600 md:inline">{{ Auth::user()->name }}</span>
          <img
            src="{{ Auth::user()->avatar_url ?? 'https://i.pravatar.cc/40' }}"
            alt="{{ __('User avatar') }}"
            class="w-10 h-10 rounded-full border-2 border-indigo-500"
          />
        </div>
      </header>

      {{-- Page Content --}}
      <main id="main-content" class="flex-1 overflow-y-auto p-8 bg-gray-50" tabindex="-1">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- Alpine.js --}}
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  @stack('scripts')
</body>
</html>
