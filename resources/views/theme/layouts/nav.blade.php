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
        <li class="nav-item dropdown me-3">
          <button
            class="btn position-relative"
            @click="toggle()"
            aria-label="Cart"
          >
            <i class="fas fa-shopping-cart" style="font-size:1.25rem;"></i>
            <span
              x-show="cartCount > 0"
              x-text="cartCount"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
            ></span>
            <span
              x-show="isLoading"
              class="position-absolute top-0 start-100 translate-middle"
            >
              <span class="spinner-border spinner-border-sm"></span>
            </span>
          </button>

          <div
            class="dropdown-menu dropdown-menu-end p-3"
            :class="{ show: open }"
            style="min-width:300px;"
          >
            <h6 class="dropdown-header">Cart Preview</h6>

            <div
              class="list-group mb-3 overflow-auto"
              style="max-height:240px;"
              x-cloak
            >
              <template x-for="item in cartItems" :key="item.id">
                <div
                  class="list-group-item list-group-item-action d-flex align-items-center"
                >
                  <img
                    :src="`/storage/${item.image}`"
                    :alt="item.name"
                    class="rounded me-3"
                    style="width:40px; height:40px; object-fit:cover;"
                  >
                  <div class="flex-grow-1">
                    <div class="fw-medium" x-text="item.name"></div>
                    <small class="text-muted">
                      Qty: <span x-text="item.qty"></span>
                    </small>
                  </div>
                  <div class="text-success fw-semibold" x-text="`KES ${item.total}`"></div>
                </div>
              </template>

              <div
                class="text-center text-muted py-4"
                x-show="!cartItems.length"
              >
                Your cart is empty.
              </div>
            </div>

            <div class="d-flex justify-content-between">
              <a
                href="{{ route('cart.index') }}"
                class="btn btn-sm btn-outline-primary"
              >
                View Cart
              </a>
              <span class="fw-semibold">
                Subtotal:
                <span x-text="cartItems.length ? `KES ${cartSubtotal}` : '0.00'"></span>
              </span>
            </div>
          </div>
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
                href="{{ route('shops.show', auth()->user()->shop) }}"
              >
                My Shop
              </a>
            </li>
          @else
            <li class="nav-item">
              <a class="nav-link" href="{{ route('shops.create') }}">
                Open Shop
              </a>
            </li>
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

<script>
  function cartDropdown() {
    return {
      open: false,
      cartCount: 0,
      cartItems: [],
      cartSubtotal: '0.00',
      isLoading: false,

      toggle() {
        this.open = !this.open;
      },

      async fetchCart() {
        this.isLoading = true;
        try {
          const res = await fetch('{{ route('cart.index') }}', {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          if (!res.ok) throw new Error('Fetch failed');
          const data = await res.json();
          this.cartCount    = data.count;
          this.cartItems    = data.items;
          this.cartSubtotal = data.subtotal;
        } catch (e) {
          console.error(e);
        } finally {
          this.isLoading = false;
        }
      }
    };
  }
</script>
