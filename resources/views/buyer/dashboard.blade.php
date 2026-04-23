@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        {{ __('Your Dashboard') }}
    </h2>
@endsection

@section('main')
<div class="buyer-dashboard py-8">
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
        .buyer-stat-card i { filter: drop-shadow(0 12px 30px rgba(15,23,42,.18)); }
        .buyer-section-card {
            border-radius: 1rem;
            border: 1px solid rgba(148,163,184,.18);
            box-shadow: 0 18px 40px rgba(15,23,42,.04);
        }
    </style>

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-span-12 lg:col-span-9">
        @if (session('status') === 'verification-link-sent')
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                {{ __('A new verification link was sent to your email.') }}
            </div>
        @endif

        @if (! auth()->user()->hasVerifiedEmail())
            <div class="mb-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 sm:flex-row sm:items-center sm:justify-between" role="alert">
                <div>{{ __('Your email is not verified. Please check your inbox for a verification link.') }}</div>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-slate-900 transition hover:bg-amber-400">{{ __('Resend verification email') }}</button>
                </form>
            </div>
        @endif

        <div class="buyer-dashboard-hero mb-4 flex flex-col items-start justify-between gap-3 lg:flex-row">
            <div>
                <div class="mb-1 text-xs font-semibold uppercase text-emerald-600">
                    <i class="fas fa-user-check mr-1"></i> {{ __('Buyer overview') }}
                </div>
                <h3 class="mb-1 text-slate-900">{{ __('Welcome back, :name', ['name' => Auth::user()->name]) }}</h3>
                <p class="mb-0 text-slate-500">{{ __('Track your orders, favourites and offers at a glance.') }}</p>
            </div>
            <div class="mt-0 flex flex-wrap gap-2">
                <span class="buyer-hero-chip"><i class="fas fa-shopping-bag"></i>{{ trans_choice('{1} :count Order|[2,*] :count Orders', $ordersCount, ['count' => $ordersCount]) }}</span>
                <span class="buyer-hero-chip"><i class="fas fa-heart"></i>{{ trans_choice('{1} :count Favourite|[2,*] :count Favourites', $wishlistCount, ['count' => $wishlistCount]) }}</span>
                <span class="buyer-hero-chip"><i class="fas fa-hand-holding-dollar"></i>{{ trans_choice('{1} :count Offer|[2,*] :count Offers', $total_offers, ['count' => $total_offers]) }}</span>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <a href="{{ route('account.orders') }}" class="buyer-stat-card block h-full rounded-2xl border border-slate-200 bg-white text-center no-underline">
                    <div class="flex flex-col items-center justify-center p-5">
                        <div class="mb-3"><i class="fas fa-shopping-cart fa-3x text-sky-600"></i></div>
                        <h5 class="font-bold text-sky-600">{{ __('Orders') }}</h5>
                        <p class="mb-0 text-lg text-slate-500">{{ trans_choice('{1} :count Order|[2,*] :count Orders', $ordersCount, ['count' => $ordersCount]) }}</p>
                    </div>
                </a>
            </div>

            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <a href="{{ localized_route('wishlist') }}" class="buyer-stat-card block h-full rounded-2xl border border-slate-200 bg-white text-center no-underline">
                    <div class="flex flex-col items-center justify-center p-5">
                        <div class="mb-3"><i class="fas fa-heart fa-3x text-emerald-600"></i></div>
                        <h5 class="font-bold text-emerald-600">{{ __('Favourites') }}</h5>
                        <p class="mb-0 text-lg text-slate-500">{{ trans_choice('{1} :count Item|[2,*] :count Items', $wishlistCount, ['count' => $wishlistCount]) }}</p>
                    </div>
                </a>
            </div>

            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <a href="{{ route('buyer.offers') }}" class="buyer-stat-card block h-full rounded-2xl border border-slate-200 bg-white text-center no-underline">
                    <div class="flex flex-col items-center justify-center p-5">
                        <div class="mb-3"><i class="fas fa-hand-holding-dollar fa-3x text-indigo-600"></i></div>
                        <h5 class="font-bold text-indigo-600">{{ __('Offers') }}</h5>
                        <p class="mb-0 text-lg text-slate-500">
                            {!! $total_offers . " <small class='ml-1 text-emerald-600' title='".e(__('Accepted'))."'>(".$accepted_offers." &check;)</small> <small class='ml-1 text-rose-600' title='".e(__('Declined'))."'>(".$declined_offers." &times;)</small>" !!}
                        </p>
                    </div>
                </a>
            </div>

            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <a href="{{ url('wallet') }}" class="buyer-stat-card block h-full rounded-2xl border border-slate-200 bg-white text-center no-underline">
                    <div class="flex flex-col items-center justify-center p-5">
                        <div class="mb-3"><i class="fas fa-wallet fa-3x text-amber-600"></i></div>
                        <h5 class="font-bold text-amber-600">{{ __('Account Balance') }}</h5>
                        <p class="mb-0 text-lg text-slate-500">{{ get_currency() }} {{ number_format(wallet(), 2) }}</p>
                    </div>
                </a>
            </div>
        </div>

        @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
            <div class="buyer-section-card mt-4 rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3 font-semibold">
                    <i class="bi bi-stars text-amber-600"></i> {{ __('Recommended For You') }}
                </div>
                @include('theme.'.theme().'.partials.product-carousel', [
                    'items' => $recommendedProducts,
                    'showHeader' => false,
                    'wrapperTag' => 'div',
                    'wrapperClass' => 'pt-3',
                    'containerClass' => '',
                    'seeMoreUrl' => localized_route('listings'),
                    'seeMoreLabel' => __('Browse more')
                ])
            </div>
        @endif

        <div class="buyer-section-card mt-4 rounded-2xl border border-slate-200 bg-white">
            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3 font-semibold">
                <i class="bi bi-star-fill text-amber-600"></i> {{ __('Your Recent Reviews') }}
            </div>
            <div class="p-4 sm:p-5">
                @if(isset($myRecentReviews) && $myRecentReviews->count())
                    <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                        @foreach($myRecentReviews as $r)
                            <li class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="font-semibold">{{ optional($r->orderItem?->product)->name ?? __('Product') }}</div>
                                    <div class="text-xs text-slate-500">{{ __('Order #:id • Rated: :rating / 5', ['id' => $r->order_id, 'rating' => $r->rating]) }}</div>
                                    @if($r->comment)
                                        <div class="mt-1 text-xs">{{ \Illuminate\Support\Str::limit($r->comment, 140) }}</div>
                                    @endif
                                </div>
                                @if($r->orderItem?->product?->slug)
                                    <a href="{{ localized_route('listing.show', $r->orderItem->product->slug) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">{{ __('View Item') }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-xs text-slate-500">{{ __("You haven't left any reviews yet.") }}</div>
                @endif
            </div>
        </div>
            </div>
        </div>
    </div>
</div>
@endsection
