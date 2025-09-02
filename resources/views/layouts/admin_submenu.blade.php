@if(Auth::user()->isAdmin())
  @php
    $groups = [
      [
        'title' => 'Overview',
        'items' => [
          ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => route('admin.dashboard'), 'match' => ['admin.dashboard']],
          ['label' => 'Reports',   'icon' => 'fas fa-chart-bar',      'url' => route('admin.reports'),   'match' => ['admin.reports']],
        ],
      ],
      [
        'title' => 'Commerce',
        'items' => [
          ['label' => 'Product Listings', 'icon' => 'fas fa-box',        'url' => route('admin.products.index'),       'match' => ['admin.products.*']],
          ['label' => 'Categories',       'icon' => 'fas fa-cogs',       'url' => route('admin.categories.index'),     'match' => ['admin.categories.*']],
          ['label' => 'Product Reports',  'icon' => 'fas fa-flag',       'url' => route('admin.product-reports.index'),'match' => ['admin.product-reports.*']],
          ['label' => 'Reviews',          'icon' => 'fas fa-star',       'url' => route('admin.reviews.index'),        'match' => ['admin.reviews.*']],
        ],
      ],
      [
        'title' => 'Orders & Disputes',
        'items' => [
          ['label' => 'Disputes', 'icon' => 'fas fa-gavel',           'url' => route('admin.admin-disputes.index'), 'match' => ['admin.admin-disputes.*']],
          ['label' => 'Appeals',  'icon' => 'fas fa-balance-scale',   'url' => route('admin.appeals.index'),        'match' => ['admin.appeals.*']],
        ],
      ],
      [
        'title' => 'Sellers',
        'items' => [
          ['label' => 'Sellers',        'icon' => 'fas fa-users',     'url' => route('admin.users.index'),     'match' => ['admin.users.*']],
          ['label' => 'KYC Management', 'icon' => 'fas fa-id-card',   'url' => route('admin.kyc.index'),       'match' => ['admin.kyc.*']],
        ],
      ],
      [
        'title' => 'Payments',
        'items' => [
          ['label' => 'Payout Requests',        'icon' => 'fas fa-money-check-alt', 'url' => route('admin.payouts.index'),           'match' => ['admin.payouts.*']],
          ['label' => 'Seller Payment Methods', 'icon' => 'fas fa-university',      'url' => route('admin.payment-methods.index'),   'match' => ['admin.payment-methods.*']],
          ['label' => 'Payment Types',          'icon' => 'fas fa-credit-card',     'url' => route('admin.payment-types.index'),     'match' => ['admin.payment-types.*']],
          ['label' => 'Payments',               'icon' => 'fas fa-shopping-cart',   'url' => route('admin.payments.index'),          'match' => ['admin.payments.*']],
        ],
      ],
      [
        'title' => 'System',
        'items' => [
          ['label' => 'Settings', 'icon' => 'fas fa-gear', 'url' => route('admin.settings'), 'match' => ['admin.settings','admin.settings.*']],
        ],
      ],
    ];
  @endphp

  <div class="card shadow-sm border-0">
    <div class="list-group list-group-flush">
      @foreach($groups as $group)
        <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">
          {{ $group['title'] }}
        </div>
        @foreach($group['items'] as $item)
          @php
            $patterns = (array) ($item['match'] ?? []);
            $active = false;
            foreach ($patterns as $p) { if (request()->routeIs($p)) { $active = true; break; } }
          @endphp
          <a href="{{ $item['url'] }}"
             class="list-group-item list-group-item-action d-flex align-items-center {{ $active ? 'active' : '' }}">
            <i class="{{ $item['icon'] }} me-2" style="width: 18px; color: {{ $active ? '#fff' : '#027333' }};"></i>
            <span class="fw-medium">{{ $item['label'] }}</span>
          </a>
        @endforeach
      @endforeach
    </div>
  </div>
@endif
