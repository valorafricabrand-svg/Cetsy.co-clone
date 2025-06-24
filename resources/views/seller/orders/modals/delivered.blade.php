{{-- Deliver Modal --}}
<div class="modal fade"
     id="deliverModal-{{ $order->id }}"
     tabindex="-1"
     aria-labelledby="deliverModalLabel-{{ $order->id }}"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('buyer.orders.status', $order) }}"
              method="post"
              class="modal-content needs-validation"
              novalidate>
            @csrf
            @method('patch')
            <input type="hidden" name="action" value="deliver">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="deliverModalLabel-{{ $order->id }}">
                    Confirm Delivery
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3">
                    Are you sure you want to mark
                    <strong>Order #{{ $order->id }}</strong>
                    as <span class="text-success fw-semibold">Delivered</span>?
                </p>

                {{-- Optional delivery note / proof --}}
                <div class="form-floating">
                    <textarea class="form-control"
                              name="delivery_note"
                              id="deliveryNote-{{ $order->id }}"
                              style="height: 100px"></textarea>
                    <label for="deliveryNote-{{ $order->id }}">Delivery note (optional)</label>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i> Mark Delivered
                </button>
            </div>
        </form>
    </div>
</div>
