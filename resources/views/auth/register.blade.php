@extends('theme.'.theme().'.layouts.app')

@section('main')
{{-- ===== Hero + Form Wrapper ===== --}}
<section class="py-5" style="background:linear-gradient(135deg,#067e46 0%,#044d2c 100%);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-5 col-md-7">

        {{-- ─────────── Card ─────────── --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

          {{-- Header --}}
          <div class="card-header text-center bg-success bg-gradient py-4">
            <h2 class="h4 fw-bold text-white mb-0">
              Create Your {{ config('app.name') }} Account
            </h2>
          </div>

          {{-- Body --}}
          <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
              @csrf

              {{-- Account Type --}}
              <div class="mb-4">
                <label class="form-label">Account Type</label>
                <div class="btn-group w-100" role="group">
                  <input class="btn-check" type="radio" name="role" id="role-buyer" value="buyer"
                         {{ old('role','buyer')=='buyer' ? 'checked' : '' }}>
                  <label class="btn btn-outline-success" for="role-buyer">
                    <i class="fa-solid fa-bag-shopping me-1"></i> Buyer
                  </label>

                  <input class="btn-check" type="radio" name="role" id="role-seller" value="seller"
                         {{ old('role')=='seller' ? 'checked' : '' }}>
                  <label class="btn btn-outline-success" for="role-seller">
                    <i class="fa-solid fa-store me-1"></i> Seller
                  </label>
                </div>
                @error('role') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              {{-- Name --}}
              <div class="form-floating mb-4">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       name="name" placeholder="John Doe" value="{{ old('name') }}" required>
                <label for="name">Your Name</label>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Email --}}
              <div class="form-floating mb-4">
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                       name="email" placeholder="name@example.com" value="{{ old('email') }}" required>
                <label for="email">Email Address</label>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Phone --}}
              <div class="form-floating mb-4">
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                       name="phone" placeholder="+254 7xx xxx xxx" value="{{ old('phone') }}" required>
                <label for="phone">Phone</label>
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Country --}}
              <div class="mb-4">
                <label for="country_id" class="form-label">Country</label>
                <select id="country_id" name="country_id"
                        class="form-select @error('country_id') is-invalid @enderror"
                        data-control="select2" required>
                  <option></option>
                  @foreach($countries as $country)
                    <option value="{{ $country->id }}"
                            {{ old('country_id')==$country->id ? 'selected' : '' }}>
                      {{ $country->name }}
                    </option>
                  @endforeach
                </select>
                @error('country_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

<div class="form-floating mb-4 position-relative">
  <input
    type="password"
    class="form-control"
    id="password"
    name="password"
    placeholder="Password"
    required>
  <label for="password">Password</label>

  <button type="button"
          class="toggle-password btn position-absolute top-50 end-0 translate-middle-y pe-3"
          data-target="#password"
          aria-label="Show password"
          style="background: transparent; border: none; cursor: pointer;">
    <i class="fa-solid fa-eye"></i>
  </button>
</div>

<div class="form-floating mb-4 position-relative">
  <input
    type="password"
    class="form-control"
    id="password_confirmation"
    name="password_confirmation"
    placeholder="Confirm Password"
    required>
  <label for="password_confirmation">Confirm Password</label>

  <button type="button"
          class="toggle-password btn position-absolute top-50 end-0 translate-middle-y pe-3"
          data-target="#password_confirmation"
          aria-label="Show password"
          style="background: transparent; border: none; cursor: pointer;">
    <i class="fa-solid fa-eye"></i>
  </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.body.addEventListener('click', e => {
    const btn = e.target.closest('.toggle-password');
    if (!btn) return;
    e.preventDefault();

    const targetSelector = btn.getAttribute('data-target');
    const input = document.querySelector(targetSelector);
    if (!input) return;

    if (input.type === 'password') {
      input.type = 'text';
      btn.querySelector('i').classList.remove('fa-eye');
      btn.querySelector('i').classList.add('fa-eye-slash');
      btn.setAttribute('aria-label', 'Hide password');
    } else {
      input.type = 'password';
      btn.querySelector('i').classList.remove('fa-eye-slash');
      btn.querySelector('i').classList.add('fa-eye');
      btn.setAttribute('aria-label', 'Show password');
    }
  });
});
</script>


              {{-- Accept Terms --}}
              <div class="form-check mb-4">
                <input
                  class="form-check-input @error('terms') is-invalid @enderror"
                  type="checkbox"
                  id="terms"
                  name="terms"
                  value="1"
                  {{ old('terms') ? 'checked' : '' }}>
                <label class="form-check-label small" for="terms">
                  I agree to the Cetsy User Agreement
                  <!-- <a href="{{ url('/terms') }}" target="_blank" class="text-success">Terms of Service</a>
                  and
                  <a href="{{ url('/privacy') }}" target="_blank" class="text-success">Privacy Policy</a>. -->
                </label>
                <div id="terms-error" class="invalid-feedback d-block" style="display:none;"></div>
                @error('terms') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              {{-- Submit --}}
              <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('login') }}" class="text-success small">Already have an account?</a>
                <button type="submit" class="btn btn-success px-4">
                  <i class="fa-solid fa-user-plus me-2"></i>Register
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')

<style>
  .select2-container--default .select2-selection--single {
    height: 45px;
    border: 1px solid #ced4da;
    border-radius: .375rem;
  }
  .select2-selection__rendered {
    line-height: 45px;
  }
  .select2-selection__arrow {
    height: 45px;
  }
  .toggle-password {
    z-index: 5;
    cursor: pointer;
  }
  .toggle-password i {
    pointer-events: none;
  }
</style>
@endpush

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Select2
  $('#country_id').select2({
    theme: 'bootstrap-5',
    placeholder: 'Select country'
  });

  // Terms checkbox validation
  const form = document.querySelector('form.needs-validation');
  const terms = document.getElementById('terms');
  const errorDiv = document.getElementById('terms-error');

  form.addEventListener('submit', function(e) {
    if (!terms.checked) {
      e.preventDefault();
      errorDiv.textContent = "You must agree to the Terms of Service and Privacy Policy.";
      errorDiv.style.display = "block";
      terms.classList.add('is-invalid');
      terms.focus();
    } else {
      errorDiv.textContent = "";
      errorDiv.style.display = "none";
      terms.classList.remove('is-invalid');
    }
  });

  terms.addEventListener('change', function() {
    if (terms.checked) {
      errorDiv.textContent = "";
      errorDiv.style.display = "none";
      terms.classList.remove('is-invalid');
    }
  });
});
</script>
@endpush
