@extends('layouts.app')

@section('title', 'Shop Orders')

@push('styles')
<style>
    .badge.text-capitalize { text-transform: capitalize; }
    .status-pill.active { text-decoration: none; }
</style>
@endpush

@section('content')
<div class="content">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2 class="mb-0 text-success">
            <i class="fa-solid fa-cart-shopping me-2"></i>
            Orders for {{ $user->name }}
        </h2>
    </div>

    {{-- FILTERS: Status pills + Search by ID --}}
    <form method="GET" action="{{ route('seller.orders.index') }}" class="row gx-2 gy-2 align-items-center mb-4">
        {{-- Preserve status --}}
        <input type="hidden" name="status" value="{{ $currentStatus }}">

        <div class="col-auto">
            <div class="btn-group" role="group" aria-label="Order status filters">
                @php
                    $statuses = [
                        'all'        => 'All',
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'completed'  => 'Completed',
                        'cancelled'  => 'Cancelled',
                    ];
                @endphp
                @foreach($statuses as $key => $label)
                    @php
                        $count    = $statusCounts[$key] ?? 0;
                        $isActive = $currentStatus === $key;
                    @endphp
                    <a href="{{ route('seller.orders.index', ['status'=>$key, 'search'=>$searchId]) }}"
                       class="badge {{ $isActive ? 'bg-primary' : 'bg-secondary' }} text-capitalize me-1 status-pill">
                        {{ $label }} ({{ $count }})
                    </a>
                @endforeach
            </div>
        </div>

        <div class="col-auto">
            <div class="input-group">
                <input type="search"
                       name="search"
                       class="form-control"
                       placeholder="Search by Order ID"
                       value="{{ $searchId }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fa-solid fa-search"></i>
                </button>
            </div>
        </div>
    </form>

    @if ($orders->isNotEmpty())
        {{-- ORDERS TABLE --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Tracking No</th>
                                <th>Placed</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $row      = $orders->firstItem() + $loop->index;
                                    $qtyTotal = $order->items->sum('quantity');
                                    $symbol   = get_currency();
                                @endphp
                                <tr>
                                    <th scope="row">{{ $row }}</th>
                                    <td>{{ $order->full_name }}</td>
                                    <td>{{ $order->phone ?? '—' }}</td>
                                    <td class="text-center">{{ $qtyTotal }}</td>
                                    <td class="text-end">{{ $symbol }} {{ number_format($order->total_amount,2) }}</td>
                                    <td>
                                        <a href="{{ route('seller.orders.index', ['status'=>$order->status, 'search'=>$searchId]) }}"
                                           class="badge {{ $order->getStatusBadgeClass() }} text-capitalize">
                                            {{ $order->status }}
                                        </a>
                                        @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                                            <br><small class="text-danger">{{ Str::limit($order->cancel_reason, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $order->tracking_no ?? '—' }}</td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('seller.orders.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           aria-label="View order {{ $order->id }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        
                                        @if(in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING]))
                                            <button class="btn btn-sm btn-outline-danger ms-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelModal-{{ $order->id }}"
                                                    aria-label="Cancel order {{ $order->id }}">
                                                <i class="fa-solid fa-times-circle"></i>
                                            </button>
                                            @include('seller.orders.modals.cancel')
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION --}}
            @if ($orders->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-center">
                        {{ $orders->appends(request()->only('status','search'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- EMPTY STATE --}}
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i>
            No orders found for your shop.
        </div>
    @endif
</div>
@endsection
