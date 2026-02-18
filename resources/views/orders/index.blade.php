@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="grid grid-cols-12 gap-4 justify-center">
    <div class="lg:col-span-8">

      <h1 class="mb-4">My Orders</h1>

      @if($orders->isEmpty())
        <div class="text-center py-5">
          <p class="text-base font-semibold text-slate-500 mb-4">You have no orders yet.</p>
          <a href="{{ route('products.index') }}"
             class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base">
            Shop Now
          </a>
        </div>
      @else
        <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
          @foreach($orders as $order)
            <a href="{{ route('orders.show', $order) }}"
               class="px-4 py-3 block transition hover:bg-slate-50 mb-3 shadow-sm rounded">
              <div class="flex w-full justify-between">
                <h5 class="mb-1">Order #{{ $order->id }}</h5>
                <small class="text-slate-500">{{ $order->created_at->format('M j, Y') }}</small>
              </div>
              <p class="mb-1">
                <span class="mr-3"><strong>Items:</strong> {{ $order->items->count() }}</span>
                <span><strong>Total:</strong> {{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span>
              </p>
              <small>
                <strong>Status:</strong>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium @switch($order->status) @case('pending') bg-amber-100 text-slate-900 @break @case('completed') bg-success @case('cancelled') bg-danger @default bg-slate-200 @endswitch">
                  {{ ucfirst($order->status) }}
                </span>
              </small>
            </a>
          @endforeach
        </div>

        <div class="mt-4">
          {{ $orders->links() }}
        </div>
      @endif

    </div>
  </div>
</div>
@endsection




