@extends('layouts.frontapp')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Your Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white overflow-hidden shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">My Orders</h3>
        <p class="mt-4 text-3xl">{{ auth()->user()->orders()->count() }}</p>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Wishlist</h3>
        <p class="mt-4 text-3xl">{{ auth()->user()->wishlistItems()->count() }}</p>
      </div>
    </div>
  </div>
</div>
@endsection
