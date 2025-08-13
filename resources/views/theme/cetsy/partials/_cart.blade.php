{{-- resources/views/theme/{{ theme() }}/partials/_details.blade.php --}}
@php
  $currency    = get_currency();
  $basePrice   = (float) ($product->price ?? 0);
  $salePrice   = (float) ($product->discounted_price ?? $basePrice);

  // Build a map of variant-combination -> price, where the key is sorted option IDs joined by '-'
  $variantPriceMap = [];
  if ($product->relationLoaded('variations')) {
      foreach ($product->variations as $v) {
          // A variant must have options and a price to be considered
          if (($v->price ?? null) !== null && $v->options && $v->options->count()) {
              $key = $v->options->pluck('id')->sort()->implode('-');
              $variantPriceMap[$key] = (float) $v->price;
          }
      }
  } else {
      // In case not eager-loaded above, load minimal
      $product->loadMissing('variations.options');
      foreach ($product->variations as $v) {
          if (($v->price ?? null) !== null && $v->options && $v->options->count()) {
              $key = $v->options->pluck('id')->sort()->implode('-');
              $variantPriceMap[$key] = (float) $v->price;
          }
      }
  }

  // Lowest variation price if any, otherwise fall back to the product's sale/base price
  $lowestVariantPrice = count($variantPriceMap)
      ? min($variantPriceMap)
      : null;

  // Default display price:
  // - If there are variants -> show the lowest variant price (like "From ...")
  // - Else -> show product's sale price (with strike-through base if discounted)
  $defaultDisplayPrice = $lowestVariantPrice ?? $salePrice;

  // For the UI we’ll format once; JS will reformat on change.
  $format = fn($amount) => number_format((float)$amount, 2);
@endphp

<div class="position-lg-sticky" style="top: 1rem;">
  <h1 class="h2 fw-bold">{{ $product->name }}</h1>

  {{-- Ratings --}}
  <div class="mb-2">
    @php $avg = round($product->reviews_avg_rating ?? 0); @endphp
    @for($i = 1; $i <= 5; $i++)
      <i class="fa-star{{ $i <= $avg ? ' fa-solid text-warning' : ' fa-regular text-muted' }}"></i>
    @endfor
    <small class="ms-1 text-muted">({{ $product->reviews_count ?? 0 }} reviews)</small>
  </div>

  {{-- Price block --}}
  @if ($lowestVariantPrice !== null)
    {{-- We have variant pricing: show "From ..." by default, and JS will live-update on selection --}}
    <div id="js-price-block"
         class="d-flex align-items-baseline gap-3 mb-3"
         data-currency="{{ $currency }}"
         data-default-amount="{{ $defaultDisplayPrice }}"
         data-variant-map='@json($variantPriceMap)'
    >
      <span class="fw-bold text-success">
        <span class="me-1 small text-muted">From</span>
        <span id="js-price-amount">{{ $currency }} {{ $format($defaultDisplayPrice) }}</span>
      </span>
    </div>
  @else
    {{-- No variant pricing: keep your existing discount logic --}}
    @if ($salePrice < $basePrice)
      <div id="js-price-block"
           class="d-flex align-items-baseline gap-3 mb-3"
           data-currency="{{ $currency }}"
           data-default-amount="{{ $salePrice }}"
           data-variant-map='{}'>
        <span class="fw-bold text-success">
          <span id="js-price-amount">{{ $currency }} {{ $format($salePrice) }}</span>
        </span>
        <span class="text-muted text-decoration-line-through">
          {{ $currency }} {{ $format($basePrice) }}
        </span>
      </div>
    @else
      <p id="js-price-block"
         class="fw-bold text-success mb-3"
         data-currency="{{ $currency }}"
         data-default-amount="{{ $basePrice }}"
         data-variant-map='{}'>
        <span id="js-price-amount">{{ $currency }} {{ $format($basePrice) }}</span>
      </p>
    @endif
  @endif

  {{-- Shop & Stock badges --}}
  <div class="mb-3 d-flex flex-wrap gap-2">
    <span class="badge bg-success bg-opacity-10 text-success">
      <i class="fa-solid fa-store me-1"></i>
      <a href="{{ route('shop.show', $product->shop->slug) }}"
         class="text-success text-decoration-none">
        {{ $product->shop->name }}
      </a>
    </span>
    @if ($product->type === 'physical')
      <span class="badge {{ $product->stock > 0 ? 'bg-primary bg-opacity-10 text-primary' : 'bg-danger bg-opacity-10 text-danger' }}">
        {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
      </span>
    @endif
  </div>

  {{-- Quick-actions --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    <form method="POST" action="{{ route('favorites.toggle') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Add to favourites">
        <i class="fa-regular fa-heart{{ $isFavorited ? ' text-danger fa-solid' : '' }}"></i>
        Favourites
      </button>
    </form>
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#offerModal">
      <i class="fa-solid fa-hand-holding-dollar me-1"></i>Make an offer
    </button>
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#messageModal">
      <i class="fa-regular fa-comments me-1"></i>Message seller
    </button>
  </div>

  {{-- Highlights --}}
  @if ($product->highlights)
    <ul class="list-unstyled mb-4 small">
      @foreach ($product->highlights as $highlight)
        <li class="d-flex mb-1">
          <i class="fa-solid fa-check text-success me-2"></i>
          <span>{{ $highlight }}</span>
        </li>
      @endforeach
    </ul>
  @endif

  {{-- Category & Country --}}
  <p class="mb-2">
    <strong class="me-1">Category:</strong>
    @if ($product->category)
      <a href="{{ route('category.show', $product->category->slug) }}"
         class="badge bg-success bg-opacity-25 text-success text-decoration-none">
        {{ $product->category->name }}
      </a>
    @else
      <span class="badge bg-secondary">Uncategorised</span>
    @endif
  </p>
  @if ($product->country)
    <p class="mb-4 small text-muted">
      <i class="fa-solid fa-globe-africa me-1"></i>
      Ship from {{ $product->country->name }}
    </p>
  @endif
</div>

@php
  // Only show the cart block for non-service products
  $showCart = ($product->type ?? '') !== 'service';
@endphp

@if($showCart)
  <aside class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="POST" action="{{ route('cart.add') }}">
        @csrf

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
                class="form-select js-variant-select"
                required
                data-variation-type-id="{{ $type->id }}"
              >
                <option value="" disabled selected>Select {{ strtolower($type->name) }}</option>
                @foreach($type->options as $opt)
                  <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                @endforeach
              </select>
            </div>
          @endforeach
        </div>

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

@push('scripts')
<script>
  (function(){
    const priceBlock   = document.getElementById('js-price-block');
    if (!priceBlock) return;

    const priceNode    = document.getElementById('js-price-amount');
    const currency     = priceBlock.getAttribute('data-currency') || '';
    const defaultAmt   = parseFloat(priceBlock.getAttribute('data-default-amount') || '0') || 0;
    const variantMap   = JSON.parse(priceBlock.getAttribute('data-variant-map') || '{}');

    const selects = Array.from(document.querySelectorAll('.js-variant-select'));

    // Helper: format to 2dp without forcing a locale (server already formats for default view)
    function fmt(amount) {
      return currency + ' ' + Number(amount).toFixed(2);
    }

    function getSelectedKey() {
      // Require all selects to have a value to consider a full combination
      if (!selects.length) return null;
      const vals = [];
      for (const s of selects) {
        if (!s.value) return null;
        vals.push(parseInt(s.value, 10));
      }
      // Create normalized key (sorted ids joined with '-')
      return vals.sort((a,b)=>a-b).join('-');
    }

    function updatePrice() {
      const key = getSelectedKey();
      if (key && Object.prototype.hasOwnProperty.call(variantMap, key)) {
        priceNode.textContent = fmt(variantMap[key]);
      } else {
        // Reset to default ("From ..." lowest variant price, or product price if no variants)
        priceNode.textContent = fmt(defaultAmt);
      }
    }

    // Bind events
    selects.forEach(s => s.addEventListener('change', updatePrice));

    // Initial render (in case some selects come prefilled)
    updatePrice();
  })();
</script>
@endpush
