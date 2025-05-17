<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name','Cetsy') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles & Scripts (Vite) -->
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800">

    <!-- Header / Navbar -->
    <header x-data="{ open: false }" class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Cetsy" class="h-8 w-auto">
                    <span class="ml-2 text-xl font-bold text-green-600">{{ config('app.name') }}</span>
                </a>

                <!-- Search Bar -->
                <div class="flex-1 mx-6">
                    <form action="{{ route('products.index') }}" method="GET" class="relative">
                        <input
                            name="search"
                            type="text"
                            value="{{ request('search') }}"
                            placeholder="Search for handmade, vintage, and more..."
                            class="w-full border border-gray-300 rounded-full py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1012 19.5a7.5 7.5 0 004.65-2.1z"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Desktop Links -->
                <nav class="hidden sm:flex space-x-6 items-center">
                    <a href="{{ route('categories.index') }}" class="text-gray-700 hover:text-green-600">
                        Categories
                    </a>

                    @guest
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-green-600">Log in</a>
                        <a href="{{ route('register') }}" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">Sign up</a>
                    @else
                        @if(auth()->user()->shop)
                            <a href="{{ route('shops.show', auth()->user()->shop) }}" class="text-gray-700 hover:text-green-600">
                                My Shop
                            </a>
                        @else
                            <a href="{{ route('shops.create') }}" class="text-gray-700 hover:text-green-600">
                                Create Shop
                            </a>
                        @endif

                        <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-green-600">
                            Products
                        </a>

                        <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 5m5-5v5m4-5v5m1-10h2"/>
                            </svg>
                        </a>

                        <!-- User Dropdown -->
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

                <!-- Mobile menu button -->
                <div class="sm:hidden flex items-center">
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path :class="{'hidden': open, 'inline-flex': !open}" stroke-linecap="round"
                                  stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path :class="{'hidden': !open, 'inline-flex': open}" stroke-linecap="round"
                                  stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="open" class="sm:hidden bg-white border-t border-gray-200">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block text-gray-700 hover:text-green-600">Home</a>
                <a href="{{ route('categories.index') }}" class="block text-gray-700 hover:text-green-600">Categories</a>

                @auth
                    @if(auth()->user()->shop)
                        <a href="{{ route('shops.show', auth()->user()->shop) }}"
                           class="block text-gray-700 hover:text-green-600">My Shop</a>
                    @else
                        <a href="{{ route('shops.create') }}"
                           class="block text-gray-700 hover:text-green-600">Create Shop</a>
                    @endif

                    <a href="{{ route('products.index') }}" class="block text-gray-700 hover:text-green-600">Products</a>
                    <a href="{{ route('cart.index') }}" class="block text-gray-700 hover:text-green-600">Cart</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left text-gray-700 hover:text-green-600">
                            Log Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block text-gray-700 hover:text-green-600">Log In</a>
                    <a href="{{ route('register') }}" class="block text-gray-700 hover:text-green-600">Sign Up</a>
                @endauth
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
                <p class="text-sm text-gray-600">
                    Bringing handmade and vintage goods from independent shops to your door.
                </p>
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

</body>
</html>
