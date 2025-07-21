@extends('layouts.app')

@section('content')
<div class="content">
  <h1 class="mb-4">Create Deal</h1>
  <form action="{{ route('seller.deals.store') }}" method="POST">
    @csrf

    {{-- Deal Name --}}
    <div class="mb-3">
      <label class="form-label">Deal Name</label>
      <input
        type="text"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name') }}"
        required
      >
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- % Discount --}}
    <div class="mb-3">
      <label class="form-label">% Discount</label>
      <input
        type="number"
        name="discount_percent"
        min="1"
        max="100"
        class="form-control @error('discount_percent') is-invalid @enderror"
        value="{{ old('discount_percent') }}"
        required
      >
      @error('discount_percent')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Applies To All --}}
    <div class="form-check mb-3">
      <input
        type="checkbox"
        name="applies_to_all"
        id="applies_to_all"
        class="form-check-input"
        {{ old('applies_to_all') ? 'checked' : '' }}
      >
      <label for="applies_to_all" class="form-check-label">
        Applies to all products
      </label>
    </div>

    {{-- Product Selector (hidden when applies_to_all is checked) --}}
    <div class="mb-3" id="product-selector">
      <label class="form-label">Select Products</label>
      <select
        name="product_ids[]"
        multiple
        size="8"
        class="form-select @error('product_ids') is-invalid @enderror"
      >
        @foreach($products as $p)
          <option
            value="{{ $p->id }}"
            {{ in_array($p->id, old('product_ids', [])) ? 'selected' : '' }}
          >
            {{ $p->name }}
          </option>
        @endforeach
      </select>
      @error('product_ids')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Starts At --}}
    <div class="mb-3">
      <label class="form-label">Starts At</label>
      <input
        type="datetime-local"
        name="starts_at"
        class="form-control @error('starts_at') is-invalid @enderror"
        value="{{ old('starts_at') }}"
        required
      >
      @error('starts_at')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Ends At --}}
    <div class="mb-3">
      <label class="form-label">Ends At</label>
      <input
        type="datetime-local"
        name="ends_at"
        class="form-control @error('ends_at') is-invalid @enderror"
        value="{{ old('ends_at') }}"
        required
      >
      @error('ends_at')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <button class="btn btn-success">Save Deal</button>
  </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const appliesCheckbox = document.getElementById('applies_to_all');
  const selector       = document.getElementById('product-selector');

  function toggleSelector() {
    selector.style.display = appliesCheckbox.checked ? 'none' : 'block';
  }

  // Initialize on load
  toggleSelector();

  // Toggle on change
  appliesCheckbox.addEventListener('change', toggleSelector);
});
</script>
@endpush
