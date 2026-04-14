@php
  $sharedProducts = collect($sharedProducts ?? []);
  $isOutgoing = (bool) ($isOutgoing ?? false);
  $cardClasses = $isOutgoing
      ? 'border border-white/15 bg-white/10 text-white hover:bg-white/15'
      : 'border border-slate-200 bg-white text-slate-900 hover:border-emerald-200 hover:bg-emerald-50';
  $metaClasses = $isOutgoing ? 'text-emerald-100/90' : 'text-slate-500';
  $priceClasses = $isOutgoing ? 'text-white' : 'text-emerald-700';
@endphp

@if($sharedProducts->isNotEmpty())
  <div class="mt-3 grid gap-2">
    @foreach($sharedProducts as $sharedProduct)
      @php
        $listingUrl = route('listing.show', $sharedProduct->slug ?: $sharedProduct->id);
        $thumbUrl = function_exists('product_thumb_url')
            ? product_thumb_url($sharedProduct)
            : media_url($sharedProduct->featured_image ?? null);
        $type = strtolower((string) ($sharedProduct->type ?? ''));
        $stock = is_numeric($sharedProduct->stock ?? null) ? (int) $sharedProduct->stock : null;
        $stockLabel = match ($type) {
            'service' => 'Service listing',
            'digital' => 'Digital listing',
            default => ($stock !== null ? $stock . ' in stock' : 'Stock unavailable'),
        };
        $price = money((float) ($sharedProduct->discounted_price ?? $sharedProduct->price ?? 0), null);
      @endphp

      <a href="{{ $listingUrl }}" class="group flex items-center gap-3 rounded-xl p-3 transition {{ $cardClasses }}">
        @if($thumbUrl)
          <img src="{{ $thumbUrl }}" alt="{{ $sharedProduct->name }}" class="h-14 w-14 shrink-0 rounded-lg object-cover">
        @else
          <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg {{ $isOutgoing ? 'bg-white/10' : 'bg-slate-100' }}">
            <i class="fa-regular fa-image {{ $metaClasses }}"></i>
          </div>
        @endif

        <div class="min-w-0 flex-1">
          <div class="truncate text-sm font-semibold">{{ $sharedProduct->name }}</div>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-xs {{ $metaClasses }}">
            <span>{{ $stockLabel }}</span>
            <span>&bull;</span>
            <span class="font-semibold {{ $priceClasses }}">{{ $price }}</span>
          </div>
          <div class="mt-1 text-[11px] {{ $metaClasses }}">Tap to review this listing</div>
        </div>

        <i class="fa-solid fa-chevron-right text-xs {{ $metaClasses }}"></i>
      </a>
    @endforeach
  </div>
@endif
