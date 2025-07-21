@php
    $user = auth()->user();
    $role = $user->isSeller() ? 'seller' : 'buyer';

    // Badge counts logic
    // Messages
    $messagesCount = 0;
    if ($role === 'seller') {
        $shop = $user->shop;
        $productIds = $shop ? $shop->products()->pluck('id') : [];
        $messagesCount = $shop ? \App\Models\Message::whereIn('product_id', $productIds)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count() : 0;
    } else {
        $messagesCount = \App\Models\Message::where('sender_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
    // Offers
    $offersCount = 0;
    if ($role === 'seller') {
        $shop = $user->shop;
        $productIds = $shop ? $shop->products()->pluck('id') : [];
        $offersCount = $shop ? \App\Models\Offer::whereIn('product_id', $productIds)
            ->where('status', 'pending')
            ->count() : 0;
    } else {
        $offersCount = \App\Models\Offer::where('buyer_id', $user->id)
            ->where('status', 'pending')
            ->count();
    }
    // Wishlist
    $wishlistCount = method_exists($user, 'wishlistItems') ? $user->wishlistItems()->count() : 0;
    
    // Orders
    $ordersCount = 0;
    if ($role === 'seller') {
        $shop = $user->shop;
        $productIds = $shop ? $shop->products()->pluck('id') : [];
        $ordersCount = $shop ? \App\Models\Order::whereIn('id', function($query) use ($productIds) {
            $query->select('order_id')
                  ->from('order_items')
                  ->whereIn('product_id', $productIds);
        })->where('status', 'pending')->count() : 0;
    } else {
        $ordersCount = \App\Models\Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
    }
    
    // Cart
    $cartCount = 0;
    if ($role === 'seller') {
        $shop = $user->shop;
        $productIds = $shop ? $shop->products()->pluck('id') : [];
        $cartCount = $shop ? \App\Models\CartItem::whereIn('product_id', $productIds)->count() : 0;
    } else {
        $cartCount = $user->cart ? $user->cart->items()->count() : 0;
    }

    $navItems = [
        'seller' => [
            [
                'label' => 'Messages',
                'icon' => 'fas fa-bell',
                'route' => route('seller.messages.index'),
                'badge' => $messagesCount,
            ],
            [
                'label' => 'Orders',
                'icon' => 'fas fa-shopping-bag',
                'route' => route('orders.index'),
                'badge' => $ordersCount,
            ],
            [
                'label' => 'Cart',
                'icon' => 'fas fa-shopping-cart',
                'route' => route('cart.view'),
                'badge' => $cartCount,
            ],
            [
                'label' => 'Offers',
                'icon' => 'fas fa-handshake',
                'route' => route('seller.offers.index'),
                'badge' => $offersCount,
            ],
            [
                'label' => 'Wishlist',
                'icon' => 'fas fa-heart',
                'route' => route('seller.favorites.index'),
                'badge' => $wishlistCount,
            ],
        ],
        'buyer' => [
            [
                'label' => 'Messages',
                'icon' => 'fas fa-bell',
                'route' => route('buyer.messages.index'),
                'badge' => $messagesCount,
            ],
            [
                'label' => 'Orders',
                'icon' => 'fas fa-shopping-bag',
                'route' => route('account.orders'),
                'badge' => $ordersCount,
            ],
            [
                'label' => 'Cart',
                'icon' => 'fas fa-shopping-cart',
                'route' => route('cart.view'),
                'badge' => $cartCount,
            ],
            [
                'label' => 'Offers',
                'icon' => 'fas fa-handshake',
                'route' => route('buyer.offers'),
                'badge' => $offersCount,
            ],
            [
                'label' => 'Wishlist',
                'icon' => 'fas fa-heart',
                'route' => route('buyer.favorites'),
                'badge' => $wishlistCount,
            ],
        ],
    ];
@endphp

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
                    <!-- <img src="{{ favicon_url() }}" alt="b2b" width="27" /> -->
                    <img src="{{ setting('logo_url') }}" style="height: 50px;">
                </div>
            </a>
        </div>

        <ul class="navbar-nav navbar-nav-icons flex-row">
            @foreach($navItems[$role] as $item)
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
                                @if(Auth::user()->isSeller())
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa fa-user"></i> <span>Profile</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ url('billings') }}">
                                        <i class="fa fa-users"></i> <span>Manage your billings</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ url('subscribe') }}">
                                        <i class="fa fa-users"></i> <span>Manage your subscriptions</span>
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