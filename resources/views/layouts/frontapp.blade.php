<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Cetsy') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body 
  x-data="cartComponent()" 
  x-init="init(); fetchCart()" 
  class="font-sans antialiased text-gray-800"
>
  <!-- Centered Toast -->
  <div
    x-cloak
    x-show="flashMessage"
    x-transition.opacity
    class="fixed inset-0 flex items-center justify-center pointer-events-none"
  >
    <div class="bg-green-600 text-white px-6 py-3 rounded shadow-lg pointer-events-auto">
      <span x-text="flashMessage"></span>
    </div>
  </div>

  <!-- Header -->
  <header @click.away="open = false" class="bg-white shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800">
          {{ config('app.name') }}
        </a>

        <!-- Search -->
        <div class="flex-1 mx-6">
          <form action="{{ route('products.index') }}" method="GET" class="relative">
            <input
              name="search" type="text" value="{{ request('search') }}"
              placeholder="Search handmade, vintage, and more..."
              class="w-full border border-gray-300 rounded-full py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <button type="submit"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    aria-label="Search">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
              </svg>
            </button>
          </form>
        </div>

        <!-- Desktop Nav -->
        <nav class="hidden sm:flex space-x-6 items-center">
          <a href="{{ route('categories.index') }}" class="text-gray-700 hover:text-green-600">Categories</a>

          <!-- Cart Icon & Dropdown -->
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open; fetchCart()"
                    class="relative text-gray-700 hover:text-green-600"
                    aria-label="Cart">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 
                         13L5.4 5M7 13l-2 5m5-5v5m4-5v5m1-10h2"/>
              </svg>
              <span x-show="cartCount > 0"
                    x-text="cartCount"
                    class="absolute -top-1 -right-2 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
              </span>
            </button>

            <div x-show="open" x-cloak @click.away="open = false"
                 class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-lg overflow-hidden text-sm">
              <template x-if="cartItems.length">
                <div class="p-4">
                  <p class="font-semibold mb-2">Cart Preview</p>
                  <ul class="divide-y divide-gray-200 max-h-60 overflow-y-auto">
                    <template x-for="item in cartItems" :key="item.id">
                      <li class="py-2 flex items-center space-x-3">
                        <img :src="`/storage/${item.image}`" x-bind:alt="item.name"
                             class="w-12 h-12 object-cover rounded">
                        <div class="flex-1">
                          <p class="font-medium text-gray-800" x-text="item.name"></p>
                          <p class="text-gray-500 text-xs">Qty: <span x-text="item.qty"></span></p>
                        </div>
                        <p class="text-green-600 font-semibold" x-text="`KES ${item.total}`"></p>
                      </li>
                    </template>
                  </ul>
                  <div class="mt-4 flex justify-between items-center">
                    <a href="{{ route('cart.index') }}" class="text-sm text-green-600 hover:underline">View Cart</a>
                    <span class="font-semibold text-gray-800">
                      Subtotal: <span x-text="`KES ${cartSubtotal}`"></span>
                    </span>
                  </div>
                </div>
              </template>
              <div class="p-4 text-gray-500" x-show="!cartItems.length">Your cart is empty.</div>
            </div>
          </div>

          @guest
            <a href="{{ route('login') }}" class="text-gray-700 hover:text-green-600">Log In</a>
            <a href="{{ route('register') }}"
               class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition">
              Sign Up
            </a>
          @else
            @if(auth()->user()->shop)
              <a href="{{ route('shops.show', auth()->user()->shop) }}"
                 class="text-gray-700 hover:text-green-600">My Shop</a>
            @else
              <a href="{{ route('shops.create') }}"
                 class="text-gray-700 hover:text-green-600">Open Shop</a>
            @endif

            <x-dropdown align="right" width="48">
              <x-slot name="trigger">
                <button class="flex items-center space-x-1 text-gray-700 hover:text-green-600">
                  <span>{{ auth()->user()->name }}</span>
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 
                             1 0 111.414 1.414l-4 4a1 1 0 
                             01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd"/>
                  </svg>
                </button>
              </x-slot>
              <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <x-dropdown-link :href="route('logout')"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                    Log Out
                  </x-dropdown-link>
                </form>
              </x-slot>
            </x-dropdown>
          @endguest
        </nav>

        <!-- Mobile Toggle -->
        <div class="sm:hidden flex items-center" x-data="{ mobileOpen: false }">
          <button @click="mobileOpen = !mobileOpen" class="text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path :class="{'hidden': mobileOpen, 'inline-flex': !mobileOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              <path :class="{'hidden': !mobileOpen, 'inline-flex': mobileOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
          <div x-show="mobileOpen" class="absolute top-full left-0 w-full bg-white border-t border-gray-200">
            <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Home</a>
            <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Categories</a>
            <a href="{{ route('cart.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Cart</a>
            @guest
              <a href="{{ route('login') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Log In</a>
              <a href="{{ route('register') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Sign Up</a>
            @else
              @if(auth()->user()->shop)
                <a href="{{ route('shops.show', auth()->user()->shop) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">My Shop</a>
              @else
                <a href="{{ route('shops.create') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Open Shop</a>
              @endif
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50">Log Out</button>
              </form>
            @endguest
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="mt-6">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="bg-gray-100 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
      <div>
        <h4 class="font-semibold text-gray-700 mb-4">Cetsy</h4>
        <p class="text-sm text-gray-600">Bringing handmade and vintage goods from independent shops to your door.</p>
      </div>
      <div>
        <h4 class="font-semibold text-gray-700 mb-4">About</h4>
        <ul class="space-y-2 text-sm text-gray-600">
          <li><a href="#" class="hover:text-green-600">Our Story</a></li>
          <li><a href="#" class="hover:text-green-600">Careers</a></li>
          <li><a href="#" class="hover:text-green-600">Press</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold text-gray-700 mb-4">Support</h4>
        <ul class="space-y-2 text-sm text-gray-600">
          <li><a href="#" class="hover:text-green-600">Help Center</a></li>
          <li><a href="#" class="hover:text-green-600">Contact Us</a></li>
          <li><a href="#" class="hover:text-green-600">Policies</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-gray-200 py-4 text-center text-sm text-gray-500">
      &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
  </footer>

  <script>
    function cartComponent() {
      return {
        flashMessage: '{{ session("success") ?? session("info") ?? "" }}',
        cartCount: 0,
        cartItems: [],
        cartSubtotal: '0.00',

        init() {
          if (this.flashMessage) {
            setTimeout(() => this.flashMessage = '', 3000);
          }
        },

        fetchCart() {
          fetch('/cart', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
              this.cartCount    = d.count;
              this.cartItems    = d.items;
              this.cartSubtotal = d.subtotal;
            });
        },

        addToCart(productId, qty = 1) {
          fetch('/cart', {
            method: 'POST',
            headers: {
              'Content-Type':'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ product_id: productId, quantity: qty })
          })
          .then(r => r.json())
          .then(d => {
            this.cartCount    = d.count;
            this.cartItems    = d.items;
            this.cartSubtotal = d.subtotal;
            this.flashMessage = 'Added to cart!';
            setTimeout(() => this.flashMessage = '', 3000);
          });
        }
      };
    }
  </script>
</body>
</html>
