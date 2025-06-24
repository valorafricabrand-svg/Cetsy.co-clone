{{-- resources/views/admin/settings/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <h1 class="mb-4 fw-bold">Site Settings</h1>

  <form action="{{ route('settings.update', $settings->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="needs-validation"
        novalidate>
    @csrf
    @method('PUT')

    <!-- ========== BRANDING ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Branding</div>
      <div class="card-body">
        <div class="row g-3">

          <!-- Site name / meta -->
          <div class="col-md-6">
            <label class="form-label">Site Name</label>
            <input type="text" name="site_name"
                   class="form-control @error('site_name') is-invalid @enderror"
                   value="{{ old('site_name', $settings->site_name) }}" required>
            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Meta Description</label>
            <input type="text" name="meta_description"
                   class="form-control @error('meta_description') is-invalid @enderror"
                   value="{{ old('meta_description', $settings->meta_description) }}" required>
            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Logo upload & URL -->
          <div class="col-md-6">
            <label class="form-label">Logo Image (PNG/JPG&nbsp;&le;&nbsp;2 MB)</label>
            <input type="file" name="logo" accept="image/*"
                   class="form-control @error('logo') is-invalid @enderror">
            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if($settings->logo_url)
              <div class="mt-2">
                <img src="{{ $settings->logo_url }}" alt="Current logo"
                     class="img-thumbnail" style="max-height:80px">
              </div>
            @endif

            <small class="text-muted">…or keep / paste a URL:</small>
            <input type="text" name="logo_url"
                   class="form-control mt-1 @error('logo_url') is-invalid @enderror"
                   value="{{ old('logo_url', $settings->logo_url) }}">
            @error('logo_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Favicon upload & URL -->
          <div class="col-md-6">
            <label class="form-label">Favicon Image (PNG/ICO/SVG&nbsp;&le;&nbsp;1 MB)</label>
            <input type="file" name="favicon" accept="image/*,.ico"
                   class="form-control @error('favicon') is-invalid @enderror">
            @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if($settings->favicon_url)
              <div class="mt-2">
                <img src="{{ $settings->favicon_url }}" alt="Current favicon"
                     class="img-thumbnail" style="max-height:48px">
              </div>
            @endif

            <small class="text-muted">…or keep / paste a URL:</small>
            <input type="text" name="favicon_url"
                   class="form-control mt-1 @error('favicon_url') is-invalid @enderror"
                   value="{{ old('favicon_url', $settings->favicon_url) }}">
            @error('favicon_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

        </div>
      </div>
    </div>

    <!-- ========== CONTACT ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Contact</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $settings->phone) }}">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $settings->email) }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2"
                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $settings->address) }}</textarea>
            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">WhatsApp Number</label>
            <input type="text" name="whatsapp_number"
                   class="form-control @error('whatsapp_number') is-invalid @enderror"
                   value="{{ old('whatsapp_number', $settings->whatsapp_number) }}">
            @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Timezone</label>
            <input type="text" name="timezone"
                   class="form-control @error('timezone') is-invalid @enderror"
                   value="{{ old('timezone', $settings->timezone) }}"
                   placeholder="e.g. Africa/Nairobi" required>
            @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- ========== SOCIAL LINKS ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Social Links</div>
      <div class="card-body">
        <div class="row g-3">
          @php
            $social = [
              'facebook_url'  => 'Facebook URL',
              'instagram_url' => 'Instagram URL',
              'x_url'         => 'X (Twitter) URL',
              'linkedin_url'  => 'LinkedIn URL',
              'tiktok_url'    => 'TikTok URL',
              'youtube_url'   => 'YouTube URL',
            ];
          @endphp
          @foreach($social as $field => $label)
            <div class="col-md-6">
              <label class="form-label">{{ $label }}</label>
              <input type="url" name="{{ $field }}"
                     class="form-control @error($field) is-invalid @enderror"
                     value="{{ old($field, $settings->$field) }}">
              @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- ========== PAYMENT & CURRENCY ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Payment &amp; Currency</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">PayPal Client&nbsp;ID</label>
            <input type="text" name="paypal_client_id"
                   class="form-control @error('paypal_client_id') is-invalid @enderror"
                   value="{{ old('paypal_client_id', $settings->paypal_client_id) }}">
            @error('paypal_client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Default Currency</label>
            <input type="text" name="default_currency"
                   class="form-control @error('default_currency') is-invalid @enderror"
                   value="{{ old('default_currency', $settings->default_currency) }}"
                   placeholder="e.g. USD" required>
            @error('default_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-end">
      <button type="submit" class="btn btn-primary px-4">Save Changes</button>
      <a href="{{ url()->previous() }}" class="btn btn-outline-secondary ms-2">Cancel</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', evt => {
      if (!form.checkValidity()) {
        evt.preventDefault();
        evt.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
@endpush
@endsection
