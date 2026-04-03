@php
  $basePrice       = (float) ($product->price ?? 0);
  $finalPrice      = (float) ($product->discounted_price ?? $basePrice);
  $discountPercent = $finalPrice < $basePrice && $basePrice > 0
      ? round((1 - $finalPrice / $basePrice) * 100)
      : 0;

  $ptype      = strtolower((string)($product->product_type ?? $product->type ?? ''));
  $isDigital  = in_array($ptype, ['digital','download','digital_download','digital-download']);
  $variants   = collect();

  if (! $isDigital && method_exists($product, 'loadMissing')) {
      try {
          $product->loadMissing('variations.options');
          $variants = $product->relationLoaded('variations') && $product->variations
              ? collect($product->variations)
                    ->filter(fn ($variant) => ($variant->options->count() ?? 0) > 0)
                    ->values()
              : collect();
      } catch (\Throwable $e) {
          $variants = collect();
      }
  }

  $isOutOfStock = $isDigital ? false : product_is_out_of_stock($product);
  $stockCount   = null;
  if (! $isDigital) {
      if ($variants->isNotEmpty()) {
          $numericStocks = $variants->pluck('stock')->filter(static fn($value) => ! is_null($value));
          if ($numericStocks->isNotEmpty()) {
              $stockCount = $numericStocks->sum();
          }
      } elseif (! is_null($product->stock)) {
          $stockCount = (int) $product->stock;
      }
  }
@endphp

<div class="space-y-4 lg:sticky lg:top-4">
  <h1 class="text-2xl font-bold text-slate-900">{{ $product->name }}</h1>

  <div>
    @php
      $shop = $product->shop;
      $avg  = round((float) ($shop->reviews_avg_rating ?? $shop->reviews()->avg('rating') ?? 0));
      $cnt  = (int) ($shop->reviews_count ?? $shop->reviews()->count() ?? 0);
    @endphp
    @for($i = 1; $i <= 5; $i++)
      <i class="fa-star{{ $i <= $avg ? ' fa-solid text-amber-500' : ' fa-regular text-slate-300' }}"></i>
    @endfor
    <small class="ml-1 text-slate-500">({{ $cnt }} reviews)</small>
  </div>

  @if ($finalPrice < $basePrice)
    <div class="flex flex-wrap items-center gap-3">
      <span class="text-2xl font-bold text-emerald-700">{{ money($finalPrice, null) }}</span>
      @if ($discountPercent > 0)
        <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">-{{ $discountPercent }}%</span>
      @endif
      <span class="text-sm text-slate-400 line-through">{{ money($basePrice, null) }}</span>
    </div>
  @else
    <p class="text-2xl font-bold text-emerald-700">{{ money($basePrice, null) }}</p>
  @endif

  <div class="flex flex-wrap gap-2">
    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
      <i class="fa-solid fa-store"></i>
      <a href="{{ route('shop.show', $product->shop->slug) }}" class="hover:text-emerald-800">
        {{ $product->shop->name }}
      </a>
    </span>
    @if (! $isDigital)
      <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $isOutOfStock ? 'bg-rose-50 text-rose-700' : 'bg-sky-50 text-sky-700' }}">
        {{ $isOutOfStock ? 'Out of Stock' : 'In Stock' }}
        @if (! $isOutOfStock && ! is_null($stockCount))
          <span class="ml-1">({{ $stockCount }})</span>
        @endif
      </span>
    @endif
  </div>

  <div class="flex flex-wrap gap-2">
    <form method="POST" action="{{ route('favorites.toggle') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <button class="inline-flex items-center gap-1 rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" title="Add to favourites">
        <i class="fa-regular fa-heart{{ $isFavorited ? ' fa-solid text-rose-600' : '' }}"></i>
        Favourites
      </button>
    </form>
    <button class="inline-flex items-center gap-1 rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" data-ui-toggle="modal" data-ui-target="#offerModal">
      <i class="fa-solid fa-hand-holding-dollar"></i>Make an offer
    </button>
    <button class="inline-flex items-center gap-1 rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" data-ui-toggle="modal" data-ui-target="#messageModal">
      <i class="fa-regular fa-comments"></i>Message seller
    </button>
  </div>

  @if ($product->highlights)
    <ul class="space-y-1 text-sm text-slate-600">
      @foreach ($product->highlights as $highlight)
        <li class="flex items-start gap-2">
          <i class="fa-solid fa-check mt-0.5 text-emerald-600"></i>
          <span>{{ $highlight }}</span>
        </li>
      @endforeach
    </ul>
  @endif

  <div class="text-sm">
    <strong class="mr-1 text-slate-700">Category:</strong>
    @if ($product->category)
      <a href="{{ route('category.show', $product->category->slug) }}" class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
        {{ $product->category->name }}
      </a>
    @else
      <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">Uncategorised</span>
    @endif
  </div>

  @if ($product->country && (($product->type ?? '') === 'physical'))
    <p class="text-xs text-slate-500">
      <i class="fa-solid fa-globe-africa mr-1"></i>
      Ship from {{ $product->country->name }}
    </p>
  @endif
</div>
