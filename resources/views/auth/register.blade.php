{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.frontapp')

@section('content')
<div class="max-w-md mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create your {{ config('app.name') }} account</h2>

  <form method="POST" action="{{ route('register') }}" class="space-y-6 bg-white p-8 shadow rounded-lg">
    @csrf

    <!-- Account Type -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
      <div class="flex space-x-4">
        <label class="inline-flex items-center">
          <input type="radio" name="role" value="buyer" checked
                 class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
          <span class="ml-2 text-gray-700">Buyer</span>
        </label>
        <label class="inline-flex items-center">
          <input type="radio" name="role" value="seller"
                 class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
          <span class="ml-2 text-gray-700">Seller</span>
        </label>
      </div>
      @error('role')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <!-- Name -->
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
      <input id="name" name="name" type="text" required autofocus
             value="{{ old('name') }}"
             class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
      @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <!-- Email Address -->
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
      <input id="email" name="email" type="email" required
             value="{{ old('email') }}"
             class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
      @error('email')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <!-- Password -->
    <div>
      <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
      <input id="password" name="password" type="password" required
             autocomplete="new-password"
             class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
      @error('password')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <!-- Confirm Password -->
    <div>
      <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
      <input id="password_confirmation" name="password_confirmation" type="password" required
             autocomplete="new-password"
             class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
      @error('password_confirmation')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div class="flex items-center justify-between">
      <a href="{{ route('login') }}" class="text-sm text-green-600 hover:underline">
        Already have an account?
      </a>
      <button type="submit"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        Register
      </button>
    </div>
  </form>
</div>
@endsection
