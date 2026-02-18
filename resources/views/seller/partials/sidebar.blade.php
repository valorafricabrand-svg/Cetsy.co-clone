{{-- resources/views/seller/partials/sidebar.blade.php --}}
@php
    $user = auth()->user();
    $shop = $user?->shop;

    $walletBalance = wallet();
    $walletHold = wallet('on_hold');
    $currency = get_currency();

    $brandColor = optional($shop)->primary_color;
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        $brandColor = '#0f766e';
    }

    $productIds = $shop?->products()->pluck('id')->all() ?? [];
    $myFavoritesCount = \App\Models\Wishlist::where('user_id', auth()->id())->count();
    $shopFavoritesCount = empty($productIds) ? 0 : \App\Models\Wishlist::whereIn('product_id', $productIds)->count();
    $unreadMessages = empty($productIds)
        ? 0
        : \App\Models\Message::whereIn('product_id', $productIds)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->count();
    $pendingOffers = empty($productIds)
        ? 0
        : \App\Models\Offer::whereIn('product_id', $productIds)->where('status', 'pending')->count();
    $disputesCount = \App\Models\Dispute::where('seller_id', auth()->id())
        ->where('status', \App\Models\Dispute::STATUS_PENDING)
        ->count();
    $unreadNotifications = \App\Models\Activity::where('user_id', auth()->id())->where('is_read', false)->count();

    $groups = [
        'Overview' => [
            ['route' => 'seller.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ],
        'Listings' => [
            ['route' => 'products.index', 'icon' => 'fas fa-box-open', 'label' => 'My Listings'],
            ['route' => 'seller.deals.index', 'icon' => 'fas fa-percent', 'label' => 'Deals'],
        ],
        'Sales' => [
            ['route' => 'seller.orders.index', 'icon' => 'fas fa-shopping-cart', 'label' => 'Shop Orders'],
            ['route' => 'seller.reviews.index', 'icon' => 'fas fa-star', 'label' => 'Reviews'],
            ['route' => 'account.orders', 'icon' => 'fas fa-bag-shopping', 'label' => 'My Orders'],
            ['route' => 'seller.orders.payments', 'icon' => 'fas fa-credit-card', 'label' => 'Payments'],
        ],
        'Engagement' => [
            ['route' => 'seller.messages.index', 'icon' => 'fas fa-comments', 'label' => 'Messages', 'badge' => $unreadMessages],
            ['route' => 'seller.offers.index', 'icon' => 'fas fa-hand-holding-usd', 'label' => 'Offers', 'badge' => $pendingOffers],
            ['route' => 'buyer.favorites', 'icon' => 'fas fa-heart', 'label' => 'Favorites', 'badge' => $myFavoritesCount],
            ['route' => 'seller.favorites.index', 'icon' => 'fas fa-store', 'label' => 'Shop Favorites', 'badge' => $shopFavoritesCount],
            ['route' => 'seller.notifications.index', 'icon' => 'fas fa-bell', 'label' => 'Notifications', 'badge' => $unreadNotifications],
        ],
        'Disputes' => [
            ['route' => 'disputes.index', 'icon' => 'fas fa-exclamation-triangle', 'label' => 'Disputes', 'badge' => $disputesCount],
        ],
        'Shop & Settings' => [
            ['route' => 'seller.shop.create', 'icon' => 'fas fa-store', 'label' => 'My Shop'],
            ['route' => 'seller.analytics.index', 'icon' => 'fas fa-chart-line', 'label' => 'Analytics'],
            ['route' => 'seller.reports.inventory', 'icon' => 'fas fa-boxes-stacked', 'label' => 'Inventory Report'],
            ['route' => 'seller.subscription', 'icon' => 'fas fa-file-invoice', 'label' => 'Subscription'],
            ['route' => 'seller.kyc', 'icon' => 'fas fa-id-card', 'label' => 'KYC'],
        ],
    ];
@endphp

<aside class="space-y-4 lg:sticky lg:top-24">
    @if(\Illuminate\Support\Facades\Route::has('products.index'))
        <form action="{{ route('products.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <label for="seller-product-search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Search Listings</label>
            <div class="flex items-center gap-2 rounded-xl border border-slate-300 px-2 py-1.5">
                <i class="fas fa-search text-slate-400"></i>
                <input id="seller-product-search" type="search" name="q" placeholder="Search products..." class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                <button type="submit" class="rounded-lg px-2 py-1 text-xs font-semibold text-white" style="background-color: {{ $brandColor }}">Go</button>
            </div>
        </form>
    @endif

    <a href="{{ \Illuminate\Support\Facades\Route::has('wallet.index') ? route('wallet.index') : '#' }}"
       class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background-color: {{ $brandColor }}">
                <i class="fas fa-wallet"></i>
            </span>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Wallet Balance</p>
                <p class="truncate text-base font-bold text-slate-900">{{ $currency }} {{ number_format((float)$walletBalance, 2) }}</p>
                @if((float)$walletHold > 0)
                    <p class="text-xs text-slate-500">On hold: {{ $currency }} {{ number_format((float)$walletHold, 2) }}</p>
                @endif
            </div>
        </div>
    </a>

    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        @foreach($groups as $groupTitle => $items)
            <div class="{{ !$loop->first ? 'mt-4' : '' }}">
                <p class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $groupTitle }}</p>
                <div class="space-y-1">
                    @foreach($items as $item)
                        @continue(!\Illuminate\Support\Facades\Route::has($item['route']))
                        @php
                            $isActive = request()->routeIs($item['route']);
                            $badge = (int)($item['badge'] ?? 0);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $isActive ? 'text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}"
                           @if($isActive) style="background-color: {{ $brandColor }}" @endif>
                            <i class="{{ $item['icon'] }} w-4 text-center $isActive ? 'text-white' : 'text-slate-500'"></i>
                            <span class="flex-1 truncate">{{ $item['label'] }}</span>
                            @if($badge > 0)
                                <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white">
                                    {{ $badge > 99 ? '99+' : $badge }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</aside>

