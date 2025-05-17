@extends('layouts.frontapp')

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Cart</h1>

    <div class="bg-white shadow rounded-lg p-8 text-center">
        <p class="text-gray-600 text-lg">
            Your shopping cart is currently empty.
        </p>
        <a href="{{ route('products.index') }}"
           class="mt-6 inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
            Browse Products
        </a>
    </div>
</div>
@endsection
