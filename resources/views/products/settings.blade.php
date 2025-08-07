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
            <label class="form-label">Status</label>
            @php $isActive = (int) old('is_active', $product->is_active); @endphp
            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
              <option value="0" {{ $isActive===0 ? 'selected' : '' }}>Pending</option>
              <option value="1" {{ $isActive===1 ? 'selected' : '' }}>Active</option>
              <option value="2" {{ $isActive===2 ? 'selected' : '' }}>Paused</option>
              <option value="3" {{ $isActive===3 ? 'selected' : '' }}>Suspended</option>
              <option value="4" {{ $isActive===4 ? 'selected' : '' }}>Closed</option>
            </select>
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
