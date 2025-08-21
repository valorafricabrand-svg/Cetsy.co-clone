@if(Auth::user()->isAdmin())
  @php
    $navItems = [
      [
        'label' => 'Dashboard',
        'url'   => route('admin.dashboard'),
        'icon'  => 'fas fa-tachometer-alt',
      ],
      [
        'label' => 'Sellers',
        'url'   => route('admin.users.index'),
        'icon'  => 'fas fa-users',
      ],
      [
        'label' => 'Product Listings',
        'url'   => route('admin.products.index'),
        'icon'  => 'fas fa-box',
      ],
      [
        'label' => 'Reports',
        'url'   => route('admin.reports'),
        'icon'  => 'fas fa-chart-bar',
      ],
      [
        'label' => 'KYC Management',
        'url'   => route('admin.kyc.index'),
        'icon'  => 'fas fa-id-card',
      ],
            [
        'label' => 'Categories',
        'url'   => route('admin.categories.index'),
        'icon'  => 'fas fa-cogs',
      ],
      [
        'label' => 'Settings',
        'url'   => route('admin.settings'),
        'icon'  => 'fas fa-cogs',
      ],
      [
        'label' => 'Payout Requests',
        'url'   => route('admin.payouts.index'),
        'icon'  => 'fas fa-money-check-alt',
      ],
      [
        'label' => 'Payment Types',
        'url'   => route('admin.payment-types.index'),
        'icon'  => 'fas fa-credit-card',
      ],
      [
        'label' => 'Payments',
        'url'   => route('admin.payments.index'),
        'icon'  => 'fas fa-shopping-cart',
      ],
      [
        'label' => 'Product Reports',
        'url'   => route('admin.product-reports.index'),
        'icon'  => 'fas fa-flag',
      ],
      [
        'label' => 'Reviews',
        'url'   => route('admin.reviews.index'),
        'icon'  => 'fas fa-star',
      ],
      [
        'label' => 'Notifications',
        'url'   => route('admin.notifications.index'),
        'icon' => 'fas fa-bell',      ],
    ];
  @endphp

  @foreach($navItems as $item)
    <div class="nav-item-wrapper">
      <a href="{{ $item['url'] }}"
         class="nav-link d-flex align-items-center text-decoration-none text-dark py-2">
        <span class="nav-link-icon me-2">
          <i class="{{ $item['icon'] }}" style="color: #027333;"></i>
        </span>
        <span class="nav-link-text fw-bold" style="font-size: 0.9rem;">
          {{ $item['label'] }}
        </span>
      </a>
    </div>
  @endforeach
@endif
