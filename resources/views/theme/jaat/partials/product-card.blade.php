{{-- resources/views/theme/cetsy/partials/product-card.blade.php --}}
@props(['item'])   {{-- expects a \App\Models\Product in $item --}}

<a href="{{ route('listing.show', $item->slug) }}"
   class="card text-decoration-none border-0 shadow-sm h-100">

  {{-- Thumbnail --}}
  <div class="ratio ratio-1x1 rounded-top overflow-hidden">
    @php($thumb = product_thumb_url($item))
    <img src="{{ $thumb }}"
         alt="{{ $item->name }}"
         class="w-100 h-100 object-fit-cover">
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
@php
    $basePrice = $item->price;
    $finalPrice = $item->discounted_price;
@endphp

@if(is_numeric($finalPrice) && is_numeric($basePrice) && $finalPrice < $basePrice)
  <div class="d-flex align-items-baseline gap-3 mb-3">
    <span class="fw-bold text-success">
      {{ money($finalPrice) }}
    </span>
    <span class="text-muted text-decoration-line-through">
      {{ money($basePrice) }}
    </span>
  </div>
@else
  <p class="fw-bold text-success mb-3">
    {{ money($basePrice) }}
  </p>
@endif



  </div>
</a>

