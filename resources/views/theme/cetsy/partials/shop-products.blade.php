<div id="gridItems">
@foreach($products as $product)
  <div class="col product-item" data-price="{{ $product->price }}" data-type="{{ $product->type }}" data-rating="{{ optional($product->shop)->reviews_avg_rating ?? 0 }}">
    @include('theme.'.theme().'.partials.product-card', ['item'=>$product])
  </div>
@endforeach
</div>

<div id="listItems">
@foreach($products as $product)
  @php
    if (!empty($product->featured_image)) {
        $thumbUrl = str_starts_with($product->featured_image, 'http')
                  ? $product->featured_image
                  : asset('storage/' . ltrim($product->featured_image, '/'));
    } else {
        $firstMedia = $product->media->first();
        if ($firstMedia) {
            $thumbUrl = asset('storage/' . ltrim($firstMedia->url, '/'));
        } else {
            $thumbUrl = ($product->shop && $product->shop->logo)
                        ? asset('storage/' . ltrim($product->shop->logo, '/'))
                        : (setting('favicon_url') ?: asset('storage/placeholder.jpg'));
        }
    }
  @endphp
  <div class="list-group-item product-item d-flex align-items-center" data-price="{{ $product->price }}" data-type="{{ $product->type }}" data-rating="{{ optional($product->shop)->reviews_avg_rating ?? 0 }}">
    <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" class="rounded" style="width:80px; height:80px; object-fit:cover;">
    <div class="ms-3 flex-grow-1">
      <h6 class="mb-1">{{ $product->name }}</h6>
      <small class="text-muted">{{ money((float)$product->price, null) }}</small>
    </div>
    <button class="btn btn-sm btn-success" onclick="addToCart({{ $product->id }})">
      <i class="fas fa-cart-plus"></i>
    </button>
  </div>
@endforeach
</div>

@if($products->hasMorePages())
  <div class="mt-4 d-flex justify-content-center">
    <button id="loadMore" class="btn btn-outline-secondary" data-next-page-url="{{ $products->nextPageUrl() }}">
      Load More
    </button>
  </div>
@endif
