{{-- resources/views/layouts/seller_submenu.blade.php --}}
@php
    use Illuminate\Support\Facades\Route as RouteFacade;

    $currentUrl     = url()->current();
    $walletBalance  = wallet();
    $walletHold     = wallet('on_hold');
    $formatMoney    = fn($amt) => number_format($amt, 2);
    $shop           = Auth::user()->shop;
    $productIds     = $shop?->products()->pluck('id')->toArray() ?? [];

    $unreadMessages = \App\Models\Message::whereIn('product_id', $productIds)
        ->where('receiver_id', Auth::id())
        ->where('is_read', false)
        ->count();

    $favoritesCount = \App\Models\Wishlist::whereIn('product_id', $productIds)->count();
    $pendingOffers  = \App\Models\Offer::whereIn('product_id', $productIds)
        ->where('status', 'pending')
        ->count();
    $pendingOrders  = $shop ? (\App\Models\Order::where('shop_id', $shop->id)->where('status', 'pending')->count()) : 0;
    $kycStatus      = optional(Auth::user()->kyc)->status; // pending|approved|rejected|null

    $groups = [
      [
        'title' => 'Overview',
        'items' => [
          ['route' => 'dashboard',              'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'match' => ['dashboard']],
        ],
      ],
      [
        'title' => 'Catalog',
        'items' => [
          ['route' => 'products.index',         'icon' => 'fas fa-box-open',        'label' => 'My Listings', 'match' => ['products.*']],
          ['route' => 'seller.deals.index',     'icon' => 'fas fa-percent',         'label' => 'Deals',        'match' => ['seller.deals.*']],
          ['route' => 'seller.favorites.index', 'icon' => 'fas fa-heart',           'label' => 'Favorites',    'match' => ['seller.favorites.*'], 'count' => $favoritesCount],
        ],
      ],
      [
        'title' => 'Messaging',
        'items' => [
          ['route' => 'seller.messages.index',  'icon' => 'fas fa-comments',        'label' => 'Messages',     'match' => ['seller.messages.*'],  'count' => $unreadMessages],
          ['route' => 'seller.offers.index',    'icon' => 'fas fa-hand-holding-usd','label' => 'Offers',       'match' => ['seller.offers.*'],    'count' => $pendingOffers],
        ],
      ],
      [
        'title' => 'Orders',
        'items' => [
          ['route' => 'orders.index',           'icon' => 'fas fa-shopping-cart',   'label' => 'Shop Orders',  'match' => ['orders.*'],           'count' => $pendingOrders],
          ['route' => 'account.orders',         'icon' => 'fas fa-clipboard-list',  'label' => 'My Orders',    'match' => ['account.orders']],
          ['route' => 'seller.orders.payments', 'icon' => 'fas fa-credit-card',     'label' => 'Payments',     'match' => ['seller.orders.payments']],
        ],
      ],
      [
        'title' => 'Payouts & Billing',
        'items' => [
          ['route' => 'wallet.index',                 'icon' => 'fas fa-wallet',       'label' => 'Wallet',           'match' => ['wallet.index']],
          ['route' => 'seller.payouts.index',         'icon' => 'fas fa-money-check',  'label' => 'Payouts',          'match' => ['seller.payouts.*']],
          ['route' => 'seller.payment-methods.index', 'icon' => 'fas fa-university',   'label' => 'Payment Methods',  'match' => ['seller.payment-methods.*']],
        ],
      ],
      [
        'title' => 'Shop & Settings',
        'items' => [
          ['route' => 'seller.shop.create',            'icon' => 'fas fa-store',        'label' => 'My Shop',         'match' => ['seller.shop.*']],
          ['route' => 'seller.shipping_profiles.index', 'icon' => 'fas fa-truck',        'label' => 'Shipping',        'match' => ['seller.shipping_profiles.*']],
          ['route' => 'seller.subscription',           'icon' => 'fas fa-file-invoice', 'label' => 'Subscriptions',   'match' => ['seller.subscription']],
          ['route' => 'seller.kyc',                    'icon' => 'fas fa-id-card',      'label' => 'KYC',             'match' => ['seller.kyc'], 'count' => ($kycStatus==='pending' ? 1 : 0)],
        ],
      ],
      [
        'title' => 'Insights',
        'items' => [
          ['route' => 'seller.analytics.index',       'icon' => 'fas fa-chart-line',   'label' => 'Analytics',      'match' => ['seller.analytics.*']],
        ],
      ],
    ];
@endphp

@if(Auth::user()->isSeller())

    <form class="d-flex flex-grow-1 mx-2" action="{{ route('products.index') }}" method="GET">
        <input class="form-control me-2" type="search" name="q" placeholder="Search products ..." aria-label="Search products ...">
        <button class="btn btn-outline-success" type="submit">
            <i class="fas fa-search"></i>
        </button>
    </form>

  <aside class="position-sticky top-3" style="max-width: 300px;">
    {{-- Balance Card --}}
    <div class="card mb-4 bg-white bg-opacity-75 border-0 shadow-sm rounded-3">
      <a href="{{ route('wallet.index') }}" class="text-decoration-none text-dark">
        <div class="card-body d-flex align-items-center">
          <i class="fas fa-wallet fa-2x text-success me-3"></i>
          <div>
            <small class="text-muted">Balance</small>
            <h5 class="mb-0">{{ get_currency() }} {{ $formatMoney($walletBalance) }}</h5>
            @if($walletHold > 0)
              <div class="small text-muted">On Hold: {{ get_currency() }} {{ $formatMoney($walletHold) }}</div>
            @endif
          </div>
        </div>
      </a>
    </div>

    {{-- Navigation --}}
    <div class="card shadow-sm border-0">
      <div class="list-group list-group-flush">
        @foreach($groups as $group)
          <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">{{ $group['title'] }}</div>
          @foreach($group['items'] as $item)
            @php
              if (!RouteFacade::has($item['route'])) { continue; }
              $patterns = (array) ($item['match'] ?? []);
              $active = false;
              foreach ($patterns as $p) { if (request()->routeIs($p)) { $active = true; break; } }
            @endphp
            <a href="{{ route($item['route']) }}"
               class="list-group-item list-group-item-action d-flex align-items-center {{ $active ? 'active' : '' }}">
              <i class="{{ $item['icon'] }} me-2" style="width:18px; color: {{ $active ? '#fff' : '#027333' }};"></i>
              <span class="fw-medium">{{ $item['label'] }}</span>
              @if(!empty($item['count']))
                <span class="badge rounded-pill ms-auto {{ $active ? 'bg-light text-dark' : 'bg-danger' }}">{{ (int)$item['count'] }}</span>
              @endif
            </a>
          @endforeach
        @endforeach
      </div>
    </div>
  </aside>
@endif

