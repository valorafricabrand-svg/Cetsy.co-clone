{{-- resources/views/theme/{{ theme() }}/partials/_details.blade.php --}}
@php
  $currency    = get_currency();
  $basePrice   = (float) ($product->price ?? 0);
  $salePrice   = (float) ($product->discounted_price ?? $basePrice);

  // Ensure variations+options are available
  $product->loadMissing('variations.options', 'variationTypes.options');

  // Build a compact index: variant-combination key -> {id, price, options:[ids]}
  // Only include variants that have a price.
  $variantIndex = [];
  foreach ($product->variations ?? [] as $v) {
      if (($v->price ?? null) !== null && $v->options && $v->options->count()) {
          $ids = $v->options->pluck('id')->sort()->values();
          $key = $ids->implode('-');
          $variantIndex[$key] = [
              'id'      => (int) $v->id,
              'price'   => (float) $v->price,
              'options' => $ids->toArray(),
          ];
      }
  }

  // Precompute the lowest price that includes each single option id
  $optionMinPrice = [];
  foreach ($variantIndex as $entry) {
      foreach ($entry['options'] as $optId) {
          if (!isset($optionMinPrice[$optId])) {
              $optionMinPrice[$optId] = $entry['price'];
          } else {
              $optionMinPrice[$optId] = min($optionMinPrice[$optId], $entry['price']);
          }
      }
  }

  // Lowest variation price (if any priced variants exist)
  $lowestVariantPrice = !empty($variantIndex)
      ? min(array_column($variantIndex, 'price'))
      : null;

  // Default display price:
  // - If there are *priced* variants -> show the lowest variant price ("From …")
  // - Else -> show product’s sale/base price
  $defaultDisplayPrice = $lowestVariantPrice ?? $salePrice;

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

  {{-- Price block (JS updates #js-price-amount) --}}
  @if ($lowestVariantPrice !== null)
    <div id="js-price-block"
         class="d-flex align-items-baseline gap-3 mb-3"
         data-currency="{{ $currency }}"
         data-default-amount="{{ $defaultDisplayPrice }}"
         data-variant-index='@json($variantIndex)'>
      <span class="fw-bold text-success">
        <span id="js-from-label" class="me-1 small text-muted">From</span>
        <span id="js-price-amount">{{ $currency }} {{ $format($defaultDisplayPrice) }}</span>
      </span>
    </div>
  @else
    {{-- No priced variants: show product pricing (with discount style if applicable) --}}
    @if ($salePrice < $basePrice)
      <div id="js-price-block"
           class="d-flex align-items-baseline gap-3 mb-3"
           data-currency="{{ $currency }}"
           data-default-amount="{{ $salePrice }}"
           data-variant-index='{}'>
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
         data-variant-index='{}'>
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
      Ships from {{ $product->country->name }}
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
      <form method="POST" action="{{ route('cart.add') }}" id="js-cart-form">
        @csrf

        <input type="hidden" name="product_id" value="{{ $product->id }}">

        {{-- Hidden field that JS sets when a priced variant is fully selected --}}
        <input type="hidden" name="variant_id" id="js-variant-id">
        {{-- Optional: client-side convenience for showing in cart preview (server MUST ignore this and price from DB) --}}
        <input type="hidden" name="variant_price" id="js-variant-price">

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

          {{-- Variation dropdowns (each sends its option ID in variations[]) --}}
          @foreach($product->variationTypes as $type)
            <div class="col-12 col-sm-6">
              <label for="var-{{ $type->id }}" class="form-label">{{ $type->name }}</label>
              <select
                name="variations[]"
                id="var-{{ $type->id }}"
                class="form-select js-variant-select"
                required
                data-variation-type-id="{{ $type->id }}"
                data-option-min='@json(collect($type->options)->mapWithKeys(fn($o)=>[$o->id => $optionMinPrice[$o->id] ?? null]))'
              >
                <option value="" disabled selected>Select {{ strtolower($type->name) }}</option>
                @foreach($type->options as $opt)
                  <option
                    value="{{ $opt->id }}"
                    data-label="{{ $opt->value }}"
                  >{{ $opt->value }}</option>
                @endforeach
              </select>
            </div>
          @endforeach
        </div>

        {{-- Actions: Add to Cart + Buy Now --}}
        <div class="d-grid gap-2 d-sm-flex">
          <button type="submit" class="btn btn-success btn-lg" id="js-add-to-cart">
            <i class="fa-solid fa-cart-plus me-1"></i>
            Add to Cart
          </button>

          {{-- Override destination just for this button --}}
          <button type="submit"
                  class="btn btn-primary btn-lg"
                  id="js-buy-now"
                  formaction="{{ route('cart.buy') }}">
            <i class="fa-solid fa-bolt me-1"></i>
            Buy Now
          </button>
        </div>
      </form>
    </div>
  </aside>
@endif

@push('scripts')
<script>
  (function(){
    const priceBlock        = document.getElementById('js-price-block');
    if (!priceBlock) return;

    const priceNode         = document.getElementById('js-price-amount');
    const fromLabel         = document.getElementById('js-from-label');
    const variantIdNode     = document.getElementById('js-variant-id');
    const variantPriceNode  = document.getElementById('js-variant-price');

    const form              = document.getElementById('js-cart-form');

    const currency   = priceBlock.getAttribute('data-currency') || '';
    const defaultAmt = parseFloat(priceBlock.getAttribute('data-default-amount') || '0') || 0;

    // variant-index: { "1-12-33": { id: 7, price: 999.00, options:[1,12,33] }, ... }
    const variantIndex = JSON.parse(priceBlock.getAttribute('data-variant-index') || '{}');

    const selects = Array.from(document.querySelectorAll('.js-variant-select'));
    const primarySelect = selects.length ? selects[0] : null; // The first variation type (price driver, for labels)

    function fmt(amount) {
      return currency + ' ' + Number(amount).toFixed(2);
    }

    function allChosen() {
      return selects.every(s => !!s.value);
    }

    function getSelectedKey() {
      if (!allChosen()) return null;
      const ids = selects.map(s => parseInt(s.value, 10)).sort((a,b)=>a-b);
      return ids.join('-');
    }

    function clearVariantHidden() {
      if (variantIdNode) variantIdNode.value = '';
      if (variantPriceNode) variantPriceNode.value = '';
    }

    // Find min price across variants that include a specific option id (used for option labels and "first-type price" preview)
    function minPriceForOption(optionId) {
      let min = null;
      for (const key in variantIndex) {
        const entry = variantIndex[key];
        if (entry && Array.isArray(entry.options) && entry.options.indexOf(optionId) !== -1) {
          const p = parseFloat(entry.price);
          if (!Number.isNaN(p)) {
            if (min === null || p < min) min = p;
          }
        }
      }
      return min;
    }

    // Get price to display based solely on the FIRST variation type selection (if multiple types exist) — preview only
    function firstTypePrice() {
      if (!primarySelect || !primarySelect.value) return null;

      // Prefer the precomputed data-option-min
      const perOptionMin = JSON.parse(primarySelect.getAttribute('data-option-min') || '{}');
      const optId = parseInt(primarySelect.value, 10);

      if (perOptionMin && perOptionMin[optId] != null) {
        const p = parseFloat(perOptionMin[optId]);
        if (!Number.isNaN(p)) return p;
      }

      // Fallback: compute from variantIndex
      return minPriceForOption(optId);
    }

    function updateMainPrice() {
      const hasMultipleTypes = selects.length > 1;

      // If full valid combo exists, always show its EXACT price and set hidden fields
      const fullKey = getSelectedKey();
      if (fullKey && Object.prototype.hasOwnProperty.call(variantIndex, fullKey)) {
        const entry = variantIndex[fullKey];
        const price = parseFloat(entry.price);

        if (priceNode && !Number.isNaN(price)) priceNode.textContent = fmt(price);
        if (fromLabel) fromLabel.style.display = 'none';

        if (variantIdNode) variantIdNode.value = entry.id;
        if (variantPriceNode) variantPriceNode.value = price.toFixed(2);
        return;
      }

      // Otherwise, preview price:
      if (hasMultipleTypes) {
        const p = firstTypePrice();
        if (priceNode) priceNode.textContent = fmt(!Number.isNaN(p) && p != null ? p : defaultAmt);
        if (fromLabel) fromLabel.style.display = ''; // show "From" for preview
      } else {
        // Single type and no valid mapping yet -> show default
        if (priceNode) priceNode.textContent = fmt(defaultAmt);
        if (fromLabel) fromLabel.style.display = ''; // in case it exists in single type scenario
      }

      // Not a fully valid combo => clear hidden fields
      clearVariantHidden();
    }

    // For each select, keep helpful per-option labels ("— From KES X.XX")
    function updateOptionLabels() {
      const otherAllChosen = (excludeTypeId) => {
        for (const s of selects) {
          const typeId = parseInt(s.getAttribute('data-variation-type-id'), 10);
          if (typeId === excludeTypeId) continue;
          if (!s.value) return false;
        }
        return true;
      };

      for (const s of selects) {
        const typeId = parseInt(s.getAttribute('data-variation-type-id'), 10);
        const perOptionMin = JSON.parse(s.getAttribute('data-option-min') || '{}');

        for (const opt of Array.from(s.options)) {
          if (!opt.value) continue; // placeholder

          const baseLabel = opt.getAttribute('data-label') || opt.textContent;
          let label = baseLabel;
          let priceToShow = null;

          const optId = parseInt(opt.value, 10);

          // If all other selects are chosen, try exact combo; else show "From" (min) for that option
          if (otherAllChosen(typeId)) {
            // Build a temporary key substituting this option
            const ids = [];
            for (const s2 of selects) {
              const tId = parseInt(s2.getAttribute('data-variation-type-id'), 10);
              if (tId === typeId) {
                ids.push(optId);
              } else {
                if (!s2.value) { priceToShow = null; break; }
                ids.push(parseInt(s2.value, 10));
              }
            }
            if (ids.length === selects.length) {
              const key = ids.sort((a,b)=>a-b).join('-');
              if (key && Object.prototype.hasOwnProperty.call(variantIndex, key)) {
                priceToShow = parseFloat(variantIndex[key].price);
              } else {
                priceToShow = perOptionMin && perOptionMin[optId] != null ? parseFloat(perOptionMin[optId]) : null;
              }
            }
          } else {
            priceToShow = perOptionMin && perOptionMin[optId] != null ? parseFloat(perOptionMin[optId]) : null;
          }

          if (priceToShow != null && !Number.isNaN(priceToShow)) {
            label = `${baseLabel} — ${fmt(priceToShow)}`;
          }

          opt.textContent = label;
        }
      }
    }

    function onChange() {
      updateOptionLabels();
      updateMainPrice();
    }

    selects.forEach(s => s.addEventListener('change', onChange));

    // Prevent submit if a priced variant combo isn't resolved
    if (form) {
      form.addEventListener('submit', function(e) {
        const hasVariants = Object.keys(variantIndex).length > 0;
        if (!hasVariants) return; // product with no priced variants -> allow normal flow

        const key = getSelectedKey();
        const valid = key && Object.prototype.hasOwnProperty.call(variantIndex, key);
        if (!valid) {
          e.preventDefault();
        }
      });
    }

    // Initial render
    updateOptionLabels();
    updateMainPrice();
  })();
</script>
@endpush
