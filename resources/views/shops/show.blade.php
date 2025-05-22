@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl py-8 space-y-6">

  {{-- Header: Logo + Name + Edit Button --}}
  <div class="flex items-center justify-between">
    <div class="flex items-center space-x-4">
      @if($shop->logo_url)
        <img src="{{ $shop->logo_url }}"
             alt="{{ $shop->name }} logo"
             class="w-16 h-16 object-cover rounded-full">
      @endif
      <div>
        <h1 class="text-3xl font-bold">{{ $shop->name }}</h1>
        <p class="text-gray-600">Owned by {{ $shop->user->name }}</p>
      </div>
    </div>

    {{-- Edit button: only the owner --}}
    @if(Auth::id() === $shop->user_id)
      <a href="{{ route('shops.edit', $shop) }}"
         class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
        Edit Shop
      </a>
    @endif
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="p-4 bg-green-100 text-green-800 rounded">
      {{ session('success') }}
    </div>
  @endif

  {{-- Bio --}}
  @if($shop->bio)
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-2">About This Shop</h2>
      <p class="text-gray-700">{{ $shop->bio }}</p>
    </div>
  @endif

  {{-- Preferences --}}
  <div class="bg-white shadow rounded-lg p-6 grid grid-cols-2 gap-4">
    <div>
      <h3 class="font-medium">Language</h3>
      <p class="text-gray-700">{{ $shop->language }}</p>
    </div>
    <div>
      <h3 class="font-medium">Country</h3>
      <p class="text-gray-700">{{ $shop->country }}</p>
    </div>
    <div>
      <h3 class="font-medium">Currency</h3>
      <p class="text-gray-700">{{ $shop->currency }}</p>
    </div>
    <div>
      <h3 class="font-medium">Shop URL</h3>
      <a href="{{ route('shops.show', $shop) }}" class="text-green-600 hover:underline">
        {{ url('shop/' . $shop->slug) }}
      </a>
    </div>
  </div>

  {{-- Payment Details --}}
  <div class="bg-white shadow rounded-lg p-6 grid grid-cols-2 gap-4">
    <div>
      <h3 class="font-medium">Bank Account #</h3>
      <p class="text-gray-700">{{ $shop->bank_account }}</p>
    </div>
    <div>
      <h3 class="font-medium">Routing #</h3>
      <p class="text-gray-700">{{ $shop->routing_number }}</p>
    </div>
  </div>

  {{-- Billing Address --}}
  <div class="bg-white shadow rounded-lg p-6 space-y-2">
    <h3 class="text-xl font-semibold">Billing Address</h3>
    <p class="text-gray-700">{{ $shop->address }}</p>
    <p class="text-gray-700">{{ $shop->city }}, {{ $shop->postal }}</p>
  </div>

  {{-- Security --}}
  <div class="bg-white shadow rounded-lg p-6 flex items-center space-x-3">
    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 2a6 6 0 016 6v2a2 2 0 01-2 2h-1v2a2 2 0 11-4 0v-2H6a2 2 0 01-2-2V8a6 6 0 016-6z"/>
    </svg>
    <span class="text-gray-700">
      Two-Factor Authentication:
      @if($shop->enable_2fa)
        <strong class="text-green-700">Enabled</strong>
      @else
        <strong class="text-red-600">Disabled</strong>
      @endif
    </span>
  </div>

  {{-- Placeholder for future listings --}}
  <div class="text-center text-gray-500">
    (Product listings will appear here soon…)
  </div>
</div>
@endsection
