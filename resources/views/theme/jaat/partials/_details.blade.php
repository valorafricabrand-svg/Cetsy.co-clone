{{-- resources/views/theme/{{ theme() }}/partials/_details.blade.php --}}
@php
  $basePrice  = (float) ($product->price ?? 0);
  $finalPrice = (float) ($product->discounted_price ?? $basePrice);
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

  {{-- Price --}}
  @if ($finalPrice < $basePrice)
    <div class="d-flex align-items-baseline gap-3 mb-3">
      <span class="fw-bold text-success">
        {{ get_currency() }} {{ number_format($finalPrice, 2) }}
      </span>
      <span class="text-muted text-decoration-line-through">
        {{ get_currency() }} {{ number_format($basePrice, 2) }}
      </span>
    </div>
  @else
    <p class="fw-bold text-success mb-3">
      {{ get_currency() }} {{ number_format($basePrice, 2) }}
    </p>
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
