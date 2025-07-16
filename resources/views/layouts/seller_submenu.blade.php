@if(Auth::user()->isSeller())
  @php
    $currentUrl     = url()->current();
    $walletBalance  = \App\Models\Wallet::where('user_id', Auth::id())
                         ->selectRaw('SUM(credit - debit) as balance')
                         ->value('balance') ?? 0;
    $pendingPayouts = \App\Models\PayoutRequest::where('user_id', Auth::id())
                         ->where('status','pending')->count();
    $formatMoney    = fn($amt) => number_format($amt,2);
    // Unread messages count for seller's products
    $shop = Auth::user()->shop;
    $unreadMessages = 0;
    $favoritesCount = 0;
    $pendingOffers = 0;
    if ($shop) {
        $productIds = $shop->products()->pluck('id');
        $unreadMessages = \App\Models\Message::whereIn('product_id', $productIds)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();
        
            // Favorites count for seller's products
    $favoritesCount = \App\Models\Wishlist::whereIn('product_id', $productIds)->count();
    
    // Pending offers count for seller's products
    $pendingOffers = \App\Models\Offer::whereIn('product_id', $productIds)
        ->where('status', 'pending')
        ->count();
    }
  @endphp

  <style>
    .seller-sidebar {
      position: sticky;
      top: 1.5rem;
      max-width: 300px;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .balance-card {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow: hidden;
      position: relative;
    }

    .balance-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .balance-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .balance-card:hover::before {
      opacity: 1;
    }

    .nav-item {
      border-radius: 0.75rem;
      margin-bottom: 0.5rem;
      transition: all 0.3s ease;
    }

    .nav-item:hover {
      background: rgba(25, 135, 84, 0.05);
      transform: translateX(4px);
    }

    .nav-item.active {
      background: #198754;
      color: white !important;
      box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
    }

    .nav-item.active .nav-link {
      color: white !important;
    }

    .nav-icon {
      width: 1.75rem;
      text-align: center;
      margin-right: 0.75rem;
      font-size: 1.1rem;
    }

    .badge-pending {
      background: #dc3545;
      font-size: 0.75rem;
      padding: 0.35em 0.65em;
    }

    .help-btn {
      border-radius: 0.5rem;
      padding: 0.75rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .help-btn:hover {
      transform: translateY(-2px);
    }

    /* Unread badge for messages */
    .badge-unread {
      background: #dc3545 !important;
      color: #fff !important;
      font-size: 0.8rem;
      padding: 0.35em 0.7em;
      border-radius: 1rem;
      margin-left: 0.5rem;
      vertical-align: middle;
    }
  </style>

  <aside class="seller-sidebar mb-4">
    <!-- Wallet & Payouts -->
    <div class="card balance-card mb-4 border-0 position-relative p-3">
      <div class="card-body p-3">
        <div class="d-flex align-items-center mb-4">
          <div class="nav-icon text-success">
            <i class="fas fa-wallet fa-lg"></i>
          </div>
          <div class="flex-grow-1">
            <a href="{{ route('wallet.index') }}" class="stretched-link text-decoration-none text-dark">
              <small class="text-muted d-block fw-medium">Available Balance</small>
              <h4 class="mb-0 fw-bold text-success">${{ $formatMoney($walletBalance) }}</h4>
            </a>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <div class="nav-icon text-primary">
            <i class="fas fa-money-check-alt fa-lg"></i>
          </div>
          <div class="flex-grow-1">
            <a href="{{ route('wallet.index') }}" class="stretched-link text-decoration-none text-dark">
              <small class="text-muted d-block fw-medium">Payout Requests</small>
              <div class="d-flex align-items-center">
                <span class="fw-semibold">Withdraw Funds</span>
                @if($pendingPayouts)
                  <span class="badge badge-pending rounded-pill ms-auto">{{ $pendingPayouts }}</span>
                @endif
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="nav flex-column">
      @foreach([
        ['url'=>route('dashboard'),'icon'=>'fas fa-gauge-high','label'=>'Dashboard'],
        ['url'=>route('products.index'),'icon'=>'fas fa-box-open','label'=>'Listings'],
        ['url'=>route('seller.kyc'),'icon'=>'fas fa-list','label'=>'KYC'],
        ['url'=>route('seller.subscription'),'icon'=>'fas fa-list','label'=>'Subscriptions'],
        ['url'=>route('orders.index'),'icon'=>'fas fa-basket-shopping','label'=>'Orders'],
        ['url'=>route('seller.orders.payments'),'icon'=>'fas fa-credit-card','label'=>'Payments'],
        ['url'=>route('seller.shop.create'),'icon'=>'fas fa-store','label'=>'My Shop'],
        ['url'=>route('seller.shop-posts.index'),'icon'=>'fas fa-newspaper','label'=>'Shop Posts'],
        ['url'=>route('seller.buyers.index'),'icon'=>'fas fa-users','label'=>'My Buyers'],
        [
          'url'=>route('seller.messages.index'),
          'icon'=>'fas fa-comments',
          'label'=>'Messages',
          'unread'=>$unreadMessages
        ],
        [
          'url'=>route('seller.offers.index'),
          'icon'=>'fas fa-hand-holding-dollar',
          'label'=>'My Offers',
          'count'=>$pendingOffers
        ],
        [
          'url'=>route('seller.favorites.index'),
          'icon'=>'fas fa-heart',
          'label'=>'My Favorites',
        ],
        ['url'=>route('shipping_profiles.index'),'icon'=>'fas fa-truck-fast','label'=>'Shipping'],
        ['url'=>route('seller.analytics.index'),'icon'=>'fas fa-chart-line','label'=>'Analytics'],
      ] as $item)
        <div class="nav-item {{ str_starts_with($currentUrl, $item['url']) ? 'active' : '' }}">
          <a href="{{ $item['url'] }}"
             class="nav-link d-flex align-items-center text-dark p-3"
             aria-current="{{ str_starts_with($currentUrl, $item['url']) ? 'page' : 'false' }}">
            <div class="nav-icon">
              <i class="{{ $item['icon'] }} fa-fw"></i>
            </div>
            <span class="fw-medium">{{ $item['label'] }}</span>
            @if(isset($item['unread']) && $item['unread'])
              <span class="badge badge-unread">{{ $item['unread'] }}</span>
            @endif
            @if(isset($item['count']) && $item['count'])
              <span class="badge badge-pending rounded-pill ms-auto">{{ $item['count'] }}</span>
            @endif
            @if(str_starts_with($currentUrl, $item['url']))
              <i class="fas fa-chevron-right ms-auto"></i>
            @endif
          </a>
        </div>
      @endforeach
    </nav>


  </aside>
@endif