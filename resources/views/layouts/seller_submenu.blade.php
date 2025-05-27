@if(Auth::user()->isSeller())
  @php
    $navItems = [
      [
        'label' => 'Seller Dashboard',
        'url'   => route('seller.dashboard'),
        'icon'  => 'fas fa-chart-line',
      ],
      [
        'label' => 'KYC Verification',
        'url'   => route('seller.kyc'),
        'icon'  => 'fas fa-id-card',
      ],
    ];

    // My Store (open or view)
    if (auth()->user()->shop) {
      $navItems[] = [
        'label' => 'My Shop',
        'url'   => route('shops.show', auth()->user()->shop),
        'icon'  => 'fas fa-store-alt',
      ];
    } else {
      $navItems[] = [
        'label' => 'Open Shop',
        'url'   => route('shops.create'),
        'icon'  => 'fas fa-store',
      ];
    }

    // Products & Orders
    $navItems[] = [
      'label' => 'Products',
      'url'   => route('products.index'),
      'icon'  => 'fas fa-box-open',
    ];
    $navItems[] = [
      'label' => 'Orders',
      'url'   => route('orders.index'),
      'icon'  => 'fas fa-shopping-cart',
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
