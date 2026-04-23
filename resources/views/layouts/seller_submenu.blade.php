{{-- resources/views/layouts/seller_submenu.blade.php --}}
@php
    $currentUrl     = url()->current();
    $walletBalance  = wallet();
    $walletHold     = wallet('on_hold');
    $formatMoney    = fn($amt) => number_format((float)$amt, 2);
    $shop           = Auth::user()->shop;
    $brandColor     = optional($shop)->primary_color;
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        $brandColor = '#198754'; // bootstrap success fallback
    }

    $productIds     = $shop?->products()->pluck('id')->toArray() ?? [];
    $myFavoritesCount = \App\Models\Activity::where('user_id', Auth::id())
        ->where('type', \App\Models\Activity::TYPE_WISHLIST)
        ->where('is_read', false)
        ->whereNull('causer_id')
        ->count();
    $unreadMessages = \App\Models\Message::whereIn('product_id', $productIds)
                         ->where('receiver_id', Auth::id())
                         ->where('is_read', false)
                         ->count();
    // Unread favorites on this seller's products (shop favorites).
    $shopFavoritesCount = \App\Models\Activity::where('user_id', Auth::id())
        ->where('type', \App\Models\Activity::TYPE_WISHLIST)
        ->where('is_read', false)
        ->whereNotNull('causer_id')
        ->whereIn('related_id', $productIds)
        ->count();
    $pendingOffers  = \App\Models\Activity::where('user_id', Auth::id())
        ->where('type', \App\Models\Activity::TYPE_OFFER)
        ->where('is_read', false)
        ->whereIn('related_id', \App\Models\Offer::query()->select('id')->whereIn('product_id', $productIds))
        ->count();
    // Pending disputes count for this seller (sidebar badge)
    $disputesCount  = \App\Models\Dispute::where('seller_id', Auth::id())
                        ->where('status', \App\Models\Dispute::STATUS_PENDING)
                        ->count();
    $unreadNotifications = \App\Models\Activity::where('user_id', Auth::id())
        ->where('is_read', false)
        ->count();
    $myShopUrl = $shop && \Illuminate\Support\Facades\Route::has('shop.show')
        ? route('shop.show', $shop->slug ?: $shop->getKey())
        : route('seller.shop.create');

    // Grouped navigation
    $groups = [
        'Overview' => [
            ['route'=>'seller.dashboard','icon'=>'fas fa-tachometer-alt','label'=>'Dashboard'],
        ],
        'Listings' => [
            ['route'=>'products.index','icon'=>'fas fa-box-open','label'=>'My Listings'],
            ['route'=>'seller.deals.index','icon'=>'fas fa-percent','label'=>'%Deals'],
            // Shipping profiles removed from side menu per request
        ],
        'Sales' => [
            ['route'=>'seller.orders.index','icon'=>'fas fa-shopping-cart','label'=>'Shop Orders'],
            ['route'=>'seller.reviews.index','icon'=>'fas fa-star','label'=>'Reviews'],
            ['route'=>'account.orders','icon'=>'fas fa-bag-shopping','label'=>'My Orders'],
            ['route'=>'seller.orders.payments','icon'=>'fas fa-credit-card','label'=>'Payments'],
        ],
        'Engagement' => [
            ['route'=>'seller.messages.index','icon'=>'fas fa-comments','label'=>'Messages','badge'=>$unreadMessages],
            ['route'=>'seller.offers.index','icon'=>'fas fa-handshake','label'=>'Offers','badge'=>$pendingOffers],
            ['route'=>'buyer.favorites','icon'=>'fas fa-heart','label'=>'Favorites','badge'=>$myFavoritesCount],
            ['route'=>'seller.favorites.index','icon'=>'fas fa-store','label'=>'Shop Favorites','badge'=>$shopFavoritesCount],
            ['route'=>'seller.notifications.index','icon'=>'fas fa-bell','label'=>'Notifications','badge'=>$unreadNotifications],
        ],
         'Disputes' => [
            ['route'=>'disputes.index','icon'=>'fas fa-exclamation-triangle','label'=>'Disputes','badge'=>$disputesCount],
        ],
        'Shop & Settings' => [
            ['href'=>$myShopUrl,'icon'=>'fas fa-store','label'=>'My Shop','activePatterns'=>['shop.show', 'seller.shops.*', 'seller.shop.create']],
            ['route'=>'seller.analytics.index','icon'=>'fas fa-chart-line','label'=>'Analytics'],
            ['route'=>'seller.reports.inventory','icon'=>'fas fa-boxes-stacked','label'=>'Inventory Report'],
            ['route'=>'seller.subscription','icon'=>'fas fa-file-invoice','label'=>'Subscription'],
            ['route'=>'seller.kyc','icon'=>'fas fa-id-card','label'=>'KYC'],
        ],
    ];
@endphp

@if(Auth::user()->isSeller())

  <style>
    .seller-nav { --brand: {{ $brandColor }}; }
    .seller-nav .section-title { font-size:.72rem; letter-spacing:.06em; color:#6c757d; padding:.5rem .75rem; text-transform:uppercase; }
    .seller-nav .list-group-item { border:0; padding:.5rem .75rem; }
    .seller-nav .list-group-item.active { background: var(--brand); color:#fff; }
    .seller-nav .nav-item-link { display:flex; align-items:center; gap:.625rem; text-decoration:none; width:100%; color:inherit; }
    .seller-nav .nav-item-link .icon { width:1.1rem; text-align:center; }
    .seller-nav .badge { font-weight:600; }
  </style>

  {{-- Search --}}
  <form class="d-flex flex-grow-1 mb-3" action="{{ route('products.index') }}" method="GET">
      <input class="form-control me-2" type="search" name="q" placeholder="Search products ..." aria-label="Search products ...">
      <button class="btn btn-outline-success" type="submit"><i class="fas fa-search"></i></button>
  </form>

  <aside class="seller-nav position-sticky top-3" style="max-width: 280px;">
    {{-- Balance Card --}}
    <div class="card mb-4 bg-white bg-opacity-75 border-0 shadow-sm rounded-3">
      <a href="{{ route('wallet.index') }}" class="text-decoration-none text-dark">
        <div class="card-body d-flex align-items-center">
          <i class="fas fa-wallet fa-2x me-3" style="color:var(--brand);"></i>
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

    {{-- Grouped Navigation --}}
    @foreach($groups as $groupLabel => $items)
      <div class="section-title">{{ $groupLabel }}</div>
      <ul class="list-group mb-2">
        @foreach($items as $item)
          @php
            $routeName = $item['route'] ?? null;
            $url = $item['href'] ?? ($routeName && \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : null);
            $activePatterns = (array) ($item['activePatterns'] ?? ($routeName ? [$routeName] : []));
            $isActive = !empty($activePatterns) ? request()->routeIs(...$activePatterns) : ($url ? str_starts_with($currentUrl, $url) : false);
            $badge = $item['badge'] ?? null;
          @endphp
          @continue(!$url)
          <li class="list-group-item {{ $isActive ? 'active' : '' }}">
            <a href="{{ $url }}" class="nav-item-link">
              <span class="icon"><i class="{{ $item['icon'] }}"></i></span>
              <span class="label flex-fill">{{ $item['label'] }}</span>
              @if(!empty($badge))
                <span class="badge bg-danger rounded-pill">{{ $badge > 99 ? '99+' : $badge }}</span>
              @endif
            </a>
          </li>
        @endforeach
      </ul>
    @endforeach
  </aside>
@endif
