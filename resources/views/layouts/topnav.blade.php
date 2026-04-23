@php
    // Ensure Activity model is correctly imported in the controller or view
    use App\Models\Activity;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $role = $user->isAdmin() ? 'admin' : ($user->isSeller() ? 'seller' : 'buyer');
    $shopName = $user->isSeller() ? optional($user->shop)->name : null;
    $displayName = $shopName ?: $user->name;

    // Notifications
    if ($user->isAdmin()) {
        // Include legacy global entries for admins
        $notificationsCount = Activity::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->count();
        $recentNotifications = Activity::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();
    } else {
        // Per-user for non-admins
        $notificationsCount = Activity::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        $recentNotifications = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();
    }

    // Recent Dispute Messages (public)
    try {
        $recentDisputeMessages = \App\Models\DisputeMessage::query()
            ->public()
            ->with(['dispute', 'user'])
            ->when(!$user->isAdmin(), function($q) use ($user) {
                $q->whereHas('dispute', function($qq) use ($user) {
                    $qq->where('buyer_id', $user->id)
                       ->orWhere('seller_id', $user->id);
                })
                // Only show messages not authored by the current user (incl. system/admin)
                ->where(function($q2) use ($user) {
                    $q2->whereNull('user_id')->orWhere('user_id', '!=', $user->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();
    } catch (\Throwable $e) {
        $recentDisputeMessages = collect();
    }

    $navItems = [
        'admin' => [
            [
                'label' => 'Notifications',
                'icon' => 'fas fa-bell',
                'route' => '#',
                'badge' => $notificationsCount,
                'is_dropdown' => true,
            ],
        ],
        'seller' => [
            [
                'label' => 'Notifications',
                'icon' => 'fas fa-bell',
                'route' => '#',
                'badge' => $notificationsCount,
                'is_dropdown' => true,
            ],
        ],
        'buyer' => [
            [
                'label' => 'Notifications',
                'icon' => 'fas fa-bell',
                'route' => '#',
                'badge' => $notificationsCount,
                'is_dropdown' => true,
            ],
        ],
    ];
@endphp

<!-- Navbar Code -->
<nav class="navbar navbar-top fixed-top navbar-expand" id="navbarDefault" style="display:none;">
    <div class="collapse navbar-collapse justify-content-between">
        <div class="navbar-logo">
            <button class="btn navbar-toggler navbar-toggler-humburger-icon hover-bg-transparent" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse"
                aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation">
                <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
            </button>
            <a class="navbar-brand me-1 me-sm-3" href="{{ url('/') }}">
                <div class="d-flex align-items-center">
                    @php
                      $__logo = logo_url();
                    @endphp
                    <img src="{{ $__logo }}" style="height: 50px;" onerror="this.onerror=null;this.src=@json(asset('assets/images/cetsylogmain.png'));">
                </div>
            </a>
        </div>

        <ul class="navbar-nav navbar-nav-icons flex-row">
            {{-- Currency selector --}}
            @php
                try {
                    $currentCurrency = get_currency();
                    $navCurrencies = \App\Models\Currency::where('is_active', true)->orderBy('code')->get(['code','symbol']);
                } catch (\Throwable $e) {
                    $currentCurrency = get_currency();
                    $navCurrencies = collect([
                        (object)['code' => 'USD','symbol' => '$'],
                        (object)['code' => 'EUR','symbol' => 'â‚¬'],
                        (object)['code' => 'GBP','symbol' => 'Â£'],
                        (object)['code' => 'KES','symbol' => 'KES'],
                    ]);
                }
            @endphp
            <li class="nav-item me-2 dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Select currency') }}">
                    <i class="fas fa-coins"></i>
                    <span class="ms-1">{{ $currentCurrency }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 220px;">
                    @php $currencyGet = \Illuminate\Support\Facades\Route::has('currency.set.get') ? route('currency.set.get') : url('/set-currency'); @endphp
                    <ul class="list-unstyled mb-0">
                        @php $siteDefault = setting('default_currency', 'USD') ?: 'USD'; @endphp
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between {{ strtoupper($currentCurrency) === strtoupper($siteDefault) ? 'active' : '' }}" href="#" data-currency-reset="1">
                                <span>{{ __('Use Site Default (:currency)', ['currency' => strtoupper($siteDefault)]) }}</span>
                                @if(strtoupper($currentCurrency) === strtoupper($siteDefault))
                                    <i class="fas fa-check text-success"></i>
                                @endif
                            </a>
                        </li>
                        @foreach($navCurrencies as $c)
                            @php $code = strtoupper($c->code); $is = $code === strtoupper($currentCurrency); @endphp
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between {{ $is ? 'active' : '' }}" href="#" data-currency-code="{{ $code }}">
                                    <span>{{ $c->symbol ? $c->symbol.' ' : '' }}{{ $code }}</span>
                                    @if($is)
                                        <i class="fas fa-check text-success"></i>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            @php
                $currentLocale = current_locale();
                $localeOptions = supported_locales();
                $localeRedirect = url()->full();
            @endphp
            <li class="nav-item me-2 dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Change language') }}">
                    <i class="fas fa-globe"></i>
                    <span class="ms-1">{{ locale_label($currentLocale) }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 220px;">
                    <div class="px-2 pb-2">
                        <div class="small fw-semibold text-body-emphasis">{{ __('Language') }}</div>
                        <div class="text-body-secondary small">{{ __('Choose your preferred interface language.') }}</div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        @foreach($localeOptions as $localeCode => $localeMeta)
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between {{ $currentLocale === $localeCode ? 'active' : '' }}" href="{{ route('locale.set', ['locale' => $localeCode, 'redirect' => $localeRedirect]) }}">
                                    <span>{{ $localeMeta['native'] ?? strtoupper($localeCode) }}</span>
                                    @if($currentLocale === $localeCode)
                                        <i class="fas fa-check text-success"></i>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            @foreach($navItems[$role] as $item)
                @if(isset($item['is_dropdown']) && $item['is_dropdown'])
                    <li class="nav-item me-2 dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ $item['label'] }}">
                            <i class="{{ $item['icon'] }} fa-lg"></i>
                            @if($item['badge'] > 0)
                                <span id="topNotifCount" class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="display: {{ $notificationsCount>0 ? 'inline-block' : 'none' }};">
                                    {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                </span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 shadow border" style="min-width: 300px;">
                            <div class="card position-relative border-0">
                                <div class="card-header bg-transparent border-bottom border-translucent">
                                    <h6 class="mb-0">{{ __('Recent Notifications') }}</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 360px; overflow-y: auto;">
                                    {{-- Recent Activity notifications --}}
                                    @if($recentNotifications->count() > 0)
                                        @foreach($recentNotifications as $notification)
                                            <div class="dropdown-item p-2 border-bottom border-translucent">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-1">
                                                        @php
                                                            $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, $user);
                                                            $linkText = \App\Services\NotificationRouteService::getLinkText($notification, $user);
                                                            $hasOpen = \Illuminate\Support\Facades\Route::has('notifications.open');
                                                            $href = $hasOpen ? route('notifications.open', $notification->id) : $route;
                                                        @endphp
                                                        @if($route && $route !== route('notifications.index'))
                                                            <a href="{{ $href }}" class="mb-1 small d-block text-decoration-none {{ $notification->is_read ? 'text-body-secondary' : 'fw-semibold' }}">
                                                                {{ $notification->description }}
                                                            </a>
                                                        @else
                                                            <p class="mb-1 text-body-secondary small">{{ $notification->description }}</p>
                                                        @endif
                                                        <small class="text-body-quaternary">{{ $notification->created_at->diffForHumans() }}</small>
                                                        @if($route && $route !== route('notifications.index'))
                                                            <div class="mt-2">
                                                                    <a href="{{ $href }}" class="btn btn-sm btn-outline-primary" data-notif-id="{{ $notification->id }}" data-unread="{{ $notification->is_read ? 0 : 1 }}">
                                                                    {{ $linkText ?: __('Open') }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if(!$notification->is_read)
                                                        <div class="ms-2">
                                                            <span class="badge bg-primary rounded-pill">{{ __('New') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="dropdown-item p-3 text-center">
                                            <p class="mb-0 text-body-quaternary">{{ __('No notifications') }}</p>
                                        </div>
                                    @endif

                                    {{-- Recent Dispute messages --}}
                                    @if($recentDisputeMessages->count() > 0)
                                        <div class="dropdown-item p-2 bg-light fw-semibold small text-muted">{{ __('Recent Dispute Messages') }}</div>
                                        @foreach($recentDisputeMessages as $msg)
                                            <div class="dropdown-item p-2 border-bottom border-translucent">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-1">
                                                        <p class="mb-1 text-body-secondary small">
                                                            @php
                                                                $from = $msg->user?->name ?? 'System';
                                                                $snippet = Str::limit(strip_tags($msg->message), 120);
                                                            @endphp
                                                            <strong>{{ $from }}:</strong> {{ $snippet }}
                                                        </p>
                                                        <small class="text-body-quaternary">{{ $msg->created_at->diffForHumans() }} â¢ Dispute #{{ $msg->dispute_id }}</small>
                                                        <div class="mt-2">
                                                            <a href="{{ route('disputes.show', $msg->dispute_id) }}" class="btn btn-sm btn-outline-warning">
                                                                {{ __('View Dispute') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="card-footer p-2 border-top border-translucent">
                                    @php
                                        $allRoute = $role === 'admin' ? route('admin.notifications.index') : route('notifications.index');
                                    @endphp
                                    <a href="{{ $allRoute }}" class="btn btn-sm btn-phoenix-secondary w-100">{{ __('View All Notifications') }}</a>
                                </div>
                            </div>
                        </div>
                    </li>
                @else
                    <li class="nav-item me-2">
                        <a href="{{ $item['route'] }}" class="nav-link position-relative" title="{{ $item['label'] }}">
                            <i class="{{ $item['icon'] }} fa-lg"></i>
                            @if($item['badge'] > 0)
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                    {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endif
            @endforeach

            <li class="nav-item dropdown">
                <a class="nav-link lh-1 pe-0" id="navbarDropdownUser" href="#" role="button" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <span class="fs-8">{{ $displayName }}</span>
                    <i class="fas fa-angle-down"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border"
                    aria-labelledby="navbarDropdownUser">
                    <div class="card position-relative border-0">
                        <div class="card-body p-0">
                            <div class="text-center pt-4 pb-3">
                                <div class="avatar avatar-xl">
                                    <img class="rounded-circle" src="{{ Auth::user()->get_gravatar(150) }}" alt="" />
                                </div>
                                <h6 class="mt-2 text-body-emphasis mb-0">{{ $displayName }} - {{ Auth::user()->id }}</h6>
                                @if($shopName)
                                    <p class="text-muted small mb-0">{{ Auth::user()->name }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer p-0 border-top border-translucent">
                            <ul class="nav d-flex flex-column my-3">
                                @if(Auth::user()->isAdmin())
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fa fa-tachometer-alt"></i> <span>{{ __('Admin Dashboard') }}</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="fa fa-users"></i> <span>{{ __('Manage Users') }}</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.kyc.index') }}">
                                        <i class="fa fa-id-card"></i> <span>{{ __('KYC Management') }}</span>
                                    </a>
                                @elseif(Auth::user()->isSeller())
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa fa-user"></i> <span>{{ __('Profile') }}</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('seller.billing.index') }}">
                                        <i class="fa fa-users"></i> <span>{{ __('Manage your billings') }}</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('seller.subscription') }}">
                                        <i class="fa fa-users"></i> <span>{{ __('Manage your subscriptions') }}</span>
                                    </a>
                                @else
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa fa-user"></i> <span>{{ __('Profile') }}</span>
                                    </a>
                                @endif
                            </ul>
                            <hr />
                            <div class="px-3">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-phoenix-secondary d-flex flex-center w-100"><span class="me-2" data-feather="log-out"></span> {{ __('Log Out') }}</button>
                                </form>
                            </div>
                            <div class="my-2 text-center fw-bold fs-10 text-body-quaternary">
                                <a class="text-body-quaternary me-1" href="{{ url('intro') }}">Intro</a>&bull;
                                <a class="text-body-quaternary me-1" href="{{ url('privacy-policy') }}">Privacy policy</a>&bull;
                                <a class="text-body-quaternary mx-1" href="{{ url('terms-of-service') }}">Terms</a>&bull;
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>
