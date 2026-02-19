@extends('theme.'.theme().'.layouts.app')

@section('title', 'My Payments')

@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 lg:col-span-9 space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <h3 class="text-2xl font-semibold text-slate-900">My Payments</h3>
                    <p class="mt-1 text-slate-500">Track payments for your orders and fees.</p>
                </div>

                @if(count($payments) > 0)
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="hidden overflow-x-auto md:block">
                            <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
                                <thead class="bg-slate-50 text-slate-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">Payment ID</th>
                                        <th class="px-4 py-3 text-left font-semibold">Order ID</th>
                                        <th class="px-4 py-3 text-left font-semibold">Shop</th>
                                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                                        <th class="px-4 py-3 text-left font-semibold">Type</th>
                                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                                        <th class="px-4 py-3 text-right font-semibold">Total ({{ get_currency() }})</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($payments as $pay)
                                        <tr>
                                            <td class="px-4 py-3">{{ $pay->id }}</td>
                                            <td class="px-4 py-3">
                                                @if(!empty($pay->order_id))
                                                    <a href="{{ route('buyer.orders.show', $pay->order_id) }}" class="font-medium text-sky-700 underline hover:text-sky-600">
                                                        #{{ $pay->order_id }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ $pay->shop_id }} - {{ optional($pay->shop)->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3">{{ optional($pay->created_at)->format('d M Y') ?? 'N/A' }}</td>
                                            <td class="px-4 py-3">
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
                                            <td class="px-4 py-3">
                                                @php
                                                    $ok = in_array($pay->payment_status, ['successful', 'completed'], true);
                                                    $statusClass = $ok ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800';
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                                                    {{ ucfirst($pay->payment_status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">{{ money($pay->total_amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="space-y-3 p-4 md:hidden">
                            @foreach($payments as $pay)
                                @php
                                  $labelMap = [
                                    'listing_fee'        => 'Listing Fee',
                                    'online_payment_fee' => 'Online Payment Fee',
                                    'subscription_fee'   => 'Subscription Fee',
                                    'order_payment'      => 'Order Payment',
                                  ];
                                  $typeLabel = $labelMap[$pay->payment_name ?? ''] ?? ($pay->order_id ? 'Order Payment' : 'Payment');
                                  $ok = in_array($pay->payment_status, ['successful','completed'], true);
                                  $statusClass = $ok ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800';
                                @endphp

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="mb-2 flex items-center justify-between">
                                        <div class="text-sm font-semibold text-slate-900">Payment #{{ $pay->id }}</div>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ ucfirst($pay->payment_status) }}</span>
                                    </div>

                                    <div class="space-y-1 text-sm text-slate-700">
                                        <div><span class="text-slate-500">Order:</span>
                                            @if(!empty($pay->order_id))
                                                <a href="{{ route('buyer.orders.show', $pay->order_id) }}" class="font-medium text-sky-700 underline hover:text-sky-600">#{{ $pay->order_id }}</a>
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <div><span class="text-slate-500">Shop:</span> {{ optional($pay->shop)->name ?? 'N/A' }}</div>
                                        <div><span class="text-slate-500">Date:</span> {{ optional($pay->created_at)->format('d M Y') ?? 'N/A' }}</div>
                                        <div><span class="text-slate-500">Type:</span> {{ $typeLabel }}</div>
                                        <div class="pt-1 font-semibold"><span class="text-slate-500 font-normal">Total:</span> {{ money($pay->total_amount) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-center text-sm text-sky-800" role="alert">
                        You have no payments at the moment.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
