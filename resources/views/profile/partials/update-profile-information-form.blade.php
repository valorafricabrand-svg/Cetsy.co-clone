{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<section>

  {{-- ===== Header ===== --}}
  <header class="mb-4">
    <h2 class="h5 fw-semibold text-body-emphasis mb-1">
      {{ __('Profile Information') }}
    </h2>
    <p class="small text-muted mb-0">
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
      <label for="name" class="form-label">{{ __('Name') }}</label>
      <input
        type="text"
        id="name"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $user->name) }}"
        required
        autocomplete="name"
        autofocus>
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Email --}}
    <div class="mb-3">
      <label for="email" class="form-label">{{ __('Email') }}</label>
      <input
        type="email"
        id="email"
        name="email"
        class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $user->email) }}"
        required
        autocomplete="username">
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror

      {{-- Verification notice --}}
      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-2 small">
          <span class="text-warning">
            {{ __('Your email address is unverified.') }}
          </span>
          <button
            form="send-verification"
            class="btn btn-link p-0 align-baseline small">
            {{ __('Click here to re-send the verification email.') }}
          </button>

          @if (session('status') === 'verification-link-sent')
            <span class="d-block text-success fw-medium mt-1">
              {{ __('A new verification link has been sent to your email address.') }}
            </span>
          @endif
        </div>
      @endif
    </div>

    {{-- Phone --}}
    <div class="mb-3">
      <label for="phone" class="form-label">{{ __('Phone') }}</label>
      <input
        type="text"
        id="phone"
        name="phone"
        class="form-control @error('phone') is-invalid @enderror"
        value="{{ old('phone', $user->phone) }}"
        placeholder="+254 7xx xxx xxx"
        autocomplete="tel">
      @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Country --}}
    <div class="mb-3">
      <label for="country_id" class="form-label">{{ __('Country') }}</label>
      <select
        id="country_id"
        name="country_id"
        class="form-select @error('country_id') is-invalid @enderror"
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
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Profile Photo --}}
    <div class="mb-3">
      <label for="photo" class="form-label">{{ __('Profile Photo') }}</label>
      <input
        type="file"
        id="photo"
        name="photo"
        class="form-control @error('photo') is-invalid @enderror"
        accept="image/*">
      @error('photo')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
      
      {{-- Show current photo if exists --}}
      @if($user->photo)
        <div class="mt-2">
          <img src="{{ asset('storage/' . $user->photo) }}" 
               alt="Profile photo" 
               class="rounded-circle" 
               width="80" 
               height="80"
               style="object-fit: cover;">
          <div class="form-text">Current profile photo</div>
        </div>
      @endif
      <div class="form-text">Upload a new profile photo (optional). Max size: 2MB.</div>
    </div>

    {{-- Save button & flash "Saved." --}}
    <div class="d-flex align-items-center gap-3">
      <button type="submit" class="btn btn-primary">
        {{ __('Save') }}
      </button>

      @if (session('status') === 'profile-updated')
        <span
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 2000)"
          class="small text-success">
          {{ __('Saved.') }}
        </span>
      @endif
    </div>
  </form>

</section>
