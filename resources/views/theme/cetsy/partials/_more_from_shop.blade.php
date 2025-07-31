{{-- resources/views/theme/{{ theme() }}/partials/_more_from_shop.blade.php --}}

@if($moreFromShop->count())
  <h3 class="h5 fw-bold mt-5 mb-3">
    More from {{ $product->shop->name }}
  </h3>
  <div class="row g-3">
    @foreach($moreFromShop as $item)
      <div class="col-6 col-md-3 col-lg-3">
        @include('theme.'.theme().'.partials.product-card', ['item' => $item])
      </div>
    @endforeach
  </div>
@endif
