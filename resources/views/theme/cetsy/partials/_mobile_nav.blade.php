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

  // Counts
  $openOrdersCount = 0;
  $unreadMessages  = 0;
  if ($user) {
    try {
      // Consider "open" as not yet delivered/completed: pending, processing, shipped
      $statusesOpen = [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_SHIPPED];
      if ($isSeller) {
        $openOrdersCount = \App\Models\Order::whereIn('status', $statusesOpen)
          ->whereHas('shop', function($q) use ($user){ $q->where('user_id', $user->id); })
          ->count();
      } else {
        $openOrdersCount = \App\Models\Order::where('user_id', $user->id)
          ->whereIn('status', $statusesOpen)
          ->count();
      }
      // Unread messages for current user
      if (class_exists('App\\Models\\Message')) {
        $unreadMessages = \App\Models\Message::where('receiver_id', $user->id)->where('is_read', false)->count();
      }
    } catch (\Throwable $e) { /* fail-safe */ }
  }
  $isWishlist = !$isSeller && $rn === 'wishlist';
  $isOrders   = $isSeller
                 ? Str::startsWith((string)$rn, 'seller.orders')
                 : ($rn === 'account.orders' || $rn === 'orders.index' || Str::startsWith((string)$rn, 'buyer.orders'));
  $isSellTab  = $isSeller && Str::startsWith((string)$rn, 'seller.');
  $isAccount  = !$isSeller && ($rn === 'dashboard' || Str::contains((string)$rn, 'account') || Str::startsWith((string)$rn, 'buyer.'));
@endphp

@if(!$hideNav)
<nav class="mobile-bottom-nav navbar fixed-bottom bg-white border-top d-md-none" role="navigation" aria-label="Primary">
  <div class="container px-2">
    <ul class="nav nav-justified w-100">
      <li class="nav-item">
        <a href="{{ route('home') }}" class="nav-link mobile-bottom-nav__item {{ $isHome ? 'active' : '' }}" aria-current="{{ $isHome ? 'page' : 'false' }}">
          <i class="fa-solid fa-house"></i>
          <span class="label d-block">Home</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('search') }}" class="nav-link mobile-bottom-nav__item {{ $isSearch ? 'active' : '' }}" aria-current="{{ $isSearch ? 'page' : 'false' }}">
          <i class="fa-solid fa-magnifying-glass"></i>
          <span class="label d-block">Search</span>
        </a>
      </li>
      <li class="nav-item">
        @if($isSeller)
          <a href="{{ route('seller.orders.index') }}" class="nav-link mobile-bottom-nav__item {{ $isOrders ? 'active' : '' }}" aria-current="{{ $isOrders ? 'page' : 'false' }}">
            <i class="fa-solid fa-box"></i>
            <span class="label d-block">Orders</span>
            @if($openOrdersCount > 0)
              <span class="mobile-bottom-nav__badge" aria-label="{{ $openOrdersCount }} open orders">{{ $openOrdersCount }}</span>
            @endif
          </a>
        @else
          @php
            $buyerOrdersUrl = session()->has('created_order_ids') && Rt::has('buyer.orders.created')
              ? route('buyer.orders.created')
              : (Rt::has('account.orders') ? route('account.orders') : (Rt::has('orders.index') ? route('orders.index') : url('/buyer/orders')));
          @endphp
          <a href="{{ $buyerOrdersUrl }}" class="nav-link mobile-bottom-nav__item {{ $isOrders ? 'active' : '' }}" aria-current="{{ $isOrders ? 'page' : 'false' }}">
            <i class="fa-solid fa-box"></i>
            <span class="label d-block">Orders</span>
            @if($openOrdersCount > 0)
              <span class="mobile-bottom-nav__badge" aria-label="{{ $openOrdersCount }} open orders">{{ $openOrdersCount }}</span>
            @endif
          </a>
        @endif
      </li>
      <li class="nav-item">
        <a href="{{ route('cart.view') }}" class="nav-link mobile-bottom-nav__item {{ $isCart ? 'active' : '' }}" aria-current="{{ $isCart ? 'page' : 'false' }}">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="label d-block">Cart</span>
          @if($cartCount > 0)
            <span class="mobile-bottom-nav__badge" aria-label="{{ $cartCount }} items in cart">{{ $cartCount }}</span>
          @endif
        </a>
      </li>
      <li class="nav-item">
        @if($isSeller)
          <a href="{{ route('seller.dashboard') }}" class="nav-link mobile-bottom-nav__item {{ $isSellTab ? 'active' : '' }}" aria-current="{{ $isSellTab ? 'page' : 'false' }}">
            <i class="fa-solid fa-store"></i>
            <span class="label d-block">Sell</span>
            @if($unreadMessages > 0)
              <span class="mobile-bottom-nav__badge" aria-label="{{ $unreadMessages }} unread messages">{{ $unreadMessages }}</span>
            @endif
          </a>
        @else
          <a href="{{ route('dashboard') }}" class="nav-link mobile-bottom-nav__item {{ $isAccount ? 'active' : '' }}" aria-current="{{ $isAccount ? 'page' : 'false' }}">
            <i class="fa-regular fa-user"></i>
            <span class="label d-block">{{ $user ? 'Account' : 'Sign in' }}</span>
          </a>
        @endif
      </li>
    </ul>
  </div>
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
<script>
  // Currency select/radio: auto-update on change
  document.addEventListener('DOMContentLoaded', function(){
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    var action = document.querySelector('meta[name="currency-set-url"]')?.getAttribute('content') || '/set-currency';
    function setCurrency(body){
      try {
        fetch(action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json, text/plain, */*',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          credentials: 'same-origin',
          body: body
        }).then(function(){ location.reload(); })
          .catch(function(){ location.reload(); });
      } catch(_) { location.reload(); }
    }
    var sel = document.getElementById('currencySelect');
    if (sel) {
      sel.addEventListener('change', function(){
        var val = this.value;
        if (!val) return;
        setCurrency(val === '__default__' ? 'reset=1' : ('code=' + encodeURIComponent(val)));
      });
    }
    document.querySelectorAll('input[name="code"]').forEach(function(radio){
      radio.addEventListener('change', function(){
        var c = this.value; if (!c) return; setCurrency('code=' + encodeURIComponent(c));
      });
    });
  });
</script>
<script>
  // Enhance topnav currency dropdown by injecting a select for quick change
  document.addEventListener('DOMContentLoaded', function(){
    try {
      var menus = Array.from(document.querySelectorAll('.dropdown-menu'));
      menus.forEach(function(menu){
        if (menu.querySelector('#currencySelectTop')) return;
        var items = Array.from(menu.querySelectorAll('[data-currency-code]'));
        var hasCurrencyList = items.length > 0 || /System Default/.test(menu.textContent||'');
        var trigger = menu.previousElementSibling;
        var isCurrencyMenu = trigger && trigger.querySelector && trigger.querySelector('.fa-coins');
        if (!hasCurrencyList && !isCurrencyMenu) return;
        var sel = document.createElement('select');
        sel.id = 'currencySelectTop';
        sel.className = 'form-select form-select-sm mb-2';
        var defLabel = 'System Default';
        var siteDefault = (document.querySelector('meta[name="default-currency"]')?.getAttribute('content')||'USD').toUpperCase();
        var opt0 = document.createElement('option'); opt0.value = ''; opt0.disabled = true; opt0.selected = true; opt0.textContent = 'Select currency…'; sel.appendChild(opt0);
        var optDef = document.createElement('option'); optDef.value = '__default__'; optDef.textContent = defLabel + ' ('+ siteDefault +')'; sel.appendChild(optDef);
        var current = (trigger && trigger.textContent) ? trigger.textContent.trim().toUpperCase() : '';
        items.forEach(function(a){ var code=a.getAttribute('data-currency-code'); if(!code) return; var o=document.createElement('option'); o.value=code; o.textContent=code; if(current===code) o.selected=true; sel.appendChild(o); });
        menu.insertBefore(sel, menu.firstChild);
        sel.addEventListener('change', function(){
          var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          var action = document.querySelector('meta[name="currency-set-url"]')?.getAttribute('content') || '/set-currency';
          var val = this.value; if(!val) return; var body = (val==='__default__') ? 'reset=1' : ('code=' + encodeURIComponent(val));
          try { fetch(action,{method:'POST',headers:{'X-CSRF-TOKEN':token||'','Accept':'application/json, text/plain, */*','Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},credentials:'same-origin',body}).then(function(){ location.reload(); }).catch(function(){ location.reload(); }); } catch(_) { location.reload(); }
        });
      });
    } catch(e) { /* noop */ }
  });
  </script>
@endif
