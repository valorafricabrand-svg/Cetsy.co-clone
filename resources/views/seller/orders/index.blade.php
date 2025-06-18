{{-- resources/views/seller/orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Shop Orders')

@section('content')
<div class="content">
    <h2 class="mb-4 text-success">Orders for {{ $user->name }}</h2>

    @if ($orders->isNotEmpty())
        <div class="table-responsive shadow-sm border rounded">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Tracking No</th>
                        <th>Placed</th>
                        <th class="text-nowrap">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($orders as $order)
                        @php
                            $row = $orders->firstItem() + $loop->index;
                            $qtyTotal = $order->items->sum('quantity');
                            $symbol = config('app.currency_symbol', 'KES');
                        @endphp

                        <tr>
                            <td>{{ $row }}</td>
                            <td>{{ $order->full_name }}</td>
                            <td>{{ $order->phone ?? '—' }}</td>
                            <td class="text-center">{{ $qtyTotal }}</td>
                            <td class="text-end">{{ $symbol }} {{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $order->getStatusBadgeClass() }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>{{ $order->tracking_no ?? '—' }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td class="d-flex gap-1">
                                {{-- View always available --}}
                                <a href="{{ route('seller.orders.show', $order) }}" class="btn btn-sm btn-outline-success">
                                    View
                                </a>
                                {{-- Additional actions can be added here --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info mt-4">No orders found for your shop.</div>
    @endif
</div>
@endsection
