@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="content py-4">
    <div class="container-xxl">

        {{-- =================== WELCOME =================== --}}
        <div class="mb-4">
            <h3 class="text-dark mb-1">Dashboard</h3>
            <p class="text-muted">
                Welcome back, <strong>{{ Auth::user()->name }}</strong>!
            </p>
        </div>

        {{-- =================== ACCOUNT OVERVIEW =================== --}}
        <div class="row g-4 mb-4">

            {{-- ORDERS --}}
            <div class="col-md-4">
                <a href="{{ route('account.orders') }}"
                   class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold text-primary">Orders</h5>
                        <p class="fs-5 text-muted mb-0">
                            {{ $ordersCount }} {{ Str::plural('Order', $ordersCount) }}
                        </p>
                    </div>
                </a>
            </div>

            {{-- WISHLIST --}}
            <div class="col-md-4">
                <a href="{{ route('wishlist') }}"
                   class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-heart fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title fw-bold text-success">Wishlist</h5>
                        <p class="fs-5 text-muted mb-0">
                            {{ $wishlistCount }} {{ Str::plural('Item', $wishlistCount) }}
                        </p>
                    </div>
                </a>
            </div>

            {{-- WALLET --}}
            <div class="col-md-4">
                <a href="{{ url('wallet') }}"
                   class="card shadow-sm border-0 h-100 text-center text-decoration-none link-hover">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-wallet fa-3x text-warning"></i>
                        </div>
                        <h5 class="card-title fw-bold text-warning">Account&nbsp;Balance</h5>
                        <p class="fs-5 text-muted mb-0">
                            {{ get_currency() }} {{ number_format(wallet(), 2) }}
                        </p>
                    </div>
                </a>
            </div>
        </div>

        {{-- =================== RECENT ORDERS =================== --}}
       {{-- =================== RECENT ORDERS =================== --}}
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
                            <th scope="col">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Total</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="fw-semibold">#{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('d M Y') }}</td>

                                {{-- status badge (reuse helper if you have one) --}}
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

                                {{-- actions --}}
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('buyer.orders.show', $order->id) }}"
                                           class="btn btn-outline-secondary btn-sm">
                                            View
                                        </a>

                                        @if($order->status === 'pending')
                                            <a href="{{ route('pay_now', $order->id) }}"
                                               class="btn btn-primary btn-sm">
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
</div>


    </div>
</div>
@endsection
