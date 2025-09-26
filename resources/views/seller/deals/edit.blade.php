@extends('layouts.app')

@section('content')
<div class="content">
  <h1 class="mb-4">Edit Deal</h1>
  <form action="{{ route('seller.deals.update', $deal) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Deal Name --}}
    <div class="mb-3">
      <label class="form-label">Deal Name</label>
      <input
        type="text"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $deal->name) }}"
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
        value="{{ old('discount_percent', $deal->discount_percent) }}"
        required
      >
      @error('discount_percent')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Applies To All --}}
    <div class="form-check mb-4">
      <input
        type="checkbox"
        name="applies_to_all"
        id="applies_to_all"
        class="form-check-input"
        {{ old('applies_to_all', $deal->applies_to_all) ? 'checked' : '' }}
      >
      <label for="applies_to_all" class="form-check-label fw-semibold">
        <i class="fas fa-globe me-1"></i>Apply to all products
      </label>
      <div class="form-text">When checked, this deal will apply to all products in your shop</div>
    </div>

    {{-- Product Selector (hidden when applies_to_all is checked) --}}
    <div class="mb-4" id="product-selector">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="form-label fw-semibold mb-0">
          <i class="fas fa-box me-1"></i>Select Specific Products
        </label>
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-primary" id="select-all-products">
            <i class="fas fa-check-double me-1"></i>Select All
          </button>
          <button type="button" class="btn btn-outline-secondary" id="deselect-all-products">
            <i class="fas fa-times me-1"></i>Clear All
          </button>
        </div>
      </div>

      {{-- Search Box --}}
      <div class="mb-3">
        <input
          type="text"
          id="product-search"
          class="form-control"
          placeholder="Search products by name..."
        >
      </div>

      {{-- Product Selection Area --}}
      <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
        <div class="row g-2" id="product-list">
          @foreach($products as $product)
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
                      {{ in_array($product->id, old('product_ids', $deal->products->pluck('id')->toArray())) ? 'checked' : '' }}
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
                              <span class="badge bg-light text-dark">{{ $product->product_type ?? 'Product' }}</span>
                              @if($product->stock > 0)
                                <span class="badge bg-success">In Stock ({{ $product->stock }})</span>
                              @else
                                <span class="badge bg-danger">Out of Stock</span>
                              @endif
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
        </div>
        
        @if($products->isEmpty())
          <div class="text-center py-4">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">No products available</p>
            <small class="text-muted">Create some products first to add them to deals</small>
          </div>
        @endif
      </div>

      {{-- Selected Count --}}
      <div class="mt-2">
        <small class="text-muted">
          <span id="selected-count">0</span> products selected
        </small>
      </div>

      @error('product_ids')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    {{-- Starts At --}}
    <div class="mb-3">
      <label class="form-label">Starts At</label>
      <input
        type="datetime-local"
        name="starts_at"
        class="form-control @error('starts_at') is-invalid @enderror"
        value="{{ old('starts_at', $deal->starts_at->format('Y-m-d\TH:i')) }}"
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
        value="{{ old('ends_at', $deal->ends_at->format('Y-m-d\TH:i')) }}"
        required
      >
      @error('ends_at')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Deal Status Info --}}
    <div class="alert alert-info">
      <strong>Deal Status:</strong> 
      @if($deal->isActive())
        <span class="text-success">Currently Active</span>
      @elseif($deal->starts_at->isFuture())
        <span class="text-warning">Scheduled (starts {{ $deal->starts_at->diffForHumans() }})</span>
      @else
        <span class="text-danger">Expired (ended {{ $deal->ends_at->diffForHumans() }})</span>
      @endif
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-success">Update Deal</button>
      <a href="{{ route('seller.deals.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection

@push('styles')
<style>
.product-card {
  transition: all 0.2s ease;
  cursor: pointer;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-card.border-primary {
  border-width: 2px !important;
}

.product-checkbox:checked + label .product-card {
  border-color: var(--bs-primary) !important;
  background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

#product-list {
  min-height: 200px;
}

.product-item {
  transition: opacity 0.3s ease;
}

.product-item[style*="display: none"] {
  opacity: 0;
}

#selected-count {
  font-weight: 600;
  color: var(--bs-primary);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const appliesCheckbox = document.getElementById('applies_to_all');
  const selector = document.getElementById('product-selector');
  const productSearch = document.getElementById('product-search');
  const selectAllBtn = document.getElementById('select-all-products');
  const deselectAllBtn = document.getElementById('deselect-all-products');
  const selectedCount = document.getElementById('selected-count');
  const productCheckboxes = document.querySelectorAll('.product-checkbox');

  function toggleSelector() {
    selector.style.display = appliesCheckbox.checked ? 'none' : 'block';
  }

  function updateSelectedCount() {
    const checked = document.querySelectorAll('.product-checkbox:checked').length;
    selectedCount.textContent = checked;
  }

  function filterProducts() {
    const searchTerm = productSearch.value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
      const productName = item.getAttribute('data-product-name');
      const matches = productName.includes(searchTerm);
      item.style.display = matches ? 'block' : 'none';
    });
  }

  function selectAllProducts() {
    productCheckboxes.forEach(checkbox => {
      if (checkbox.closest('.product-item').style.display !== 'none') {
        checkbox.checked = true;
      }
    });
    updateSelectedCount();
  }

  function deselectAllProducts() {
    productCheckboxes.forEach(checkbox => {
      checkbox.checked = false;
    });
    updateSelectedCount();
  }

  // Initialize on load
  toggleSelector();
  updateSelectedCount();

  // Toggle on change
  appliesCheckbox.addEventListener('change', toggleSelector);

  // Search functionality
  productSearch.addEventListener('input', filterProducts);

  // Select all/none functionality
  selectAllBtn.addEventListener('click', selectAllProducts);
  deselectAllBtn.addEventListener('click', deselectAllProducts);

  // Update count when checkboxes change
  productCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
  });

  // Add visual feedback for product cards
  productCheckboxes.forEach(checkbox => {
    const card = checkbox.closest('.product-card');
    checkbox.addEventListener('change', function() {
      if (this.checked) {
        card.classList.add('border-primary', 'bg-light');
      } else {
        card.classList.remove('border-primary', 'bg-light');
      }
    });
  });

  // Initialize visual state for pre-selected products
  productCheckboxes.forEach(checkbox => {
    const card = checkbox.closest('.product-card');
    if (checkbox.checked) {
      card.classList.add('border-primary', 'bg-light');
    }
  });
});
</script>
@endpush
