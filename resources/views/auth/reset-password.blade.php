@extends('theme.layouts.main')

@section('main')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 col-sm-10">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Reset Password</h2>

      <form method="POST" action="{{ route('password.store') }}" class="bg-white p-4 shadow rounded-lg my-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-4">
          <label for="email" class="form-label">Email</label>
          <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
          @error('email')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="new-password">
          @error('password')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <input id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" type="password" name="password_confirmation" required autocomplete="new-password">
          @error('password_confirmation')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success">
            Reset Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
