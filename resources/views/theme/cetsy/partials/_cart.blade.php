{{-- resources/views/theme/{{ theme() }}/partials/_details.blade.php --}}
@php
  $currency    = get_currency();
  $basePrice   = (float) ($product->price ?? 0);
  $salePrice   = (float) ($product->discounted_price ?? $basePrice);

  // Ensure variations+options are available
  $product->loadMissing('variations.options', 'variationTypes.options', 'shop', 'category', 'country');

  // Build a compact index: variant-combination key -> {id, price, options:[ids]}
  // Only include variants that have a price.
  $variantIndex = [];
  foreach ($product->variations ?? [] as $v) {
      if (($v->price ?? null) !== null && $v->options && $v->options->count()) {
          $ids = $v->options->pluck('id')->sort()->values();
          $key = $ids->implode('-');
          $variantIndex[$key] = [
              'id'      => (int) $v->id,
              'price'   => (float) $product->applyDiscount($v->price),
              'options' => $ids->toArray(),
          ];
      }
  }

  // Precompute the lowest price that includes each single option id
  $optionMinPrice = [];
  foreach ($variantIndex as $entry) {
      foreach ($entry['options'] as $optId) {
          $optionMinPrice[$optId] = isset($optionMinPrice[$optId])
              ? min($optionMinPrice[$optId], $entry['price'])
              : $entry['price'];
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
        {{-- Client-side hint only; server must price from DB --}}
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
          <button type="submit" class="btn btn-success btn-lg" id="js-add-to-cart" disabled>
            <i class="fa-solid fa-cart-plus me-1"></i>
            <span class="js-cta-label">Select options</span>
          </button>

          {{-- Override destination just for this button --}}
          <button type="submit"
                  class="btn btn-primary btn-lg"
                  id="js-buy-now"
                  formaction="{{ route('cart.buy') }}"
                  disabled>
            <i class="fa-solid fa-bolt me-1"></i>
            <span class="js-buy-label">Select options</span>
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
    const btnAdd            = document.getElementById('js-add-to-cart');
    const btnBuy            = document.getElementById('js-buy-now');
    const ctaLabel          = btnAdd ? btnAdd.querySelector('.js-cta-label') : null;
    const buyLabel          = btnBuy ? btnBuy.querySelector('.js-buy-label') : null;

    const currency   = priceBlock.getAttribute('data-currency') || '';
    const defaultAmt = parseFloat(priceBlock.getAttribute('data-default-amount') || '0') || 0;

    // variant-index: { "1-12-33": { id: 7, price: 999.00, options:[1,12,33] }, ... }
    const variantIndex = JSON.parse(priceBlock.getAttribute('data-variant-index') || '{}');

    // All selects
    const selects = Array.from(document.querySelectorAll('.js-variant-select'));

    // Build a lookup of viable option IDs (present in any priced variant)
    const viableOptionIdSet = (function(){
      const set = new Set();
      for (const key in variantIndex) {
        const entry = variantIndex[key];
        if (entry && Array.isArray(entry.options)) {
          entry.options.forEach(id => set.add(Number(id)));
        }
      }
      return set;
    })();

    // Determine which selects are actually relevant to priced variants
    function isSelectRelevant(selectEl) {
      const opts = Array.from(selectEl.options).filter(o => o.value);
      return opts.some(o => viableOptionIdSet.has(parseInt(o.value, 10)));
    }

    // Only consider relevant selects when deciding if "all chosen"
    const relevantSelects = selects.filter(isSelectRelevant);

    // Auto-pick a value for any relevant select that has exactly one viable option
    function autoPickSingletons() {
      relevantSelects.forEach(s => {
        const viableOpts = Array.from(s.options).filter(o => o.value && viableOptionIdSet.has(parseInt(o.value, 10)));
        if (viableOpts.length === 1) {
          const already = s.value && s.value === viableOpts[0].value;
          if (!already) {
            s.value = viableOpts[0].value;
            s.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }
      });
    }

    function fmt(amount) {
      return currency + ' ' + Number(amount).toFixed(2);
    }

    function allChosen() {
      // If there are no relevant selects (edge case), treat as chosen
      if (relevantSelects.length === 0) return true;
      return relevantSelects.every(s => !!s.value);
    }

    function selectedKey() {
      if (!allChosen()) return null;
      // Use only relevant selects to build the key
      const ids = relevantSelects.map(s => parseInt(s.value, 10)).sort((a,b)=>a-b);
      return ids.join('-');
    }

    function clearVariantHidden() {
      if (variantIdNode) variantIdNode.value = '';
      if (variantPriceNode) variantPriceNode.value = '';
    }

    function setButtonsEnabled(enabled) {
      if (!btnAdd || !btnBuy) return;
      btnAdd.disabled = !enabled;
      btnBuy.disabled = !enabled;
      if (ctaLabel) ctaLabel.textContent = enabled ? 'Add to Cart' : 'Select options';
      if (buyLabel) buyLabel.textContent = enabled ? 'Buy Now' : 'Select options';
    }

    // Find min price across variants that include a specific option id
    function minPriceForOption(optionId) {
      let min = null;
      for (const key in variantIndex) {
        const entry = variantIndex[key];
        if (entry && Array.isArray(entry.options) && entry.options.indexOf(Number(optionId)) !== -1) {
          const p = parseFloat(entry.price);
          if (!Number.isNaN(p)) {
            if (min === null || p < min) min = p;
          }
        }
      }
      return min;
    }

    // Preview price from the FIRST visible/relevant type (optional UX)
    function firstTypePrice() {
      const primarySelect = relevantSelects[0] || null;
      if (!primarySelect || !primarySelect.value) return null;

      const perOptionMin = JSON.parse(primarySelect.getAttribute('data-option-min') || '{}');
      const optId = parseInt(primarySelect.value, 10);

      if (perOptionMin && perOptionMin[optId] != null) {
        const p = parseFloat(perOptionMin[optId]);
        if (!Number.isNaN(p)) return p;
      }
      return minPriceForOption(optId);
    }

    // Keep helpful per-option labels ("— From KES X.XX")
    function updateOptionLabels() {
      for (const s of selects) {
        const perOptionMin = JSON.parse(s.getAttribute('data-option-min') || '{}');
        for (const opt of Array.from(s.options)) {
          if (!opt.value) continue; // placeholder
          const baseLabel = opt.getAttribute('data-label') || opt.textContent;
          const optId = parseInt(opt.value, 10);

          // Only show price suffix for options that actually appear in any priced variant
          if (viableOptionIdSet.has(optId)) {
            const min = perOptionMin && perOptionMin[optId] != null ? parseFloat(perOptionMin[optId]) : minPriceForOption(optId);
            opt.textContent = (min != null && !Number.isNaN(min))
              ? `${baseLabel} — ${fmt(min)}`
              : baseLabel;
          } else {
            opt.textContent = baseLabel; // no priced mapping -> leave plain
          }
        }
      }
    }

    function updateMainPriceAndState() {
      const hasVariants = Object.keys(variantIndex).length > 0;

      if (!hasVariants) {
        // No priced variants: allow submit with product price
        if (priceNode) priceNode.textContent = fmt(defaultAmt);
        if (fromLabel) fromLabel.style.display = 'none';
        setButtonsEnabled(true);
        return;
      }

      // If relevantSelects exist, require them only
      const key = selectedKey();

      if (key && Object.prototype.hasOwnProperty.call(variantIndex, key)) {
        // Valid exact combo
        const entry = variantIndex[key];
        const price = parseFloat(entry.price);

        if (!Number.isNaN(price) && priceNode) priceNode.textContent = fmt(price);
        if (fromLabel) fromLabel.style.display = 'none';

        if (variantIdNode) variantIdNode.value = entry.id;
        if (variantPriceNode) variantPriceNode.value = price.toFixed(2);

        setButtonsEnabled(true);
        return;
      }

      // Not fully selected / invalid combo -> preview & disable
      const p = firstTypePrice();
      if (priceNode) priceNode.textContent = fmt((p != null && !Number.isNaN(p)) ? p : defaultAmt);
      if (fromLabel) fromLabel.style.display = ''; // show "From"
      clearVariantHidden();
      setButtonsEnabled(false);
    }

    function onChange() {
      updateOptionLabels();
      updateMainPriceAndState();
    }

    selects.forEach(s => s.addEventListener('change', onChange));

    // INITIALIZATION: label options, auto-pick singletons, then compute price/state
    updateOptionLabels();
    autoPickSingletons();
    updateMainPriceAndState();

    // Prevent submit if a priced variant combo isn't resolved
    if (form) {
      form.addEventListener('submit', function(e) {
        const hasVariants = Object.keys(variantIndex).length > 0;
        if (!hasVariants) return;

        const key = selectedKey();
        const valid = key && Object.prototype.hasOwnProperty.call(variantIndex, key);
        if (!valid) {
          e.preventDefault();
        }
      });
    }
  })();
</script>
@endpush
