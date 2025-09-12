<div id="gridItems">
@foreach($products as $product)
  <div class="col product-item" data-price="{{ $product->price }}" data-type="{{ $product->type }}" data-rating="{{ $product->average_rating }}">
    @include('theme.'.theme().'.partials.product-card', ['item'=>$product])
  </div>
@endforeach
</div>

<div id="listItems">
@foreach($products as $product)
  <div class="list-group-item product-item d-flex align-items-center" data-price="{{ $product->price }}" data-type="{{ $product->type }}" data-rating="{{ $product->average_rating }}">
    <img src="{{ $product->featured_image }}" alt="{{ $product->name }}" class="rounded" style="width:80px; height:80px; object-fit:cover;">
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
