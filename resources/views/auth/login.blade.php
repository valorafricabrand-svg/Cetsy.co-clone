@extends('theme.'.theme().'.layouts.app')

@section('main')
{{-- ===== Hero + Form Wrapper ===== --}}
<section class="py-5" style="background:linear-gradient(135deg,#067e46 0%,#044d2c 100%);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-5 col-md-7">

        {{-- ─────────── Card ─────────── --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

          {{-- Header --}}
          <div class="card-header text-center bg-success bg-gradient py-4">
            <h2 class="h4 fw-bold text-white mb-0">
              Log in to {{ config('app.name') }}
            </h2>
          </div>

          {{-- Body --}}
          <div class="card-body p-4 p-lg-5">
            {{-- Session Status --}}
            @if(session('status'))
              <div class="alert alert-success mb-4">
                {{ session('status') }}
              </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
              @csrf

              {{-- Email --}}
              <div class="form-floating mb-4">
                <input
                  type="email"
                  class="form-control @error('email') is-invalid @enderror"
                  id="email"
                  name="email"
                  placeholder="name@example.com"
                  value="{{ old('email') }}"
                  required>
                <label for="email">Email Address</label>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Password --}}
              <div class="form-floating mb-4">
                <input
                  type="password"
                  class="form-control @error('password') is-invalid @enderror"
                  id="password"
                  name="password"
                  placeholder="Password"
                  autocomplete="current-password"
                  required>
                <label for="password">Password</label>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Remember Me --}}
              <div class="form-check mb-4">
                <input
                  type="checkbox"
                  class="form-check-input"
                  id="remember_me"
                  name="remember">
                <label class="form-check-label" for="remember_me">Remember me</label>
              </div>

              {{-- Buttons --}}
              <div class="d-flex justify-content-between align-items-center">
                @if (Route::has('password.request'))
                  <a href="{{ route('password.request') }}" class="small text-success">
                    Forgot your password?
                  </a>
                @endif
                <button type="submit" class="btn btn-success px-4">
                  <i class="fa-solid fa-right-to-bracket me-2"></i>Log in
                </button>
              </div>

              {{-- Divider --}}
              <hr class="my-4">

              {{-- Footnote --}}
              <div class="text-center text-muted small">
                Don’t have an account?
                <a href="{{ route('register') }}" class="text-success fw-semibold">
                  Create one
                </a>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
