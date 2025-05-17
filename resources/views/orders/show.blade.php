@extends('layouts.frontapp')

@section('content')
<div class="max-w-3xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl font-bold mb-6">Order #{{ $order->id }}</h1>

  <div class="bg-white shadow rounded-lg p-6 mb-8">
    <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
    <p class="text-gray-700 whitespace-pre-wrap">{{ $order->shipping_address }}</p>
  </div>

  <div class="bg-white shadow rounded-lg p-6 mb-8">
    <h2 class="text-xl font-semibold mb-4">Items</h2>
    <ul class="divide-y divide-gray-200">
      @foreach($order->items as $item)
        <li class="py-4 flex justify-between">
          <span>{{ $item->product->name }} (×{{ $item->quantity }})</span>
          <span>KES {{ number_format($item->total_price,2) }}</span>
        </li>
      @endforeach
      <li class="pt-4 flex justify-between font-semibold">
        <span>Total</span>
        <span>KES {{ number_format($order->total_amount,2) }}</span>
      </li>
    </ul>
  </div>

  <a href="{{ route('orders.index') }}"
     class="inline-block bg-primary text-white px-6 py-3 rounded hover:bg-primary-dark">
     Back to Orders
  </a>
</div>
@endsection
