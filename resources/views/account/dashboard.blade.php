@extends('theme.'.theme().'.layouts.app')
@section('main')
<div class="content-wrapper p-4">
    <div class="dashboard-container">
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
        <!-- Welcome Section -->
        <div class="mb-4">
            <h3 class="text-slate-900">Dashboard</h3>
            <p class="text-slate-500">Welcome back, <strong>{{ Auth::user()->name }}</strong>!</p>
        </div>

<!-- Account Overview -->
<div class="grid grid-cols-12 gap-4 mb-4">
    <div class="md:col-span-4">
        <a href="{{ route('account.orders') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full text-center no-underline link-hover">
            <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                <div class="icon-container mb-3">
                    <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                </div>
                <h5 class="text-lg font-semibold text-slate-900 font-bold text-primary">Orders</h5>
                <p class="fs-5 text-slate-500 mb-0">{{ $ordersCount }} Orders</p>
            </div>
        </a>
    </div>
    <div class="md:col-span-4">
        <a href="{{ route('wishlist.index') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full text-center no-underline link-hover">
            <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                <div class="icon-container mb-3">
                    <i class="fas fa-heart fa-3x text-emerald-600"></i>
                </div>
                <h5 class="text-lg font-semibold text-slate-900 font-bold text-emerald-600">Wishlist</h5>
                <p class="fs-5 text-slate-500 mb-0">{{ $wishlistCount }} Items</p>
            </div>
        </a>
    </div>
    <div class="md:col-span-4">
        <a href="{{ url('client.wallet') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full text-center no-underline link-hover">
            <div class="p-4 sm:p-5 flex flex-col items-center justify-center py-4">
                <div class="icon-container mb-3">
                    <i class="fas fa-wallet fa-3x text-amber-600"></i>
                </div>
                <h5 class="text-lg font-semibold text-slate-900 font-bold text-amber-600">Account Balance</h5>
                <p class="fs-5 text-slate-500 mb-0">{{ money($accountBalance) }}</p>
            </div>
        </a>
    </div>
</div>




        <!-- Recent Orders -->
        <div class="recent-orders-section">
            <h4 class="text-slate-900 mb-3">Recent Orders</h4>
            @if($recentOrders->isEmpty())
                <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800" role="alert">
                    You have no recent orders.
                </div>
            @else
                <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                    @foreach($recentOrders as $order)
                        <div class="px-4 py-3 flex justify-between items-center">
                            <div>
                                <h6 class="mb-1">Order ID: #{{ $order->id }}</h6>
                                <p class="mb-1 text-slate-500">
                                    <strong>Date:</strong> {{ $order->created_at->format('d M, Y') }}
                                </p>
                                <p class="mb-0">
                                    <strong>Status:</strong> {{ ucfirst($order->status) }} |
                                    <strong>Total:</strong> {{ money($order->total) }}
                                </p>
                            </div>
                            @if($order->status == 'pending')

                         <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500 px-3 py-1.5 text-xs">View Details</a>
                         
                            <a href="{{ route('pay_now', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-1.5 text-xs">Pay Now</a>
                        @else
                            <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs">View Details</a>
                        @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recommended Products -->
        <div class="recommended-products mt-5">
            <h4 class="text-slate-900 mb-3">Recommended for You</h4>
            @if($recommendedProducts->isEmpty())
                <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800" role="alert">
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




