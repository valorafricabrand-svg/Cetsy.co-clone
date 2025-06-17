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

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="processModalLabel-{{ $order->id }}">
                    Confirm Processing
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3">
                    Are you sure you want to move
                    <strong>Order #{{ $order->id }}</strong>
                    from <span class="fw-semibold text-secondary">Pending</span>
                    to <span class="fw-semibold text-primary">Processing</span>?
                </p>

                {{-- Optional internal note --}}
                <div class="form-floating">
                    <textarea class="form-control"
                              name="process_note"
                              id="processNote-{{ $order->id }}"
                              style="height:80px"></textarea>
                    <label for="processNote-{{ $order->id }}">Internal note (optional)</label>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-gear me-1"></i> Process Order
                </button>
            </div>
        </form>
    </div>
</div>
