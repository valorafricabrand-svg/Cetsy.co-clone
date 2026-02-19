@extends('theme.'.theme().'.layouts.app')

@section('title', 'My Orders')

@section('main')
<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>

      <div class="col-span-12 lg:col-span-9 space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
          <h1 class="text-2xl font-semibold text-slate-900">My Orders</h1>
          <p class="mt-1 text-slate-500">View and track all your orders.</p>
        </div>

        @if($orders->isEmpty())
          <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <p class="mb-4 text-base font-semibold text-slate-500">You have no orders yet.</p>
            <a href="{{ route('listings') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
              Shop Now
            </a>
          </div>
        @else
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto md:block">
              <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
                <thead class="bg-slate-50 text-slate-700">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold">Order #</th>
                    <th class="px-4 py-3 text-left font-semibold">Date</th>
                    <th class="px-4 py-3 text-left font-semibold">Items</th>
                    <th class="px-4 py-3 text-left font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                  @foreach($orders as $order)
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
                    <tr>
                      <td class="px-4 py-3 font-semibold text-slate-900">#{{ $order->id }}</td>
                      <td class="px-4 py-3 text-slate-600">{{ $order->created_at->format('M j, Y') }}</td>
                      <td class="px-4 py-3 text-slate-700">{{ $order->items->count() }}</td>
                      <td class="px-4 py-3 font-medium text-slate-900">{{ get_currency() }} {{ number_format((float)$order->total_amount, 2) }}</td>
                      <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                      </td>
                      <td class="px-4 py-3">
                        <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="space-y-3 p-4 md:hidden">
              @foreach($orders as $order)
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
                <a href="{{ route('orders.show', $order) }}" class="block rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                  <div class="mb-2 flex items-start justify-between gap-3">
                    <div class="font-semibold text-slate-900">Order #{{ $order->id }}</div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                  </div>
                  <div class="space-y-1 text-sm text-slate-600">
                    <div>Date: {{ $order->created_at->format('M j, Y') }}</div>
                    <div>Items: {{ $order->items->count() }}</div>
                    <div class="font-medium text-slate-900">Total: {{ get_currency() }} {{ number_format((float)$order->total_amount, 2) }}</div>
                  </div>
                </a>
              @endforeach
            </div>
          </div>

          <div>
            {{ $orders->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
