@if(Auth::user()->isAdmin())
  @php
    $navItems = [
      [
        'label' => 'Dashboard',
        'url'   => route('admin.dashboard'),
        'icon'  => 'fas fa-tachometer-alt',
      ],
      [
        'label' => 'Users',
        'url'   => route('admin.users.index'),
        'icon'  => 'fas fa-users',
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
        'label' => 'Settings',
        'url'   => route('admin.settings'),
        'icon'  => 'fas fa-cogs',
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
