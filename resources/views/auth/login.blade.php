@extends('theme.layouts.main')

@section('main')
<div class="container py-16">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 col-sm-10">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Log in to {{ config('app.name') }}</h2>

      {{-- Session Status --}}
      @if(session('status'))
        <div class="alert alert-success mb-4">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="bg-white p-4 shadow rounded-lg">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
          <label for="email" class="form-label">Email</label>
          <input id="email" name="email" type="email" required autofocus
                 value="{{ old('email') }}"
                 class="form-control @error('email') is-invalid @enderror">
          @error('email')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input id="password" name="password" type="password" required
                 autocomplete="current-password"
                 class="form-control @error('password') is-invalid @enderror">
          @error('password')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-4 form-check">
          <input id="remember_me" name="remember" type="checkbox"
                 class="form-check-input">
          <label for="remember_me" class="form-check-label">
            Remember me
          </label>
        </div>

        <div class="d-flex justify-content-between">
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-sm text-success">
              Forgot your password?
            </a>
          @endif

          <button type="submit" class="btn btn-success">
            Log in
          </button>
        </div>

        <div class="text-center text-sm text-muted mt-4">
          Don't have an account?
          <a href="{{ route('register') }}" class="text-success">
            Create one
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
