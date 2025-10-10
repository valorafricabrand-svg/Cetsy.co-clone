{{-- resources/views/theme/{{ theme() }}/partials/_details.blade.php --}}
@php
  $currency        = get_currency();
  $basePrice       = (float) ($product->price ?? 0);
  $salePrice       = apply_discount($basePrice, $product->id);
  $discountPercent = $salePrice < $basePrice && $basePrice > 0
      ? round((1 - $salePrice / $basePrice) * 100)
      : 0;

  // Ensure variations+options are available
  $product->loadMissing('variations.options', 'variationTypes.options', 'shop', 'category', 'country');

  // Determine out-of-stock status for physical items
  $isPhysical = ($product->type ?? '') === 'physical';
  $hasVariants = $product->variations && $product->variations->count() > 0;
  if ($hasVariants) {
      $hasAvailableVariant = $product->variations->contains(function($v){
          return is_null($v->stock) || (int)$v->stock > 0;
      });
      $isOutOfStock = $isPhysical && ! $hasAvailableVariant;
  } else {
      $isOutOfStock = $isPhysical && (!is_null($product->stock)) && ((int)$product->stock < 1);
  }

  // Build a compact index: variant-combination key -> {id, price, options:[ids]}
  // Only include variants that have a price.
  $variantIndex = [];
  foreach ($product->variations ?? [] as $v) {
      if (($v->price ?? null) !== null && $v->options && $v->options->count()) {
          $ids = $v->options->pluck('id')->sort()->values();
          $key = $ids->implode('-');
          $variantIndex[$key] = [
              'id'      => (int) $v->id,
              'price'   => (float) apply_discount($v->price, $product->id),
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
  // Resolve currency decimal places safely (0..6)
  $__dec = 2;
  try {
      $cur = \App\Models\Currency::where('code', $currency)->first();
      if ($cur) {
          $__dec = max(0, min(6, (int) $cur->decimal_places));
      }
  } catch (\Throwable $e) {
      // ignore
  }
@endphp

<div class="position-lg-sticky" style="top: 1rem;">
  <h1 class="h2 fw-bold">{{ $product->name }}</h1>

  {{-- Ratings (shop-wide) --}}
  <div class="mb-2">
    @php
      $shop = $product->shop;
      $avg  = round((float) ($shop->reviews_avg_rating ?? $shop->reviews()->avg('rating') ?? 0));
      $cnt  = (int) ($shop->reviews_count ?? $shop->reviews()->count() ?? 0);
    @endphp
    @for($i = 1; $i <= 5; $i++)
      <i class="fa-star{{ $i <= $avg ? ' fa-solid text-warning' : ' fa-regular text-muted' }}"></i>
    @endfor
    <small class="ms-1 text-muted">({{ $cnt }} reviews)</small>
  </div>

  {{-- Price block (JS updates #js-price-amount) --}}
  @if ($lowestVariantPrice !== null)
    <div id="js-price-block"
         class="d-flex align-items-baseline gap-3 mb-3"
         data-currency="{{ $currency }}"
         data-default-amount="{{ $defaultDisplayPrice }}"
         data-fx-rate="{{ max(0, (float) fx_rate($currency)) }}"
         data-decimals="{{ $__dec }}"
         data-variant-index='@json($variantIndex)'>
      <span class="fw-bold text-success">
        <span id="js-from-label" class="me-1 small text-muted">From</span>
        <span id="js-price-amount">{{ $currency }} {{ $format($defaultDisplayPrice) }}</span>
      </span>
      @if ($discountPercent > 0)
        <span class="badge bg-danger bg-opacity-10 text-danger">-{{ $discountPercent }}%</span>
      @endif
    </div>
  @else
    {{-- No priced variants: show product pricing (with discount style if applicable) --}}
    @if ($salePrice < $basePrice)
      <div id="js-price-block"
           class="d-flex align-items-baseline gap-3 mb-3"
           data-currency="{{ $currency }}"
           data-default-amount="{{ $salePrice }}"
           data-fx-rate="{{ max(0, (float) fx_rate($currency)) }}"
           data-decimals="{{ $__dec }}"
           data-variant-index='{}'>
        <span class="fw-bold text-success">
          <span id="js-price-amount">{{ $currency }} {{ $format($salePrice) }}</span>
        </span>
        @if ($discountPercent > 0)
          <span class="badge bg-danger bg-opacity-10 text-danger">-{{ $discountPercent }}%</span>
        @endif
        <span class="text-muted text-decoration-line-through">
          {{ $currency }} {{ $format($basePrice) }}
        </span>
      </div>
    @else
      <p id="js-price-block"
         class="fw-bold text-success mb-3"
         data-currency="{{ $currency }}"
         data-default-amount="{{ $basePrice }}"
         data-fx-rate="{{ max(0, (float) fx_rate($currency)) }}"
         data-decimals="{{ $__dec }}"
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
      <span class="badge {{ $isOutOfStock ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }}">
        {{ $isOutOfStock ? 'Out of Stock' : 'In Stock' }}
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
        @if($isOutOfStock)
          <input type="hidden" id="js-out-of-stock-flag" value="1">
        @endif

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
                @if($type->affects_price) required @endif
                data-variation-type-id="{{ $type->id }}"
                data-price-affecting="{{ $type->affects_price ? 1 : 0 }}"
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
        @if($isOutOfStock)
          <div class="alert alert-warning small mb-3">
            This product is out of stock.
          </div>
        @endif

        <div class="d-grid gap-2 d-sm-flex">
          <button type="submit" class="btn btn-success btn-lg" id="js-add-to-cart" {{ $isOutOfStock ? 'disabled' : 'disabled' }}>
            <i class="fa-solid fa-cart-plus me-1"></i>
            <span class="js-cta-label">{{ $isOutOfStock ? 'Out of Stock' : 'Select options' }}</span>
          </button>

          {{-- Override destination just for this button --}}
          <button type="submit"
                   class="btn btn-primary btn-lg"
                   id="js-buy-now"
                   formaction="{{ route('cart.buy') }}"
                   {{ $isOutOfStock ? 'disabled' : 'disabled' }}>
            <i class="fa-solid fa-bolt me-1"></i>
            <span class="js-buy-label">{{ $isOutOfStock ? 'Out of Stock' : 'Select options' }}</span>
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

    const outOfStock        = !!document.getElementById('js-out-of-stock-flag');

    const currency   = priceBlock.getAttribute('data-currency') || '';
    const defaultAmt = parseFloat(priceBlock.getAttribute('data-default-amount') || '0') || 0;
    const fxRate     = Math.max(0, parseFloat(priceBlock.getAttribute('data-fx-rate') || '0') || 0);
    const decimals   = Math.max(0, parseInt(priceBlock.getAttribute('data-decimals') || '2', 10) || 2);

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

    // Determine which selects are actually relevant to priced variants (price-affecting only)
    function isSelectRelevant(selectEl) {
      if (String(selectEl.getAttribute('data-price-affecting')) !== '1') return false;
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
      const r = fxRate > 0 ? fxRate : 1;
      return currency + ' ' + (Number(amount) * r).toFixed(decimals);
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
      if (outOfStock) {
        btnAdd.disabled = true;
        btnBuy.disabled = true;
        if (ctaLabel) ctaLabel.textContent = 'Out of Stock';
        if (buyLabel) buyLabel.textContent = 'Out of Stock';
        return;
      }
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

      // Collect chosen option IDs for price-affecting selects
      const priceChosen = relevantSelects.map(s => parseInt(s.value || '0', 10)).filter(Boolean);
      // Require price-affecting selects only
      if (relevantSelects.length && priceChosen.length !== relevantSelects.length) {
        // Not enough info to resolve price; preview
        const p = firstTypePrice();
        if (priceNode) priceNode.textContent = fmt((p != null && !Number.isNaN(p)) ? p : defaultAmt);
        if (fromLabel) fromLabel.style.display = '';
        clearVariantHidden();
        setButtonsEnabled(false);
        return;
      }

      // Find best matching variant that includes all priceChosen IDs (irrespective of non-price selections)
      let best = null;
      for (const key in variantIndex) {
        const entry = variantIndex[key];
        const opts = Array.isArray(entry?.options) ? entry.options : [];
        const containsAll = priceChosen.every(id => opts.indexOf(Number(id)) !== -1);
        if (!containsAll) continue;
        if (!best || parseFloat(entry.price) < parseFloat(best.price)) best = entry;
      }

      if (best) {
        // Auto-pick non-price selections according to the best variant
        selects.forEach(s => {
          if (relevantSelects.indexOf(s) !== -1) return; // skip price-affecting
          if (s.value) return; // already chosen
          const match = Array.from(s.options).find(o => o.value && best.options.indexOf(Number(o.value)) !== -1);
          if (match) {
            s.value = match.value;
          }
        });

        const price = parseFloat(best.price);
        if (!Number.isNaN(price) && priceNode) priceNode.textContent = fmt(price);
        if (fromLabel) fromLabel.style.display = 'none';
        if (variantIdNode) variantIdNode.value = best.id;
        if (variantPriceNode) variantPriceNode.value = price.toFixed(2);
        setButtonsEnabled(true);
        return;
      }

      // Fallback: preview min price from first type
      const p = firstTypePrice();
      if (priceNode) priceNode.textContent = fmt((p != null && !Number.isNaN(p)) ? p : defaultAmt);
      if (fromLabel) fromLabel.style.display = '';
      clearVariantHidden();
      setButtonsEnabled(false);
    }

    function onChange() {
      if (typeof updateOptionLabelsPA === 'function') { updateOptionLabelsPA(); } else { updateOptionLabels(); }
      updateMainPriceAndState();
    }

    selects.forEach(s => s.addEventListener('change', onChange));

    // INITIALIZATION: label options, auto-pick singletons, then compute price/state
    if (typeof updateOptionLabelsPA === 'function') { updateOptionLabelsPA(); } else { updateOptionLabels(); }
    autoPickSingletons();
    updateMainPriceAndState();

    // Prevent submit if a priced variant combo isn't resolved
    if (form) {
      form.addEventListener('submit', function(e) {
        const hasVariants = Object.keys(variantIndex).length > 0;
        if (!hasVariants) return;
        const valid = !!(variantIdNode && variantIdNode.value);
        if (!valid) e.preventDefault();
      });
    }
  })();
  // Override: show price suffix only for price-affecting selects
  function updateOptionLabelsPA(){
    try{
      const selects = Array.from(document.querySelectorAll('.js-variant-select'));
      const isPriceSelect = (el) => String(el.getAttribute('data-price-affecting')) === '1';
      const priceBlock = document.getElementById('js-price-block');
      const variantIndex = JSON.parse(priceBlock?.getAttribute('data-variant-index') || '{}');
      const viableOptionIdSet = (function(){
        const set = new Set();
        for (const key in variantIndex) {
          const entry = variantIndex[key];
          if (entry && Array.isArray(entry.options)) entry.options.forEach(id => set.add(Number(id)));
        }
        return set;
      })();
      function minPriceForOption(optionId){
        let min = null;
        for (const key in variantIndex) {
          const entry = variantIndex[key];
          const opts = Array.isArray(entry?.options) ? entry.options : [];
          if (opts.indexOf(Number(optionId)) !== -1) {
            const p = parseFloat(entry.price);
            if (!Number.isNaN(p)) min = (min===null||p<min)?p:min;
          }
        }
        return min;
      }
      for (const s of selects) {
        const priceAffecting = isPriceSelect(s);
        const perOptionMin = JSON.parse(s.getAttribute('data-option-min') || '{}');
        for (const opt of Array.from(s.options)) {
          if (!opt.value) continue;
          const baseLabel = opt.getAttribute('data-label') || opt.textContent;
          const optId = parseInt(opt.value,10);
          if (priceAffecting && viableOptionIdSet.has(optId)){
            const min = perOptionMin && perOptionMin[optId] != null ? parseFloat(perOptionMin[optId]) : minPriceForOption(optId);
            opt.textContent = (min != null && !Number.isNaN(min)) ? `${baseLabel} – ${min}` : baseLabel;
          } else {
            opt.textContent = baseLabel;
          }
        }
      }
    }catch(_){ /* no-op */ }
  }
</script>
@endpush
