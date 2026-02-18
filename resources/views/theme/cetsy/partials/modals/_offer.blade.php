{{-- resources/views/theme/{{ theme() }}/partials/modals/_offer.blade.php --}}

<div id="offerModal" class="tw-modal fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4" aria-hidden="true">
  <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <form method="POST" action="{{ route('offers.store') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">

      <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <h3 class="text-base font-semibold text-slate-900">
          Make an Offer for {{ $product->name }}
        </h3>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100" data-tw-modal-close aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 py-4">
        <label for="offerPrice" class="mb-1 block text-sm font-medium text-slate-700">
          Your offer price ({{ get_currency() }})
        </label>
        <input
          type="number"
          name="offer_price"
          id="offerPrice"
          min="1"
          step="1"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
          required
        >
      </div>

      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
        <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-tw-modal-close>
          Cancel
        </button>
        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
          Submit Offer
        </button>
      </div>
    </form>
  </div>
</div>
