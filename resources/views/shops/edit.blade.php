{{-- resources/views/shops/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
    <h2 class="text-center fw-bold mb-5">Edit Your Shop</h2>

    {{-- Flash Success --}}
    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
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
      action="{{ route('seller.shops.update', $shop) }}" 
      method="POST" 
      enctype="multipart/form-data"
      x-data="{ 
        name: '{{ old('name', $shop->name) }}', 
        slug: '{{ old('slug', $shop->slug) }}' 
      }"
      @input.debounce.300ms="
        slug = name.toLowerCase()
                   .trim()
                   .replace(/[^a-z0-9]+/g,'-')
                   .replace(/(^-|-$)/g,'');
      "
    >
      @csrf
      @method('PATCH')

      {{-- 1) Shop Preferences --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">1. Shop Preferences</div>
        <div class="card-body row g-3">
          <div class="col-md-4">
            <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
            <select name="language" id="language" class="form-select" required>
              <!-- <option disabled selected>Select language</option> -->
              <option value="English" {{ old('language', $shop->language)=='English' ? 'selected':'' }}>{{$shop->language}}</option>
             
            </select>
          </div>
          <div class="col-md-4">
            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
            <select name="country" id="country" class="form-select" required>
              <option value="{{$shop->country}}" {{ old('country', $shop->country)=='United States' ? 'selected':'' }}>{{country_name($shop->country)}}</option>
              @foreach($countries as $country)
                <option value="{{$country->id}}" {{ old('country', $shop->country)=='United States' ? 'selected':'' }}>{{country_name($country->id)}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
            <select name="currency" id="currency" class="form-select" required>
              <option disabled selected>Select currency</option>
              <option value="USD" {{ old('currency', $shop->currency)=='USD' ? 'selected':'' }}>USD</option>
              <option value="CAD" {{ old('currency', $shop->currency)=='CAD' ? 'selected':'' }}>CAD</option>
              <option value="GBP" {{ old('currency', $shop->currency)=='GBP' ? 'selected':'' }}>GBP</option>
            </select>
          </div>
        </div>
      </div>

      {{-- 2) Name & Slug --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">2. Name Your Shop</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="name" class="form-label">Shop Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" x-model="name" class="form-control" required>
          </div>
          <div>
            <label for="slug" class="form-label">Slug (URL Identifier)</label>
            <div class="input-group">
              <span class="input-group-text">{{ url('shop') }}/</span>
              <input type="text" id="slug" name="slug" x-model="slug" class="form-control" required>
            </div>
            <div class="form-text">You may customize, but it must be unique.</div>
          </div>
        </div>
      </div>

      

      {{-- 3) Billing Info --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">4. Share Your Billing Info</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
            <input type="text" id="address" name="address" value="{{ old('address', $shop->address) }}" class="form-control" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="city" class="form-label">City <span class="text-danger">*</span></label>
              <input type="text" id="city" name="city" value="{{ old('city', $shop->city) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="postal" class="form-label">Postal Code <span class="text-danger">*</span></label>
              <input type="text" id="postal" name="postal" value="{{ old('postal', $shop->postal) }}" class="form-control" required>
            </div>
          </div>
        </div>
      </div>

      {{-- 4) Security --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">5. Your Shop Security</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="password" class="form-label">Confirm Your Password <span class="text-danger">*</span></label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your account password">
          </div>
          <input type="hidden" name="enable_2fa" value="0">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="enable_2fa" name="enable_2fa" {{ old('enable_2fa', $shop->enable_2fa) ? 'checked' : '' }}>
            <label class="form-check-label" for="enable_2fa">Enable two-factor authentication</label>
          </div>

          <div class="mt-4">
            <label for="logo" class="form-label">Logo (optional)</label>
            <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
            @if($shop->logo_url)
              <div class="mt-2">
                <img src="{{ $shop->logo_url }}" alt="logo" class="rounded-circle" width="50" height="50">
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <div class="text-end">
        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
      </div>
    </form>
</div>
@endsection
