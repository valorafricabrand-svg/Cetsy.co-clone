@extends('layouts.app')

@section('title', 'KYC Management')

@section('content')
<div class="content">
    <h3 class="mb-4 text-success">My Payments</h3>

    @if(count($payments) > 0)
        <div class="table-responsive shadow-sm border rounded">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Shop</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total ({{ get_currency() }})</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $pay)
                        <tr class="text-center">
                            <td>{{ $pay->id }}</td>
                            <td>
                                <a href="{{ route('seller.orders.show', $pay->order_id) }}" class="text-primary text-decoration-underline">
                                    #{{ $pay->order_id }}
                                </a>
                            </td>
                            <td>
                                {{ $pay->shop_id }} -
                                {{ optional($pay->shop)->name ?? 'N/A' }}
                            </td>
                            <td>{{ optional($pay->created_at)->format('d M Y') ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $pay->payment_status == 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($pay->payment_status) }}
                                </span>
                            </td>
                            <td>{{ money($pay->total_amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info text-center mt-4" role="alert">
            <p class="mb-0">You have no payments at the moment.</p>
        </div>
    @endif
</div>
@endsection

