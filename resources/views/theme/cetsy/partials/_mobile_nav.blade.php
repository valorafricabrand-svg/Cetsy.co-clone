@php
  use Illuminate\Support\Facades\Route as Rt;
  use Illuminate\Support\Str;

  $user = auth()->user();
  $cart = session('cart', []);
  $cartCount = collect($cart)->sum(function($i){ return (int)($i['quantity'] ?? 1); });
  $rn = Rt::currentRouteName();
  $hideOnRoutes = [
    'cart.checkout', 'checkout', 'checkout.index', 'checkout.store', 'checkout.success', 'cart.buy',
  ];
  $hideNav = in_array($rn, $hideOnRoutes, true);

  $isHome     = $rn === 'home';
  $isSearch   = $rn === 'search' || Str::startsWith((string)$rn, 'category.');
  $isCart     = Str::startsWith((string)$rn, 'cart.');
  $isSeller   = $user && method_exists($user, 'isSeller') && $user->isSeller();
  $isBuyer    = $user && method_exists($user, 'isBuyer')  && $user->isBuyer();
  $isWishlist = !$isSeller && $rn === 'wishlist';
  $isOrders   = $isSeller
                 ? Str::startsWith((string)$rn, 'seller.orders')
                 : ($rn === 'account.orders' || $rn === 'orders.index' || Str::startsWith((string)$rn, 'buyer.orders'));
  $isSellTab  = $isSeller && Str::startsWith((string)$rn, 'seller.');
  $isAccount  = !$isSeller && ($rn === 'dashboard' || Str::contains((string)$rn, 'account') || Str::startsWith((string)$rn, 'buyer.'));
@endphp

@if(!$hideNav)
<nav class="mobile-bottom-nav d-md-none" role="navigation" aria-label="Primary">
  <a href="{{ route('home') }}" class="mobile-bottom-nav__item {{ $isHome ? 'active' : '' }}" aria-current="{{ $isHome ? 'page' : 'false' }}">
    <i class="fa-solid fa-house"></i>
    <span>Home</span>
  </a>
  <a href="{{ route('search') }}" class="mobile-bottom-nav__item {{ $isSearch ? 'active' : '' }}" aria-current="{{ $isSearch ? 'page' : 'false' }}">
    <i class="fa-solid fa-magnifying-glass"></i>
    <span>Search</span>
  </a>
  @if($isSeller)
    <a href="{{ route('seller.orders.index') }}" class="mobile-bottom-nav__item {{ $isOrders ? 'active' : '' }}" aria-current="{{ $isOrders ? 'page' : 'false' }}">
      <i class="fa-solid fa-box"></i>
      <span>Orders</span>
    </a>
  @else
    @php
      $buyerOrdersUrl = Rt::has('account.orders') ? route('account.orders') : (Rt::has('orders.index') ? route('orders.index') : url('/buyer/orders'));
    @endphp
    <a href="{{ $buyerOrdersUrl }}" class="mobile-bottom-nav__item {{ $isOrders ? 'active' : '' }}" aria-current="{{ $isOrders ? 'page' : 'false' }}">
      <i class="fa-solid fa-box"></i>
      <span>Orders</span>
    </a>
  @endif
  <a href="{{ route('cart.view') }}" class="mobile-bottom-nav__item {{ $isCart ? 'active' : '' }}" aria-current="{{ $isCart ? 'page' : 'false' }}">
    <i class="fa-solid fa-cart-shopping"></i>
    <span>Cart</span>
    @if($cartCount > 0)
      <span class="mobile-bottom-nav__badge" aria-label="{{ $cartCount }} items in cart">{{ $cartCount }}</span>
    @endif
  </a>
  @if($isSeller)
    <a href="{{ route('seller.dashboard') }}" class="mobile-bottom-nav__item {{ $isSellTab ? 'active' : '' }}" aria-current="{{ $isSellTab ? 'page' : 'false' }}">
      <i class="fa-solid fa-store"></i>
      <span>Sell</span>
    </a>
  @else
    <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ $isAccount ? 'active' : '' }}" aria-current="{{ $isAccount ? 'page' : 'false' }}">
      <i class="fa-regular fa-user"></i>
      <span>{{ $user ? 'Account' : 'Sign in' }}</span>
    </a>
  @endif
</nav>

<script>
  // Add body padding only when nav is present/visible
  document.addEventListener('DOMContentLoaded', function(){
    var nav = document.querySelector('.mobile-bottom-nav');
    if (!nav) return;
    var isVisible = window.getComputedStyle(nav).display !== 'none';
    if (isVisible) document.body.classList.add('has-mobile-nav');
  });
</script>
@endif
