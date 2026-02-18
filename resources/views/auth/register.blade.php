@extends('theme.'.theme().'.layouts.app')

@section('title','Register')

@section('main')
<section class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-10 md:py-14">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="mx-auto grid w-full max-w-6xl gap-6 rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm md:grid-cols-5 md:p-6">
      <div class="md:col-span-2 flex flex-col justify-between rounded-2xl bg-slate-900/40 p-6 text-white">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Join {{ config('app.name') }}</p>
          <h1 class="mt-3 text-3xl font-extrabold leading-tight">Create your account</h1>
          <p class="mt-3 text-sm text-emerald-50/90">Choose buyer or seller, complete your profile, and start trading in minutes.</p>
        </div>
        <div class="mt-8 space-y-2 text-xs text-emerald-50/90">
          <p class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Buy unique products from trusted shops</p>
          <p class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Launch your storefront and manage orders</p>
          <p class="rounded-xl border border-white/20 bg-white/5 px-3 py-2">Secure account and clear profile setup</p>
        </div>
      </div>

      <div class="md:col-span-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <form method="POST" action="{{ route('register') }}" id="register-form" novalidate>
          @csrf

          <div>
            <p class="mb-2 text-sm font-semibold text-slate-700">Account Type</p>
            <div class="grid grid-cols-2 gap-2">
              <label class="cursor-pointer rounded-xl border px-3 py-2.5 text-center text-sm font-semibold transition {{ old('role','buyer')=='buyer' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-300 text-slate-700 hover:border-slate-400' }}">
                <input class="sr-only" type="radio" name="role" value="buyer" {{ old('role','buyer')=='buyer' ? 'checked' : '' }}>
                <i class="fa-solid fa-bag-shopping mr-1" aria-hidden="true"></i> Buyer
              </label>
              <label class="cursor-pointer rounded-xl border px-3 py-2.5 text-center text-sm font-semibold transition {{ old('role')=='seller' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-300 text-slate-700 hover:border-slate-400' }}">
                <input class="sr-only" type="radio" name="role" value="seller" {{ old('role')=='seller' ? 'checked' : '' }}>
                <i class="fa-solid fa-store mr-1" aria-hidden="true"></i> Seller
              </label>
            </div>
            @error('role')
              <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Your Name</label>
              <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="John Doe"
              >
              @error('name')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="md:col-span-2">
              <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
              <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="name@example.com"
              >
              @error('email')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone</label>
              <input
                id="phone"
                name="phone"
                type="text"
                value="{{ old('phone') }}"
                required
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="+254 7xx xxx xxx"
              >
              @error('phone')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="country_id" class="mb-1 block text-sm font-semibold text-slate-700">Country</label>
              <select
                id="country_id"
                name="country_id"
                required
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none"
              >
                <option value="">Select country</option>
                @foreach($countries as $country)
                  <option value="{{ $country->id }}" {{ old('country_id')==$country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                  </option>
                @endforeach
              </select>
              @error('country_id')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="relative md:col-span-2">
              <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
              <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="Create a password"
              >
              <button
                type="button"
                class="toggle-password absolute bottom-0 right-0 inline-flex h-[42px] items-center justify-center px-3 text-slate-500 hover:text-slate-700"
                data-target="#password"
                aria-label="Show password"
              >
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
              </button>
              @error('password')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="relative md:col-span-2">
              <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none"
                placeholder="Repeat your password"
              >
              <button
                type="button"
                class="toggle-password absolute bottom-0 right-0 inline-flex h-[42px] items-center justify-center px-3 text-slate-500 hover:text-slate-700"
                data-target="#password_confirmation"
                aria-label="Show password"
              >
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
            <label class="inline-flex items-start gap-2 text-sm text-slate-700">
              <input
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                type="checkbox"
                id="terms"
                name="terms"
                value="1"
                {{ old('terms') ? 'checked' : '' }}
              >
              <span>
                I agree to the
                <a href="{{ url('/user-agreement') }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:text-emerald-600">
                  Cetsy User Agreement
                </a>
              </span>
            </label>
            <p id="terms-error" class="mt-1 hidden text-xs font-medium text-rose-600"></p>
            @error('terms')
              <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600">Already have an account?</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500">
              <i class="fa-solid fa-user-plus mr-2" aria-hidden="true"></i>
              Register
            </button>
          </div>
        </form>
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

    const targetSelector = btn.getAttribute('data-target');
    const input = targetSelector ? document.querySelector(targetSelector) : null;
    if (!input) return;

    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';

    const icon = btn.querySelector('i');
    if (icon) {
      icon.classList.toggle('fa-eye', showing);
      icon.classList.toggle('fa-eye-slash', !showing);
    }
    btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  });

  const form = document.getElementById('register-form');
  const terms = document.getElementById('terms');
  const termsError = document.getElementById('terms-error');

  if (!form || !terms || !termsError) return;

  form.addEventListener('submit', function (event) {
    if (!terms.checked) {
      event.preventDefault();
      termsError.textContent = 'You must agree to the Cetsy User Agreement.';
      termsError.classList.remove('hidden');
      terms.focus();
    } else {
      termsError.textContent = '';
      termsError.classList.add('hidden');
    }
  });

  terms.addEventListener('change', function () {
    if (terms.checked) {
      termsError.textContent = '';
      termsError.classList.add('hidden');
    }
  });

  const roleInputs = Array.from(document.querySelectorAll('input[name=\"role\"]'));
  const roleLabels = roleInputs.map(function (input) {
    return input.closest('label');
  }).filter(Boolean);

  function refreshRoleStyles() {
    roleLabels.forEach(function (label) {
      const input = label.querySelector('input[name=\"role\"]');
      if (!input) return;
      if (input.checked) {
        label.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-700');
        label.classList.remove('border-slate-300', 'text-slate-700', 'hover:border-slate-400');
      } else {
        label.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-700');
        label.classList.add('border-slate-300', 'text-slate-700', 'hover:border-slate-400');
      }
    });
  }

  roleInputs.forEach(function (input) {
    input.addEventListener('change', refreshRoleStyles);
  });
  refreshRoleStyles();
});
</script>
@endpush
