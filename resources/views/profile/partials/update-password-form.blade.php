{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<section>

  {{-- ===== Header ===== --}}
  <header class="mb-4">
    <h2 class="h5 fw-semibold text-body-emphasis mb-1">
      {{ __('Update Password') }}
    </h2>
    <p class="small text-muted mb-0">
      {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </p>
  </header>

  {{-- ===== Password update form ===== --}}
  <form method="POST"
        action="{{ route('password.update') }}"
        class="needs-validation"
        novalidate>
    @csrf
    @method('PUT')

    {{-- Current Password --}}
    <div class="mb-3">
      <label for="current_password" class="form-label">
        {{ __('Current Password') }}
      </label>
      <input
        type="password"
        id="current_password"
        name="current_password"
        class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
        autocomplete="current-password"
        required>
      @error('current_password', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- New Password --}}
    <div class="mb-3">
      <label for="password" class="form-label">
        {{ __('New Password') }}
      </label>
      <input
        type="password"
        id="password"
        name="password"
        class="form-control @error('password', 'updatePassword') is-invalid @enderror"
        autocomplete="new-password"
        required>
      @error('password', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="mb-3">
      <label for="password_confirmation" class="form-label">
        {{ __('Confirm Password') }}
      </label>
      <input
        type="password"
        id="password_confirmation"
        name="password_confirmation"
        class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
        autocomplete="new-password"
        required>
      @error('password_confirmation', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Save button & flash message --}}
    <div class="d-flex align-items-center gap-3">
      <button type="submit" class="btn btn-primary">
        {{ __('Save') }}
      </button>

      @if (session('status') === 'password-updated')
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
