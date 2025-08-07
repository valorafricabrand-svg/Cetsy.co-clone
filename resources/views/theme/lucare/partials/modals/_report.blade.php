{{-- resources/views/theme/{{ theme() }}/partials/modals/_report.blade.php --}}

<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('product-reports.store') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="reportModalLabel">Report Listing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">
          Help us keep our community safe by reporting listings that violate our policies.
        </p>
        <div class="mb-3">
          <label for="reportReason" class="form-label">Reason for report</label>
          <select name="reason" id="reportReason" class="form-select" required>
            <option value="">Select a reason...</option>
            <option value="inappropriate">Inappropriate content</option>
            <option value="counterfeit">Counterfeit or fake item</option>
            <option value="spam">Spam or misleading</option>
            <option value="misleading">False advertising</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="reportDescription" class="form-label">Please provide details</label>
          <textarea
            name="description"
            id="reportDescription"
            rows="4"
            class="form-control"
            placeholder="Please describe the issue in detail..."
            required
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Submit Report</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>
