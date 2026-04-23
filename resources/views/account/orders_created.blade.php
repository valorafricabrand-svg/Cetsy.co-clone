{{-- resources/views/account/orders_created.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Orders Created')

@section('main')
<section class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>

      <div class="col-span-12 lg:col-span-9 space-y-4">
        @if(session('success'))
          <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{!! session('success') !!}</div>
        @endif
        @if(session('error'))
          <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
          <h3 class="text-2xl font-semibold text-slate-900">Your Orders by Shop</h3>
          <p class="mt-1 text-slate-500">We created separate orders for each shop in your cart. Review and pay each order individually.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
              <thead class="bg-slate-50 text-slate-700">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">Order #</th>
                  <th class="px-4 py-3 text-left font-semibold">Shop</th>
                  <th class="px-4 py-3 text-left font-semibold">Status</th>
                  <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
                  <th class="px-4 py-3 text-right font-semibold">Shipping</th>
                  <th class="px-4 py-3 text-right font-semibold">Total</th>
                  <th class="px-4 py-3 text-left font-semibold">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                @foreach($orders as $order)
                  <tr>
                    <td class="px-4 py-3">#{{ $order->id }}</td>
                    <td class="px-4 py-3">
                      @if($order->shop)
                        <a href="{{ localized_route('shop.show', $order->shop->slug ?: $order->shop->getKey()) }}" class="font-medium text-slate-700 hover:text-emerald-700">{{ $order->shop->name }}</a>
                      @else
                        <span class="text-slate-500">Unknown shop</span>
                      @endif
                    </td>
                    <td class="px-4 py-3">{{ ucfirst($order->status) }}</td>
                    <td class="px-4 py-3 text-right">{{ get_currency() }} {{ number_format((float)$order->subtotal,2) }}</td>
                    <td class="px-4 py-3 text-right">{{ get_currency() }} {{ number_format((float)$order->shipping_cost,2) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ get_currency() }} {{ number_format((float)$order->total_amount,2) }}</td>
                    <td class="px-4 py-3">
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

          <div class="block p-4 md:hidden">
            <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
              @foreach($orders as $order)
                @php
                  $currency = get_currency();
                @endphp
                <div class="px-4 py-3">
                  <div class="mb-1 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <div class="font-semibold text-slate-900">#{{ $order->id }}</div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ method_exists($order,'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-slate-200 text-slate-700' }}">
                      {{ ucfirst($order->status) }}
                    </span>
                  </div>

                  <div class="mb-2 truncate text-sm">
                    <span class="text-slate-500">Shop:</span>
                    @if($order->shop)
                      <a href="{{ localized_route('shop.show', $order->shop->slug ?: $order->shop->getKey()) }}" class="text-slate-700 hover:text-emerald-700">{{ $order->shop->name }}</a>
                    @else
                      <span class="text-slate-500">Unknown shop</span>
                    @endif
                  </div>

                  <div class="space-y-1 text-sm">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                      <div class="text-slate-500">Subtotal</div>
                      <div>{{ $currency }} {{ number_format((float)$order->subtotal,2) }}</div>
                    </div>
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                      <div class="text-slate-500">Shipping</div>
                      <div>{{ $currency }} {{ number_format((float)$order->shipping_cost,2) }}</div>
                    </div>
                    <div class="flex flex-col gap-1 font-semibold sm:flex-row sm:items-center sm:justify-between">
                      <div>Total</div>
                      <div>{{ $currency }} {{ number_format((float)$order->total_amount,2) }}</div>
                    </div>
                  </div>

                  <div class="mt-3">
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
        </div>

        <div>
          <a class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" href="{{ route('account.orders') }}">Go to My Orders</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
