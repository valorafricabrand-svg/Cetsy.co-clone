{{-- Process Modal --}}
<div class="tw-modal"
 id="processModal-{{ $order->id }}"
 tabindex="-1"
 aria-labelledby="processModalLabel-{{ $order->id }}"
 aria-hidden="true">
 <div class="tw-modal-dialog tw-modal-dialog-centered">
 <form action="{{ route('seller.orders.process', $order) }}"
 method="post"
 class="tw-modal-content needs-validation"
 novalidate>
 @csrf
 @method('patch')

 <div class="tw-modal-header bg-slate-50">
 <h5 class="tw-modal-title" id="processModalLabel-{{ $order->id }}">
 Confirm Processing
 </h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>

 <div class="tw-modal-body">
 <p class="mb-3">
 Are you sure you want to move
 <strong>Order #{{ $order->id }}</strong>
 from <span class="font-semibold text-slate-600">Pending</span>
 to <span class="font-semibold text-emerald-700">Processing</span>?
 </p>

 {{-- Optional internal note --}}
 <div class="form-floating">
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
 name="process_note"
 id="processNote-{{ $order->id }}"
 style="height:80px"></textarea>
 <label for="processNote-{{ $order->id }}">Internal note (optional)</label>
 </div>
 </div>

 <div class="tw-modal-footer bg-slate-50">
 <button type="button"
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100"
 data-ui-dismiss="modal">
 Cancel
 </button>
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-solid fa-gear mr-1"></i> Process Order
 </button>
 </div>
 </form>
 </div>
</div>




