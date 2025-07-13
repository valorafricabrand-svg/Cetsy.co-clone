{{-- resources/views/theme/cetsy/partials/product-card.blade.php --}}
@props(['item'])   {{-- expects a \App\Models\Product in $item --}}

<a href="{{ route('listing.show', $item->slug) }}"
   class="card text-decoration-none border-0 shadow-sm h-100">

  {{-- Thumbnail --}}
  <div class="ratio ratio-1x1 rounded-top overflow-hidden">
    <img src="{{ asset('storage/' . ($item->featured_image ?? $item->media->first()->url ?? 'placeholder.jpg')) }}"
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
    <div class="mt-auto">
      @if($item->discount_price && $item->discount_price < $item->price)
        <span class="fw-semibold text-success">
          {{ get_currency() }} {{ number_format($item->discount_price, 2) }}
        </span>
        <span class="small text-muted text-decoration-line-through">
          {{ get_currency() }} {{ number_format($item->price, 2) }}
        </span>
      @else
        <span class="fw-semibold text-success">
          {{ get_currency() }} {{ number_format($item->price, 2) }}
        </span>
      @endif
    </div>
  </div>
</a>
