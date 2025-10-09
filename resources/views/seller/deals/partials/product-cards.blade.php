in seller @foreach($products as $product)
  @php
    $ptype = strtolower((string)($product->product_type ?? $product->type ?? ''));
    $isDigital = in_array($ptype, ['digital','download','digital_download','digital-download']);
  @endphp
  <div class="col-md-6 col-lg-4 product-item" data-product-name="{{ strtolower($product->name) }}">
    <div class="card h-100 product-card">
      <div class="card-body p-3">
        <div class="form-check">
          <input
            class="form-check-input product-checkbox"
            type="checkbox"
            name="product_ids[]"
            value="{{ $product->id }}"
            id="product_{{ $product->id }}"
            {{ in_array($product->id, $selectedIds) ? 'checked' : '' }}
          >
          <label class="form-check-label w-100" for="product_{{ $product->id }}">
            <div class="d-flex align-items-start">
              @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" 
                     class="rounded me-3" 
                     style="width: 50px; height: 50px; object-fit: cover;"
                     alt="{{ $product->name }}">
              @else
                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                     style="width: 50px; height: 50px;">
                  <i class="fas fa-image text-muted"></i>
                </div>
              @endif
              <div class="flex-grow-1">
                <h6 class="mb-1 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                <div class="text-muted small">
                  <div class="d-flex justify-content-between">
                    <span>Price: <strong>{{ get_currency() }} {{ number_format($product->price, 2) }}</strong></span>
                    @if($product->discount_percent > 0)
                      <span class="text-success">Already {{ $product->discount_percent }}% off</span>
                    @endif
                  </div>
                  <div class="mt-1">
                    <span class="badge bg-light text-dark">{{ $isDigital ? 'Digital' : ($product->product_type ?? 'Product') }}</span>
                    @unless($isDigital)
                      @if($product->stock > 0)
                        <span class="badge bg-success">In Stock ({{ $product->stock }})</span>
                      @else
                        <span class="badge bg-danger">Out of Stock</span>
                      @endif
                    @endunless
                  </div>
                </div>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>
  </div>
@endforeach
