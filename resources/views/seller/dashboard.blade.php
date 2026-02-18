{{-- resources/views/seller/dashboard.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Seller Dashboard')

@section('main')
@php
    $shop = auth()->user()->shop;
    $brandColor = optional($shop)->primary_color;
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        $brandColor = '#0f766e';
    }

    $walletBalance = wallet();
    $walletHold = wallet('on_hold');
    $currency = get_currency();

    $cards = [
        [
            'value' => $total_orders,
            'sub' => null,
            'label' => 'Total Orders',
            'icon' => 'fas fa-credit-card',
            'href' => route('seller.orders.index'),
            'tone' => 'text-amber-600',
        ],
        [
            'value' => $total_products,
            'sub' => null,
            'label' => 'Total Listings',
            'icon' => 'fas fa-box-open',
            'href' => route('products.index'),
            'tone' => 'text-sky-600',
        ],
        [
            'value' => $currency.' '.number_format((float)$walletBalance, 2),
            'sub' => 'On hold: '.$currency.' '.number_format((float)$walletHold, 2),
            'label' => 'Wallet Balance',
            'icon' => 'fas fa-wallet',
            'href' => route('wallet.index'),
            'tone' => 'text-emerald-600',
        ],
        [
            'value' => $total_offers,
            'sub' => $accepted_offers.' accepted | '.$declined_offers.' declined',
            'label' => 'Offers Received',
            'icon' => 'fas fa-handshake',
            'href' => route('seller.offers.index'),
            'tone' => 'text-indigo-600',
        ],
        [
            'value' => $favorites_messages_total,
            'sub' => 'Last 7 days: '.$favorites_messages_week,
            'label' => 'Favorites Messages',
            'icon' => 'fas fa-comments',
            'href' => route('seller.favorites.index'),
            'tone' => 'text-slate-600',
        ],
    ];

    $statusTone = static function ($status) {
        return match ((string)$status) {
            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            'processing' => 'border-sky-200 bg-sky-50 text-sky-700',
            'shipped' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
            'delivered', 'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-100 text-slate-700',
        };
    };
@endphp

<section class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        @if (session('status') === 'verification-link-sent')
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                A new verification link was sent to your email.
            </div>
        @endif

        @if (! auth()->user()->hasVerifiedEmail())
            <div class="mb-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                <p>Your email is not verified. Please verify to unlock all seller features.</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="inline-flex rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                        Resend verification email
                    </button>
                </form>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            @include('seller.partials.sidebar')

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-white" style="background-color: {{ $brandColor }}">
                                <i class="fas fa-store"></i>
                            </span>
                            <div>
                                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Seller Dashboard</h1>
                                <p class="text-sm text-slate-500">
                                    {{ $shop->name ?? 'Your Shop' }}
                                    <span class="mx-1">|</span>
                                    Active: <span class="font-semibold text-emerald-700">{{ $activeProducts }}</span>
                                    <span class="mx-1">|</span>
                                    Paused: <span class="font-semibold text-slate-700">{{ $pausedProducts }}</span>
                                    @if($isHolidayMode)
                                        <span class="ml-2 inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Holiday mode</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('products.create') }}" class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-white shadow-sm" style="background-color: {{ $brandColor }}">
                                <i class="fas fa-plus mr-1"></i> New Listing
                            </a>
                            <a href="{{ route('seller.orders.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-receipt mr-1"></i> Orders
                            </a>
                            <a href="{{ route('seller.offers.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-handshake mr-1"></i> Offers
                            </a>
                            <a href="{{ route('wallet.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-wallet mr-1"></i> Wallet
                            </a>
                            <a href="{{ route('seller.analytics.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-chart-line mr-1"></i> Analytics
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach($cards as $card)
                        <a href="{{ $card['href'] }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $card['label'] }}</p>
                                <i class="{{ $card['icon'] }} $card['tone']"></i>
                            </div>
                            <p class="mt-2 text-xl font-extrabold text-slate-900">{{ $card['value'] }}</p>
                            @if($card['sub'])
                                <p class="mt-1 text-xs text-slate-500">{{ $card['sub'] }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                            <h2 class="text-base font-bold text-slate-900">
                                <i class="fas fa-receipt mr-2 text-emerald-600"></i>
                                Recent Orders
                            </h2>
                            <a href="{{ route('seller.orders.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-600">View all</a>
                        </div>

                        @if($orders->count())
                            <div class="hidden overflow-x-auto md:block">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50 text-slate-600">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold">ID</th>
                                            <th class="px-3 py-2 text-left font-semibold">Customer</th>
                                            <th class="px-3 py-2 text-right font-semibold">Total</th>
                                            <th class="px-3 py-2 text-left font-semibold">Status</th>
                                            <th class="px-3 py-2 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @foreach($orders as $o)
                                            @php
                                                $minDays = null; $maxDays = null;
                                                foreach (($o->items ?? []) as $it) {
                                                    $sp = $it->shippingProfile;
                                                    $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                                                    $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                                                    if (is_numeric($pMin)) $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin);
                                                    if (is_numeric($pMax)) $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax);
                                                }
                                                $placedAt = $o->created_at instanceof \Carbon\Carbon ? $o->created_at : ($o->created_at ? \Carbon\Carbon::parse($o->created_at) : null);
                                                $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
                                                $shipEnd = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
                                                $dispatchBy = $shipEnd?->format('M j') ?? $shipStart?->format('M j');
                                            @endphp
                                            <tr>
                                                <td class="px-3 py-2 text-slate-900">#{{ $o->id }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ optional($o->customer)->name ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ $currency }} {{ number_format((float)($o->total ?? $o->total_amount ?? 0), 2) }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusTone($o->status) }}">
                                                        {{ ucfirst($o->status) }}
                                                    </span>
                                                    <p class="mt-1 text-[11px] text-slate-500">
                                                        {{ $dispatchBy ? 'Dispatch by '.$dispatchBy : 'Dispatch soon' }}
                                                    </p>
                                                </td>
                                                <td class="px-3 py-2 text-slate-500">{{ optional($o->created_at)->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="space-y-2 p-3 md:hidden">
                                @foreach($orders as $o)
                                    <div class="rounded-xl border border-slate-200 p-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-slate-900">#{{ $o->id }}</p>
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusTone($o->status) }}">
                                                {{ ucfirst($o->status) }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">{{ optional($o->customer)->name ?? '-' }}</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $currency }} {{ number_format((float)($o->total ?? $o->total_amount ?? 0), 2) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="px-4 py-6 text-sm text-slate-500">No recent orders.</p>
                        @endif
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                                <h2 class="text-base font-bold text-slate-900">
                                    <i class="fas fa-star mr-2 text-amber-500"></i>
                                    Recent Reviews
                                </h2>
                                <a href="{{ route('seller.reviews.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-600">View all</a>
                            </div>
                            @if(isset($recentReviews) && $recentReviews->count())
                                <ul class="divide-y divide-slate-200">
                                    @foreach($recentReviews as $r)
                                        <li class="px-4 py-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ optional($r->orderItem?->product)->name ?? 'Product' }}</p>
                                                    <p class="text-xs text-slate-500">Order #{{ $r->order_id }} | Rated {{ $r->rating }}/5</p>
                                                    @if($r->comment)
                                                        <p class="mt-1 text-xs text-slate-700">{{ \Illuminate\Support\Str::limit($r->comment, 120) }}</p>
                                                    @endif
                                                </div>
                                                <a href="{{ route('orders.chat.show', $r->order_id) }}" class="inline-flex rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    Respond
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="px-4 py-6 text-sm text-slate-500">No reviews yet.</p>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 px-4 py-3">
                                <h2 class="text-base font-bold text-slate-900">
                                    <i class="fas fa-bolt mr-2 text-amber-500"></i>
                                    Quick Links
                                </h2>
                            </div>
                            <div class="grid gap-2 p-4 sm:grid-cols-2">
                                <a class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('products.index') }}"><i class="fas fa-boxes-stacked mr-1"></i>Manage Listings</a>
                                <a class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('seller.analytics.index') }}"><i class="fas fa-chart-line mr-1"></i>View Analytics</a>
                                <a class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('seller.payouts.index') }}"><i class="fas fa-money-bill-transfer mr-1"></i>Payouts</a>
                                <a class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('seller.messages.index') }}"><i class="fas fa-comments mr-1"></i>Messages</a>
                                <a class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 sm:col-span-2" href="{{ route('seller.buyers.index') }}"><i class="fas fa-users mr-1"></i>Buyers</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

