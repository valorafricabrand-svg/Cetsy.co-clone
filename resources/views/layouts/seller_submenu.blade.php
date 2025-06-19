@if(Auth::user()->isSeller())
  @php
    $currentUrl     = url()->current();
    $walletBalance  = \App\Models\Wallet::where('user_id', Auth::id())
                         ->selectRaw('SUM(credit - debit) as balance')
                         ->value('balance') ?? 0;
    $pendingPayouts = \App\Models\PayoutRequest::where('user_id', Auth::id())
                         ->where('status','pending')->count();
    $formatMoney    = fn($amt) => number_format($amt,2);
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
            <small class="text-muted d-block fw-medium">Available Balance</small>
            <h4 class="mb-0 fw-bold text-success">${{ $formatMoney($walletBalance) }}</h4>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <div class="nav-icon text-primary">
            <i class="fas fa-money-check-alt fa-lg"></i>
          </div>
          <div class="flex-grow-1">
            <a href="{{ route('seller.payouts.index') }}" class="stretched-link text-decoration-none text-dark">
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
        ['url'=>route('orders.index'),'icon'=>'fas fa-basket-shopping','label'=>'Orders'],
        ['url'=>route('seller.orders.payments'),'icon'=>'fas fa-credit-card','label'=>'Payments'],
        ['url'=>route('seller.shop.create'),'icon'=>'fas fa-store','label'=>'My Shop'],
        ['url'=>route('seller.buyers.index'),'icon'=>'fas fa-users','label'=>'My Buyers'],
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
            @if(str_starts_with($currentUrl, $item['url']))
              <i class="fas fa-chevron-right ms-auto"></i>
            @endif
          </a>
        </div>
      @endforeach
    </nav>

    <!-- Help Section -->
    <div class="mt-4 pt-4 border-top">
      <a href="#" class="btn btn-outline-success help-btn w-100 mb-3 d-flex align-items-center">
        <i class="fas fa-question-circle me-2"></i> Help Center
      </a>
      <a href="#" class="btn btn-outline-secondary help-btn w-100 d-flex align-items-center">
        <i class="fas fa-comments me-2"></i> Contact Support
      </a>
    </div>
  </aside>
@endif