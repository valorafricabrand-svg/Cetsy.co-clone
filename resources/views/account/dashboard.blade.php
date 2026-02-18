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
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-slate-900 transition hover:bg-amber-400">Resend verification email</button>
                </form>
            </div>
        @endif
        <!-- Welcome Section -->
        <div class="mb-4">
            <h3 class="text-2xl font-semibold text-slate-900">Dashboard</h3>
            <p class="text-slate-500">Welcome back, <strong>{{ Auth::user()->name }}</strong>!</p>
        </div>

<!-- Account Overview -->
<div class="grid grid-cols-12 gap-4 mb-4">
    <div class="md:col-span-4">
        <a href="{{ route('account.orders') }}" class="block h-full rounded-2xl border border-slate-200 bg-white text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex flex-col items-center justify-center p-5">
                <div class="icon-container mb-3">
                    <i class="fas fa-shopping-cart text-4xl text-sky-600"></i>
                </div>
                <h5 class="text-lg font-bold text-sky-700">Orders</h5>
                <p class="mt-1 text-sm text-slate-500">{{ $ordersCount }} Orders</p>
            </div>
        </a>
    </div>
    <div class="md:col-span-4">
        <a href="{{ route('wishlist.index') }}" class="block h-full rounded-2xl border border-slate-200 bg-white text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex flex-col items-center justify-center p-5">
                <div class="icon-container mb-3">
                    <i class="fas fa-heart text-4xl text-emerald-600"></i>
                </div>
                <h5 class="text-lg font-bold text-emerald-700">Wishlist</h5>
                <p class="mt-1 text-sm text-slate-500">{{ $wishlistCount }} Items</p>
            </div>
        </a>
    </div>
    <div class="md:col-span-4">
        <a href="{{ url('client.wallet') }}" class="block h-full rounded-2xl border border-slate-200 bg-white text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex flex-col items-center justify-center p-5">
                <div class="icon-container mb-3">
                    <i class="fas fa-wallet text-4xl text-amber-600"></i>
                </div>
                <h5 class="text-lg font-bold text-amber-700">Account Balance</h5>
                <p class="mt-1 text-sm text-slate-500">{{ money($accountBalance) }}</p>
            </div>
        </a>
    </div>
</div>




        <!-- Recent Orders -->
        <div class="recent-orders-section">
            <h4 class="mb-3 text-xl font-semibold text-slate-900">Recent Orders</h4>
            @if($recentOrders->isEmpty())
                <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800" role="alert">
                    You have no recent orders.
                </div>
            @else
                <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                    @foreach($recentOrders as $order)
                        <div class="px-4 py-3 flex justify-between items-center">
                            <div>
                                <h6 class="mb-1 text-sm font-semibold text-slate-900">Order ID: #{{ $order->id }}</h6>
                                <p class="mb-1 text-sm text-slate-500">
                                    <strong>Date:</strong> {{ $order->created_at->format('d M, Y') }}
                                </p>
                                <p class="text-sm">
                                    <strong>Status:</strong> {{ ucfirst($order->status) }} |
                                    <strong>Total:</strong> {{ money($order->total) }}
                                </p>
                            </div>
                            @if($order->status == 'pending')

                         <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-600">View Details</a>
                         
                            <a href="{{ route('pay_now', $order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">Pay Now</a>
                        @else
                            <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View Details</a>
                        @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recommended Products -->
        <div class="recommended-products mt-5">
            <h4 class="mb-3 text-xl font-semibold text-slate-900">Recommended for You</h4>
            @if($recommendedProducts->isEmpty())
                <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800" role="alert">
                    No recommended products yet. Browse the marketplace to personalize your picks.
                </div>
            @else
                @include('theme.'.theme().'.partials.product-carousel', [
                    'items' => $recommendedProducts,
                    'showHeader' => false,
                    'wrapperTag' => 'div',
                    'wrapperClass' => 'recommended-carousel relative',
                    'containerClass' => '',
                    'seeMoreUrl' => route('listings'),
                    'seeMoreLabel' => 'Explore marketplace'
                ])
            @endif
        </div>
    </div>
</div>
@endsection




