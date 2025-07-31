{{-- resources/views/theme/{{ theme() }}/partials/_share.blade.php --}}
<div class="mt-3 small">
  <span class="me-1">Share:</span>
  <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank">
    <i class="fa-brands fa-facebook fa-lg text-primary"></i>
  </a>
  <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" class="mx-2" target="_blank">
    <i class="fa-brands fa-x-twitter fa-lg"></i>
  </a>
  <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&media={{ asset('storage/'.$product->featured_image) }}&description={{ urlencode($product->name) }}" target="_blank">
    <i class="fa-brands fa-pinterest fa-lg text-danger"></i>
  </a>
  <button class="btn btn-link text-decoration-none p-0 ms-2" data-bs-toggle="modal" data-bs-target="#reportModal" title="Report this listing">
    <i class="fa-solid fa-flag fa-lg text-muted"></i>Report
  </button>
</div>