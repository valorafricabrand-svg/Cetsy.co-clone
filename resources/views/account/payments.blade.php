@extends('theme.'.theme().'.layouts.app')

@section('title', 'KYC Management')

@section('main')
<div class="content">
    <h3 class="mb-4 text-emerald-600">My Payments</h3>

    @if(count($payments) > 0)
        <div class="overflow-x-auto shadow-sm border rounded">
            <table class="min-w-full divide-y divide-slate-200 text-sm border border-slate-200 align-middle mb-0">
                <thead class="bg-slate-50 text-center">
                    <tr>
                        <th>Payment ID</th>
                         <th>Order ID</th>
                         <th>Shop</th>
                         <th>Date</th>
                         <th>Type</th>
                         <th>Status</th>
                         <th>Total ({{ get_currency() }})</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $pay)
                        <tr class="text-center">
                            <td>{{ $pay->id }}</td>
                            <td>
                                @if(!empty($pay->order_id))
                                    <a href="{{ route('buyer.orders.show', $pay->order_id) }}" class="text-primary text-decoration-underline">
                                        #{{ $pay->order_id }}
                                    </a>
                                @else
                                    â€”
                                @endif
                            </td>
                            <td>
                                {{ $pay->shop_id }} -
                                {{ optional($pay->shop)->name ?? 'N/A' }}
                            </td>
                            <td>{{ optional($pay->created_at)->format('d M Y') ?? 'N/A' }}</td>
                            <td>
                                @php
                                  $labelMap = [
                                    'listing_fee'        => 'Listing Fee',
                                    'online_payment_fee' => 'Online Payment Fee',
                                    'subscription_fee'   => 'Subscription Fee',
                                    'order_payment'      => 'Order Payment',
                                  ];
                                  $typeLabel = $labelMap[$pay->payment_name ?? ''] ?? ($pay->order_id ? 'Order Payment' : 'Payment');
                                @endphp
                                {{ $typeLabel }}
                            </td>
                            <td>
                                @php($ok = in_array($pay->payment_status, ['successful','completed'], true))
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $ok ? 'bg-success' : 'bg-warning text-dark' }}">
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
        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-center mt-4" role="alert">
            <p class="mb-0">You have no payments at the moment.</p>
        </div>
    @endif
</div>
@endsection





