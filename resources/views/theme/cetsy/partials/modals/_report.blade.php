{{-- resources/views/theme/{{ theme() }}/partials/modals/_report.blade.php --}}

<div id="reportModal" class="tw-modal fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4" aria-hidden="true">
  <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <form method="POST" action="{{ route('product-reports.store') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">

      <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <h3 class="text-base font-semibold text-slate-900">Report Listing</h3>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100" data-tw-modal-close aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="space-y-4 px-5 py-4">
        <p class="text-sm text-slate-500">
          Help us keep our community safe by reporting listings that violate our policies.
        </p>

        <div>
          <label for="reportReason" class="mb-1 block text-sm font-medium text-slate-700">Reason for report</label>
          <select name="reason" id="reportReason" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none" required>
            <option value="">Select a reason...</option>
            <option value="inappropriate">Inappropriate content</option>
            <option value="counterfeit">Counterfeit or fake item</option>
            <option value="spam">Spam or misleading</option>
            <option value="misleading">False advertising</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div>
          <label for="reportDescription" class="mb-1 block text-sm font-medium text-slate-700">Please provide details</label>
          <textarea
            name="description"
            id="reportDescription"
            rows="4"
            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
            placeholder="Please describe the issue in detail..."
            required
          ></textarea>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
        <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-tw-modal-close>
          Cancel
        </button>
        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
          Submit Report
        </button>
      </div>
    </form>
  </div>
</div>
