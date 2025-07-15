@props(['product'])

{{-- EXISTING VARIATIONS --}}
<div class="card mb-4 shadow-sm">
  <div class="card-header bg-light"><h5>Current Variations</h5></div>
  <div class="card-body">
    @if($product->variations->count())
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Type</th>
              <th>Option</th>
              <th>Price</th>
              <th>Stock</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($product->variations as $var)
              <tr>
                <td>{{ $var->type }}</td>
                <td>{{ $var->variation_option }}</td>
                <td>{{ get_currency() }}{{ number_format($var->price,2) }}</td>
                <td>{{ $var->stock }}</td>
                <td class="text-end">
                  <button type="button"
                          class="btn btn-sm btn-warning me-1"
                          data-bs-toggle="modal"
                          data-bs-target="#editVarModal{{ $var->id }}">
                    Edit
                  </button>
                  <form action="{{ route('variations.destroy', $var) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Delete this variation?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <p class="text-muted">No variations yet.</p>
    @endif
  </div>
</div>

{{-- EDIT VARIATION MODALS --}}
@foreach($product->variations as $var)
  <div class="modal fade" id="editVarModal{{ $var->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content"
            action="{{ route('variations.update', $var) }}"
            method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-header">
          <h5 class="modal-title">Edit Variation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Type</label>
            <input name="type"
                   value="{{ $var->type }}"
                   class="form-control"
                   required>
          </div>
          <div class="mb-3">
            <label class="form-label">Option</label>
            <input name="variation_option"
                   value="{{ $var->variation_option }}"
                   class="form-control"
                   required>
          </div>
          <div class="row g-3">
            <div class="col">
              <label class="form-label">Price</label>
              <input name="price"
                     type="number"
                     step="0.01"
                     value="{{ $var->price }}"
                     class="form-control"
                     required>
            </div>
            <div class="col">
              <label class="form-label">Stock</label>
              <input name="stock"
                     type="number"
                     value="{{ $var->stock }}"
                     class="form-control"
                     required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
@endforeach

{{-- ADD MULTIPLE VARIATIONS FORM --}}
<div class="card mb-5 shadow-sm">
  <div class="card-header bg-light"><h5>Add New Variations</h5></div>
  <div class="card-body">
    <form id="bulkVariationsForm"
          action="{{ route('variations.bulkStore', $product) }}"
          method="POST">
      @csrf

      <div id="variationRows">
        <div class="variation-row row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Type</label>
            <input name="variations[0][type]"
                   class="form-control"
                   placeholder="e.g. Color"
                   required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Option</label>
            <input name="variations[0][variation_option]"
                   class="form-control"
                   placeholder="e.g. Red"
                   required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Price</label>
            <input name="variations[0][price]"
                   type="number"
                   step="0.01"
                   class="form-control"
                   required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Stock</label>
            <input name="variations[0][stock]"
                   type="number"
                   class="form-control"
                   required>
          </div>
        </div>
      </div>

      <button type="button"
              id="addRowBtn"
              class="btn btn-outline-primary mb-3">
        <i class="fas fa-plus me-1"></i> Add Another Variation
      </button>

      <button type="submit" class="btn btn-success">Save All Variations</button>
    </form>
  </div>
</div>

{{-- Bulk Variation JS --}}
<script>
  document.addEventListener('DOMContentLoaded', function(){
    let rowContainer = document.getElementById('variationRows');
    let addRowBtn = document.getElementById('addRowBtn');

    addRowBtn.addEventListener('click', () => {
      let rows = rowContainer.querySelectorAll('.variation-row');
      let index = rows.length;
      let newRow = rows[0].cloneNode(true);

      newRow.querySelectorAll('input').forEach(input => {
        let name = input.getAttribute('name');
        let updated = name.replace(/\[\d+\]/, `[${index}]`);
        input.setAttribute('name', updated);
        input.value = '';
      });

      rowContainer.appendChild(newRow);
    });
  });
</script>
