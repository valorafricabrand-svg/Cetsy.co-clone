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
