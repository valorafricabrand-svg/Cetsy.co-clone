@extends('layouts.app')
@section('title', $product->name . ' | Edit Settings')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .tab-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
  .tab-scroll .nav-link{border-radius:999px}
  .rounded-4{border-radius:1rem!important}
</style>
@endpush

@section('content')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<div class="content">
  {{-- Header tabs --}}
  <div class="page-header-sticky">
    <div class="container-fluid px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class="nav-item"><a class="nav-link {{ $current === 'products.show' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question me-1"></i> About</a></li>
          <li class="nav-item"><a class="nav-link {{ $current === 'products.pricing' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags me-1"></i> Price & Inventory</a></li>
          <li class="nav-item"><a class="nav-link {{ $current === 'products.variations' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group me-1"></i> Variations</a></li>
          <li class="nav-item"><a class="nav-link {{ $current === 'products.details' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list me-1"></i> Details</a></li>
          <li class="nav-item"><a class="nav-link {{ $current === 'products.shipping' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck me-1"></i> Shipping</a></li>
          <li class="nav-item"><a class="nav-link {{ $current === 'products.settings' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear me-1"></i> Settings</a></li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} — Edit Settings</h2>
    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-dark btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>

  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
      <form action="{{ route('products.settings.update', $product) }}" method="POST">
        @csrf @method('PATCH')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Listing Status</label>
            @php
              $isActive = (int) old('is_active', $product->is_active);
              $hasFeatured = !empty($product->featured_image);
              // A listing is eligible to activate when it's been paid at least once
              // and the next due date is either empty or in the future.
              $eligibleToActivate = !empty($product->listing_paid_at)
                                    && (empty($product->next_due_date) || \Carbon\Carbon::parse($product->next_due_date)->isFuture());
              $canToggleOn = $eligibleToActivate && $hasFeatured;
            @endphp
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="statusToggle"
                     {{ $isActive===1 ? 'checked' : '' }} {{ $canToggleOn || $isActive===1 ? '' : 'disabled' }}>
              <label class="form-check-label" for="statusToggle">{{ $isActive===1 ? 'Active' : 'Paused' }}</label>
            </div>
            <input type="hidden" name="is_active" id="is_active" value="{{ $isActive===1 ? 1 : 2 }}">
            <div class="form-text">
              @if(!$hasFeatured)
                Add a featured image to enable activation.
              @endif
              {{-- Only show pay/renew guidance if the listing is not currently active --}}
              @if($isActive !== 1 && !$eligibleToActivate)
                @if(empty($product->listing_paid_at))
                  <div class="mt-1">
                    <span>Pay the listing fee to activate.</span>
                    <form class="d-inline" method="POST" action="{{ route('products.pay-fee', $product) }}">
                      @csrf
                      <input type="hidden" name="plan" value="monthly">
                      <button class="btn btn-link p-0 align-baseline">Pay to activate</button>
                    </form>
                  </div>
                @else
                  <div class="mt-1">Renew your listing to activate.</div>
                @endif
              @endif
            </div>
            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Renewal Type</label>
            <select name="renewal_type" class="form-select @error('renewal_type') is-invalid @enderror" required>
              <option value="automatic" {{ old('renewal_type',$product->renewal_type)==='automatic' ? 'selected' : '' }}>Automatic</option>
              <option value="manual"    {{ old('renewal_type',$product->renewal_type)==='manual'    ? 'selected' : '' }}>Manual</option>
            </select>
            @error('renewal_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Visibility</label>
            @php $visibility = old('visibility', $product->visibility ?? 'Public'); @endphp
            <select name="visibility" class="form-select @error('visibility') is-invalid @enderror">
              <option value="Public"  {{ $visibility==='Public'  ? 'selected' : '' }}>Public</option>
              <option value="Private" {{ $visibility==='Private' ? 'selected' : '' }}>Private</option>
              <option value="Unlisted"{{ $visibility==='Unlisted'? 'selected' : '' }}>Unlisted</option>
            </select>
            @error('visibility') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Slug (optional)</label>
            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                   value="{{ old('slug', $product->slug) }}" placeholder="custom-url-slug">
            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Tags (comma-separated, optional)</label>
            <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror"
                   value="{{ old('tags', $product->tags ?? '') }}" placeholder="electronics, phone, samsung">
            @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
          <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var cb = document.getElementById('statusToggle');
    var hidden = document.getElementById('is_active');
    if(cb && hidden){
      cb.addEventListener('change', function(){ hidden.value = cb.checked ? 1 : 2; });
    }
  });
  </script>
@endpush
