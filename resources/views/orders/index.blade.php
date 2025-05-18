@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl font-bold mb-6">My Orders</h1>

  @if($orders->isEmpty())
    <p class="text-gray-600">You have no orders yet.</p>
    <a href="{{ route('products.index') }}"
       class="mt-4 inline-block bg-primary text-white px-6 py-2 rounded hover:bg-primary-dark">
       Shop Now
    </a>
  @else
    <ul class="space-y-6">
      @foreach($orders as $order)
        <li class="bg-white shadow rounded-lg overflow-hidden">
          <a href="{{ route('orders.show',$order) }}" class="block p-6 hover:bg-gray-50">
            <div class="flex justify-between">
              <span class="font-semibold">Order #{{ $order->id }}</span>
              <span class="text-sm text-gray-500">{{ $order->created_at->format('M j, Y') }}</span>
            </div>
            <div class="mt-2 flex justify-between">
              <span class="text-gray-700">Items: {{ $order->items->count() }}</span>
              <span class="text-gray-900 font-semibold">KES {{ number_format($order->total_amount,2) }}</span>
            </div>
            <div class="mt-1 text-sm">
              Status: <span class="font-medium">{{ ucfirst($order->status) }}</span>
            </div>
          </a>
        </li>
      @endforeach
    </ul>
  @endif
</div>
@endsection
