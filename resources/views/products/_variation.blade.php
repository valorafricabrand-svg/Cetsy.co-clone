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
              @foreach($product->category->attributes as $attr)
                <th>{{ $attr->name }}</th>
              @endforeach
              <th>SKU</th>
              <th>Price</th>
              <th>Stock</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($product->variations as $var)
              <tr>
                @foreach($product->category->attributes as $attr)
                  @php 
                    $val = $var->values->firstWhere('category_attribute_id', $attr->id);
                  @endphp
                  <td>{{ $val->value ?? '—' }}</td>
                @endforeach
                <td>{{ $var->sku }}</td>
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
          @foreach($product->category->attributes as $attr)
            <div class="mb-3">
              <label class="form-label">{{ $attr->name }}</label>
              <select name="values[{{ $attr->id }}]" class="form-select" required>
                <option value="">Choose {{ $attr->name }}</option>
                @foreach($attr->values as $val)
                  <option value="{{ $val->id }}"
                    @if($var->values->pluck('id')->contains($val->id)) selected @endif>
                    {{ $val->value }}
                  </option>
                @endforeach
              </select>
            </div>
          @endforeach

          <div class="mb-3">
            <label class="form-label">SKU</label>
            <input name="sku"
                   value="{{ $var->sku }}"
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

{{-- ADD NEW VARIATION FORM --}}
<div class="card mb-5 shadow-sm">
  <div class="card-header bg-light"><h5>Add New Variation</h5></div>
  <form class="card-body"
        action="{{ route('variations.store', $product) }}"
        method="POST">
    @csrf

    <div class="row g-3 mb-3">
      @foreach($product->category->attributes as $attr)
        <div class="col-md-4">
          <label class="form-label">{{ $attr->name }}</label>
          <select name="values[{{ $attr->id }}]"
                  class="form-select @error('values.' . $attr->id) is-invalid @enderror"
                  required>
            <option value="">Choose {{ $attr->name }}</option>
            @foreach($attr->values as $val)
              <option value="{{ $val->id }}"
                @selected(old('values.' . $attr->id) == $val->id)>
                {{ $val->value }}
              </option>
            @endforeach
          </select>
          @error('values.' . $attr->id)
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      @endforeach
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input name="sku"
               value="{{ old('sku') }}"
               class="form-control @error('sku') is-invalid @enderror"
               required>
        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
      <div class="col-md-4">
        <label class="form-label">Price</label>
        <input name="price"
               type="number"
               step="0.01"
               value="{{ old('price') }}"
               class="form-control @error('price') is-invalid @enderror"
               required>
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
      <div class="col-md-4">
        <label class="form-label">Stock</label>
        <input name="stock"
               type="number"
               value="{{ old('stock') }}"
               class="form-control @error('stock') is-invalid @enderror"
               required>
        @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>

    <button type="submit" class="btn btn-outline-success">Add Variation</button>
  </form>
</div>
