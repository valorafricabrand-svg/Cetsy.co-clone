{{-- resources/views/theme/{{ theme() }}/partials/_cart.blade.php --}}

@php
  $currency    = get_currency();
  $basePrice   = (float) ($product->price ?? 0);
  $finalPrice  = (float) ($product->discounted_price ?? $basePrice);
  $hasDiscount = $finalPrice + 0.0001 < $basePrice; // allow minor float noise

  // Variation data
  $variationTypes = $product->variationTypes ?? collect();
  $variants       = $product->variations ?? collect();

  // Build JSON payload for client-side variant resolution
  $typesJson = $variationTypes->map(function ($t) {
      return [
          'id'            => (int)$t->id,
          'name'          => (string)$t->name,
          'affects_price' => (bool)($t->affects_price ?? false),
          'options'       => ($t->options ?? collect())->map(function ($o) {
              return [ 'id' => (int)$o->id, 'value' => (string)$o->value ];
          })->values()->all(),
      ];
  })->values();

  $variantsJson = $variants->map(function ($v) {
      $byType = optional($v->options)->mapWithKeys(function ($o) {
          // Each option knows its variation_type_id
          return [ (string)$o->variation_type_id => (int)$o->id ];
      })->all();
      return [
          'id'     => (int)$v->id,
          'price'  => $v->price !== null ? (float)$v->price : null,
          'byType' => $byType,
      ];
  })->values();

  // Processing/Dispatch labels
  $procMin = null; $procMax = null;
  try {
    if (!empty($product->processing_time_id)) {
      $pt = \App\Models\ProcessingTime::find($product->processing_time_id);
      if ($pt) {
        if (isset($pt->days) && is_numeric($pt->days)) {
          $procMin = $procMax = (int)$pt->days;
        } else {
          $procMin = is_numeric($pt->start_day ?? null) ? (int)$pt->start_day : null;
          $procMax = is_numeric($pt->end_day   ?? null) ? (int)$pt->end_day   : null;
        }
      }
    }
    // Per-product shipping rows can also define processing windows
    $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
    if (($procMin === null && $procMax === null) && $rows->isNotEmpty()) {
      $minRow = $rows->min(function($r){ return (int) ($r->processing_custom_min ?? PHP_INT_MAX); });
      if (is_int($minRow) && $minRow !== PHP_INT_MAX) { $procMin = $minRow; }
      $rowPtId = optional($rows->firstWhere('processing_time_id', '!=', null))->processing_time_id;
      if ($rowPtId && ($pt2 = \App\Models\ProcessingTime::find($rowPtId))) {
        if ($procMin === null && isset($pt2->days) && is_numeric($pt2->days)) { $procMin = (int)$pt2->days; }
        if (isset($pt2->start_day) && is_numeric($pt2->start_day)) { $procMin = $procMin ?? (int)$pt2->start_day; }
        if (isset($pt2->end_day)   && is_numeric($pt2->end_day))   { $procMax = (int)$pt2->end_day; }
      }
    }
  } catch (\Throwable $e) { /* ignore */ }

  $procLabel = null; $dispatchLabel = null;
  try {
    if ($procMin !== null && $procMax !== null) {
      $procLabel = ($procMin === $procMax) ? ($procMin.' day'.($procMin==1?'':'s')) : ($procMin.'-'.$procMax.' days');
    } elseif ($procMin !== null) {
      $procLabel = $procMin.' day'.($procMin==1?'':'s');
    }
    $base = now();
    $fmt = function($d){ return $d ? $d->format('M j') : null; };
    $start = $procMin !== null ? $base->copy()->addDays((int)$procMin) : null;
    $end   = $procMax !== null ? $base->copy()->addDays((int)$procMax) : ($procMin !== null ? $base->copy()->addDays((int)$procMin) : null);
    if ($start && $end) {
      $dispatchLabel = $start->isSameDay($end) ? $fmt($start) : ($fmt($start).' - '.$fmt($end));
    } elseif ($start) { $dispatchLabel = $fmt($start); }
    elseif ($end)     { $dispatchLabel = $fmt($end); }
  } catch (\Throwable $e) { /* ignore */ }
@endphp

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    {{-- Price --}}
    <div class="d-flex align-items-baseline gap-3 mb-3">
      <div class="h4 m-0 text-success"
           id="price-current"
           data-base="{{ number_format($finalPrice, 2, '.', '') }}"
           data-currency="{{ $currency }}">
        {{ $currency }} {{ number_format($finalPrice, 2) }}
      </div>
      @if($hasDiscount)
        <div class="text-muted text-decoration-line-through" id="price-compare">
          {{ $currency }} {{ number_format($basePrice, 2) }}
        </div>
      @endif
    </div>

    {{-- Processing / Dispatch hint --}}
    @if($procLabel || $dispatchLabel)
      <div class="alert alert-light border d-flex align-items-center gap-3 mb-3 text-dark">
        <i class="fa-regular fa-clock text-success"></i>
        <div>
          @if($procLabel)
            <div class="small">Processing: <span class="fw-semibold">{{ $procLabel }}</span></div>
          @endif
          @if($dispatchLabel)
            <div class="small">Dispatch by <span class="fw-semibold">{{ $dispatchLabel }}</span></div>
          @endif
        </div>
      </div>
    @endif

    {{-- Add to Cart form --}}
    <form method="POST" action="{{ route('cart.add') }}" id="add-to-cart-form">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <input type="hidden" name="variant_id" id="variant_id" value="">
      @if(!empty($defaultShipId))
        <input type="hidden" name="shipping_profile_id" value="{{ (int)$defaultShipId }}">
      @endif

      {{-- Variations --}}
      @if($variationTypes->isNotEmpty())
        @foreach($variationTypes as $vt)
          <div class="mb-3">
            <label class="form-label">{{ $vt->name }}</label>
            <select class="form-select js-variant-select" name="variations[]" data-type-id="{{ $vt->id }}">
              <option value="">Select {{ strtolower($vt->name) }}</option>
              @foreach(($vt->options ?? collect()) as $opt)
                <option value="{{ $opt->id }}">{{ $opt->value }}</option>
              @endforeach
            </select>
          </div>
        @endforeach
        <div id="variant-price-note" class="small text-muted mb-2 d-none">
          Selected price: <span id="variant-price-val"></span>
        </div>
      @endif

      {{-- Quantity --}}
      <div class="d-flex align-items-center gap-2 mb-3">
        <button type="button" class="btn btn-outline-secondary" @click="dec">&minus;</button>
        <input type="number" class="form-control text-center" style="max-width:90px" name="quantity" min="1" x-model="qty">
        <button type="button" class="btn btn-outline-secondary" @click="inc">+</button>
      </div>

      {{-- Actions --}}
      <div class="d-grid gap-2 d-sm-flex">
        <button type="submit" class="btn btn-success flex-fill" id="btn-add" disabled>
          <i class="fa-solid fa-cart-plus me-1"></i> Add to Cart
        </button>
        <button type="submit" class="btn btn-primary flex-fill" id="btn-buy" formmethod="post" formaction="{{ route('cart.buy') }}" disabled>
          <i class="fa-solid fa-bolt me-1"></i> Buy Now
        </button>
      </div>

      @error('cart')
        <div class="text-danger small mt-2">{{ $message }}</div>
      @enderror
    </form>
  </div>
</div>

@push('scripts')
<script>
  (function(){
    const types     = @json($typesJson);
    const variants  = @json($variantsJson);
    const basePrice = {{ json_encode((float)$finalPrice) }}; // price displayed when no variant selected
    const currency  = @json($currency);
    const priceEl   = document.getElementById('price-current');
    const vNote     = document.getElementById('variant-price-note');
    const vNoteVal  = document.getElementById('variant-price-val');

    function resolveVariant(selectedByType){
      // selectedByType: { typeId: optionId }
      // Return matching variant or null
      const typeIds = types.map(t => String(t.id));
      if (!typeIds.every(tid => selectedByType[tid] && String(selectedByType[tid]) !== '')) return null;
      return variants.find(v => typeIds.every(tid => String(v.byType[tid]) === String(selectedByType[tid]))) || null;
    }

    function formatPrice(num){
      try { return (new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })).format(num); }
      catch(e){ return (Math.round(num*100)/100).toFixed(2); }
    }

    function updateUI(){
      const selects = Array.from(document.querySelectorAll('.js-variant-select'));
      const selectedByType = {};
      selects.forEach(sel => { selectedByType[String(sel.dataset.typeId)] = sel.value; });
      const btnAdd = document.getElementById('btn-add');
      const btnBuy = document.getElementById('btn-buy');
      const variantIdInput = document.getElementById('variant_id');

      if (types.length === 0) {
        variantIdInput.value = '';
        btnAdd.disabled = btnBuy.disabled = false;
        updatePrice(null);
        return;
      }

      const allSelected = types.every(t => {
        const val = selectedByType[String(t.id)];
        return val !== undefined && String(val) !== '';
      });

      if (!allSelected) {
        variantIdInput.value = '';
        btnAdd.disabled = btnBuy.disabled = true;
        updatePrice(null);
        return;
      }

      const variant = resolveVariant(selectedByType);
      if (variant) {
        variantIdInput.value = variant.id;
        updatePrice(variant);
      } else {
        variantIdInput.value = '';
        updatePrice(null);
      }
      btnAdd.disabled = btnBuy.disabled = false;
    }

    function updatePrice(variant){
      if (!priceEl) return;
      const price = (variant && typeof variant.price === 'number' && !isNaN(variant.price))
        ? variant.price
        : basePrice;
      priceEl.textContent = `${currency} ${formatPrice(price)}`;
      if (vNote && vNoteVal) {
        const hasVariantPrice = !!(variant && typeof variant.price === 'number' && !isNaN(variant.price));
        const show = hasVariantPrice && (variant.price !== basePrice);
        vNote.classList.toggle('d-none', !show);
        if (show) vNoteVal.textContent = `${currency} ${formatPrice(variant.price)}`;
      }
    }

    document.addEventListener('change', function(e){
      if (e.target && e.target.classList.contains('js-variant-select')) updateUI();
    });
    document.addEventListener('DOMContentLoaded', updateUI);
  })();
</script>
@endpush
