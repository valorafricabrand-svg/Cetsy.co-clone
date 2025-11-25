{{-- resources/views/theme/{{ theme() }}/partials/modals/_message.blade.php --}}

<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="receiver_id" value="{{ $product->shop->user_id }}">
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">
          Message Seller – {{ $product->shop->name }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="messageBody" class="form-label">Your message</label>
        <textarea
          id="messageBody"
          name="message"
          rows="4"
          class="form-control"
          required
        ></textarea>
        <div class="mt-3">
          <label for="messageAttachment" class="form-label">Attachment (optional)</label>
          <input type="file" name="attachment" id="messageAttachment" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
          <small class="text-muted">Images or PDF, max 5MB.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Send Message</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>
