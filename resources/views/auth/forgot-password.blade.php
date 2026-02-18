@extends('theme.'.theme().'.layouts.app')

@section('title','Forgot Password')

@section('main')
<section class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-10 md:py-14">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="mx-auto w-full max-w-4xl rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm md:p-6">
      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-slate-900/40 p-6 text-white">
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Account recovery</p>
          <h1 class="mt-3 text-3xl font-extrabold leading-tight">Forgot your password?</h1>
          <p class="mt-3 text-sm text-emerald-50/90">
            Enter your email address and we will send a password reset link so you can securely set a new password.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
          <x-auth-session-status :status="session('status')" class="mb-4" />

          <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div>
              <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
              <input
                id="email"
                name="email"
                type="email"
                required
                autofocus
                value="{{ old('email') }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="name@example.com"
              >
              @error('email')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
              <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600">Back to login</a>
              <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                Email Password Reset Link
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
