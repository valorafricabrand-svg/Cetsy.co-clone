@extends('layouts.frontapp')

@section('content')
<div class="max-w-3xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl font-bold mb-6">Checkout</h1>

  <div class="bg-white shadow rounded-lg p-6 mb-8">
    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
    <ul class="divide-y divide-gray-200">
      @foreach($items as $item)
        <li class="py-4 flex justify-between">
          <span>{{ $item->product->name }} (×{{ $item->quantity }})</span>
          <span>KES {{ number_format($item->product->price * $item->quantity,2) }}</span>
        </li>
      @endforeach
      <li class="pt-4 flex justify-between font-semibold">
        <span>Subtotal</span>
        <span>KES {{ number_format($subtotal,2) }}</span>
      </li>
    </ul>
  </div>

  <form action="{{ route('checkout.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
    @csrf
    <div class="mb-4">
      <label for="shipping_address" class="block font-medium mb-1">Shipping Address</label>
      <textarea name="shipping_address" id="shipping_address" rows="4"
                class="w-full border rounded px-3 py-2">{{ old('shipping_address') }}</textarea>
      @error('shipping_address')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit"
            class="bg-primary text-white px-6 py-3 rounded hover:bg-primary-dark">
      Place Order
    </button>
  </form>
</div>
@endsection
