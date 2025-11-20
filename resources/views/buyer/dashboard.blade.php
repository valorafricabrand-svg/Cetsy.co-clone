@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Dashboard') }}
    </h2>
@endsection

@section('content')
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
            <div class="alert alert-success" role="alert">
                A new verification link was sent to your email.
            </div>
        @endif
        @if (! auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning d-flex justify-content-between align-items-center" role="alert">
                <div>
                    Your email is not verified. Please check your inbox for a verification link.
                </div>
                <form method="POST" action="{{ route('verification.send') }}" class="ms-3">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">Resend verification email</button>
                </form>
            </div>
        @endif

        {{-- ========== WELCOME ==========' --}}
        <div class="buyer-dashboard-hero mb-4 d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <div class="text-uppercase small text-success fw-semibold mb-1">
                    <i class="fas fa-user-check me-1"></i> Buyer overview
                </div>
                <h3 class="text-dark mb-1">Welcome back, {{ Auth::user()->name }}</h3>
                <p class="text-muted mb-0">
                    Track your orders, favourites and offers at a glance.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-lg-0">
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
        <div class="row g-4 mb-4">

            {{-- ORDERS --}}
            <div class="col-md-3">
                <a href="{{ route('account.orders') }}" class="card buyer-stat-card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold text-primary">Orders</h5>
                        <p class="fs-5 text-muted mb-0">
                            {{ $ordersCount }} {{ Str::plural('Order', $ordersCount) }}
                        </p>
                    </div>
                </a>
            </div>

            {{-- WISHLIST --}}
            <div class="col-md-3">
                <a href="{{ route('wishlist') }}" class="card buyer-stat-card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-heart fa-3x text-success"></i>
                        </div>
                        <h5 class="fw-bold text-success">Favourites</h5>
                        <p class="fs-5 text-muted mb-0">
                            {{ $wishlistCount }} {{ Str::plural('Item', $wishlistCount) }}
                        </p>
                    </div>
                </a>
            </div>

            {{-- OFFERS --}}
            <div class="col-md-3">
                <a href="{{ route('buyer.offers') }}" class="card buyer-stat-card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-hand-holding-dollar fa-3x text-info"></i>
                        </div>
                        <h5 class="fw-bold text-info">Offers</h5>
                        <p class="fs-5 text-muted mb-0">
                            {!! $total_offers . " <small class='text-success ms-1' title='Accepted'>(".$accepted_offers." &check;)</small> <small class='text-danger ms-1' title='Declined'>(".$declined_offers." &times;)</small>" !!}
                        </p>
                    </div>
                </a>
            </div>

            {{-- WALLET --}}
            <div class="col-md-3">
                <a href="{{ url('wallet') }}" class="card buyer-stat-card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-wallet fa-3x text-warning"></i>
                        </div>
                        <h5 class="fw-bold text-warning">Account&nbsp;Balance</h5>
                        <p class="fs-5 text-muted mb-0">
                            {{ get_currency() }} {{ number_format(wallet(), 2) }}
                        </p>
                    </div>
                </a>
            </div>
        </div>

        @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
            <div class="card buyer-section-card shadow-sm border-0 mt-4">
                <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-stars text-warning"></i> Recommended For You
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
        <div class="card buyer-section-card shadow-sm border-0 mt-4">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-star-fill text-warning"></i> Your Recent Reviews
            </div>
            <div class="card-body">
                @if(isset($myRecentReviews) && $myRecentReviews->count())
                    <ul class="list-group list-group-flush">
                        @foreach($myRecentReviews as $r)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">{{ optional($r->orderItem?->product)->name ?? 'Product' }}</div>
                                    <div class="small text-muted">Order #{{ $r->order_id }} • Rated: {{ $r->rating }} / 5</div>
                                    @if($r->comment)
                                        <div class="small mt-1">{{ \Illuminate\Support\Str::limit($r->comment, 140) }}</div>
                                    @endif
                                </div>
                                @if($r->orderItem?->product?->slug)
                                    <a href="{{ route('listing.show', $r->orderItem->product->slug) }}" class="btn btn-sm btn-outline-secondary">View Item</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted small">You haven't left any reviews yet.</div>
                @endif
            </div>
        </div>

        

    </div>
</div>
@endsection
