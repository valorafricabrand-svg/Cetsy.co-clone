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
  // optionMinPrice[option_id] = min price among all variants containing that option
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

  {{-- Price block (JS updates #js-price-amount when a priced variant is selected) --}}
  @if ($lowestVariantPrice !== null)
    <div id="js-price-block"
         class="d-flex align-items-baseline gap-3 mb-3"
         data-currency="{{ $currency }}"
         data-default-amount="{{ $defaultDisplayPrice }}"
         data-variant-index='@json($variantIndex)'>
      <span class="fw-bold text-success">
        <span class="me-1 small text-muted">From</span>
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
                {{-- pass optionMinPrice map to JS for initial "From" per option --}}
                data-option-min='@json(collect($type->options)->mapWithKeys(fn($o)=>[$o->id => $optionMinPrice[$o->id] ?? null]))'
              >
                <option value="" disabled selected>Select {{ strtolower($type->name) }}</option>
                @foreach($type->options as $opt)
                  {{-- store original label in data-label; JS will append prices --}}
                  <option
                    value="{{ $opt->id }}"
                    data-label="{{ $opt->value }}"
                  >{{ $opt->value }}</option>
                @endforeach
              </select>
            </div>
          @endforeach
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-success btn-lg" id="js-add-to-cart">
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
    const priceBlock        = document.getElementById('js-price-block');
    if (!priceBlock) return;

    const priceNode         = document.getElementById('js-price-amount');
    const variantIdNode     = document.getElementById('js-variant-id');
    const variantPriceNode  = document.getElementById('js-variant-price');

    const currency   = priceBlock.getAttribute('data-currency') || '';
    const defaultAmt = parseFloat(priceBlock.getAttribute('data-default-amount') || '0') || 0;

    // variant-index: { "1-12-33": { id: 7, price: 999.00, options:[1,12,33] }, ... }
    const variantIndex = JSON.parse(priceBlock.getAttribute('data-variant-index') || '{}');

    const selects = Array.from(document.querySelectorAll('.js-variant-select'));

    function fmt(amount) {
      return currency + ' ' + Number(amount).toFixed(2);
    }

    function getSelectedKey(excludeSelectId = null, substituteOptionId = null) {
      // Build a key from current selections; if excludeSelectId is provided,
      // substituteOptionId is used for that select instead of its current value.
      const ids = [];
      for (const s of selects) {
        const typeId = parseInt(s.getAttribute('data-variation-type-id'), 10);
        if (excludeSelectId !== null && typeId === excludeSelectId) {
          // use substitute value
          if (substituteOptionId === null) return null;
          ids.push(parseInt(substituteOptionId, 10));
          continue;
        }
        if (!s.value) return null; // not fully selected yet
        ids.push(parseInt(s.value, 10));
      }
      return ids.sort((a,b)=>a-b).join('-');
    }

    function getFullySelectedKey() {
      for (const s of selects) {
        if (!s.value) return null;
      }
      const ids = selects.map(s => parseInt(s.value, 10)).sort((a,b)=>a-b);
      return ids.join('-');
    }

    function clearVariantHidden() {
      if (variantIdNode) variantIdNode.value = '';
      if (variantPriceNode) variantPriceNode.value = '';
    }

    function updateMainPrice() {
      const key = getFullySelectedKey();
      if (key && Object.prototype.hasOwnProperty.call(variantIndex, key)) {
        const entry = variantIndex[key];
        const price = parseFloat(entry.price);
        if (priceNode) priceNode.textContent = fmt(price);
        if (variantIdNode) variantIdNode.value = entry.id;
        if (variantPriceNode) variantPriceNode.value = price.toFixed(2);
      } else {
        if (priceNode) priceNode.textContent = fmt(defaultAmt);
        clearVariantHidden();
      }
    }

    // For each select, show price per option:
    // - If all other selects are chosen, show the exact price for the completed combo with this option.
    // - Otherwise, show "From <lowest price including this option>" using data-option-min.
    function updateOptionLabels() {
      // Check if "other selects" are all chosen for each select
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

        // Iterate options (skip placeholder)
        for (const opt of Array.from(s.options)) {
          if (!opt.value) continue; // placeholder

          const baseLabel = opt.getAttribute('data-label') || opt.textContent;
          let label = baseLabel;
          let priceToShow = null;

          const optId = parseInt(opt.value, 10);

          if (otherAllChosen(typeId)) {
            // We can compute exact price for this completed combo
            const key = getSelectedKey(typeId, optId);
            if (key && Object.prototype.hasOwnProperty.call(variantIndex, key)) {
              priceToShow = parseFloat(variantIndex[key].price);
            } else {
              // No direct priced combo with current other selections
              priceToShow = perOptionMin && perOptionMin[optId] != null ? parseFloat(perOptionMin[optId]) : null;
            }
          } else {
            // Not fully chosen yet: show the minimum price that includes this option
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

    // Bind changes
    selects.forEach(s => s.addEventListener('change', onChange));

    // Initial render (covers prefilled forms)
    updateOptionLabels();
    updateMainPrice();
  })();
</script>
@endpush
