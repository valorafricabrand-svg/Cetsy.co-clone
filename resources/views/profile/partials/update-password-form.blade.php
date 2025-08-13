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

  {{-- Debug Info --}}
  @if(config('app.debug'))
    <div class="alert alert-info">
      <strong>Debug Info:</strong><br>
      Route: {{ route('password.update') }}<br>
      Method: PUT<br>
      User ID: {{ auth()->id() }}
    </div>
  @endif

  {{-- Success Messages --}}
  @if(session('status') === 'password-updated')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      <strong>Success!</strong> Your password has been updated successfully.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Error Messages --}}
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <strong>Error!</strong> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Validation Errors Summary --}}
  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <strong>Please fix the following errors:</strong>
      <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

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
      <div class="input-group">
        <input
          type="password"
          id="current_password"
          name="current_password"
          class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
          autocomplete="current-password"
          required>
        <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
          <i class="fas fa-eye" id="currentEyeIcon"></i>
        </button>
      </div>
      @error('current_password', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
      
      {{-- Forgot Password Link --}}
      <div class="mt-2">
        <a href="{{ route('password.request') }}" class="text-decoration-none small">
          <i class="fas fa-question-circle me-1"></i>
          {{ __('Forgot your current password?') }}
        </a>
      </div>
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
      <div class="form-text">
        <i class="fas fa-info-circle me-1"></i>
        Password must be at least 8 characters long.
      </div>
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
        <i class="fas fa-save me-2"></i>
        {{ __('Update Password') }}
      </button>

      @if (session('status') === 'password-updated')
        <span
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 5000)"
          class="small text-success">
          <i class="fas fa-check-circle me-1"></i>
          {{ __('Password updated successfully!') }}
        </span>
      @endif
    </div>
  </form>

</section>
