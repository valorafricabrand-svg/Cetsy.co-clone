{{-- resources/views/shipping_profiles/_create_modal.blade.php --}}
<div class="modal fade" id="newProfileModal" tabindex="-1"
     aria-labelledby="newProfileLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form  action="{{ route('seller.shipping_profiles.store') }}"
           method="POST" class="modal-content">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title" id="newProfileLabel">Add Shipping Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        {{-- NAME --}}
        <div class="mb-3">
          <label class="form-label">Name <span class="text-danger">*</span></label>
          <input  type="text" name="name" value="{{ old('name') }}"
                  class="form-control @error('name') is-invalid @enderror" required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- SHIP-TO COUNTRY --}}
        <div class="mb-3">
          <label class="form-label">Ships to {{ setting('region') }} <span class="text-danger">*</span></label>
          <select name="country_id"
                  class="form-select @error('country_id') is-invalid @enderror" required>
            <option value="">Select {{ setting('region') }}</option>
            @foreach($countries as $country)
              <option value="{{ $country->id }}" @selected(old('country_id')==$country->id)>
                {{ $country->name }} ({{ $country->iso_code }})
              </option>
            @endforeach
          </select>
          @error('country_id') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- RATE & DAYS --}}
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Base Rate ({{ get_currency() }}) <span class="text-danger">*</span></label>
            <input  type="number" name="base_rate" min="0" step="0.01"
                    value="{{ old('base_rate') }}"
                    class="form-control @error('base_rate') is-invalid @enderror" required>
            @error('base_rate') <div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Delivery Days <span class="text-danger">*</span></label>
            <input  type="number" name="delivery_days" min="0"
                    value="{{ old('delivery_days') }}"
                    class="form-control @error('delivery_days') is-invalid @enderror" required>
            @error('delivery_days') <div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- PICKUP SWITCH --}}
        <input type="hidden" name="pickup_available" value="0">
        <div class="form-check form-switch mt-3">
          <input  type="checkbox" class="form-check-input @error('pickup_available') is-invalid @enderror"
                  id="pickup_available" name="pickup_available" value="1"
                  {{ old('pickup_available') ? 'checked' : '' }}>
          <label class="form-check-label" for="pickup_available">Pickup available</label>
          @error('pickup_available') <div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Profile</button>
      </div>
    </form>
  </div>
</div>
