{{-- resources/views/seller/partials/sidebar.blade.php --}}
@section('cetsy_mobile_nav_context', 'seller')
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
    $myShopUrl = '#';
    if ($shop && \Illuminate\Support\Facades\Route::has('shop.show')) {
        $myShopUrl = route('shop.show', $shop->slug ?: $shop->getKey());
    } elseif (\Illuminate\Support\Facades\Route::has('seller.shop.create')) {
        $myShopUrl = route('seller.shop.create');
    }

    $groups = [
        __('Overview') => [
            ['route' => 'seller.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => __('Dashboard')],
        ],
        __('Listings') => [
            ['route' => 'products.index', 'icon' => 'fas fa-box-open', 'label' => __('My Listings')],
            ['route' => 'seller.deals.index', 'icon' => 'fas fa-percent', 'label' => __('Deals')],
        ],
        __('Sales') => [
            ['route' => 'seller.orders.index', 'icon' => 'fas fa-shopping-cart', 'label' => __('Shop Orders')],
            ['route' => 'seller.reviews.index', 'icon' => 'fas fa-star', 'label' => __('Reviews')],
            ['route' => 'account.orders', 'icon' => 'fas fa-bag-shopping', 'label' => __('My Orders')],
            ['route' => 'seller.orders.payments', 'icon' => 'fas fa-credit-card', 'label' => __('Payments')],
        ],
        __('Engagement') => [
            ['route' => 'seller.messages.index', 'icon' => 'fas fa-comments', 'label' => __('Messages'), 'badge' => $unreadMessages],
            ['route' => 'seller.offers.index', 'icon' => 'fas fa-handshake', 'label' => __('Offers'), 'badge' => $pendingOffers],
            ['route' => 'buyer.favorites', 'icon' => 'fas fa-heart', 'label' => __('Favorites'), 'badge' => $myFavoritesCount],
            ['route' => 'seller.favorites.index', 'icon' => 'fas fa-store', 'label' => __('Shop Favorites'), 'badge' => $shopFavoritesCount],
            ['route' => 'seller.notifications.index', 'icon' => 'fas fa-bell', 'label' => __('Notifications'), 'badge' => $unreadNotifications],
        ],
        __('Disputes') => [
            ['route' => 'disputes.index', 'icon' => 'fas fa-exclamation-triangle', 'label' => __('Disputes'), 'badge' => $disputesCount],
        ],
        __('Shop & Settings') => [
            ['href' => $myShopUrl, 'icon' => 'fas fa-store', 'label' => __('My Shop'), 'activePatterns' => ['shop.show', 'seller.shops.*', 'seller.shop.create']],
            ['route' => 'seller.analytics.index', 'icon' => 'fas fa-chart-line', 'label' => __('Analytics')],
            ['route' => 'seller.reports.inventory', 'icon' => 'fas fa-boxes-stacked', 'label' => __('Inventory Report')],
            ['route' => 'seller.subscription', 'icon' => 'fas fa-file-invoice', 'label' => __('Subscription')],
            ['route' => 'seller.kyc', 'icon' => 'fas fa-id-card', 'label' => __('KYC')],
        ],
    ];

    $primaryBottomNav = [];
    if (\Illuminate\Support\Facades\Route::has('seller.dashboard')) {
        $primaryBottomNav[] = [
            'route' => 'seller.dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'label' => __('Dashboard'),
            'active' => request()->routeIs('seller.dashboard'),
            'badge' => 0,
        ];
    }
    if (\Illuminate\Support\Facades\Route::has('products.index')) {
        $primaryBottomNav[] = [
            'route' => 'products.index',
            'icon' => 'fas fa-box-open',
            'label' => __('Listings'),
            'active' => request()->routeIs('products.*', 'seller.deals.*'),
            'badge' => 0,
        ];
    }
    if (\Illuminate\Support\Facades\Route::has('seller.orders.index')) {
        $primaryBottomNav[] = [
            'route' => 'seller.orders.index',
            'icon' => 'fas fa-receipt',
            'label' => __('Orders'),
            'active' => request()->routeIs('seller.orders.*'),
            'badge' => 0,
        ];
    }
    if (\Illuminate\Support\Facades\Route::has('seller.messages.index')) {
        $primaryBottomNav[] = [
            'route' => 'seller.messages.index',
            'icon' => 'fas fa-comments',
            'label' => __('Messages'),
            'active' => request()->routeIs('seller.messages.*'),
            'badge' => (int) $unreadMessages,
        ];
    }
@endphp

<style>
    @media (max-width: 1023px) {
        body.has-seller-bottom-nav { padding-bottom: calc(5.5rem + env(safe-area-inset-bottom)); }
    }
</style>

<aside class="hidden space-y-4 lg:sticky lg:top-24 lg:block">
    @if(\Illuminate\Support\Facades\Route::has('products.index'))
        <form action="{{ route('products.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <label for="seller-product-search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('Search Listings') }}</label>
            <div class="flex items-center gap-2 rounded-xl border border-slate-300 px-2 py-1.5">
                <i class="fas fa-search text-slate-400"></i>
                <input id="seller-product-search" type="search" name="q" placeholder="{{ __('Search products...') }}" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                <button type="submit" class="rounded-lg px-2 py-1 text-xs font-semibold text-white" style="background-color: {{ $brandColor }}">{{ __('Go') }}</button>
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
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Wallet Balance') }}</p>
                <p class="truncate text-base font-bold text-slate-900">{{ $currency }} {{ number_format((float)$walletBalance, 2) }}</p>
                @if((float)$walletHold > 0)
                    <p class="text-xs text-slate-500">{{ __('On hold: :amount', ['amount' => $currency . ' ' . number_format((float)$walletHold, 2)]) }}</p>
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
                        @php
                            $routeName = $item['route'] ?? null;
                            $href = $item['href'] ?? ($routeName && \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : null);
                            $activePatterns = (array) ($item['activePatterns'] ?? ($routeName ? [$routeName] : []));
                            $isActive = !empty($activePatterns) ? request()->routeIs(...$activePatterns) : false;
                            $badge = (int)($item['badge'] ?? 0);
                        @endphp
                        @continue(!$href)
                        <a href="{{ $href }}"
                           class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $isActive ? 'text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}"
                           @if($isActive) style="background-color: {{ $brandColor }}" @endif>
                            <i class="{{ $item['icon'] }} w-4 text-center {{ $isActive ? 'text-white' : 'text-slate-500' }}"></i>
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

<div class="lg:hidden" data-seller-mobile-nav-root>
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 px-2 py-2 shadow-[0_-8px_24px_rgba(15,23,42,0.12)] backdrop-blur">
        <ul class="grid grid-cols-5 gap-1">
            @foreach($primaryBottomNav as $tab)
                <li>
                    <a href="{{ route($tab['route']) }}"
                       class="relative flex min-h-[3.4rem] flex-col items-center justify-center rounded-xl px-1 text-[11px] font-semibold {{ $tab['active'] ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600' }}">
                        <i class="{{ $tab['icon'] }} text-base"></i>
                        <span class="mt-1 truncate">{{ $tab['label'] }}</span>
                        @if(($tab['badge'] ?? 0) > 0)
                            <span class="absolute right-2 top-1 inline-flex min-w-[1rem] items-center justify-center rounded-full bg-rose-600 px-1 py-0.5 text-[9px] font-bold text-white">
                                {{ $tab['badge'] > 99 ? '99+' : $tab['badge'] }}
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
                    <li>
                        <button type="button"
                        id="seller-menu-drawer-open"
                        class="flex min-h-[3.4rem] w-full flex-col items-center justify-center rounded-xl px-1 text-[11px] font-semibold text-slate-600">
                    <i class="fas fa-bars text-base"></i>
                    <span class="mt-1">{{ __('Menu') }}</span>
                </button>
            </li>
        </ul>
    </nav>

    <div id="seller-menu-drawer-backdrop" class="fixed inset-0 z-[60] hidden bg-slate-900/45"></div>

    <aside id="seller-menu-drawer-panel" class="fixed inset-x-0 bottom-0 z-[61] hidden max-h-[84vh] overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Seller Menu') }}</h3>
            <button type="button" data-seller-drawer-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="max-h-[calc(84vh-58px)] overflow-y-auto p-4">
            @if(\Illuminate\Support\Facades\Route::has('products.index'))
                <form action="{{ route('products.index') }}" method="GET" class="mb-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <label for="seller-product-search-mobile" class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('Search Listings') }}</label>
                    <div class="flex items-center gap-2 rounded-xl border border-slate-300 px-2 py-1.5">
                        <i class="fas fa-search text-slate-400"></i>
                        <input id="seller-product-search-mobile" type="search" name="q" placeholder="{{ __('Search products...') }}" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                        <button type="submit" class="rounded-lg px-2 py-1 text-xs font-semibold text-white" style="background-color: {{ $brandColor }}">{{ __('Go') }}</button>
                    </div>
                </form>
            @endif

            <a href="{{ \Illuminate\Support\Facades\Route::has('wallet.index') ? route('wallet.index') : '#' }}"
               class="mb-4 block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background-color: {{ $brandColor }}">
                        <i class="fas fa-wallet"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Wallet Balance') }}</p>
                        <p class="truncate text-base font-bold text-slate-900">{{ $currency }} {{ number_format((float)$walletBalance, 2) }}</p>
                        @if((float)$walletHold > 0)
                            <p class="text-xs text-slate-500">{{ __('On hold: :amount', ['amount' => $currency . ' ' . number_format((float)$walletHold, 2)]) }}</p>
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
                                @php
                                    $routeName = $item['route'] ?? null;
                                    $href = $item['href'] ?? ($routeName && \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : null);
                                    $activePatterns = (array) ($item['activePatterns'] ?? ($routeName ? [$routeName] : []));
                                    $isActive = !empty($activePatterns) ? request()->routeIs(...$activePatterns) : false;
                                    $badge = (int)($item['badge'] ?? 0);
                                @endphp
                                @continue(!$href)
                                <a href="{{ $href }}"
                                   class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $isActive ? 'text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}"
                                   @if($isActive) style="background-color: {{ $brandColor }}" @endif>
                                    <i class="{{ $item['icon'] }} w-4 text-center {{ $isActive ? 'text-white' : 'text-slate-500' }}"></i>
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
        </div>
    </aside>
</div>

<script>
    (function () {
        function initSellerMobileNav() {
            const root = document.querySelector('[data-seller-mobile-nav-root]');
            if (!root) return;

            document.body.classList.add('has-seller-bottom-nav');

            const openBtn = root.querySelector('#seller-menu-drawer-open');
            const panel = root.querySelector('#seller-menu-drawer-panel');
            const backdrop = root.querySelector('#seller-menu-drawer-backdrop');
            const closeBtn = root.querySelector('[data-seller-drawer-close]');

            if (!openBtn || !panel || !backdrop) return;

            const openDrawer = () => {
                panel.classList.remove('hidden');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeDrawer = () => {
                panel.classList.add('hidden');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            openBtn.addEventListener('click', openDrawer);
            backdrop.addEventListener('click', closeDrawer);
            closeBtn?.addEventListener('click', closeDrawer);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
                    closeDrawer();
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSellerMobileNav);
        } else {
            initSellerMobileNav();
        }
    })();
</script>
