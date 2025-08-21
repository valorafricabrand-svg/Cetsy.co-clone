{{-- Seller Cancel Order Modal --}}
<div class="modal fade"
     id="cancelModal-{{ $order->id }}"
     tabindex="-1"
     aria-labelledby="cancelModalLabel-{{ $order->id }}"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('seller.orders.cancel', $order) }}"
              method="post"
              class="modal-content needs-validation"
              novalidate>
            @csrf
            @method('patch')

            <div class="modal-header bg-light">
                <h5 class="modal-title text-danger" id="cancelModalLabel-{{ $order->id }}">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Cancel Order #{{ $order->id }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fa-solid fa-info-circle me-2"></i>
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
                    <select class="form-select" name="cancel_reason" id="cancelReason-{{ $order->id }}" required>
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
                    <textarea class="form-control"
                              name="cancel_reason"
                              id="cancelReasonText-{{ $order->id }}"
                              style="height:80px"
                              placeholder="Provide additional details..."
                              required></textarea>
                    <label for="cancelReasonText-{{ $order->id }}">Additional Details *</label>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Keep Order
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-times-circle me-1"></i> Cancel Order
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
