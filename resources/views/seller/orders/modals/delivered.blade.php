{{-- Deliver Modal --}}
<div class="tw-modal"
 id="deliverModal-{{ $order->id }}"
 tabindex="-1"
 aria-labelledby="deliverModalLabel-{{ $order->id }}"
 aria-hidden="true">
 <div class="tw-modal-dialog tw-modal-dialog-centered">
 <form action="{{ route('buyer.orders.status', $order) }}"
 method="post"
 class="tw-modal-content needs-validation"
 novalidate>
 @csrf
 @method('patch')
 <input type="hidden" name="action" value="deliver">

 <div class="tw-modal-header bg-slate-50">
 <h5 class="tw-modal-title" id="deliverModalLabel-{{ $order->id }}">
 Confirm Delivery
 </h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>

 <div class="tw-modal-body">
 <p class="mb-3">
 Are you sure you want to mark
 <strong>Order #{{ $order->id }}</strong>
 as <span class="text-emerald-600 font-semibold">Delivered</span>?
 </p>

 {{-- Optional delivery note / proof --}}
 <div class="form-floating">
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
 name="delivery_note"
 id="deliveryNote-{{ $order->id }}"
 style="height: 100px"></textarea>
 <label for="deliveryNote-{{ $order->id }}">Delivery note (optional)</label>
 </div>
 </div>

 <div class="tw-modal-footer bg-slate-50">
 <button type="button"
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100"
 data-ui-dismiss="modal">
 Cancel
 </button>
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-solid fa-circle-check mr-1"></i> Mark Delivered
 </button>
 </div>
 </form>
 </div>
</div>




