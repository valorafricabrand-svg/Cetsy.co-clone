@extends('theme.'.theme().'.layouts.app')

@section('title','Login')

@section('main')
<section class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-10 md:py-14">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="mx-auto grid w-full max-w-5xl gap-6 rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm md:grid-cols-2 md:p-6">
      <div class="flex flex-col justify-between rounded-2xl bg-slate-900/40 p-6 text-white">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Welcome back</p>
          <h1 class="mt-3 text-3xl font-extrabold leading-tight">Sign in to {{ config('app.name') }}</h1>
          <p class="mt-3 text-sm text-emerald-50/90">Access your listings, orders, favorites, and account settings.</p>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-3 text-xs text-emerald-50/90">
          <div class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Fast checkout</div>
          <div class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Saved favorites</div>
          <div class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Seller dashboard</div>
          <div class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Secure access</div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <x-auth-session-status :status="session('status')" class="mb-4" />

        <form method="POST" action="{{ route('login') }}" novalidate>
          @csrf

          <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
            <input
              id="email"
              type="email"
              name="email"
              value="{{ old('email') }}"
              autocomplete="username"
              required
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
              placeholder="name@example.com"
            >
            @error('email')
              <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="mt-4">
            <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
            <div class="relative">
              <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="Enter your password"
              >
              <button
                type="button"
                id="togglePassword"
                class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-3 text-slate-500 hover:text-slate-700"
                aria-label="Show password"
              >
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
              </button>
            </div>
            @error('password')
              <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
              <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              Remember me
            </label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600">Forgot password?</a>
            @endif
          </div>

          <button
            type="submit"
            class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500"
          >
            <i class="fa-solid fa-right-to-bracket mr-2" aria-hidden="true"></i>
            Log in
          </button>

          <p class="mt-4 text-center text-sm text-slate-500">
            Do not have an account?
            <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Create one</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('togglePassword');
  const pwd = document.getElementById('password');
  if (!toggle || !pwd) return;

  toggle.addEventListener('click', function () {
    const showing = pwd.type === 'text';
    pwd.type = showing ? 'password' : 'text';

    const icon = toggle.querySelector('i');
    if (!icon) return;
    icon.classList.toggle('fa-eye', showing);
    icon.classList.toggle('fa-eye-slash', !showing);
    toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  });
});
</script>
@endpush
