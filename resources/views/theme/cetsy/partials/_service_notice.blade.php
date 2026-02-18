{{-- resources/views/theme/{{ theme() }}/partials/_service_notice.blade.php --}}

@php
  // Only show this block for service-type products
  $isService = ($product->type ?? '') === 'service';
@endphp

@if($isService)
  <div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50 p-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-sky-700">
        <i class="fa-solid fa-concierge-bell fa-lg"></i>
      </div>
      <div class="flex-1">
        <h6 class="mb-1 text-sm font-semibold text-sky-800">Service Listing</h6>
        <p class="text-sm text-slate-600">
          This is a <strong>service</strong>. Contact the seller below for quotes.
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          class="rounded-full border border-sky-300 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100"
          data-bs-toggle="modal"
          data-bs-target="#messageModal"
        >
          <i class="fa-regular fa-comments mr-1"></i>Message Seller
        </button>
        <button
          class="rounded-full bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-500"
          data-bs-toggle="modal"
          data-bs-target="#offerModal"
        >
          <i class="fa-solid fa-handshake-simple mr-1"></i>Make an Offer
        </button>
      </div>
    </div>
  </div>
@endif
