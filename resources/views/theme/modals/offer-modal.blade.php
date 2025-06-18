{{-- resources/views/items/partials/offer-modal.blade.php --}}
<div class="modal fade" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('offers.store') }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">Make an Offer for {{ $product->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="offerPrice" class="form-label">Your offer price (KES)</label>
                <input
                    type="number"
                    name="offer_price"
                    id="offerPrice"
                    min="1"
                    step="1"
                    class="form-control"
                    placeholder="Enter a price"
                    required
                >
                <small class="text-muted d-block mt-2">
                    The seller will be notified and can accept or counter your offer.
                </small>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    Submit Offer
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
