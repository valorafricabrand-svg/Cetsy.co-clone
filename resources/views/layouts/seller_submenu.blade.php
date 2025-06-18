@if(Auth::user()->isSeller())
  @php
    $currentRoute = Route::currentRouteName();
    $navItems = [
        [
            'label' => 'Dashboard',
            'url'   => route('dashboard'),
            'icon'  => 'fas fa-tachometer-alt',
        ],
        [
            'label' => 'Listings',
            'url'   => route('products.index'),
            'icon'  => 'fas fa-box-open',
        ],
        [
            'label' => 'Orders',
            'url'   => route('orders.index'),
            'icon'  => 'fas fa-shopping-cart',
        ],
        // ... add other items as needed
    ];


        $navItems[] = [
      'label' => 'Payments',
      'url'   => route('seller.orders.payments'),
      'icon'  => 'fas fa-shopping-cart',
    ];


    $navItems[] = [
      'label' => 'My Shop',
      'url'   => route('seller.shop.create'),
      'icon'  => 'fas fa-chart-line me-2 text-success',
    ];


      $navItems[] = [
      'label' => 'Shipping Profiles',
      'url'   => route('shipping_profiles.index'),
      'icon'  => 'fas fa-chart-line me-2 text-success',
    ];

            $navItems[] = [
      'label' => 'Analytics',
      'url'   => route('seller.analytics.index'),
      'icon'  => 'fas fa-chart-line me-2 text-success',
    ];


    
  @endphp

  <style>
  .submenu {
      display: none;
      flex-direction: column;
      margin-left: 2rem;
      margin-top: 0.25rem;
      border-left: 2px solid #e0e0e0;
      padding-left: 0.75rem;
  }
  .submenu.show {
      display: flex;
  }
  .submenu-link {
      padding: 0.5rem 0;
      color: #027333;
      text-decoration: none;
      font-size: 0.95rem;
      border-radius: 4px;
      transition: background 0.2s;
  }
  .submenu-link.active, .submenu-link:hover {
      background: #e6f4ea;
      color: #014d25;
      font-weight: bold;
  }
  .nav-link.active {
      background: #e6f4ea;
      color: #014d25;
      font-weight: bold;
  }
  </style>

  <div class="sidebar-menu">
    @foreach($navItems as $item)
        <div class="nav-item-wrapper">
            @if(isset($item['items']))
                @php
                    $isActive = false;
                    foreach($item['items'] as $subItem) {
                        if (isset($subItem['url']) && url()->current() === $subItem['url']) {
                            $isActive = true;
                            break;
                        }
                    }
                @endphp
                <a href="#"
                   class="nav-link d-flex align-items-center text-decoration-none text-dark py-2 {{ $isActive ? 'active' : '' }}"
                   onclick="event.preventDefault(); document.getElementById('submenu-{{ Str::slug($item['label']) }}').classList.toggle('show');">
                    <span class="nav-link-icon me-2">
                        <i class="{{ $item['icon'] }}" style="color: #027333;"></i>
                    </span>
                    <span class="nav-link-text fw-bold" style="font-size: 0.9rem;">
                        {{ $item['label'] }}
                    </span>
                    <span class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <div id="submenu-{{ Str::slug($item['label']) }}" class="submenu {{ $isActive ? 'show' : '' }}">
                    @foreach($item['items'] as $subItem)
                        <a href="{{ $subItem['url'] }}"
                           class="submenu-link d-flex align-items-center {{ url()->current() === $subItem['url'] ? 'active' : '' }}">
                            <i class="{{ $subItem['icon'] }} me-2"></i>
                            {{ $subItem['label'] }}
                        </a>
                    @endforeach
                </div>
            @else
                <a href="{{ $item['url'] }}"
                   class="nav-link d-flex align-items-center text-decoration-none text-dark py-2 {{ url()->current() === $item['url'] ? 'active' : '' }}">
                    <span class="nav-link-icon me-2">
                        <i class="{{ $item['icon'] }}" style="color: #027333;"></i>
                    </span>
                    <span class="nav-link-text fw-bold" style="font-size: 0.9rem;">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endif
        </div>
    @endforeach
  </div>
@endif
