{{-- resources/views/profile/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
@php
  $showBuyerSidebar = auth()->check() && auth()->user()->isBuyer();
  $showSellerSidebar = auth()->check() && auth()->user()->isSeller();
  $hasSidebar = $showBuyerSidebar || $showSellerSidebar;
@endphp

<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      @if($hasSidebar)
        <div class="col-span-12 lg:col-span-3">
          @if($showBuyerSidebar)
            @include('buyer.partials.sidebar')
          @elseif($showSellerSidebar)
            @include('seller.partials.sidebar')
          @endif
        </div>
      @endif

      <div class="{{ $hasSidebar ? 'col-span-12 lg:col-span-9' : 'col-span-12' }} space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
          <h1 class="text-2xl font-semibold text-slate-900">{{ __('Profile Settings') }}</h1>
          <p class="mt-1 text-slate-500">{{ __('Manage your profile, password, and account preferences.') }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-3">
            <h5 class="font-semibold text-slate-900">{{ __('Update Profile Information') }}</h5>
          </div>
          <div class="p-4 sm:p-5">
            @include('profile.partials.update-profile-information-form', ['countries' => $countries])
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-3">
            <h5 class="font-semibold text-slate-900">{{ __('Change Password') }}</h5>
          </div>
          <div class="p-4 sm:p-5">
            @include('profile.partials.update-password-form')
          </div>
        </div>

        <div class="rounded-2xl border border-rose-200 bg-white shadow-sm">
          <div class="border-b border-rose-200 px-4 py-3">
            <h5 class="font-semibold text-rose-600">{{ __('Delete Account') }}</h5>
          </div>
          <div class="p-4 sm:p-5">
            @include('profile.partials.delete-user-form')
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('#country_id').select2({
    placeholder: @json(__('Select a country')),
    width: '100%',
    minimumResultsForSearch: 0
  });

  const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
  if (toggleCurrentPassword) {
    toggleCurrentPassword.addEventListener('click', function() {
      const passwordInput = document.getElementById('current_password');
      const eyeIcon = document.getElementById('currentEyeIcon');
      if (!passwordInput || !eyeIcon) return;

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    });
  }

  const passwordForm = document.querySelector('form[action*="password"]');
  if (!passwordForm) return;

  passwordForm.addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current_password')?.value || '';
    const newPassword = document.getElementById('password')?.value || '';
    const confirmPassword = document.getElementById('password_confirmation')?.value || '';

    if (!currentPassword || !newPassword || !confirmPassword) {
      e.preventDefault();
      showAlert(@json(__('Please fill in all password fields.')), 'danger');
      return false;
    }

    if (newPassword !== confirmPassword) {
      e.preventDefault();
      showAlert(@json(__('New password and confirmation do not match.')), 'danger');
      return false;
    }

    if (newPassword.length < 8) {
      e.preventDefault();
      showAlert(@json(__('New password must be at least 8 characters long.')), 'danger');
      return false;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    if (!submitBtn) return;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + @json(__('Updating...'));
    submitBtn.disabled = true;

    setTimeout(() => {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }, 5000);
  });

  const newPasswordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('password_confirmation');

  function validatePasswordMatch() {
    if (!newPasswordInput || !confirmPasswordInput) return;
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (confirmPassword && newPassword !== confirmPassword) {
      confirmPasswordInput.classList.add('border-rose-500');
      confirmPasswordInput.classList.remove('border-emerald-500');
    } else if (confirmPassword && newPassword === confirmPassword) {
      confirmPasswordInput.classList.remove('border-rose-500');
      confirmPasswordInput.classList.add('border-emerald-500');
    } else {
      confirmPasswordInput.classList.remove('border-rose-500', 'border-emerald-500');
    }
  }

  if (newPasswordInput && confirmPasswordInput) {
    newPasswordInput.addEventListener('input', validatePasswordMatch);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
  }

  function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    const color = type === 'danger'
      ? 'border-rose-200 bg-rose-50 text-rose-800'
      : 'border-sky-200 bg-sky-50 text-sky-800';

    alertDiv.className = `mb-3 rounded-xl border px-4 py-3 text-sm ${color}`;
    alertDiv.innerHTML = `<div class="flex items-start justify-between gap-3"><span>${message}</span><button type="button" class="text-sm font-semibold" aria-label="Close">X</button></div>`;

    const closeBtn = alertDiv.querySelector('button');
    if (closeBtn) closeBtn.addEventListener('click', () => alertDiv.remove());

    passwordForm.insertBefore(alertDiv, passwordForm.firstChild);

    setTimeout(() => {
      if (alertDiv.parentNode) alertDiv.remove();
    }, 5000);
  }
});
</script>
@endsection
