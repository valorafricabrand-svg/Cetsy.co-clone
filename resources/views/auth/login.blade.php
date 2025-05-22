{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.frontapp')

@section('content')
<div class="max-w-md mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Log in to {{ config('app.name') }}</h2>

  {{-- Session Status --}}
  @if(session('status'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
      {{ session('status') }}
    </div>
  @endif

  <form method="POST" action="{{ route('login') }}" class="space-y-6 bg-white p-8 shadow rounded-lg">
    @csrf

    <!-- Email Address -->
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
      <input id="email" name="email" type="email" required autofocus
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
             autocomplete="current-password"
             class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
      @error('password')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <!-- Remember Me -->
    <div class="flex items-center">
      <input id="remember_me" name="remember" type="checkbox"
             class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
      <label for="remember_me" class="ml-2 block text-sm text-gray-600">
        Remember me
      </label>
    </div>

    <div class="flex items-center justify-between">
      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}"
           class="text-sm text-green-600 hover:underline">
          Forgot your password?
        </a>
      @endif

      <button type="submit"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        Log in
      </button>
    </div>

    <div class="text-center text-sm text-gray-600">
      Don't have an account?
      <a href="{{ route('register') }}" class="text-green-600 hover:underline font-medium">
        Create one
      </a>
    </div>
  </form>
</div>
@endsection
