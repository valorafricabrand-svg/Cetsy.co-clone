@extends('layouts.frontapp')

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl font-bold mb-6">Your Cart</h1>

  @if($items->isEmpty())
    <p class="text-gray-600">Your cart is empty.</p>
    <a href="{{ route('listings') }}"
       class="mt-4 inline-block bg-primary text-white px-6 py-2 rounded hover:bg-primary-dark">
       Browse Products
    </a>
  @else
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($items as $item)
            <tr>
              <td class="px-6 py-4 flex items-center space-x-4">
                @if($img = $item->product->media->first())
                  <img src="{{ asset('storage/'.$img->url) }}" class="w-16 h-16 object-cover rounded">
                @endif
                <div>
                  <a href="{{ route('products.show', $item->product) }}"
                     class="font-semibold text-gray-800 hover:underline">
                    {{ $item->product->name }}
                  </a>
                </div>
              </td>
              <td class="px-6 py-4">KES {{ number_format($item->product->price,2) }}</td>
              <td class="px-6 py-4">
                <form action="{{ route('cart.update', $item->product->id) }}" 
                      method="POST" class="flex items-center space-x-2">
                  @csrf
                  @method('PATCH')
                  <input type="number" name="quantity" value="{{ $item->quantity }}"
                         min="1"
                         class="w-20 border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary">
                  <button type="submit" class="text-blue-600 hover:underline">
                    Update
                  </button>
                </form>
              </td>
              <td class="px-6 py-4">
                KES {{ number_format($item->product->price * $item->quantity,2) }}
              </td>
              <td class="px-6 py-4">
                <form action="{{ route('cart.destroy', $item->product->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:underline">
                    Remove
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6 flex justify-between items-center">
      <p class="text-xl font-semibold">Subtotal: KES {{ number_format($subtotal,2) }}</p>
      <a href="{{ route('checkout.index') }}"
         class="bg-primary text-white px-6 py-3 rounded hover:bg-primary-dark">
         Proceed to Checkout
      </a>
    </div>
  @endif
</div>
@endsection
