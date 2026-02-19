@php
    use Illuminate\Support\Facades\Route as RouteFacade;

    $active = fn($routes) => collect((array) $routes)->contains(fn($r) => request()->routeIs($r));

    $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())
        ->where('is_read', false)
        ->count();
    $offersPending = \App\Models\Offer::where('buyer_id', auth()->id())
        ->where('status', 'pending')
        ->count();
    $favoritesCount = \App\Models\Wishlist::where('user_id', auth()->id())->count();
@endphp

@if(auth()->check() && auth()->user()->isBuyer())
    <aside class="hidden lg:block">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Overview</div>
            <a href="{{ route('buyer.dashboard') }}" class="mb-4 flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active(['buyer.dashboard', 'account.dashboard']) ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'text-slate-700 hover:bg-slate-100' }}">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><i class="fas fa-gauge"></i></span>
                <span>Dashboard</span>
            </a>

            <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Shopping</div>
            <div class="space-y-1.5 mb-4">
                <a href="{{ route('account.orders') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active(['account.orders', 'orders.*', 'buyer.orders.*', 'pay_now']) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-receipt"></i></span>
                    <span>Orders</span>
                </a>
                <a href="{{ route('buyer.offers') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active(['buyer.offers', 'buyer.offers.*']) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-hand-holding-dollar"></i></span>
                    <span>Offers</span>
                    @if($offersPending > 0)
                        <span class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $offersPending }}</span>
                    @endif
                </a>
                @if(RouteFacade::has('buyer.messages.index'))
                    <a href="{{ route('buyer.messages.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active(['buyer.messages.*']) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-comments"></i></span>
                        <span>Messages</span>
                        @if($unreadMessages > 0)
                            <span class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                @endif
            </div>

            <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Saved</div>
            <div class="space-y-1.5 mb-4">
                @if(RouteFacade::has('buyer.favorites'))
                    <a href="{{ route('buyer.favorites') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active('buyer.favorites') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-heart"></i></span>
                        <span>Favorites</span>
                        @if($favoritesCount > 0)
                            <span class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-slate-700 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $favoritesCount }}</span>
                        @endif
                    </a>
                @endif
                @if(RouteFacade::has('wishlist'))
                    <a href="{{ route('wishlist') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active(['wishlist', 'wishlist.*']) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-bookmark"></i></span>
                        <span>Wishlist</span>
                    </a>
                @endif
            </div>

            <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Wallet & Account</div>
            <div class="space-y-1.5">
                <a href="{{ route('wallet.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active(['wallet.index', 'wallet.*']) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-wallet"></i></span>
                    <span>Wallet</span>
                </a>
                @if(\Illuminate\Support\Facades\Route::has('notifications.index'))
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active('notifications.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-bell"></i></span>
                        <span>Notifications</span>
                    </a>
                @endif
                <a href="{{ route('account.payments') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active('account.payments') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-credit-card"></i></span>
                    <span>Payments</span>
                </a>
                <a href="{{ route('account.details') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active('account.details') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-user"></i></span>
                    <span>Account</span>
                </a>
                <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-base font-medium transition {{ $active('account.addresses') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-location-dot"></i></span>
                    <span>Addresses</span>
                </a>
            </div>
        </div>
    </aside>
@endif
