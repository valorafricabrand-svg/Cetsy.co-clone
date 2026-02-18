@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold fs-3 text-slate-900">
        {{ __('Your Dashboard') }}
    </h2>
@endsection

@section('main')
<div class="content buyer-dashboard">
    <style>
        .buyer-dashboard {
            background:
                radial-gradient(1200px 600px at -10% -10%, rgba(59,130,246,.06), transparent 60%),
                radial-gradient(1200px 600px at 110% 0%, rgba(16,185,129,.06), transparent 60%),
                linear-gradient(180deg, #f9fafb, #f3f4ff);
        }
        .buyer-dashboard-hero {
            border-radius: 1rem;
            background: linear-gradient(135deg, #ffffff, #e0f2fe);
            padding: 1.5rem 1.75rem;
            box-shadow: 0 18px 45px rgba(15,23,42,.08);
        }
        .buyer-dashboard-hero h3 { font-weight: 700; }
        .buyer-hero-chip {
            font-size: .8rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            border: 1px solid rgba(34,197,94,.28);
            background: rgba(34,197,94,.06);
            color: #166534;
        }
        .buyer-hero-chip i { margin-right: .35rem; }
        .buyer-stat-card {
            border-radius: 1rem;
            border: 1px solid rgba(148,163,184,.22) !important;
            box-shadow: 0 18px 40px rgba(15,23,42,.04);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .buyer-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 60px rgba(15,23,42,.12);
            border-color: rgba(34,197,94,.55) !important;
        }
        .buyer-stat-card i {
            filter: drop-shadow(0 12px 30px rgba(15,23,42,.18));
        }
        .buyer-section-card {
            border-radius: 1rem;
            border: 1px solid rgba(148,163,184,.18);
            box-shadow: 0 18px 40px rgba(15,23,42,.04);
        }
        .buyer-section-card .card-header {
            border-bottom: 0;
            background: transparent;
            padding-bottom: .35rem;
        }
        .buyer-section-card .card-header i { font-size: 1.1rem; }
        .buyer-section-card table thead {
            background: #f9fafb;
        }
        .buyer-section-card table tbody tr:hover {
            background: #f9fafb;
        }
    </style>
    <div class="container-xxl">

        @if (session('status') === 'verification-link-sent')
            <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800" role="alert">
                A new verification link was sent to your email.
            </div>
        @endif
        @if (! auth()->user()->hasVerifiedEmail())
            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex justify-between items-center" role="alert">
                <div>
                    Your email is not verified. Please check your inbox for a verification link.
                </div>
                <form method="POST" action="{{ route('verification.send') }}" class="ml-3">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400">Resend verification email</button>
                </form>
            </div>
        @endif

        {{-- ========== WELCOME ==========' --}}
        <div class="buyer-dashboard-hero mb-4 flex flex-col lg:flex-row justify-between items-start gap-3">
            <div>
                <div class="text-uppercase text-xs text-emerald-600 font-semibold mb-1">
                    <i class="fas fa-user-check mr-1"></i> Buyer overview
                </div>
                <h3 class="text-slate-900 mb-1">Welcome back, {{ Auth::user()->name }}</h3>
                <p class="text-slate-500 mb-0">
                    Track your orders, favourites and offers at a glance.
                </p>
            </div>
            <div class="flex flex-wrap gap-2 mt-3 mt-0 lg:mt-0">
                <span class="buyer-hero-chip">
                    <i class="fas fa-shopping-bag"></i>
                    {{ $ordersCount }} {{ Str::plural('Order', $ordersCount) }}
                </span>
                <span class="buyer-hero-chip">
                    <i class="fas fa-heart"></i>
                    {{ $wishlistCount }} {{ Str::plural('Favourite', $wishlistCount) }}
                </span>
                <span class="buyer-hero-chip">
                    <i class="fas fa-hand-holding-dollar"></i>
                    {{ $total_offers }} {{ Str::plural('Offer', $total_offers) }}
                </span>
            </div>
        </div>

        {{-- ========== ACCOUNT OVERVIEW ==========' --}}
        <div class="grid grid-cols-12 gap-4 mb-4">

            {{-- ORDERS --}}
            <div class="md:col-span-3">
                <a href="{{ route('account.orders') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm buyer-stat-card border-0 h-full text-center no-underline link-hover">
                    <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                        </div>
                        <h5 class="font-bold text-primary">Orders</h5>
                        <p class="fs-5 text-slate-500 mb-0">
                            {{ $ordersCount }} {{ Str::plural('Order', $ordersCount) }}
                        </p>
                    </div>
                </a>
            </div>

            {{-- WISHLIST --}}
            <div class="md:col-span-3">
                <a href="{{ route('wishlist') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm buyer-stat-card border-0 h-full text-center no-underline link-hover">
                    <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-heart fa-3x text-emerald-600"></i>
                        </div>
                        <h5 class="font-bold text-emerald-600">Favourites</h5>
                        <p class="fs-5 text-slate-500 mb-0">
                            {{ $wishlistCount }} {{ Str::plural('Item', $wishlistCount) }}
                        </p>
                    </div>
                </a>
            </div>

            {{-- OFFERS --}}
            <div class="md:col-span-3">
                <a href="{{ route('buyer.offers') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm buyer-stat-card border-0 h-full text-center no-underline link-hover">
                    <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-hand-holding-dollar fa-3x text-sky-600"></i>
                        </div>
                        <h5 class="font-bold text-sky-600">Offers</h5>
                        <p class="fs-5 text-slate-500 mb-0">
                            {!! $total_offers . " <small class='text-emerald-600 ml-1' title='Accepted'>(".$accepted_offers." &check;)</small> <small class='text-rose-600 ml-1' title='Declined'>(".$declined_offers." &times;)</small>" !!}
                        </p>
                    </div>
                </a>
            </div>

            {{-- WALLET --}}
            <div class="md:col-span-3">
                <a href="{{ url('wallet') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm buyer-stat-card border-0 h-full text-center no-underline link-hover">
                    <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-wallet fa-3x text-amber-600"></i>
                        </div>
                        <h5 class="font-bold text-amber-600">Account&nbsp;Balance</h5>
                        <p class="fs-5 text-slate-500 mb-0">
                            {{ get_currency() }} {{ number_format(wallet(), 2) }}
                        </p>
                    </div>
                </a>
            </div>
        </div>

        @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm buyer-section-card border-0 mt-4">
                <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
                    <i class="bi bi-stars text-amber-600"></i> Recommended For You
                </div>
                @include('theme.'.theme().'.partials.product-carousel', [
                    'items' => $recommendedProducts,
                    'showHeader' => false,
                    'wrapperTag' => 'div',
                    'wrapperClass' => 'card-body pt-3',
                    'containerClass' => '',
                    'seeMoreUrl' => route('listings'),
                    'seeMoreLabel' => 'Browse more'
                ])
            </div>
        @endif

        </div>

        {{-- ========== YOUR RECENT REVIEWS ==========' --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm buyer-section-card border-0 mt-4">
            <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
                <i class="bi bi-star-fill text-amber-600"></i> Your Recent Reviews
            </div>
            <div class="p-4 sm:p-5">
                @if(isset($myRecentReviews) && $myRecentReviews->count())
                    <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
                        @foreach($myRecentReviews as $r)
                            <li class="px-4 py-3 flex justify-between items-start">
                                <div>
                                    <div class="font-semibold">{{ optional($r->orderItem?->product)->name ?? 'Product' }}</div>
                                    <div class="text-xs text-slate-500">Order #{{ $r->order_id }} â€¢ Rated: {{ $r->rating }} / 5</div>
                                    @if($r->comment)
                                        <div class="text-xs mt-1">{{ \Illuminate\Support\Str::limit($r->comment, 140) }}</div>
                                    @endif
                                </div>
                                @if($r->orderItem?->product?->slug)
                                    <a href="{{ route('listing.show', $r->orderItem->product->slug) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50">View Item</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-slate-500 text-xs">You haven't left any reviews yet.</div>
                @endif
            </div>
        </div>

        

    </div>
</div>
@endsection




