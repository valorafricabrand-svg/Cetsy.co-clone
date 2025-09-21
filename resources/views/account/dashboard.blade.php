@extends('layouts.appbar')
@section('content')
<div class="content-wrapper p-4">
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="mb-4">
            <h3 class="text-dark">Dashboard</h3>
            <p class="text-muted">Welcome back, <strong>{{ Auth::user()->name }}</strong>!</p>
        </div>

<!-- Account Overview -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="{{ route('account.orders') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <div class="icon-container mb-3">
                    <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                </div>
                <h5 class="card-title fw-bold text-primary">Orders</h5>
                <p class="fs-5 text-muted mb-0">{{ $ordersCount }} Orders</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('wishlist.index') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <div class="icon-container mb-3">
                    <i class="fas fa-heart fa-3x text-success"></i>
                </div>
                <h5 class="card-title fw-bold text-success">Wishlist</h5>
                <p class="fs-5 text-muted mb-0">{{ $wishlistCount }} Items</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ url('client.wallet') }}" class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <div class="icon-container mb-3">
                    <i class="fas fa-wallet fa-3x text-warning"></i>
                </div>
                <h5 class="card-title fw-bold text-warning">Account Balance</h5>
                <p class="fs-5 text-muted mb-0">{{ money($accountBalance) }}</p>
            </div>
        </a>
    </div>
</div>




        <!-- Recent Orders -->
        <div class="recent-orders-section">
            <h4 class="text-dark mb-3">Recent Orders</h4>
            @if($recentOrders->isEmpty())
                <div class="alert alert-warning" role="alert">
                    You have no recent orders.
                </div>
            @else
                <div class="list-group">
                    @foreach($recentOrders as $order)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Order ID: #{{ $order->id }}</h6>
                                <p class="mb-1 text-muted">
                                    <strong>Date:</strong> {{ $order->created_at->format('d M, Y') }}
                                </p>
                                <p class="mb-0">
                                    <strong>Status:</strong> {{ ucfirst($order->status) }} |
                                    <strong>Total:</strong> {{ money($order->total) }}
                                </p>
                            </div>
                            @if($order->status == 'pending')

                         <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary btn-sm">View Details</a>
                         
                            <a href="{{ route('pay_now', $order->id) }}" class="btn btn-primary btn-sm">Pay Now</a>
                        @else
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm">View Details</a>
                        @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recommended Products -->
        <div class="recommended-products mt-5">
            <h4 class="text-dark mb-3">Recommended for You</h4>
            @if($recommendedProducts->isEmpty())
                <div class="alert alert-info" role="alert">
                    No recommended products yet. Browse the marketplace to personalize your picks.
                </div>
            @else
                @include('theme.'.theme().'.partials.product-carousel', [
                    'items' => $recommendedProducts,
                    'showHeader' => false,
                    'wrapperTag' => 'div',
                    'wrapperClass' => 'recommended-carousel position-relative',
                    'containerClass' => '',
                    'seeMoreUrl' => route('listings'),
                    'seeMoreLabel' => 'Explore marketplace'
                ])
            @endif
        </div>
    </div>
</div>
@endsection

