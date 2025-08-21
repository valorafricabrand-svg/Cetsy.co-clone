{{-- resources/views/theme/{{ theme() }}/partials/_related.blade.php --}}

@if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
  <section class="related-products mt-12">
    <h2 class="text-2xl font-semibold mb-6">You May Also Like</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      @foreach($relatedProducts as $item)
        <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
      @endforeach
    </div>
  </section>
@endif
