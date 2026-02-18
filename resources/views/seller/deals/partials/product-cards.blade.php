in seller @foreach($products as $product)
  @php
    $ptype      = strtolower((string)($product->product_type ?? $product->type ?? ''));
    $isDigital  = in_array($ptype, ['digital','download','digital_download','digital-download']);
    $isInStock  = $isDigital ? true : product_has_available_stock($product);
    $stockTotal = null;

    if (! $isDigital) {
        try {
            if (method_exists($product, 'loadMissing')) {
                $product->loadMissing('variations');
            }
            $variants = method_exists($product, 'variations') && $product->relationLoaded('variations')
                ? ($product->variations ?? collect())
                : collect();
        } catch (\Throwable $e) {
            $variants = collect();
        }

        if ($variants->isNotEmpty()) {
            $numeric = $variants->pluck('stock')->filter(static fn($value) => ! is_null($value));
            if ($numeric->isNotEmpty()) {
                $stockTotal = $numeric->sum();
            }
        } elseif (! is_null($product->stock)) {
            $stockTotal = (int) $product->stock;
        }
    }
  @endphp
  <div class="col-span-12 md:col-span-6 lg:col-span-4 product-item" data-product-name="{{ strtolower($product->name) }}">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full product-card">
      <div class="p-4 p-3">
        <div class="form-check">
          <input
            class="form-check-input product-checkbox"
            type="checkbox"
            name="product_ids[]"
            value="{{ $product->id }}"
            id="product_{{ $product->id }}"
            {{ in_array($product->id, $selectedIds) ? 'checked' : '' }}
          >
          <label class="form-check-label w-full" for="product_{{ $product->id }}">
            <div class="flex items-start">
              @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" 
                     class="rounded mr-3" 
                     style="width: 50px; height: 50px; object-fit: cover;"
                     alt="{{ $product->name }}">
              @else
                <div class="bg-slate-50 rounded mr-3 flex items-center justify-center" 
                     style="width: 50px; height: 50px;">
                  <i class="fas fa-image text-slate-500"></i>
                </div>
              @endif
              <div class="flex-1">
                <h6 class="mb-1 truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                <div class="text-slate-500 text-xs">
                  <div class="flex justify-between">
                    <span>Price: <strong>{{ get_currency() }} {{ number_format($product->price, 2) }}</strong></span>
                    @if($product->discount_percent > 0)
                      <span class="text-emerald-600">Already {{ $product->discount_percent }}% off</span>
                    @endif
                  </div>
                  <div class="mt-1">
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900">{{ $isDigital ? 'Digital' : ($product->product_type ?? 'Product') }}</span>
                    @unless($isDigital)
                      @if($isInStock)
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">
                          In Stock
                          @if(! is_null($stockTotal))
                            ({{ $stockTotal }})
                          @endif
                        </span>
                      @else
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-rose-100 text-rose-800 border-rose-200">Out of Stock</span>
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


