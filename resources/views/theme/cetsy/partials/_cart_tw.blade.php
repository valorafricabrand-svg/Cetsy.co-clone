{{-- Tailwind details + cart block for listing_show --}}
@php
  $currency = get_currency();
  $basePrice = (float) ($product->price ?? 0);
  $salePrice = apply_discount($basePrice, $product->id);
  $discountPercent = $salePrice < $basePrice && $basePrice > 0
      ? round((1 - $salePrice / $basePrice) * 100)
      : 0;

  $product->loadMissing('variations.options', 'variationTypes.options', 'shop', 'category', 'country');

  $isPhysical = ($product->type ?? '') === 'physical';
  $validVariants = collect($product->variations ?? [])
      ->filter(fn ($variant) => ($variant->options->count() ?? 0) > 0)
      ->values();
  $hasVariants = $validVariants->isNotEmpty();
  if ($hasVariants) {
      $hasAvailableVariant = $validVariants->contains(function ($v) {
          return is_null($v->stock) || (int) $v->stock > 0;
      });
      $isOutOfStock = $isPhysical && ! $hasAvailableVariant;
  } else {
      $isOutOfStock = $isPhysical && (!is_null($product->stock)) && ((int) $product->stock < 1);
  }

  try {
      if ($isPhysical && (int) ($product->stock ?? 0) === 1 && ($product->is_reserved ?? false)) {
          $isOutOfStock = true;
      }
  } catch (\Throwable $e) {
      // ignore
  }

  $variantIndex = [];
  foreach ($validVariants as $v) {
      if (($v->price ?? null) !== null && $v->options && $v->options->count()) {
          $ids = $v->options->pluck('id')->sort()->values();
          $variantIndex[] = [
              'key' => $ids->implode('-'),
              'id' => (int) $v->id,
              'price' => (float) apply_discount($v->price, $product->id),
              'options' => $ids->toArray(),
          ];
      }
  }

  $lowestVariantPrice = !empty($variantIndex)
      ? min(array_column($variantIndex, 'price'))
      : null;

  $defaultDisplayPrice = $lowestVariantPrice ?? $salePrice;

  $shop = $product->shop;
  $avg = round((float) ($shop->reviews_avg_rating ?? ($shop ? $shop->reviews()->avg('rating') : 0)));
  $cnt = (int) ($shop->reviews_count ?? ($shop ? $shop->reviews()->count() : 0));

  $showCart = ($product->type ?? '') !== 'service';
@endphp

<div class="space-y-4">
  <aside class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $product->localized_name ?? $product->name }}</h1>

    <div class="mt-2 flex items-center gap-1 text-amber-500">
      @for($i = 1; $i <= 5; $i++)
        <i class="fa-star{{ $i <= $avg ? ' fa-solid' : ' fa-regular text-slate-300' }}"></i>
      @endfor
      <span class="ml-2 text-xs text-slate-500">({{ $cnt }} reviews)</span>
    </div>

    <div
      id="js-price-block-tw"
      class="mt-3 flex flex-wrap items-center gap-2"
      data-default-amount="{{ (float) $defaultDisplayPrice }}"
      data-currency="{{ $currency }}"
      data-variant-index='@json($variantIndex)'
    >
      @if ($lowestVariantPrice !== null)
        <span id="js-from-label-tw" class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
          {{ (($product->type ?? '') === 'service') ? 'Priced From' : 'From' }}
        </span>
      @endif
      <span id="js-price-amount-tw" class="text-xl font-bold text-emerald-700">{{ money((float) $defaultDisplayPrice) }}</span>
      @if ($discountPercent > 0)
        <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">-{{ $discountPercent }}%</span>
      @endif
      @if ($lowestVariantPrice === null && $salePrice < $basePrice)
        <span class="text-sm text-slate-400 line-through">{{ money((float) $basePrice) }}</span>
      @endif
    </div>

    <div class="mt-3 flex flex-wrap gap-2 text-xs">
      <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
        <i class="fa-solid fa-store"></i>
        <a href="{{ route('shop.show', $product->shop->slug) }}" class="hover:text-emerald-600">{{ $product->shop->localized_name ?? $product->shop->name }}</a>
      </span>

      @if ($product->type === 'physical')
        <span class="inline-flex items-center rounded-full px-3 py-1 font-semibold {{ $isOutOfStock ? 'border border-rose-200 bg-rose-50 text-rose-700' : 'border border-sky-200 bg-sky-50 text-sky-700' }}">
          {{ $isOutOfStock ? 'Out of Stock' : 'In Stock' }}
        </span>
      @endif
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
      <form method="POST" action="{{ route('favorites.toggle') }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-400">
          <i class="fa-regular fa-heart{{ $isFavorited ? ' text-rose-600 fa-solid' : '' }} mr-1"></i>Favourites
        </button>
      </form>

      <button type="button" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-400" data-tw-modal-open="offerModal">
        <i class="fa-solid fa-hand-holding-dollar mr-1"></i>Make an offer
      </button>

      <button type="button" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-400" data-tw-modal-open="messageModal">
        <i class="fa-regular fa-comments mr-1"></i>Message seller
      </button>
    </div>

    @if ($product->highlights)
      <ul class="mt-4 space-y-1 text-sm text-slate-600">
        @foreach ($product->highlights as $highlight)
          <li><i class="fa-solid fa-check mr-2 text-emerald-600"></i>{{ $highlight }}</li>
        @endforeach
      </ul>
    @endif

    <p class="mt-4 text-sm text-slate-700">
      <strong class="mr-1">Category:</strong>
      @if ($product->category)
        <a href="{{ route('category.show', $product->category->slug) }}" class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:text-emerald-600">
          {{ $product->category->name }}
        </a>
      @else
        <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Uncategorised</span>
      @endif
    </p>

    @if ($product->country && $isPhysical)
      <p class="mt-2 text-sm text-slate-500">
        <i class="fa-solid fa-globe-africa mr-1"></i> Ships from {{ $product->country->name }}
      </p>
    @endif
  </aside>

  @if($showCart)
    <aside class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <form method="POST" action="{{ route('cart.add') }}" id="js-cart-form-tw" class="space-y-4">
        @csrf
        @if($isOutOfStock)
          <input type="hidden" id="js-out-of-stock-flag-tw" value="1">
        @endif

        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_id" id="js-variant-id-tw">
        <input type="hidden" name="variant_price" id="js-variant-price-tw">

        <div>
          <label for="quantity" class="mb-1 block text-sm font-semibold text-slate-700">Quantity</label>
          <input
            type="number"
            name="quantity"
            id="quantity"
            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
            min="1"
            value="1"
            required
          >
        </div>

        @foreach($product->variationTypes as $type)
          <div>
            <label for="var-{{ $type->id }}" class="mb-1 block text-sm font-semibold text-slate-700">{{ $type->name }}</label>
            <select
              name="variations[]"
              id="var-{{ $type->id }}"
              class="js-variant-select-tw w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
              @if($type->affects_price) required @endif
              data-price-affecting="{{ $type->affects_price ? 1 : 0 }}"
            >
              <option value="" disabled selected>Select {{ strtolower($type->name) }}</option>
              @foreach($type->options as $opt)
                <option value="{{ $opt->id }}">{{ $opt->value }}</option>
              @endforeach
            </select>
          </div>
        @endforeach

        @if($isOutOfStock)
          <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            This product is out of stock.
          </div>
        @endif

        <div class="grid gap-2 sm:grid-cols-2">
          <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-emerald-300" id="js-add-to-cart-tw" {{ $isOutOfStock ? 'disabled' : 'disabled' }}>
            <i class="fa-solid fa-cart-plus mr-1"></i>
            <span class="js-cta-label-tw">{{ $isOutOfStock ? 'Out of Stock' : 'Select options' }}</span>
          </button>

          <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-slate-400" id="js-buy-now-tw" formaction="{{ route('cart.buy') }}" {{ $isOutOfStock ? 'disabled' : 'disabled' }}>
            <i class="fa-solid fa-bolt mr-1"></i>
            <span class="js-buy-label-tw">{{ $isOutOfStock ? 'Out of Stock' : 'Select options' }}</span>
          </button>
        </div>
      </form>
    </aside>
  @endif
</div>

@push('scripts')
<script>
(function () {
  const priceBlock = document.getElementById('js-price-block-tw');
  if (!priceBlock) return;

  const priceNode = document.getElementById('js-price-amount-tw');
  const variantIdNode = document.getElementById('js-variant-id-tw');
  const variantPriceNode = document.getElementById('js-variant-price-tw');
  const selects = Array.from(document.querySelectorAll('.js-variant-select-tw'));
  const btnAdd = document.getElementById('js-add-to-cart-tw');
  const btnBuy = document.getElementById('js-buy-now-tw');
  const ctaLabel = btnAdd ? btnAdd.querySelector('.js-cta-label-tw') : null;
  const buyLabel = btnBuy ? btnBuy.querySelector('.js-buy-label-tw') : null;
  const outOfStock = !!document.getElementById('js-out-of-stock-flag-tw');

  const currencyCode = priceBlock.getAttribute('data-currency') || '';
  const defaultAmount = parseFloat(priceBlock.getAttribute('data-default-amount') || '0') || 0;
  const variantIndex = JSON.parse(priceBlock.getAttribute('data-variant-index') || '[]');

  function formatAmount(amount) {
    if (typeof Intl !== 'undefined' && currencyCode) {
      try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(amount);
      } catch (e) {
        // fall back
      }
    }
    return `${currencyCode} ${Number(amount).toFixed(2)}`;
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

  function refreshVariantState() {
    if (!variantIndex.length) {
      if (priceNode) priceNode.textContent = formatAmount(defaultAmount);
      setButtonsEnabled(true);
      return;
    }

    const relevant = selects.filter(s => s.getAttribute('data-price-affecting') === '1');
    const selected = relevant.map(s => parseInt(s.value || '0', 10)).filter(Boolean);

    if (relevant.length && selected.length !== relevant.length) {
      if (variantIdNode) variantIdNode.value = '';
      if (variantPriceNode) variantPriceNode.value = '';
      if (priceNode) priceNode.textContent = formatAmount(defaultAmount);
      setButtonsEnabled(false);
      return;
    }

    let matched = null;
    variantIndex.forEach(v => {
      if (!Array.isArray(v.options)) return;
      const ok = selected.every(id => v.options.includes(id));
      if (!ok) return;
      if (!matched || Number(v.price) < Number(matched.price)) {
        matched = v;
      }
    });

    if (matched) {
      if (variantIdNode) variantIdNode.value = String(matched.id || '');
      if (variantPriceNode) variantPriceNode.value = String(matched.price || '');
      if (priceNode) priceNode.textContent = formatAmount(Number(matched.price || defaultAmount));
      setButtonsEnabled(true);
    } else {
      if (variantIdNode) variantIdNode.value = '';
      if (variantPriceNode) variantPriceNode.value = '';
      if (priceNode) priceNode.textContent = formatAmount(defaultAmount);
      setButtonsEnabled(false);
    }
  }

  selects.forEach(s => s.addEventListener('change', refreshVariantState));
  refreshVariantState();
})();
</script>
@endpush
