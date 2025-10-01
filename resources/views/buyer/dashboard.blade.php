@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">

        {{-- ========== WELCOME ==========' --}}
        <div class="mb-4">
            <h3 class="text-dark mb-1">Dashboard</h3>
            <p class="text-muted">
                Welcome back, <strong>{{ Auth::user()->name }}</strong>!
            </p>
        </div>

        {{-- ========== ACCOUNT OVERVIEW ==========' --}}
        <div class="row g-4 mb-4">

            {{-- ORDERS --}}
            <div class="col-md-3">
                <a href="{{ route('account.orders') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
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
                <a href="{{ route('wishlist') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
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
                <a href="{{ route('buyer.offers') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
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
                <a href="{{ url('wallet') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
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

        {{-- ========== RECENT ORDERS ==========' --}}
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Recent&nbsp;Orders
            </div>

            @if($recentOrders->isEmpty())
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        You have no recent orders.
                    </div>
                </div>
            @else
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="fw-semibold">#{{ $order->id }}</td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>
                                            @php
                                                $badge = [
                                                    'pending'   => 'bg-secondary',
                                                    'paid'      => 'bg-success',
                                                    'shipped'   => 'bg-info',
                                                    'delivered' => 'bg-primary',
                                                    'canceled'  => 'bg-danger',
                                                ][$order->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $badge }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ get_currency() }} {{ number_format($order->total, 2) }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm">
                                                    View
                                                </a>
                                                @if($order->status === 'pending')
                                                    <a href="{{ route('pay_now', $order->id) }}" class="btn btn-primary btn-sm">
                                                        Pay&nbsp;Now
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
            <div class="card shadow-sm border-0 mt-4">
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
        <div class="card shadow-sm border-0 mt-4">
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
