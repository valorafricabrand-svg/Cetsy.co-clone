@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Admin Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white overflow-hidden shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Users</h3>
        <p class="mt-4 text-3xl">{{ \App\Models\User::count() }}</p>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Orders</h3>
        <p class="mt-4 text-3xl">{{ \App\Models\Order::count() }}</p>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Revenue (KES)</h3>
        <p class="mt-4 text-3xl">
          {{ number_format(\App\Models\Order::sum('total'), 2) }}
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
