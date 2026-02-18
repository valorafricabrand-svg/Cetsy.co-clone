{{-- resources/views/theme/{{ theme() }}/partials/_related.blade.php --}}

@if(isset($relatedProducts) && $relatedProducts->count())
  <section class="related-products mt-8">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-lg font-bold text-slate-900">You May Also Like</h2>
      @isset($moreUrl)
        <a href="{{ $moreUrl }}" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
          View more
        </a>
      @endisset
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
      @foreach($relatedProducts as $item)
        <div>
          @include('theme.'.theme().'.partials.product-card', ['item' => $item])
        </div>
      @endforeach
    </div>
  </section>
@endif
