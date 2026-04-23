{{-- resources/views/theme/{{ theme() }}/partials/modals/_message.blade.php --}}

<div id="messageModal" class="tw-modal fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4" aria-hidden="true">
  <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <form method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="receiver_id" value="{{ $product->shop->user_id }}">
      <input type="hidden" name="product_id" value="{{ $product->id }}">

      <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <h3 class="text-base font-semibold text-slate-900">
          Message Seller - {{ $product->shop->localized_name ?? $product->shop->name }}
        </h3>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100" data-tw-modal-close aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="space-y-4 px-5 py-4">
        <div>
          <label for="messageBody" class="mb-1 block text-sm font-medium text-slate-700">Your message</label>
          <textarea
            id="messageBody"
            name="message"
            rows="4"
            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
            required
          ></textarea>
        </div>
        <div>
          <label for="messageAttachment" class="mb-1 block text-sm font-medium text-slate-700">Attachment (optional)</label>
          <input type="file" name="attachment" id="messageAttachment" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
          <p class="mt-1 text-xs text-slate-500">Images or PDF, max 5MB.</p>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
        <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-tw-modal-close>
          Cancel
        </button>
        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
          Send Message
        </button>
      </div>
    </form>
  </div>
</div>
