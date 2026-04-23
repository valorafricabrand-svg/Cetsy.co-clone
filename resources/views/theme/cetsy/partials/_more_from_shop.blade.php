{{-- resources/views/theme/{{ theme() }}/partials/_more_from_shop.blade.php --}}

@if($moreFromShop->count())
  <h3 class="mt-8 mb-3 text-lg font-bold text-slate-900">
    More from {{ $product->shop->localized_name ?? $product->shop->name }}
  </h3>
  <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
    @foreach($moreFromShop as $item)
      <div>
        @include('theme.'.theme().'.partials.product-card', ['item' => $item])
      </div>
    @endforeach
  </div>
@endif
