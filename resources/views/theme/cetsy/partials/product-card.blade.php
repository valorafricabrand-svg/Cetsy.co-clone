{{-- resources/views/theme/cetsy/partials/product-card.blade.php --}}
@props(['item'])   {{-- expects a \App\Models\Product in $item --}}

@php
  $currency   = get_currency();
  $basePrice  = (float) ($item->price ?? 0);
  $salePrice  = (float) ($item->discounted_price ?? $basePrice);

  // Lowest variation price (only consider variants that actually have a price)
  // Make sure controller eager-loads: with('variations.options', 'media', 'shop', ...)
  $lowestVariantPrice = null;
  if ($item->relationLoaded('variations')) {
      $lowestVariantPrice = optional($item->variations)->whereNotNull('price')->min('price');
  } else {
      // still works without eager load (will lazy load)
      $lowestVariantPrice = optional($item->variations)->whereNotNull('price')->min('price');
  }

  // The price to display on listing:
  // - If variants exist with prices => "From {lowest variant}"
  // - Else => product sale/base price (show strike-through if discounted)
  $format = fn($n) => number_format((float)$n, 2);

  // Decide how to render:
  $hasVariantPricing = $lowestVariantPrice !== null;
@endphp

<a href="{{ route('listing.show', $item->slug) }}"
   class="card text-decoration-none border-0 shadow-sm h-100">

  {{-- Thumbnail --}}
  <div class="ratio ratio-1x1 rounded-top overflow-hidden bg-light d-flex align-items-center justify-content-center">
    @php
      // If featured_image is a full URL use it; otherwise assume it's a storage path
      $thumb = null;
      $mediaType = 'image';
      if (!empty($item->featured_image)) {
          $thumb = str_starts_with($item->featured_image, 'http')
                  ? $item->featured_image
                  : asset('storage/' . ltrim($item->featured_image, '/'));
      } else {
          $firstMedia = $item->media->first();
          $thumb = $firstMedia
                  ? asset('storage/' . ltrim($firstMedia->url, '/'))
                  : asset('storage/placeholder.jpg');
          $mediaType = $firstMedia->type ?? 'image';
      }
    @endphp

    @if($mediaType === 'video')
      <video src="{{ $thumb }}" class="w-100 h-100" style="object-fit: contain;" controls preload="none"></video>
    @else
      <img src="{{ $thumb }}"
           alt="{{ $item->name }}"
           class="img-fluid w-100 h-100"
           loading="lazy" decoding="async"
           style="object-fit: contain;">
    @endif
  </div>

  <div class="card-body p-2 d-flex flex-column">

    {{-- Title --}}
    <h3 class="h6 mb-1 text-truncate fw-semibold text-dark">{{ $item->name }}</h3>

    {{-- Rating (if any) --}}
    @php $avg = round($item->reviews_avg_rating ?? 0); @endphp
    <div class="mb-1 small text-warning">
      @for($i = 1; $i <= 5; $i++)
        <i class="fa-star{{ $i <= $avg ? ' fa-solid' : ' fa-regular text-muted' }}"></i>
      @endfor
      @if($item->reviews_count)
        <span class="text-muted">({{ $item->reviews_count }})</span>
      @endif
    </div>

    {{-- Price --}}
    @if ($hasVariantPricing)
      {{-- Variant-based listing price: show the lowest as "From" --}}
      <div class="mb-3">
        <div class="small text-muted lh-1">From</div>
        <div class="fw-bold text-success">{{ $currency }} {{ $format($lowestVariantPrice) }}</div>
      </div>
    @else
      {{-- No variant pricing, use product pricing with discount display if applicable --}}
      @if ($salePrice < $basePrice)
        <div class="d-flex align-items-baseline gap-3 mb-3">
          <span class="fw-bold text-success">{{ $currency }} {{ $format($salePrice) }}</span>
          <span class="text-muted text-decoration-line-through">{{ $currency }} {{ $format($basePrice) }}</span>
        </div>
      @else
        <p class="fw-bold text-success mb-3">{{ $currency }} {{ $format($basePrice) }}</p>
      @endif
    @endif

  </div>
</a>
