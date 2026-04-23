@php
    use Illuminate\Support\Facades\Route as RouteFacade;
    $active = fn($routes) => collect((array) $routes)->contains(fn($r) => request()->routeIs($r));
    // Balances
    $walletBalance = wallet();
    $walletBalanceFormatted = number_format((float) $walletBalance, 2);
    // Counters
    $unreadMessages = \App\Models\Message::where('receiver_id', Auth::id())
        ->where('is_read', false)
        ->count();
    $pendingOrders  = \App\Models\Order::where('user_id', Auth::id())
        ->where('status','pending')
        ->count();
    $favoritesCount = \App\Models\Activity::where('user_id', Auth::id())
        ->where('type', \App\Models\Activity::TYPE_WISHLIST)
        ->where('is_read', false)
        ->whereNull('causer_id')
        ->count();
    $offersPending  = \App\Models\Activity::where('user_id', Auth::id())
        ->where('type', \App\Models\Activity::TYPE_OFFER)
        ->where('is_read', false)
        ->whereIn('related_id', \App\Models\Offer::query()->select('id')->where('buyer_id', Auth::id()))
        ->count();
    // Disputes count (where user is buyer or seller, excluding closed/finalized)
    $disputesCount  = \App\Models\Dispute::where(function($q){
                          $q->where('buyer_id', Auth::id())
                            ->orWhere('seller_id', Auth::id());
                        })
                        ->where('status', \App\Models\Dispute::STATUS_PENDING)
                        ->count();
@endphp

@if(Auth::user()->isBuyer())
    <style>
        .badge-pill { border-radius: 1rem; }
    </style>
    <nav class="d-flex flex-column h-100 bg-white border-end shadow-sm p-3" style="min-height: 100vh; width: 260px;" aria-label="Buyer Sidebar">
        <!-- Balance card -->
        <div class="card mb-3 border-0 shadow-sm">
            <a href="{{ route('wallet.index') }}" class="text-decoration-none text-dark">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-wallet fa-lg text-success me-2"></i>
                    <div>
                        <small class="text-muted">Balance</small>
                        <div class="fw-semibold">{{ get_currency() }} {{ $walletBalanceFormatted }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Group: Overview -->
        <div class="list-group mb-3">
            <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">Overview</div>
            <a href="{{ route('buyer.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('buyer.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt me-2" style="width:18px;"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Group: Orders & Payments -->
        <div class="list-group mb-3">
            <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">Orders &amp; Payments</div>
            <a href="{{ route('account.orders') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('account.orders') ? 'active' : '' }}">
                <i class="fas fa-box me-2" style="width:18px;"></i>
                <span>My Orders</span>
                @if($pendingOrders>0)
                    <span class="badge bg-danger ms-auto badge-pill">{{ $pendingOrders }}</span>
                @endif
            </a>
            @if(RouteFacade::has('account.payments'))
            <a href="{{ route('account.payments') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('account.payments') ? 'active' : '' }}">
                <i class="fas fa-credit-card me-2" style="width:18px;"></i>
                <span>Payments</span>
            </a>
            @endif
        </div>

        <!-- Group: Messaging -->
        <div class="list-group mb-3">
            <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">Messaging</div>
            <a href="{{ route('buyer.messages.index') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('buyer.messages.*') ? 'active' : '' }}">
                <i class="fas fa-comments me-2" style="width:18px;"></i>
                <span>Messages</span>
                @if($unreadMessages>0)
                    <span class="badge bg-danger ms-auto badge-pill">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="{{ route('buyer.notifications.index') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('buyer.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell me-2" style="width:18px;"></i>
                <span>Notifications</span>
                @php($buyerUnreadNotifs = \App\Models\Activity::where('user_id', Auth::id())->where('is_read', false)->count())
                @if($buyerUnreadNotifs>0)
                    <span class="badge bg-warning text-dark ms-auto badge-pill">{{ $buyerUnreadNotifs }}</span>
                @endif
            </a>
        </div>

        <!-- Group: Favorites & Offers -->
        <div class="list-group mb-3">
            <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">Saved &amp; Offers</div>
            <a href="{{ route('buyer.favorites') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('buyer.favorites') ? 'active' : '' }}">
                <i class="fas fa-heart me-2" style="width:18px;"></i>
                <span>Favorites</span>
                @if($favoritesCount>0)
                    <span class="badge bg-secondary ms-auto badge-pill">{{ $favoritesCount }}</span>
                @endif
            </a>
            <a href="{{ route('buyer.offers') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('buyer.offers') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd me-2" style="width:18px;"></i>
                <span>Offers</span>
                @if($offersPending>0)
                    <span class="badge bg-warning text-dark ms-auto badge-pill">{{ $offersPending }}</span>
                @endif
            </a>
        </div>

        <!-- Group: Support -->
        <div class="list-group mb-3">
            <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">Support</div>
            <a href="{{ route('disputes.index') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('disputes.*') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle me-2" style="width:18px;"></i>
                <span>Disputes</span>
                @if($disputesCount>0)
                    <span class="badge bg-warning text-dark ms-auto badge-pill">{{ $disputesCount }}</span>
                @endif
            </a>
        </div>

        <!-- Group: Account -->
        <div class="list-group mb-3">
            <div class="list-group-item bg-light fw-semibold text-uppercase small text-muted">Account</div>
            <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $active('profile.edit') ? 'active' : '' }}">
                <i class="fas fa-user me-2" style="width:18px;"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>
@endif
