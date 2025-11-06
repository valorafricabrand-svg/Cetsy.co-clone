@extends('layouts.app')
@section('title', $product->name . ' | Edit Price & Inventory')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .tab-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
  .tab-scroll .nav-link{border-radius:999px}
  .rounded-4,.rounded-top-4{border-radius:1rem!important}
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
          <li class="nav-item"><a class="nav-link {{ $current==='products.show' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question me-1"></i> About</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.pricing' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags me-1"></i> Price & Inventory</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.variations' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group me-1"></i> Variations</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.details' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list me-1"></i> Details</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.shipping' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck me-1"></i> Shipping</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.media' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.media', $product) }}"><i class="fa-regular fa-images me-1"></i> Media</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.settings' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear me-1"></i> Settings</a></li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 ps-3">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
  @endif

  {{-- Heading --}}
  <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} — Edit Price & Inventory</h2>
    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-dark btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>

  {{-- Form --}}
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
      <form action="{{ route('products.pricing.update', $product) }}" method="POST" novalidate x-data="pricingForm()">
        @csrf @method('PATCH')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label"
                   x-data="{ t: '{{ $product->type }}' }"
                   x-text="t==='service' ? 'Priced From ({{ get_currency() }})' : 'Price ({{ get_currency() }})'">
              Price ({{ get_currency() }})
            </label>
            <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror"
                   x-model.number="price" value="{{ old('price', $product->price) }}" required>
            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">% Discount</label>
            <input type="number" step="1" min="0" max="100" name="discount_percent"
                   class="form-control @error('discount_percent') is-invalid @enderror"
                   x-model.number="discount" value="{{ old('discount_percent', $product->discount_percent) }}">
            @error('discount_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Final: <strong x-text="formattedFinal()"></strong></div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Stock</label>
            <input type="number" step="1" min="0" name="stock" class="form-control @error('stock') is-invalid @enderror"
                   value="{{ old('stock', $product->stock) }}">
            @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">SKU (optional)</label>
            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                   value="{{ old('sku', $product->sku) }}">
            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function pricingForm(){
  return {
    price: Number('{{ old('price', $product->price ?? 0) }}') || 0,
    discount: Number('{{ old('discount_percent', $product->discount_percent ?? 0) }}') || 0,
    finalAmount(){ const p=Number(this.price)||0, d=Math.min(100,Math.max(0,Number(this.discount)||0)); return p*(1-d/100); },
    formattedFinal(){ return '{{ get_currency() }} ' + (this.finalAmount().toFixed(2)); }
  }
}
</script>
@endpush
