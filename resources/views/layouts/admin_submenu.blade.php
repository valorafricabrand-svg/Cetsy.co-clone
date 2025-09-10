@if(Auth::user()->isAdmin())
  @php
    // Small counters for quick attention
    try {
      $pendingPayouts = \App\Models\PayoutRequest::where('status','pending')->count();
    } catch (\Throwable $e) { $pendingPayouts = 0; }
    try {
      $openDisputes = \App\Models\Dispute::whereIn('status', [\App\Models\Dispute::STATUS_PENDING, \App\Models\Dispute::STATUS_UNDER_REVIEW])->count();
    } catch (\Throwable $e) { $openDisputes = 0; }
    try {
      $openAppeals = \App\Models\Appeal::whereIn('status', [\App\Models\Appeal::STATUS_PENDING, \App\Models\Appeal::STATUS_UNDER_REVIEW, \App\Models\Appeal::STATUS_EVIDENCE_REQUESTED])->count();
    } catch (\Throwable $e) { $openAppeals = 0; }
    try {
      $pendingKyc = \App\Models\Kyc::where('status','pending')->count();
    } catch (\Throwable $e) { $pendingKyc = 0; }
    try {
      $pendingReviews = \App\Models\Review::where(function($q){ $q->whereNull('approved')->orWhere('approved',false); })->count();
    } catch (\Throwable $e) { $pendingReviews = 0; }

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
          ['label' => 'Reviews',          'icon' => 'fas fa-star',       'url' => route('admin.reviews.index'),        'match' => ['admin.reviews.*'],        'count' => $pendingReviews],
        ],
      ],
      [
        'title' => 'Orders & Disputes',
        'items' => array_values(array_filter([
          ['label' => 'Disputes', 'icon' => 'fas fa-gavel',         'url' => route('admin.admin-disputes.index'), 'match' => ['admin.admin-disputes.*'], 'count' => $openDisputes],
          config('disputes.enable_appeals') ? ['label' => 'Appeals',  'icon' => 'fas fa-balance-scale', 'url' => route('admin.appeals.index'), 'match' => ['admin.appeals.*'], 'count' => ($openAppeals ?? 0)] : null,
        ])),
      ],
      [
        'title' => 'Sellers',
        'items' => [
          ['label' => 'Sellers',        'icon' => 'fas fa-users',     'url' => route('admin.users.index'),     'match' => ['admin.users.*']],
          ['label' => 'KYC Management', 'icon' => 'fas fa-id-card',   'url' => route('admin.kyc.index'),       'match' => ['admin.kyc.*'],              'count' => $pendingKyc],
        ],
      ],
      [
        'title' => 'Payments',
        'items' => [
          ['label' => 'Payout Requests',        'icon' => 'fas fa-money-check-alt', 'url' => route('admin.payouts.index'),           'match' => ['admin.payouts.*'],         'count' => $pendingPayouts],
          ['label' => 'Seller Payment Methods', 'icon' => 'fas fa-university',      'url' => route('admin.payment-methods.index'),   'match' => ['admin.payment-methods.*']],
          ['label' => 'Payment Types',          'icon' => 'fas fa-credit-card',     'url' => route('admin.payment-types.index'),     'match' => ['admin.payment-types.*']],
          ['label' => 'Payments',               'icon' => 'fas fa-shopping-cart',   'url' => route('admin.payments.index'),          'match' => ['admin.payments.*']],
        ],
      ],
      [
        'title' => 'System',
        'items' => [
          ['label' => 'Settings', 'icon' => 'fas fa-gear', 'url' => route('admin.settings'), 'match' => ['admin.settings','admin.settings.*']],
          ['label' => 'Notifications', 'icon' => 'fas fa-bell', 'url' => route('notifications.index'), 'match' => ['notifications.index','notifications.index*']],
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
            @if(!empty($item['count']) && (int)$item['count'] > 0)
              <span class="badge rounded-pill ms-auto {{ $active ? 'bg-light text-dark' : 'bg-danger' }}">{{ (int)$item['count'] }}</span>
            @endif
          </a>
        @endforeach
      @endforeach
    </div>
  </div>
@endif

