{{-- resources/views/theme/{{ theme() }}/partials/_related.blade.php --}}

@if(isset($relatedProducts) && $relatedProducts->count())
  <section class="related-products mt-5">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h2 class="h4 fw-bold mb-0">You May Also Like</h2>
      {{-- Optional: pass $moreUrl from the parent view if you want a CTA --}}
      @isset($moreUrl)
        <a href="{{ $moreUrl }}" class="btn btn-sm btn-outline-success">
          View more
        </a>
      @endisset
    </div>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 g-3">
      @foreach($relatedProducts as $item)
        <div class="col">
          @include('theme.'.theme().'.partials.product-card', ['item' => $item])
        </div>
      @endforeach
    </div>
  </section>
@endif
