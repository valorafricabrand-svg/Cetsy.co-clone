@php
    // Ensure Activity model is correctly imported in the controller or view
    use App\Models\Activity;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $role = $user->isAdmin() ? 'admin' : ($user->isSeller() ? 'seller' : 'buyer');

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
                    <img src="{{ setting('logo_url') }}" style="height: 50px;">
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
                        (object)['code' => 'EUR','symbol' => '€'],
                        (object)['code' => 'GBP','symbol' => '£'],
                        (object)['code' => 'KES','symbol' => 'KES'],
                    ]);
                }
            @endphp
            <li class="nav-item me-2 dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Select currency">
                    <i class="fas fa-coins"></i>
                    <span class="ms-1">{{ $currentCurrency }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 220px;">
                    @php $currencyGet = \Illuminate\Support\Facades\Route::has('currency.set.get') ? route('currency.set.get') : url('/set-currency'); @endphp
                    <ul class="list-unstyled mb-0">
                        @php $siteDefault = setting('default_currency', 'USD') ?: 'USD'; @endphp
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between {{ strtoupper($currentCurrency) === strtoupper($siteDefault) ? 'active' : '' }}" href="#" data-currency-reset="1">
                                <span>Use Site Default ({{ strtoupper($siteDefault) }})</span>
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
                                    <h6 class="mb-0">Recent Notifications</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 360px; overflow-y: auto;">
                                    {{-- Recent Activity notifications --}}
                                    @if($recentNotifications->count() > 0)
                                        @foreach($recentNotifications as $notification)
                                            <div class="dropdown-item p-2 border-bottom border-translucent">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-1">
                                                        <p class="mb-1 text-body-secondary small">{{ $notification->description }}</p>
                                                        <small class="text-body-quaternary">{{ $notification->created_at->diffForHumans() }}</small>
                                                        @php
                                                            $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, $user);
                                                            $linkText = \App\Services\NotificationRouteService::getLinkText($notification, $user);
                                                        @endphp
                                                        @if($route && $route !== route('notifications.index'))
                                                            <div class="mt-2">
                                                                <a href="{{ $route }}" class="btn btn-sm btn-outline-primary">
                                                                    {{ $linkText }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if(!$notification->is_read)
                                                        <div class="ms-2">
                                                            <span class="badge bg-primary rounded-pill">New</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="dropdown-item p-3 text-center">
                                            <p class="mb-0 text-body-quaternary">No notifications</p>
                                        </div>
                                    @endif

                                    {{-- Recent Dispute messages --}}
                                    @if($recentDisputeMessages->count() > 0)
                                        <div class="dropdown-item p-2 bg-light fw-semibold small text-muted">Recent Dispute Messages</div>
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
                                                        <small class="text-body-quaternary">{{ $msg->created_at->diffForHumans() }} • Dispute #{{ $msg->dispute_id }}</small>
                                                        <div class="mt-2">
                                                            <a href="{{ route('disputes.show', $msg->dispute_id) }}" class="btn btn-sm btn-outline-warning">
                                                                View Dispute
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
                                    <a href="{{ $allRoute }}" class="btn btn-sm btn-phoenix-secondary w-100">View All Notifications</a>
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
                    <span class="fs-8">{{ Auth::user()->name }}</span>
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
                                <h6 class="mt-2 text-body-emphasis">{{ Auth::user()->name }} - {{ Auth::user()->id }} </h6>
                            </div>
                        </div>
                        <div class="card-footer p-0 border-top border-translucent">
                            <ul class="nav d-flex flex-column my-3">
                                @if(Auth::user()->isAdmin())
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fa fa-tachometer-alt"></i> <span>Admin Dashboard</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="fa fa-users"></i> <span>Manage Users</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.kyc.index') }}">
                                        <i class="fa fa-id-card"></i> <span>KYC Management</span>
                                    </a>
                                @elseif(Auth::user()->isSeller())
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa fa-user"></i> <span>Profile</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ url('billings') }}">
                                        <i class="fa fa-users"></i> <span>Manage your billings</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ url('subscribe') }}">
                                        <i class="fa fa-users"></i> <span>Manage your subscriptions</span>
                                    </a>
                                @else
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa fa-user"></i> <span>Profile</span>
                                    </a>
                                @endif
                            </ul>
                            <hr />
                            <div class="px-3">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-phoenix-secondary d-flex flex-center w-100"><span class="me-2" data-feather="log-out"></span> Log Out</button>
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
