{{-- resources/views/shops/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card shadow-sm">
        <div class="card-header bg-white border-0">
          <h2 class="h4 mb-0 text-center">Create Your Shop</h2>
        </div>
        <div class="card-body">

          {{-- Flash Success --}}
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          {{-- Validation Errors --}}
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form 
            action="{{ route('seller.shops.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
            x-data="{ name: '{{ old('name') }}', slug: '{{ old('slug') }}' }"
            @input.debounce.300ms="slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');"
            class="needs-validation" 
            novalidate
          >
            @csrf

            {{-- 1) Shop Preferences --}}
            <h5 class="mt-4">1. Shop Preferences</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
                <select name="language" id="language" required class="form-select">
                  <option value="" disabled selected>Select language</option>
                  <option value="English" {{ old('language')=='English'?'selected':'' }}>English</option>
                </select>
                <div class="invalid-feedback">Please select a language.</div>
              </div>
              <div class="col-md-4">
                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                <select name="country" id="country" required class="form-select">
                  <option value="" disabled selected>Select country</option>
                  @foreach($countries as $country)
                    <option value="{{ $country->id }}" {{ old('country')==$country->id?'selected':'' }}>{{ $country->name }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">Please select a country.</div>
              </div>
              <div class="col-md-4">
                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                <select name="currency" id="currency" required class="form-select">
                  <option value="" disabled selected>Select currency</option>
                  <option {{ old('currency')=='USD'?'selected':'' }}>USD</option>
                  <option {{ old('currency')=='CAD'?'selected':'' }}>CAD</option>
                  <option {{ old('currency')=='GBP'?'selected':'' }}>GBP</option>
                </select>
                <div class="invalid-feedback">Please select a currency.</div>
              </div>
            </div>

            {{-- 2) Name & Slug --}}
            <h5 class="mt-4">2. Name Your Shop</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-8">
                <label for="name" class="form-label">Shop Name <span class="text-danger">*</span></label>
                <input 
                  id="name" name="name" type="text"
                  x-model="name"
                  required
                  class="form-control"
                  placeholder="e.g. MyCraftShop"
                >
                <div class="invalid-feedback">Please enter your shop name.</div>
              </div>
              <div class="col-md-4">
                <label for="slug" class="form-label">Slug (URL Identifier)</label>
                <div class="input-group">
                  <span class="input-group-text">{{ url('shop') }}/</span>
                  <input 
                    id="slug" name="slug" type="text"
                    x-model="slug"
                    readonly
                    class="form-control bg-light"
                  >
                </div>
                <div class="form-text">Auto-generated from your shop name.</div>
              </div>
            </div>

           

            {{-- 3) Share Your Billing Info --}}
            <h5 class="mt-4">3. Share Your Billing Info</h5>
            <div class="row g-3 mb-4">
              <div class="col-12">
                <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
                <input 
                  id="address" name="address" type="text"
                  value="{{ old('address') }}"
                  required
                  class="form-control"
                  placeholder="123 Main St"
                >
                <div class="invalid-feedback">Please enter your address.</div>
              </div>
              <div class="col-md-6">
                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                <input 
                  id="city" name="city" type="text"
                  value="{{ old('city') }}"
                  required
                  class="form-control"
                  placeholder="Anytown"
                >
                <div class="invalid-feedback">Please enter your city.</div>
              </div>
              <div class="col-md-6">
                <label for="postal" class="form-label">Postal Code <span class="text-danger">*</span></label>
                <input 
                  id="postal" name="postal" type="text"
                  value="{{ old('postal') }}"
                  required
                  class="form-control"
                  placeholder="12345"
                >
                <div class="invalid-feedback">Please enter your postal code.</div>
              </div>
            </div>

            {{-- Submit --}}
            <div class="text-end mb-3">
              <button 
                type="submit"
                class="btn btn-success px-5"
              >
                Finish & Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Bootstrap validation script --}}
@push('scripts')
<script>
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
  })()
</script>
@endpush

@endsection
