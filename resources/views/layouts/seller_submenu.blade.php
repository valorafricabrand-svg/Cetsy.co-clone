{{-- resources/views/partials/seller_sidebar.blade.php --}}
@php
    $currentUrl     = url()->current();
    $walletBalance  = wallet();
    $walletHold     = wallet('on_hold');
    $formatMoney    = fn($amt) => number_format($amt,2);
    $shop           = Auth::user()->shop;
    $productIds     = $shop?->products()->pluck('id')->toArray() ?? [];
    $unreadMessages = \App\Models\Message::whereIn('product_id', $productIds)
                         ->where('receiver_id', Auth::id())
                         ->where('is_read', false)
                         ->count();
    $favoritesCount = \App\Models\Wishlist::whereIn('product_id', $productIds)->count();
    $pendingOffers  = \App\Models\Offer::whereIn('product_id', $productIds)
                         ->where('status', 'pending')->count();

    $navItems = [
      ['route'=>'dashboard','icon'=>'fas fa-tachometer-alt','label'=>'Dashboard'],
      ['route'=>'products.index','icon'=>'fas fa-box-open','label'=>'My Listings'],
      ['route'=>'seller.messages.index','icon'=>'fas fa-comments','label'=>'Messages','badge'=>$unreadMessages],
      ['route'=>'seller.offers.index','icon'=>'fas fa-hand-holding-usd','label'=>'Offers','badge'=>$pendingOffers],
      ['route'=>'seller.favorites.index','icon'=>'fas fa-heart','label'=>'Favorites','badge'=>$favoritesCount],
      ['route'=>'orders.index','icon'=>'fas fa-shopping-cart','label'=>'Shop Orders'],
        ['route'=>'account.orders','icon'=>'fas fa-shopping-cart','label'=>'My Orders'],
      ['route'=>'seller.orders.payments','icon'=>'fas fa-credit-card','label'=>'Payments'],
      ['route'=>'seller.deals.index','icon'=>'fas fa-percent','label'=>'Deals'],
      ['route'=>'seller.shop.create','icon'=>'fas fa-store','label'=>'My Shop'],
      ['route'=>'seller.shipping_profiles.index','icon'=>'fas fa-truck','label'=>'Shipping'],
      ['route'=>'seller.kyc','icon'=>'fas fa-id-card','label'=>'KYC'],
      ['route'=>'seller.subscription','icon'=>'fas fa-file-invoice','label'=>'Subscriptions'],
      ['route'=>'seller.analytics.index','icon'=>'fas fa-chart-line','label'=>'Analytics'],
    ];
@endphp

@if(Auth::user()->isSeller())

    <form class="d-flex flex-grow-1 mx-2" action="{{ route('products.index') }}" method="GET">
        <input class="form-control me-2" type="search" name="q" placeholder="Search products ..." aria-label="Search products ...">
        <button class="btn btn-outline-success" type="submit">
            <i class="fas fa-search"></i>
        </button>
    </form>


  <aside class="position-sticky top-3" style="max-width: 280px;">
    {{-- Balance Card --}}
    <div class="card mb-4 bg-white bg-opacity-75 border-0 shadow-sm rounded-3">
      <a href="{{ route('wallet.index') }}" class="text-decoration-none text-dark">
        <div class="card-body d-flex align-items-center">
          <i class="fas fa-wallet fa-2x text-success me-3"></i>
          <div>
            <small class="text-muted">Balance</small>
            <h5 class="mb-0">${{ $formatMoney($walletBalance) }}</h5>
            @if($walletHold > 0)
              <div class="small text-muted">On Hold: ${{ $formatMoney($walletHold) }}</div>
            @endif
          </div>
        </div>
      </a>
    </div>

    {{-- Navigation --}}
    <ul class="list-group">
      @foreach($navItems as $item)
        @php
          $url = route($item['route']);
          $isActive = str_starts_with($currentUrl, $url);
        @endphp
        <li class="list-group-item d-flex align-items-center {{ $isActive ? 'active bg-success text-white' : 'border-0' }}">
          <a href="{{ $url }}"
             class="d-flex align-items-center flex-grow-1 text-reset {{ $isActive ? '' : 'text-secondary' }}">
            <i class="{{ $item['icon'] }} me-3"></i>
            <span>{{ $item['label'] }}</span>
          </a>
          @if(!empty($item['badge']))
            <span class="badge bg-danger rounded-pill">{{ $item['badge'] }}</span>
          @endif
        </li>
      @endforeach
    </ul>
  </aside>
@endif
