{{-- resources/views/theme/{{ theme() }}/partials/_tabs_nav.blade.php --}}
@php $isService = ($product->type ?? '') === 'service'; @endphp
<div class="mt-6 flex flex-wrap gap-2" id="itemTab" role="tablist" aria-label="Listing detail tabs">
  <button
    class="listing-tab-btn is-active rounded-full border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:border-emerald-400"
    id="desc-tab"
    data-tab-target="desc-pane"
    type="button"
    role="tab"
    aria-controls="desc-pane"
    aria-selected="true"
  >
    Description
  </button>

  @if(!$isService)
    <button
      class="listing-tab-btn rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700"
      id="shipping-tab"
      data-tab-target="shipping-pane"
      type="button"
      role="tab"
      aria-controls="shipping-pane"
      aria-selected="false"
    >
      Shipping & Returns
    </button>
  @endif

  @php
    $shopReviewsCount = (int) (optional($product->shop)->reviews_count ?? 0);
    if (!$shopReviewsCount && $product->relationLoaded('shop') && $product->shop) {
        try {
            $shopReviewsCount = $product->shop->reviews()->count();
        } catch (\Throwable $e) {
            $shopReviewsCount = 0;
        }
    }
  @endphp

  <button
    class="listing-tab-btn rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700"
    id="reviews-tab"
    data-tab-target="reviews-pane"
    type="button"
    role="tab"
    aria-controls="reviews-pane"
    aria-selected="false"
  >
    Reviews ({{ $shopReviewsCount }})
  </button>

  <button
    class="listing-tab-btn rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700"
    id="faq-tab"
    data-tab-target="faq-pane"
    type="button"
    role="tab"
    aria-controls="faq-pane"
    aria-selected="false"
  >
    FAQs
  </button>
</div>
