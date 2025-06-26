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
            <h2 class="h4 fw-bold text-white mb-0">Create Your {{ config('app.name') }} Account</h2>
          </div>

          {{-- Body --}}
          <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
              @csrf

              {{-- Account Type (segmented toggle) --}}
              <div class="mb-4">
                <label class="form-label">Account Type</label>
                <div class="btn-group w-100" role="group">
                  <input
                    type="radio"
                    class="btn-check"
                    name="role"
                    id="role-buyer"
                    value="buyer"
                    {{ old('role','buyer')=='buyer' ? 'checked' : '' }}>
                  <label class="btn btn-outline-success" for="role-buyer">
                    <i class="fa-solid fa-bag-shopping me-1"></i> Buyer
                  </label>

                  <input
                    type="radio"
                    class="btn-check"
                    name="role"
                    id="role-seller"
                    value="seller"
                    {{ old('role')=='seller' ? 'checked' : '' }}>
                  <label class="btn btn-outline-success" for="role-seller">
                    <i class="fa-solid fa-store me-1"></i> Seller
                  </label>
                </div>
                @error('role') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              {{-- Name --}}
              <div class="form-floating mb-4">
                <input
                  type="text"
                  class="form-control @error('name') is-invalid @enderror"
                  id="name"
                  name="name"
                  placeholder="John Doe"
                  value="{{ old('name') }}"
                  required>
                <label for="name">Your Name</label>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Email --}}
              <div class="form-floating mb-4">
                <input
                  type="email"
                  class="form-control @error('email') is-invalid @enderror"
                  id="email"
                  name="email"
                  placeholder="name@example.com"
                  value="{{ old('email') }}"
                  required>
                <label for="email">Email Address</label>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Phone --}}
              <div class="form-floating mb-4">
                <input
                  type="text"
                  class="form-control @error('phone') is-invalid @enderror"
                  id="phone"
                  name="phone"
                  placeholder="+254 7xx xxx xxx"
                  value="{{ old('phone') }}"
                  required>
                <label for="phone">Phone</label>
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Country (Select2) --}}
              <div class="mb-4">
                <label for="country_id" class="form-label">Country</label>
                <select
                  id="country_id"
                  name="country_id"
                  class="form-select @error('country_id') is-invalid @enderror"
                  data-control="select2"
                  data-placeholder="Select country"
                  required>
                  <option></option>
                  @foreach($countries as $country)
                    <option value="{{ $country->id }}" {{ old('country_id')==$country->id ? 'selected' : '' }}>
                      {{ $country->name }}
                    </option>
                  @endforeach
                </select>
                @error('country_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>

              {{-- Password --}}
              <div class="form-floating mb-4">
                <input
                  type="password"
                  class="form-control @error('password') is-invalid @enderror"
                  id="password"
                  name="password"
                  placeholder="Password"
                  autocomplete="new-password"
                  required>
                <label for="password">Password</label>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Confirm Password --}}
              <div class="form-floating mb-4">
                <input
                  type="password"
                  class="form-control @error('password_confirmation') is-invalid @enderror"
                  id="password_confirmation"
                  name="password_confirmation"
                  placeholder="Repeat Password"
                  autocomplete="new-password"
                  required>
                <label for="password_confirmation">Confirm Password</label>
                @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Action Buttons --}}
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
  </section>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/select2.min.css" rel="stylesheet" />
<style>
  .select2-container--default .select2-selection--single{
    height: 45px; border:1px solid #ced4da; border-radius:.375rem;
  }
  .select2-selection__rendered{ line-height:45px }
  .select2-selection__arrow{ height:45px }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/select2.min.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    $('#country_id').select2({
      theme: 'bootstrap-5',
      placeholder: $(this).data('placeholder')
    });
  });
</script>
@endpush
