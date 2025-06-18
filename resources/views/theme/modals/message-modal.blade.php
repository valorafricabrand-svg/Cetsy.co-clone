{{-- resources/views/items/partials/message-modal.blade.php --}}
<div class="modal fade" id="messageModal" tabindex="-1"
     aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('messages.store') }}">
            @csrf
            {{-- Receiver (shop owner) --}}
            <input type="hidden" name="receiver_id" value="{{ $product->shop->user_id }}">
            {{-- Optional product context --}}
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">
                    Message&nbsp;Seller – {{ $product->shop->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="messageBody" class="form-label">
                    Your message
                </label>
                <textarea id="messageBody"
                          name="message"
                          rows="4"
                          class="form-control"
                          placeholder="Hi, I’d like to ask about…"
                          required></textarea>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    Send&nbsp;Message
                </button>
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
