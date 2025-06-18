{{-- resources/views/layouts/partials/navbar.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    {{-- Brand --}}
    <a class="navbar-brand" href="{{ url('/') }}">
      <img src="{{ asset('assets/img/logo.jpg') }}" style="height: 100px;">
    </a>

    {{-- Mobile toggle --}}
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#mainNavbar"
      aria-controls="mainNavbar"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
     

      {{-- Search --}}
      <form
        class="d-flex me-3 flex-grow-1"
        method="GET"
        action="{{ route('search') }}"
      >
        <input
          class="form-control flex-grow-1"
          type="search"
          name="q"
          placeholder="Search handmade, vintage, and more..."
          aria-label="Search"
          value="{{ request('q') }}"
        >
        <button class="btn btn-outline-secondary ms-2" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </form>

      {{-- Cart + User --}}
      <ul
        class="navbar-nav ms-auto align-items-center"
        x-data="cartDropdown()"
        x-init="fetchCart()"
      >
      

           {{-- Cart --}}
        @php
          $cartCount = count(session('cart', []));
        @endphp
        <li class="nav-item me-3">
          <a href="{{ route('cart.view') }}" class="nav-link position-relative text-dark">
            <i class="fas fa-shopping-cart fa-lg"></i>
            @if($cartCount)
              <span class="badge bg-success position-absolute top-0 start-100 translate-middle">
                {{ $cartCount }}
              </span>
            @endif
          </a>
        </li>

        {{-- Authentication Links --}}
        @guest
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}">Log In</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-success btn-sm" href="{{ route('register') }}">
              Sign Up
            </a>
          </li>
        @else
          @if(auth()->user()->shop)
            <li class="nav-item">
              <a
                class="nav-link"
                href="{{ route('seller.shops.show', auth()->user()->shop) }}"
              >
                My Shop
              </a>
            </li>
          @else
           
          @endif

          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              id="userMenu"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              {{ auth()->user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">

              <li>
                <a class="dropdown-item" href="{{ route('dashboard') }}">
                  Dashboard
                </a>
              </li>


              <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                  Profile
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="dropdown-item" type="submit">
                    Log Out
                  </button>
                </form>
              </li>
            </ul>
          </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>


