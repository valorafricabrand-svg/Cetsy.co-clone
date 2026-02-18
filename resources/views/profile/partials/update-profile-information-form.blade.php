{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<section>

  {{-- ===== Header ===== --}}
  <header class="mb-4">
    <h2 class="text-base font-semibold font-semibold text-body-emphasis mb-1">
      {{ __('Profile Information') }}
    </h2>
    <p class="text-xs text-slate-500 mb-0">
      {{ __("Update your account's profile information and email address.") }}
    </p>
  </header>

  {{-- ===== Hidden form to re-send verification link ===== --}}
  <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
    @csrf
  </form>

  {{-- ===== Main Update form ===== --}}
  <form method="POST"
        action="{{ route('profile.update') }}"
        class="needs-validation"
        enctype="multipart/form-data"
        novalidate>
    @csrf @method('PATCH')

    {{-- Name --}}
    <div class="mb-3">
      <label for="name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
      <input
        type="text"
        id="name"
        name="name"
        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        value="{{ old('name', $user->name) }}"
        required
        autocomplete="name"
        autofocus>
      @error('name')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
    </div>

    {{-- Email --}}
    <div class="mb-3">
      <label for="email" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
      <input
        type="email"
        id="email"
        name="email"
        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        value="{{ old('email', $user->email) }}"
        required
        autocomplete="username">
      @error('email')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror

      {{-- Verification notice --}}
      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-2 text-xs">
          <span class="text-amber-600">
            {{ __('Your email address is unverified.') }}
          </span>
          <button
            form="send-verification"
            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition text-emerald-700 hover:text-emerald-600 underline-offset-2 hover:underline p-0 align-baseline text-xs">
            {{ __('Click here to re-send the verification email.') }}
          </button>

          @if (session('status') === 'verification-link-sent')
            <span class="block text-emerald-600 fw-medium mt-1">
              {{ __('A new verification link has been sent to your email address.') }}
            </span>
          @endif
        </div>
      @endif
    </div>

    {{-- Phone --}}
    <div class="mb-3">
      <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Phone') }}</label>
      <input
        type="text"
        id="phone"
        name="phone"
        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('phone') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        value="{{ old('phone', $user->phone) }}"
        placeholder="+254 7xx xxx xxx"
        autocomplete="tel">
      @error('phone')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
    </div>

    {{-- Country --}}
    <div class="mb-3">
      <label for="country_id" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Country') }}</label>
      <select
        id="country_id"
        name="country_id"
        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('country_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        data-control="select2">
        <option value="">{{ __('Select a country') }}</option>
        @foreach($countries as $country)
          <option
            value="{{ $country->id }}"
            {{ old('country_id', $user->country_id) == $country->id ? 'selected' : '' }}>
            {{ $country->name }}
          </option>
        @endforeach
      </select>
      @error('country_id')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
    </div>

    {{-- Profile Photo --}}
    <div class="mb-3">
      <label for="photo" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Profile Photo') }}</label>
      <input
        type="file"
        id="photo"
        name="photo"
        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('photo') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        accept="image/*">
      @error('photo')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
      
      {{-- Show current photo if exists --}}
      @if($user->photo)
        <div class="mt-2">
          <img src="{{ asset('storage/' . $user->photo) }}" 
               alt="Profile photo" 
               class="rounded-full" 
               width="80" 
               height="80"
               style="object-fit: cover;">
          <div class="mt-1 text-xs text-slate-500">Current profile photo</div>
        </div>
      @endif
      <div class="mt-1 text-xs text-slate-500">Upload a new profile photo (optional). Max size: 2MB.</div>
    </div>

    {{-- Save button & flash "Saved." --}}
    <div class="flex items-center gap-3">
      <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
        {{ __('Save') }}
      </button>

      @if (session('status') === 'profile-updated')
        <span
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 2000)"
          class="text-xs text-emerald-600">
          {{ __('Saved.') }}
        </span>
      @endif
    </div>
  </form>

</section>

