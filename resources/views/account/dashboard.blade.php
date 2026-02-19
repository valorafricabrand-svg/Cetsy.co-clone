@extends('theme.'.theme().'.layouts.app')
@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 lg:col-span-9">
                @if (session('status') === 'verification-link-sent')
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                        A new verification link was sent to your email.
                    </div>
                @endif

                @if (! auth()->user()->hasVerifiedEmail())
                    <div class="mb-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 sm:flex-row sm:items-center sm:justify-between" role="alert">
                        <div>Your email is not verified. Please check your inbox for a verification link.</div>
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-slate-900 transition hover:bg-amber-400">Resend verification email</button>
                        </form>
                    </div>
                @endif

                <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <h3 class="text-2xl font-semibold text-slate-900">Dashboard</h3>
                    <p class="mt-1 text-slate-500">Welcome back, <strong>{{ Auth::user()->name }}</strong>.</p>
                </div>

                <div class="mb-4 grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-4">
                        <a href="{{ route('account.orders') }}" class="block h-full rounded-2xl border border-slate-200 bg-white text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex flex-col items-center justify-center p-5">
                                <div class="mb-3"><i class="fas fa-shopping-cart text-4xl text-sky-600"></i></div>
                                <h5 class="text-lg font-bold text-sky-700">Orders</h5>
                                <p class="mt-1 text-sm text-slate-500">{{ $ordersCount }} {{ \Illuminate\Support\Str::plural('Order', $ordersCount) }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <a href="{{ route('wishlist') }}" class="block h-full rounded-2xl border border-slate-200 bg-white text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex flex-col items-center justify-center p-5">
                                <div class="mb-3"><i class="fas fa-heart text-4xl text-emerald-600"></i></div>
                                <h5 class="text-lg font-bold text-emerald-700">Wishlist</h5>
                                <p class="mt-1 text-sm text-slate-500">{{ $wishlistCount }} {{ \Illuminate\Support\Str::plural('Item', $wishlistCount) }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <a href="{{ route('wallet.index') }}" class="block h-full rounded-2xl border border-slate-200 bg-white text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex flex-col items-center justify-center p-5">
                                <div class="mb-3"><i class="fas fa-wallet text-4xl text-amber-600"></i></div>
                                <h5 class="text-lg font-bold text-amber-700">Wallet</h5>
                                <p class="mt-1 text-sm text-slate-500">{{ money($accountBalance) }}</p>
                            </div>
                        </a>
                    </div>
                </div>

                <section class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h4 class="text-xl font-semibold text-slate-900">Recent Orders</h4>
                    </div>
                    <div class="p-4">
                        @if($recentOrders->isEmpty())
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
                                You have no recent orders.
                            </div>
                        @else
                            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                                @foreach($recentOrders as $order)
                                    <li class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Order #{{ $order->id }}</div>
                                            <div class="text-sm text-slate-500">Date: {{ $order->created_at->format('d M, Y') }}</div>
                                            <div class="text-sm text-slate-700">
                                                Status: {{ ucfirst($order->status) }} | Total: {{ money($order->total_amount ?? $order->total) }}
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('buyer.orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View Details</a>
                                            @if($order->status === 'pending')
                                                <a href="{{ route('pay_now', $order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">Pay Now</a>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h4 class="text-xl font-semibold text-slate-900">Recommended for You</h4>
                    </div>
                    <div class="p-4">
                        @if($recommendedProducts->isEmpty())
                            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800" role="alert">
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
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
