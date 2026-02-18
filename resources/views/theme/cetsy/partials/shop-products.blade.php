<div id="gridItems">
@foreach($products as $product)
  <div class="product-item shop-product-item" data-price="{{ (float) ($product->price ?? 0) }}" data-type="{{ $product->type }}" data-rating="{{ optional($product->shop)->reviews_avg_rating ?? 0 }}">
    @include('theme.'.theme().'.partials.product-card', ['item' => $product])
  </div>
@endforeach
</div>

<div id="listItems">
@foreach($products as $product)
  @php $thumbUrl = product_thumb_url($product); @endphp
  <article class="product-item product-item-list shop-product-item flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3" data-price="{{ (float) ($product->price ?? 0) }}" data-type="{{ $product->type }}" data-rating="{{ optional($product->shop)->reviews_avg_rating ?? 0 }}">
    <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" class="h-20 w-20 rounded-xl border border-slate-200 object-cover">
    <div class="min-w-0 flex-1">
      <h3 class="line-clamp-1 text-sm font-semibold text-slate-900">{{ $product->name }}</h3>
      <p class="mt-1 text-sm font-bold text-emerald-700">{{ money((float) $product->price, null) }}</p>
    </div>
    <button type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500" onclick="addToCart({{ $product->id }})">
      <i class="fas fa-cart-plus"></i>
    </button>
  </article>
@endforeach
</div>

@if($products->hasMorePages())
  <div class="text-center">
    <button id="loadMore" class="rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" data-next-page-url="{{ $products->nextPageUrl() }}">
      Load More
    </button>
  </div>
@endif
