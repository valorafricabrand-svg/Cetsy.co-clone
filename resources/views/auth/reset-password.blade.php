@extends('theme.'.theme().'.layouts.app')

@section('title','Reset Password')

@section('main')
<section class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-10 md:py-14">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="mx-auto w-full max-w-4xl rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm md:p-6">
      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-slate-900/40 p-6 text-white">
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Set new password</p>
          <h1 class="mt-3 text-3xl font-extrabold leading-tight">Reset your password</h1>
          <p class="mt-3 text-sm text-emerald-50/90">
            Use a strong password you have not used before. This keeps your account secure.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
          <form method="POST" action="{{ route('password.store') }}" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
              <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
              <input
                id="email"
                name="email"
                type="email"
                required
                autofocus
                autocomplete="username"
                value="{{ old('email', $request->email) }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="name@example.com"
              >
              @error('email')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="mt-4">
              <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">New Password</label>
              <div class="relative">
                <input
                  id="password"
                  name="password"
                  type="password"
                  required
                  autocomplete="new-password"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                  placeholder="Create a new password"
                >
                <button type="button" class="toggle-password absolute inset-y-0 right-0 inline-flex items-center justify-center px-3 text-slate-500 hover:text-slate-700" data-target="#password" aria-label="Show password">
                  <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
              </div>
              @error('password')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="mt-4">
              <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
              <div class="relative">
                <input
                  id="password_confirmation"
                  name="password_confirmation"
                  type="password"
                  required
                  autocomplete="new-password"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                  placeholder="Repeat your new password"
                >
                <button type="button" class="toggle-password absolute inset-y-0 right-0 inline-flex items-center justify-center px-3 text-slate-500 hover:text-slate-700" data-target="#password_confirmation" aria-label="Show password">
                  <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
              </div>
              @error('password_confirmation')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="mt-5 flex justify-end">
              <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                Reset Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.body.addEventListener('click', function (event) {
    const btn = event.target.closest('.toggle-password');
    if (!btn) return;
    event.preventDefault();

    const selector = btn.getAttribute('data-target');
    const input = selector ? document.querySelector(selector) : null;
    if (!input) return;

    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';

    const icon = btn.querySelector('i');
    if (!icon) return;
    icon.classList.toggle('fa-eye', showing);
    icon.classList.toggle('fa-eye-slash', !showing);
    btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  });
});
</script>
@endpush
