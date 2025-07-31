{{-- resources/views/theme/{{ theme() }}/partials/_tabs_nav.blade.php --}}
<ul class="nav nav-tabs mt-5" id="itemTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane" type="button">
      Description
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping-pane" type="button">
      Shipping & Returns
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button">
      Reviews ({{ $product->reviews_count ?? 0 }})
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq-pane" type="button">
      FAQs
    </button>
  </li>
</ul>