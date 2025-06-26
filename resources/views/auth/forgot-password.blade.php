@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 col-sm-10">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Forgot Password</h2>

      <div class="mb-4 text-sm text-muted text-center">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
      </div>

      {{-- Session Status --}}
      @if(session('status'))
        <div class="alert alert-success mb-4">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" class="bg-white p-4 shadow rounded-lg my-5">
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

        <div class="d-flex justify-content-between">
          <a href="{{ route('login') }}" class="text-sm text-success">
            Back to login
          </a>

          <button type="submit" class="btn btn-success">
            Email Password Reset Link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
