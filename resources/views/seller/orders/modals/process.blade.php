{{-- Process Modal --}}
<div class="modal fade"
     id="processModal-{{ $order->id }}"
     tabindex="-1"
     aria-labelledby="processModalLabel-{{ $order->id }}"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('seller.orders.process', $order) }}"
              method="post"
              class="modal-content needs-validation"
              novalidate>
            @csrf
            @method('patch')

            <div class="modal-header bg-slate-50">
                <h5 class="modal-title" id="processModalLabel-{{ $order->id }}">
                    Confirm Processing
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3">
                    Are you sure you want to move
                    <strong>Order #{{ $order->id }}</strong>
                    from <span class="font-semibold text-secondary">Pending</span>
                    to <span class="font-semibold text-primary">Processing</span>?
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

            <div class="modal-footer bg-slate-50">
                <button type="button"
                        class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="bi bi-gear mr-1"></i> Process Order
                </button>
            </div>
        </form>
    </div>
</div>


