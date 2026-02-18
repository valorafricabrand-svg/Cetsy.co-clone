{{-- resources/views/account/orders_created.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Orders Created')

@section('main')
<section class="py-5" style="margin-top:100px;">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    @if(session('success'))
      <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
      <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">{{ session('error') }}</div>
    @endif

    <h3 class="mb-4 text-2xl font-semibold text-slate-900">Your Orders by Shop</h3>
    <p class="text-slate-500">We created separate orders for each shop in your cart. You can review and pay each order individually.</p>

    <div class="overflow-x-auto hidden md:block">
      <table class="min-w-full divide-y divide-slate-200 text-sm border border-slate-200 align-middle">
        <thead class="bg-slate-50">
          <tr>
            <th>Order #</th>
            <th>Shop</th>
            <th>Status</th>
            <th class="text-right">Subtotal</th>
            <th class="text-right">Shipping</th>
            <th class="text-right">Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($orders as $order)
            <tr>
              <td>{{ $order->id }}</td>
              <td>
                @if($order->shop)
<a href="{{ route('shop.show', $order->shop->slug) }}">{{ $order->shop->name }}</a>
                @else
                  <span class="text-slate-500">Unknown shop</span>
                @endif
              </td>
              <td>{{ ucfirst($order->status) }}</td>
              <td class="text-right">{{ get_currency() }} {{ number_format((float)$order->subtotal,2) }}</td>
              <td class="text-right">{{ get_currency() }} {{ number_format((float)$order->shipping_cost,2) }}</td>
              <td class="text-right font-semibold">{{ get_currency() }} {{ number_format((float)$order->total_amount,2) }}</td>
              <td>
                @if(method_exists($order,'isPaid') && $order->isPaid())
                  <a class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" href="{{ route('buyer.orders.show', $order->id) }}">View</a>
                @else
                  <a class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500" href="{{ route('pay_now', $order->id) }}">Pay Now</a>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Mobile list (cards) --}}
    <div class="block md:hidden mb-3">
      <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
        @foreach($orders as $order)
          @php
            $currency = get_currency();
          @endphp
          <div class="px-4 py-3">
            <div class="flex justify-between items-start mb-1">
              <div class="font-semibold">#{{ $order->id }}</div>
              <div class="text-xs">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ method_exists($order,'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-slate-200 text-slate-700' }}">
                  {{ ucfirst($order->status) }}
                </span>
              </div>
            </div>
            <div class="mb-2 truncate">
              <span class="text-slate-500 text-xs">Shop:</span>
              @if($order->shop)
<a href="{{ route('shop.show', $order->shop->slug) }}" class="text-slate-700 hover:text-emerald-700">{{ $order->shop->name }}</a>
              @else
                <span class="text-slate-500">Unknown shop</span>
              @endif
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="text-xs text-slate-500">Subtotal</div>
              <div>{{ $currency }} {{ number_format((float)$order->subtotal,2) }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="text-xs text-slate-500">Shipping</div>
              <div>{{ $currency }} {{ number_format((float)$order->shipping_cost,2) }}</div>
            </div>
            <div class="flex justify-between items-center">
              <div class="font-semibold">Total</div>
              <div class="font-semibold">{{ $currency }} {{ number_format((float)$order->total_amount,2) }}</div>
            </div>
            <div class="mt-2">
              @if(method_exists($order,'isPaid') && $order->isPaid())
                <a class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" href="{{ route('buyer.orders.show', $order->id) }}">View</a>
              @else
                <a class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500" href="{{ route('pay_now', $order->id) }}">Pay Now</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="mt-3">
      <a class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" href="{{ route('account.orders') }}">Go to My Orders</a>
    </div>
  </div>
  </section>
@endsection


