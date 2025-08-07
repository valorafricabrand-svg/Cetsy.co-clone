{{-- resources/views/products/shipping.blade.php --}}
@extends('layouts.app')
@section('title', $product->name . ' | Edit Shipping')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .charge-free{opacity:.6}
</style>
@endpush

@section('content')
@php $current = Route::currentRouteName(); @endphp

<div class="content">

  {{-- TABS --}}
  <div class="page-header-sticky mb-3">
    <ul class="nav nav-pills gap-2 flex-nowrap">
      <li class="nav-item"><a class="nav-link {{ $current==='products.show'      ?'active':'btn-outline-secondary' }}" href="{{ route('products.show',$product) }}"><i class="fa-regular fa-circle-question me-1"></i> About</a></li>
      <li class="nav-item"><a class="nav-link {{ $current==='products.pricing'   ?'active':'btn-outline-secondary' }}" href="{{ route('products.pricing',$product) }}"><i class="fa-solid fa-tags me-1"></i> Price & Inventory</a></li>
      <li class="nav-item"><a class="nav-link {{ $current==='products.variations'? 'active':'btn-outline-secondary' }}" href="{{ route('products.variations',$product) }}"><i class="fa-solid fa-layer-group me-1"></i> Variations</a></li>
      <li class="nav-item"><a class="nav-link {{ $current==='products.details'   ?'active':'btn-outline-secondary' }}" href="{{ route('products.details',$product) }}"><i class="fa-regular fa-rectangle-list me-1"></i> Details</a></li>
      <li class="nav-item"><a class="nav-link {{ $current==='products.shipping'  ?'active':'btn-outline-secondary' }}" href="{{ route('products.shipping',$product) }}"><i class="fa-solid fa-truck me-1"></i> Shipping</a></li>
      <li class="nav-item"><a class="nav-link {{ $current==='products.settings'  ?'active':'btn-outline-secondary' }}" href="{{ route('products.settings',$product) }}"><i class="fa-solid fa-gear me-1"></i> Settings</a></li>
    </ul>
  </div>

  {{-- ALERTS --}}
  @if(session('success'))  <div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())      <div class="alert alert-danger"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

  {{-- HEADER --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>{{ $product->name }} — Edit Shipping</h2>
    <a href="{{ route('products.show',$product) }}" class="btn btn-outline-dark btn-sm">Back</a>
  </div>

  {{-- PROFILE INFO FORM --}}
  <form method="POST" action="{{ route('products.shipping.update',$product) }}" class="row g-3 mb-4">
    @csrf @method('PATCH')
    <div class="col-md-4">
      <label class="form-label">Ship-from country</label>
      <select name="country_id" class="form-select">
        @foreach($countries as $c)
          <option value="{{ $c->id }}" @selected($c->id==$currentProfile->country_id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Postal code</label>
      <input type="text" name="origin_postal_code" class="form-control" value="{{ $currentProfile->origin_postal_code }}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Processing time</label>
      <select name="processing_time_id" class="form-select">
        <option value="">Select…</option>
        @foreach($processingTimes as $pt)
          <option value="{{ $pt->id }}" @selected($pt->id==$currentProfile->processing_time_id)>{{ $pt->days }} day(s)</option>
        @endforeach
        <option value="custom" @selected(old('processing_time_id')==='custom')>Custom</option>
      </select>
    </div>
    @if(old('processing_time_id')==='custom' || ($currentProfile->processing_time_id===null && ($currentProfile->processing_custom_min||$currentProfile->processing_custom_max)))
      <div class="col-md-2"><input type="number" name="processing_custom_min" class="form-control" placeholder="Min days" value="{{ $currentProfile->processing_custom_min }}"></div>
      <div class="col-md-2"><input type="number" name="processing_custom_max" class="form-control" placeholder="Max days" value="{{ $currentProfile->processing_custom_max }}"></div>
    @endif
    <div class="col-12 text-end"><button class="btn btn-primary">Save Info</button></div>
  </form>

  {{-- SHIPPING ROWS --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5>Standard shipping</h5>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addRowModal">+ Add row</button>
  </div>

  <table class="table table-bordered mb-4">
    <thead class="table-light"><tr><th>Location</th><th>Service</th><th>Days</th><th>Charge</th><th>One item</th><th>Additional</th><th></th></tr></thead>
    <tbody>
      @foreach($shippingProfiles as $row)
        <tr class="{{ $row->charge_type==='free' ? 'charge-free' : '' }}">
          <td>
            @if($row->dest_location_type==='everywhere_else') Everywhere
            @elseif($row->dest_country_id)                  Ship to {{ $countries->firstWhere('id',$row->dest_country_id)->name }}
            @else                                           – @endif
          </td>
          <td>{{ $row->service }}</td>
          <td>{{ $row->days_min }}–{{ $row->days_max }}</td>
          <td>{{ ucfirst($row->charge_type) }}</td>
          <td>{{ number_format($row->base_rate,2) }}</td>
          <td>{{ number_format($row->additional_rate,2) }}</td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRowModal{{ $row->id }}">✎</button>
            <form method="POST" action="{{ route('products.shipping.rows.destroy',[$product,$row]) }}" class="d-inline" onsubmit="return confirm('Delete?')">
              @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger">×</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- ADD ROW MODAL --}}
<div class="modal fade" id="addRowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('products.shipping.rows.store',$product) }}" class="modal-content">
      @csrf
      <div class="modal-header"><h5 class="modal-title">Add shipping row</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        {{-- Persist profile-level values so the row inherits them --}}
        <input type="hidden" name="profile_name"          value="{{ $currentProfile->profile_name }}">
        <input type="hidden" name="set_default"           value="{{ $currentProfile->is_default?1:0 }}">
        <input type="hidden" name="country_id"            value="{{ $currentProfile->country_id }}">
        <input type="hidden" name="origin_postal_code"    value="{{ $currentProfile->origin_postal_code }}">
        <input type="hidden" name="processing_time_id"    value="{{ $currentProfile->processing_time_id }}">
        <input type="hidden" name="processing_custom_min" value="{{ $currentProfile->processing_custom_min }}">
        <input type="hidden" name="processing_custom_max" value="{{ $currentProfile->processing_custom_max }}">

        <div class="mb-3">
          <label class="form-label">Location type</label>
          <select name="row[location_type]" class="form-select" id="add-location-type" required>
            <option value="country">Country</option>
            <option value="everywhere_else">Everywhere</option>
          </select>
        </div>
        <div class="mb-3" id="add-country-wrap">
          <label class="form-label">Country</label>
          <select name="row[country_id]" class="form-select">
            <option value="">Select…</option>
            @foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Service</label>
          <select name="row[service]" class="form-select" required>
            <option>Other</option><option>Postal service</option><option>Courier</option><option>Express</option>
          </select>
        </div>

        <div class="row gx-2 mb-3">
          <div class="col"><label class="form-label">Min days</label><input type="number" name="row[days_min]" class="form-control" min="1" value="1" required></div>
          <div class="col"><label class="form-label">Max days</label><input type="number" name="row[days_max]" class="form-control" min="1" value="1" required></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Charge type</label>
          <select name="row[charge_type]" class="form-select">
            <option value="fixed">Fixed</option><option value="free">Free</option>
          </select>
        </div>

        <div class="row gx-2 mb-3">
          <div class="col"><label class="form-label">One item</label><input type="number" name="row[price_one]" step="0.01" class="form-control" value="0.00" required></div>
          <div class="col"><label class="form-label">Additional</label><input type="number" name="row[price_additional]" step="0.01" class="form-control" value="0.00" required></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add row</button></div>
    </form>
  </div>
</div>

{{-- EDIT ROW MODALS --}}
@foreach($shippingProfiles as $row)
<div class="modal fade" id="editRowModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('products.shipping.rows.update',[$product,$row]) }}" class="modal-content">
      @csrf @method('PATCH')
      <div class="modal-header"><h5 class="modal-title">Edit shipping row</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        {{-- hidden profile-level fields --}}
        <input type="hidden" name="profile_name"          value="{{ $row->profile_name }}">
        <input type="hidden" name="set_default"           value="{{ $row->is_default?1:0 }}">
        <input type="hidden" name="country_id"            value="{{ $row->country_id }}">
        <input type="hidden" name="origin_postal_code"    value="{{ $row->origin_postal_code }}">
        <input type="hidden" name="processing_time_id"    value="{{ $row->processing_time_id }}">
        <input type="hidden" name="processing_custom_min" value="{{ $row->processing_custom_min }}">
        <input type="hidden" name="processing_custom_max" value="{{ $row->processing_custom_max }}">

        <div class="mb-3">
          <label class="form-label">Location type</label>
          <select name="row[location_type]" class="form-select edit-location-type" data-target="#country-wrap-{{ $row->id }}" required>
            <option value="country"        @selected($row->dest_location_type==='country')>Country</option>
            <option value="everywhere_else"@selected($row->dest_location_type==='everywhere_else')>Everywhere</option>
          </select>
        </div>
        <div class="mb-3" id="country-wrap-{{ $row->id }}">
          <label class="form-label">Country</label>
          <select name="row[country_id]" class="form-select">
            <option value="">Select…</option>
            @foreach($countries as $c)<option value="{{ $c->id }}" @selected($c->id==$row->dest_country_id)>{{ $c->name }}</option>@endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Service</label>
          <select name="row[service]" class="form-select" required>
            <option @selected($row->service==='Other')>Other</option>
            <option @selected($row->service==='Postal service')>Postal service</option>
            <option @selected($row->service==='Courier')>Courier</option>
            <option @selected($row->service==='Express')>Express</option>
          </select>
        </div>

        <div class="row gx-2 mb-3">
          <div class="col"><label class="form-label">Min days</label><input type="number" name="row[days_min]" class="form-control" min="1" value="{{ $row->days_min }}" required></div>
          <div class="col"><label class="form-label">Max days</label><input type="number" name="row[days_max]" class="form-control" min="1" value="{{ $row->days_max }}" required></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Charge type</label>
          <select name="row[charge_type]" class="form-select">
            <option value="fixed" @selected($row->charge_type==='fixed')>Fixed</option>
            <option value="free"  @selected($row->charge_type==='free')>Free</option>
          </select>
        </div>

        <div class="row gx-2 mb-3">
          <div class="col"><label class="form-label">One item</label><input type="number" name="row[price_one]" step="0.01" class="form-control" value="{{ $row->base_rate }}" required></div>
          <div class="col"><label class="form-label">Additional</label><input type="number" name="row[price_additional]" step="0.01" class="form-control" value="{{ $row->additional_rate }}" required></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save changes</button></div>
    </form>
  </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
/* Toggle country picker visibility */
const toggleCountry = (selectEl, wrap) => {
  document.querySelector(wrap).style.display = (selectEl.value === 'country') ? 'block' : 'none';
};

/* Add-row modal */
document.getElementById('add-location-type').addEventListener('change', e => toggleCountry(e.target, '#add-country-wrap'));

/* Edit-row modals (delegated) */
document.querySelectorAll('.edit-location-type').forEach(sel => {
  const wrap = sel.dataset.target;
  toggleCountry(sel, wrap); // init on load
  sel.addEventListener('change', e => toggleCountry(e.target, wrap));
});
</script>
@endpush
