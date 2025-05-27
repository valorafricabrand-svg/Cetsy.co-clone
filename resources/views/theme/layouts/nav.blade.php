<nav class="navbar navbar-expand-sm navbar-light bg-white shadow-sm sticky-top py-3">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand fw-bold" href="{{ route('home') }}">
      {{ config('app.name') }}
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <form class="d-flex me-auto my-2 my-sm-0" action="{{ route('products.index') }}" method="GET">
        <input class="form-control rounded-pill me-2" type="search" name="search" value="{{ request('search') }}" placeholder="Search handmade, vintage & more…" aria-label="Search">
        <button class="btn btn-outline-success rounded-pill" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </form>

      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link text-success fw-semibold" href="{{ route('listings') }}">Products</a></li>

        <!-- Cart -->
        <li class="nav-item dropdown" x-data="{ open: false }">
          <a class="nav-link position-relative" href="#" @click.prevent="open = !open; fetchCart()" :class="{ show: open }">
            <i class="fas fa-shopping-cart"></i>
            <span x-show="cartCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" x-text="cartCount"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end p-3" :class="{ show: open }" style="min-width: 20rem" @click.away="open = false">
            <template x-if="cartItems.length">
              <div>
                <p class="fw-semibold mb-2">Cart Preview</p>
                <ul class="list-unstyled mb-3" style="max-height: 12rem; overflow: auto">
                  <template x-for="item in cartItems" :key="item.id">
                    <li class="d-flex align-items-center mb-2">
                      <img :src="`/storage/${item.image}`" class="rounded me-2" style="width: 3rem; height: 3rem; object-fit: cover">
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

          <!-- User Dropdown -->
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
