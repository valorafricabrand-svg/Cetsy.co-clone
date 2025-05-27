@if(Auth::user()->isBuyer())
  @php
    $navItems = [
      [
        'label' => 'Dashboard',
        'url'   => route('buyer.dashboard'),
        'icon'  => 'fas fa-tachometer-alt',
      ],
      [
        'label' => 'Browse Products',
        'url'   => route('listings'),
        'icon'  => 'fas fa-th-list',
      ],
      [
        'label' => 'Cart',
        'url'   => route('cart.index'),
        'icon'  => 'fas fa-shopping-cart',
      ],
      [
        'label' => 'My Orders',
        'url'   => route('orders.index'),
        'icon'  => 'fas fa-box',
      ],
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
