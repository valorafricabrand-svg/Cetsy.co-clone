@extends('layouts.frontapp')

@section('content')
<div class="max-w-3xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
  <h1 class="text-3xl font-bold mb-6">Thank You!</h1>

  <p class="mb-4">Your order <strong>#{{ $order->id }}</strong> has been placed successfully.</p>
  <p class="mb-8">Total paid: <strong>KES {{ number_format($order->total, 2) }}</strong></p>

  <a href="{{ route('home') }}"
     class="bg-primary text-white px-6 py-3 rounded hover:bg-primary-dark">
    Continue Shopping
  </a>
</div>
@endsection
