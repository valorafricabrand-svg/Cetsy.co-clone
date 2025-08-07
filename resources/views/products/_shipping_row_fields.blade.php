{{-- resources/views/products/_shipping_row_fields.blade.php --}}
{{-- Shared shipping row fields for both Add & Edit modals --}}

<div class="mb-3">
  <label class="form-label">Location type</label>
  <select name="row[location_type]" class="form-select" x-model="row.location_type">
    <option value="country">Country</option>
    <option value="everywhere_else">Everywhere else</option>
  </select>
</div>

<div class="mb-3" x-show="row.location_type==='country'">
  <label class="form-label">Country</label>
  <select name="row[country_id]" class="form-select" x-model.number="row.country_id">
    <option value="">Select…</option>
    @foreach($countries as $c)
      <option value="{{ $c->id }}">{{ $c->name }}</option>
    @endforeach
  </select>
</div>

<div class="mb-3">
  <label class="form-label">Service</label>
  <select name="row[service]" class="form-select" x-model="row.service">
    <option value="Other">Other</option>
    <option value="Postal service">Postal service</option>
    <option value="Courier">Courier</option>
    <option value="Express">Express</option>
  </select>
</div>

<div class="row gx-2 mb-3">
  <div class="col">
    <label class="form-label">Min days</label>
    <input type="number" name="row[days_min]" class="form-control" min="1" x-model.number="row.days_min">
  </div>
  <div class="col">
    <label class="form-label">Max days</label>
    <input type="number" name="row[days_max]" class="form-control" min="1" x-model.number="row.days_max">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">Charge type</label>
  <select name="row[charge_type]" class="form-select" x-model="row.charge_type">
    <option value="fixed">Fixed</option>
    <option value="free">Free</option>
  </select>
</div>

<div class="row gx-2 mb-3">
  <div class="col">
    <label class="form-label">One item</label>
    <input type="number" name="row[price_one]" step="0.01" class="form-control" x-model.number="row.price_one">
  </div>
  <div class="col">
    <label class="form-label">Additional</label>
    <input type="number" name="row[price_additional]" step="0.01" class="form-control" x-model.number="row.price_additional">
  </div>
</div>
