{{-- resources/views/theme/{{ theme() }}/partials/modals/_offer.blade.php --}}

<div class="modal fade" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('offers.store') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="offerModalLabel">
          Make an Offer for {{ $product->name }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="offerPrice" class="form-label">
          Your offer price ({{ get_currency() }})
        </label>
        <input
          type="number"
          name="offer_price"
          id="offerPrice"
          min="1"
          step="1"
          class="form-control"
          required
        >
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Submit Offer</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>
