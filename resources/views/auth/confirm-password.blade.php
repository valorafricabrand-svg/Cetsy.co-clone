@extends('theme.'.theme().'.layouts.app')

@section('title', 'Confirm Password')

@section('main')
<section class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-10 md:py-14">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="mx-auto w-full max-w-4xl rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm md:p-6">
      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-slate-900/40 p-6 text-white">
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Secure area</p>
          <h1 class="mt-3 text-3xl font-extrabold leading-tight">Confirm your password</h1>
          <p class="mt-3 text-sm text-emerald-50/90">
            For security reasons, please confirm your password before continuing.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
          <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            @csrf

            <div>
              <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
              <div class="relative">
                <input
                  id="password"
                  type="password"
                  name="password"
                  required
                  autocomplete="current-password"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                  placeholder="Enter your password"
                >
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-3 text-slate-500 hover:text-slate-700" aria-label="Show password">
                  <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
              </div>
              @error('password')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="mt-5 flex justify-end">
              <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                Confirm
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
  const toggle = document.getElementById('togglePassword');
  const input = document.getElementById('password');
  if (!toggle || !input) return;

  toggle.addEventListener('click', function () {
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';

    const icon = toggle.querySelector('i');
    if (!icon) return;
    icon.classList.toggle('fa-eye', showing);
    icon.classList.toggle('fa-eye-slash', !showing);
    toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  });
});
</script>
@endpush
