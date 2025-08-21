{{-- resources/views/theme/{{ theme() }}/partials/_cart.blade.php --}}

@php
  // Only show the cart block for non-service products
  $showCart = ($product->type ?? '') !== 'service';
@endphp

@if($showCart)
  <aside class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="POST" action="{{ route('cart.add') }}">
        @csrf

        {{-- Hidden product_id --}}
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="row g-3 mb-4">
          {{-- Quantity --}}
          <div class="col-12 col-sm-6">
            <label for="quantity" class="form-label">Quantity</label>
            <input
              type="number"
              name="quantity"
              id="quantity"
              class="form-control"
              min="1"
              value="1"
              required
            >
          </div>

          {{-- Variation dropdowns (each sends its opt ID in variations[]) --}}
          @foreach($product->variationTypes as $type)
            <div class="col-12 col-sm-6">
              <label for="var-{{ $type->id }}" class="form-label">{{ $type->name }}</label>
              <select
                name="variations[]"
                id="var-{{ $type->id }}"
                class="form-select"
                required
              >
                <option value="" disabled selected>Select {{ strtolower($type->name) }}</option>
                @foreach($type->options as $opt)
                  <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                @endforeach
              </select>
            </div>
          @endforeach
        </div>

        {{-- Add to Cart --}}
        <div class="d-grid">
          <button type="submit" class="btn btn-success btn-lg">
            <i class="fa-solid fa-cart-plus me-1"></i>
            Add to Cart
          </button>
        </div>
      </form>
    </div>
  </aside>
@endif