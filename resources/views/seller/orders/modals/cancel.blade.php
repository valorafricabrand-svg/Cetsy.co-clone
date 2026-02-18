{{-- Seller Cancel Order Modal --}}
<div class="tw-modal"
 id="cancelModal-{{ $order->id }}"
 tabindex="-1"
 aria-labelledby="cancelModalLabel-{{ $order->id }}"
 aria-hidden="true">
 <div class="tw-modal-dialog tw-modal-dialog-centered">
 <form action="{{ route('seller.orders.cancel', $order) }}"
 method="post"
 class="tw-modal-content needs-validation"
 novalidate>
 @csrf
 @method('patch')

 <div class="tw-modal-header bg-slate-50">
 <h5 class="tw-modal-title text-rose-600" id="cancelModalLabel-{{ $order->id }}">
 <i class="fa-solid fa-exclamation-triangle mr-2"></i>
 Cancel Order #{{ $order->id }}
 </h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>

 <div class="tw-modal-body">
 <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
 <i class="fa-solid fa-info-circle mr-2"></i>
 <strong>Warning:</strong> This action cannot be undone. 
 @if($order->status === \App\Models\Order::STATUS_PROCESSING)
 The buyer will be automatically refunded.
 @endif
 </div>

 <p class="mb-3">
 Are you sure you want to cancel 
 <strong>Order #{{ $order->id }}</strong>?
 </p>

 <div class="form-floating mb-3">
 <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" name="cancel_reason" id="cancelReason-{{ $order->id }}" required>
 <option value="">Select a reason...</option>
 <option value="Out of stock">Out of stock</option>
 <option value="Product discontinued">Product discontinued</option>
 <option value="Pricing error">Pricing error</option>
 <option value="Cannot ship to location">Cannot ship to location</option>
 <option value="Product quality issues">Product quality issues</option>
 <option value="Business decision">Business decision</option>
 <option value="Other">Other</option>
 </select>
 <label for="cancelReason-{{ $order->id }}">Cancellation Reason *</label>
 </div>

 <div class="form-floating">
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
 name="cancel_reason"
 id="cancelReasonText-{{ $order->id }}"
 style="height:80px"
 placeholder="Provide additional details..."
 required></textarea>
 <label for="cancelReasonText-{{ $order->id }}">Additional Details *</label>
 </div>
 </div>

 <div class="tw-modal-footer bg-slate-50">
 <button type="button"
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100"
 data-ui-dismiss="modal">
 Keep Order
 </button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-rose-600 bg-rose-600 text-white hover:bg-rose-700">
 <i class="fa-solid fa-times-circle mr-1"></i> Cancel Order
 </button>
 </div>
 </form>
 </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
 const reasonSelect = document.getElementById('cancelReason-{{ $order->id }}');
 const reasonText = document.getElementById('cancelReasonText-{{ $order->id }}');
 
 reasonSelect.addEventListener('change', function() {
 if (this.value === 'Other') {
 reasonText.placeholder = 'Please specify the reason...';
 } else {
 reasonText.placeholder = 'Provide additional details...';
 }
 });
});
</script>



