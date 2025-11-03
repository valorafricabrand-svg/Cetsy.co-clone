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
    try {
      $draftPosts = \App\Models\BlogPost::where('status', \App\Models\BlogPost::STATUS_DRAFT)->count();
    } catch (\Throwable $e) { $draftPosts = 0; }

    // Notifications counter
    try {
      $unreadNotifications = \App\Models\Activity::where('is_read', false)->count();
    } catch (\Throwable $e) { $unreadNotifications = 0; }

    $groups = [
      [
        'title' => 'Overview',
        'items' => [
          ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => route('admin.dashboard'), 'match' => ['admin.dashboard']],
          ['label' => 'Reports',   'icon' => 'fas fa-chart-bar',      'url' => route('admin.reports'),   'match' => ['admin.reports']],
          ['label' => 'MRR',        'icon' => 'fas fa-chart-line', 'url' => route('admin.reports.mrr'), 'match' => ['admin.reports.mrr']],
          ['label' => 'Notifications', 'icon' => 'fas fa-bell', 'url' => route('admin.notifications.index'), 'match' => ['admin.notifications.*'], 'count' => $unreadNotifications],
        ],
      ],
      [
        'title' => 'Commerce',
        'items' => [
          ['label' => 'Product Listings', 'icon' => 'fas fa-box',        'url' => route('admin.products.index'),       'match' => ['admin.products.*']],
          ['label' => 'Categories',       'icon' => 'fas fa-cogs',       'url' => route('admin.categories.index'),     'match' => ['admin.categories.*']],
          ['label' => 'Product Reports',  'icon' => 'fas fa-flag',       'url' => route('admin.product-reports.index'),'match' => ['admin.product-reports.*']],
          ['label' => 'Product Activity', 'icon' => 'fas fa-list-alt',   'url' => route('admin.product-activities.index'),'match' => ['admin.product-activities.*']],
          ['label' => 'Reviews',          'icon' => 'fas fa-star',       'url' => route('admin.reviews.index'),        'match' => ['admin.reviews.*'],        'count' => $pendingReviews],
        ],
      ],
      [
        'title' => 'Content',
        'items' => [
          ['label' => 'Blog Posts',      'icon' => 'fas fa-newspaper', 'url' => route('admin.blog-posts.index'),      'match' => ['admin.blog-posts.*'],      'count' => $draftPosts],
          ['label' => 'Blog Categories', 'icon' => 'fas fa-folder',    'url' => route('admin.blog-categories.index'), 'match' => ['admin.blog-categories.*']],
          ['label' => 'User Agreement', 'icon' => 'fas fa-file-contract', 'url' => route('admin.policies.index'), 'match' => ['admin.policies.*']],
          ['label' => 'About Page', 'icon' => 'fas fa-circle-info', 'url' => route('admin.policies.edit', 'about-cetsy'), 'match' => ['admin.policies.edit']],
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
          ['label' => 'Buyers',         'icon' => 'fas fa-user-friends','url' => route('admin.buyers.index'),  'match' => ['admin.buyers.*']],
          ['label' => 'KYC Management', 'icon' => 'fas fa-id-card',   'url' => route('admin.kyc.index'),       'match' => ['admin.kyc.*'],              'count' => $pendingKyc],
        ],
      ],
      [
        'title' => 'Payments',
        'items' => [
          ['label' => 'Wallets',               'icon' => 'fas fa-wallet',         'url' => route('admin.wallets.index'),        'match' => ['admin.wallets.*']],
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

