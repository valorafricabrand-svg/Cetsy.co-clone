{{-- resources/views/profile/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="container-xxl">

    {{-- ================== GRID ================== --}}
    <div class="grid grid-cols-12 gap-4 gap-4">

      {{-- 1️⃣  Update profile information --}}
      <div class="col-span-12">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-light-subtle rounded-3">
          <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
            <h5 class="mb-0 font-semibold">Update&nbsp;Profile&nbsp;Information</h5>
          </div>
          <div class="p-4 sm:p-5">
            @include('profile.partials.update-profile-information-form', ['countries' => $countries])
          </div>
        </div>
      </div>

      {{-- 2️⃣  Change password --}}
      <div class="col-span-12">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-light-subtle rounded-3">
          <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
            <h5 class="mb-0 font-semibold">Change&nbsp;Password</h5>
          </div>
          <div class="p-4 sm:p-5">
            @include('profile.partials.update-password-form')
          </div>
        </div>
      </div>

      {{-- 3️⃣  Delete account --}}
      <div class="col-span-12">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-danger-subtle border-2 rounded-3">
          <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
            <h5 class="mb-0 text-rose-600 font-semibold">Delete&nbsp;Account</h5>
          </div>
          <div class="p-4 sm:p-5">
            @include('profile.partials.delete-user-form')
          </div>
        </div>
      </div>

    </div> {{-- /row --}}
  </div>   {{-- /container --}}
</div>

{{-- Select2 CSS and JS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Select2 for country dropdown
  $('#country_id').select2({
    placeholder: 'Select a country',
    width: '100%',
    minimumResultsForSearch: 0
  });

  // Password visibility toggle for current password
  document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('current_password');
    const eyeIcon = document.getElementById('currentEyeIcon');
    
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

  // Debug password form submission
  const passwordForm = document.querySelector('form[action*="password"]');
  if (passwordForm) {
    passwordForm.addEventListener('submit', function(e) {
      console.log('Password form submitting...');
      console.log('Form data:', new FormData(this));
      
      // Check if all required fields are filled
      const currentPassword = document.getElementById('current_password').value;
      const newPassword = document.getElementById('password').value;
      const confirmPassword = document.getElementById('password_confirmation').value;
      
      console.log('Current password length:', currentPassword.length);
      console.log('New password length:', newPassword.length);
      console.log('Confirm password length:', confirmPassword.length);
      
      if (!currentPassword || !newPassword || !confirmPassword) {
        console.log('Some fields are empty!');
        e.preventDefault();
        showAlert('Please fill in all password fields.', 'danger');
        return false;
      }
      
      if (newPassword !== confirmPassword) {
        console.log('Passwords do not match!');
        e.preventDefault();
        showAlert('New password and confirmation do not match.', 'danger');
        return false;
      }
      
      if (newPassword.length < 8) {
        console.log('Password too short!');
        e.preventDefault();
        showAlert('New password must be at least 8 characters long.', 'danger');
        return false;
      }
      
      console.log('Form validation passed, submitting...');
      
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
      submitBtn.disabled = true;
      
      // Re-enable after a delay (in case of errors)
      setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }, 5000);
    });
    
    // Real-time password confirmation validation
    const newPasswordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    
    function validatePasswordMatch() {
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;
      
      if (confirmPassword && newPassword !== confirmPassword) {
        confirmPasswordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.remove('is-valid');
      } else if (confirmPassword && newPassword === confirmPassword) {
        confirmPasswordInput.classList.remove('is-invalid');
        confirmPasswordInput.classList.add('is-valid');
      } else {
        confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
      }
    }
    
    newPasswordInput.addEventListener('input', validatePasswordMatch);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
  }
  
  // Function to show alerts
  function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
      <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
      ${message}
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Insert at the top of the form
    const form = document.querySelector('form[action*="password"]');
    form.insertBefore(alertDiv, form.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }
});
</script>

@endsection



