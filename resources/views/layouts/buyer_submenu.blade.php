@php
    $active = fn($routes) => collect((array) $routes)
        ->contains(fn($r) => request()->routeIs($r));
    $walletBalance = wallet();
    $walletBalanceFormatted = number_format($walletBalance, 2);
    $hasShop = \App\Models\Shop::where('user_id', Auth::id())->exists();
    // Unread messages count for buyer
    $unreadMessages = \App\Models\Message::where('receiver_id', Auth::id())
        ->where('is_read', false)
        ->count();
@endphp

@if(Auth::user()->isBuyer())
    <style>
        .badge-unread {
            background: #dc3545 !important;
            color: #fff !important;
            font-size: 0.8rem;
            padding: 0.35em 0.7em;
            border-radius: 1rem;
            margin-left: 0.5rem;
            vertical-align: middle;
        }
    </style>
    <nav class="d-flex flex-column h-100 bg-white border-end shadow-sm p-3" style="min-height: 100vh; width: 250px;" aria-label="Buyer Sidebar">
        <!-- Dashboard -->
        <div class="mb-2">
            <a
                href="{{ route('buyer.dashboard') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('buyer.dashboard') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
                aria-current="page"
            >
                <i class="fas fa-tachometer-alt me-2 text-success"></i>
                Dashboard
            </a>
        </div>
        <div class="mb-2">
            <a
                href="{{ route('wallet.index') }}" 
                class="nav-link d-flex align-items-center mt-2 bg-light text-success fw-bold rounded px-2 py-2"
                role="status"
            >
                <i class="fas fa-dollar-sign me-2 text-success"></i>
                Balance: USD {{ $walletBalanceFormatted }}
            </a>
        </div>
        <!-- My Orders -->
        <div class="mb-2">
            <a
                href="{{ route('account.orders') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('account.orders') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
            >
                <i class="fas fa-box me-2 text-success"></i>
                My Orders
            </a>
        </div>



        
        <!-- Payments -->
        <div class="mb-2">
            <a
                href="{{ route('account.payments') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('account.payments') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
            >
                <i class="fas fa-credit-card me-2 text-success"></i>
                Payments
            </a>
        </div>
        <div class="mb-2">
            <a
                href="{{ route('buyer.messages.index') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('messages.index') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
            >
                <i class="fas fa-comments me-2 text-success"></i>
                Messages
                @if($unreadMessages)
                    <span class="badge badge-unread">{{ $unreadMessages }}</span>
                @endif
            </a>
        </div>

        <div class="mb-2">
            <a
                href="{{ route('buyer.favorites') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('buyer.favorites') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
            >
                <i class="fas fa-heart me-2 text-success"></i>
                Favorites
            </a>
        </div>
        <div class="mb-2">
            <a
                href="{{ route('buyer.offers') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('buyer.offers') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
            >
                <i class="fas fa-heart me-2 text-success"></i>
                Offers
            </a>
        </div>
        <!-- Profiles -->
        <div class="mb-2">
            <a
                href="{{ route('profile.edit') }}"
                class="nav-link d-flex align-items-center mt-2 {{ $active('profile.edit') ? 'bg-light text-success fw-bold rounded px-2 py-2' : 'text-dark py-2' }}"
            >
                <i class="fas fa-user me-2 text-success"></i>
                Profiles
            </a>
        </div>
    </nav>
@endif
