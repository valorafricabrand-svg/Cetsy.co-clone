@extends('theme.'.theme().'.layouts.app')

@section('title', 'Order Details')

@section('main')
<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>

      <div class="col-span-12 lg:col-span-9 space-y-4">
        @php
          $statusClass = match($order->status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'processing' => 'bg-sky-100 text-sky-700',
            'shipped' => 'bg-indigo-100 text-indigo-700',
            'delivered', 'completed' => 'bg-emerald-100 text-emerald-700',
            'cancelled', 'refunded' => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-200 text-slate-700',
          };
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h1 class="text-2xl font-semibold text-slate-900">Order #{{ $order->id }}</h1>
              <div class="mt-1 text-sm text-slate-500">Placed on {{ optional($order->created_at)->format('M j, Y g:i A') }}</div>
            </div>
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
          </div>
          <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Back to Orders</a>
            <a href="{{ route('orders.chat.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">Open Chat</a>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="mb-3 text-lg font-semibold text-slate-900">Shipping Address</h2>
          <p class="whitespace-pre-wrap text-sm text-slate-700">{{ $order->shipping_address ?: 'No shipping address provided.' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-3">
            <h2 class="text-lg font-semibold text-slate-900">Items</h2>
          </div>

          <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
              <thead class="bg-slate-50 text-slate-700">
                <tr>
                  <th class="px-5 py-3 text-left font-semibold">Product</th>
                  <th class="px-5 py-3 text-left font-semibold">Qty</th>
                  <th class="px-5 py-3 text-right font-semibold">Line Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                @foreach($order->items as $item)
                  <tr>
                    <td class="px-5 py-3 text-slate-800">{{ optional($item->product)->name ?? 'Product removed' }}</td>
                    <td class="px-5 py-3 text-slate-700">{{ $item->quantity }}</td>
                    <td class="px-5 py-3 text-right font-medium text-slate-900">{{ money((float)($item->total_price ?? 0)) }}</td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr class="bg-slate-50">
                  <td class="px-5 py-3 text-right text-sm font-semibold text-slate-900" colspan="2">Total</td>
                  <td class="px-5 py-3 text-right text-sm font-semibold text-slate-900">{{ money((float)$order->total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="space-y-3 p-4 md:hidden">
            @foreach($order->items as $item)
              <div class="rounded-xl border border-slate-200 p-4">
                <div class="text-sm font-semibold text-slate-900">{{ optional($item->product)->name ?? 'Product removed' }}</div>
                <div class="mt-1 text-sm text-slate-600">Qty: {{ $item->quantity }}</div>
                <div class="mt-1 text-sm font-medium text-slate-900">{{ money((float)($item->total_price ?? 0)) }}</div>
              </div>
            @endforeach
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-900">
              Total: {{ money((float)$order->total_amount) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
