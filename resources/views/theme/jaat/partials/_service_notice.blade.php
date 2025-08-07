{{-- resources/views/theme/{{ theme() }}/partials/_service_notice.blade.php --}}

@php
  // Only show this block for service-type products
  $isService = ($product->type ?? '') === 'service';
@endphp

@if($isService)
  <div class="card border-info border-start-4 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
      <div
        class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
        style="width:48px;height:48px"
      >
        <i class="fa-solid fa-concierge-bell fa-lg"></i>
      </div>
      <div class="flex-grow-1">
        <h6 class="mb-1 fw-semibold text-info">Service Listing</h6>
        <p class="mb-0 small text-muted">
          This is a <strong>service</strong>. Contact the seller below for quotes.
        </p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <button
          class="btn btn-outline-info btn-sm"
          data-bs-toggle="modal"
          data-bs-target="#messageModal"
        >
          <i class="fa-regular fa-comments me-1"></i>Message Seller
        </button>
        <button
          class="btn btn-info btn-sm text-white"
          data-bs-toggle="modal"
          data-bs-target="#offerModal"
        >
          <i class="fa-solid fa-handshake-simple me-1"></i>Make an Offer
        </button>
      </div>
    </div>
  </div>
@endif
