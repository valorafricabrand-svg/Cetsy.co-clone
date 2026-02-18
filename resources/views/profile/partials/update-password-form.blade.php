{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<section>

  {{-- ===== Header ===== --}}
  <header class="mb-4">
    <h2 class="text-base font-semibold font-semibold text-body-emphasis mb-1">
      {{ __('Update Password') }}
    </h2>
    <p class="text-xs text-slate-500 mb-0">
      {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </p>
  </header>

  {{-- Debug Info --}}
  @if(config('app.debug'))
    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
      <strong>Debug Info:</strong><br>
      Route: {{ route('password.update') }}<br>
      Method: PUT<br>
      User ID: {{ auth()->id() }}
    </div>
  @endif

  {{-- Success Messages --}}
  @if(session('status') === 'password-updated')
    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 alert-dismissible" role="alert">
      <i class="fas fa-check-circle mr-2"></i>
      <strong>Success!</strong> Your password has been updated successfully.
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Error Messages --}}
  @if(session('error'))
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 alert-dismissible" role="alert">
      <i class="fas fa-exclamation-triangle mr-2"></i>
      <strong>Error!</strong> {{ session('error') }}
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Validation Errors Summary --}}
  @if($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 alert-dismissible" role="alert">
      <i class="fas fa-exclamation-triangle mr-2"></i>
      <strong>Please fix the following errors:</strong>
      <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert" aria-label="Close"></button>
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
      <label for="current_password" class="mb-1 block text-sm font-medium text-slate-700">
        {{ __('Current Password') }}
      </label>
      <div class="flex w-full items-stretch">
        <input
          type="password"
          id="current_password"
          name="current_password"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('current_password', 'updatePassword') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
          autocomplete="current-password"
          required>
        <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" type="button" id="toggleCurrentPassword">
          <i class="fas fa-eye" id="currentEyeIcon"></i>
        </button>
      </div>
      @error('current_password', 'updatePassword')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
      
      {{-- Forgot Password Link --}}
      <div class="mt-2">
        <a href="{{ route('password.request') }}" class="no-underline text-xs">
          <i class="fas fa-question-circle mr-1"></i>
          {{ __('Forgot your current password?') }}
        </a>
      </div>
    </div>

    {{-- New Password --}}
    <div class="mb-3">
      <label for="password" class="mb-1 block text-sm font-medium text-slate-700">
        {{ __('New Password') }}
      </label>
      <input
        type="password"
        id="password"
        name="password"
        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('password', 'updatePassword') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        autocomplete="new-password"
        required>
      @error('password', 'updatePassword')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
      <div class="mt-1 text-xs text-slate-500">
        <i class="fas fa-info-circle mr-1"></i>
        Password must be at least 8 characters long.
      </div>
    </div>

    {{-- Confirm Password --}}
    <div class="mb-3">
      <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">
        {{ __('Confirm Password') }}
      </label>
      <input
        type="password"
        id="password_confirmation"
        name="password_confirmation"
        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('password_confirmation', 'updatePassword') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
        autocomplete="new-password"
        required>
      @error('password_confirmation', 'updatePassword')
        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
      @enderror
    </div>

    {{-- Save button & flash message --}}
    <div class="flex items-center gap-3">
      <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
        <i class="fas fa-save mr-2"></i>
        {{ __('Update Password') }}
      </button>

      @if (session('status') === 'password-updated')
        <span
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 5000)"
          class="text-xs text-emerald-600">
          <i class="fas fa-check-circle mr-1"></i>
          {{ __('Password updated successfully!') }}
        </span>
      @endif
    </div>
  </form>

</section>

