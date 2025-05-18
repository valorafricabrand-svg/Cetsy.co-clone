<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <a href="{{ auth()->user()->isAdmin()
                       ? route('admin.dashboard')
                       : (auth()->user()->isSeller()
                           ? route('seller.dashboard')
                           : route('buyer.dashboard')) }}"
           class="flex-shrink-0">
          <x-application-logo class="h-9 w-auto text-gray-800" />
        </a>
      </div>

      <!-- Primary Navigation Links -->
      <div class="hidden space-x-8 sm:flex">
        @auth
          @if(auth()->user()->isAdmin())
            <x-nav-link :href="route('admin.dashboard')"
                        :active="request()->routeIs('admin.dashboard')">
              {{ __('Admin Dashboard') }}
            </x-nav-link>

            <x-nav-link :href="route('admin.users.index')"
                        :active="request()->routeIs('admin.users.*')">
              {{ __('Manage Users') }}
            </x-nav-link>

            <x-nav-link :href="route('admin.reports')"
                        :active="request()->routeIs('admin.reports')">
              {{ __('Reports') }}
            </x-nav-link>

          @elseif(auth()->user()->isSeller())
            <x-nav-link :href="route('seller.dashboard')"
                        :active="request()->routeIs('seller.dashboard')">
              {{ __('Seller Dashboard') }}
            </x-nav-link>

            <x-nav-link :href="route('shops.show', auth()->user()->shop)"
                        :active="request()->routeIs('shops.show')">
              {{ __('My Shop') }}
            </x-nav-link>

            <x-nav-link :href="route('products.index')"
                        :active="request()->routeIs('products.*')">
              {{ __('Products') }}
            </x-nav-link>

            <x-nav-link :href="route('orders.index')"
                        :active="request()->routeIs('orders.*')">
              {{ __('Orders') }}
            </x-nav-link>

          @else {{-- Buyer --}}
            <x-nav-link :href="route('buyer.dashboard')"
                        :active="request()->routeIs('buyer.dashboard')">
              {{ __('Your Dashboard') }}
            </x-nav-link>

            {{-- Browse all products --}}
            <x-nav-link :href="route('products.index')"
                        :active="request()->routeIs('products.index')">
              {{ __('Browse Products') }}
            </x-nav-link>

            <x-nav-link :href="route('cart.index')"
                        :active="request()->routeIs('cart.*')">
              {{ __('Cart') }}
            </x-nav-link>

            <x-nav-link :href="route('orders.index')"
                        :active="request()->routeIs('orders.*')">
              {{ __('My Orders') }}
            </x-nav-link>
          @endif
        @endauth
      </div>

      <!-- User Dropdown -->
      <div class="hidden sm:flex sm:items-center sm:ml-6">
        <x-dropdown align="right" width="48">
          <x-slot name="trigger">
            <button class="inline-flex items-center px-3 py-2 border border-transparent 
                           text-sm font-medium rounded-md text-gray-500 bg-white 
                           hover:text-gray-700 focus:outline-none">
              <div>{{ Auth::user()->name }}</div>
              <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 
                         1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
              </svg>
            </button>
          </x-slot>

          <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">
              {{ __('Profile') }}
            </x-dropdown-link>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <x-dropdown-link :href="route('logout')"
                               onclick="event.preventDefault(); this.closest('form').submit();">
                {{ __('Log Out') }}
              </x-dropdown-link>
            </form>
          </x-slot>
        </x-dropdown>
      </div>

      <!-- Mobile Hamburger -->
      <div class="-mr-2 flex items-center sm:hidden">
        <button @click="open = ! open"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 
                       hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open}" 
                  class="inline-flex" stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            <path :class="{'hidden': !open}" 
                  class="hidden" stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div :class="open ? 'block' : 'hidden'" class="sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
      @auth
        @if(auth()->user()->isAdmin())
          {{-- repeat your admin links here --}}
        @elseif(auth()->user()->isSeller())
          {{-- repeat your seller links here --}}
        @else
          {{-- Buyer mobile links: --}}
          <x-responsive-nav-link :href="route('buyer.dashboard')"
                                 :active="request()->routeIs('buyer.dashboard')">
            {{ __('Your Dashboard') }}
          </x-responsive-nav-link>

          <x-responsive-nav-link :href="route('products.index')"
                                 :active="request()->routeIs('products.index')">
            {{ __('Browse Products') }}
          </x-responsive-nav-link>

          <x-responsive-nav-link :href="route('cart.index')"
                                 :active="request()->routeIs('cart.*')">
            {{ __('Cart') }}
          </x-responsive-nav-link>

          <x-responsive-nav-link :href="route('orders.index')"
                                 :active="request()->routeIs('orders.*')">
            {{ __('My Orders') }}
          </x-responsive-nav-link>
        @endif
      @endauth
    </div>

    <!-- Mobile Settings -->
    <div class="pt-4 pb-1 border-t border-gray-200">
      <div class="px-4">
        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
      </div>
      <div class="mt-3 space-y-1">
        <x-responsive-nav-link :href="route('profile.edit')">
          {{ __('Profile') }}
        </x-responsive-nav-link>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <x-responsive-nav-link :href="route('logout')"
                                 onclick="event.preventDefault(); this.closest('form').submit();">
            {{ __('Log Out') }}
          </x-responsive-nav-link>
        </form>
      </div>
    </div>
  </div>
</nav>
