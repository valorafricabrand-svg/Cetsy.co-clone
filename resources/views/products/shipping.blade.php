{{-- resources/views/products/shipping.blade.php --}}
@extends('layouts.app')
@section('title', ($product->name ?? 'Product') . ' | Edit Shipping')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .charge-free{opacity:.6}
  .nav.nav-pills.flex-nowrap{overflow:auto;white-space:nowrap;scrollbar-width:thin}
  .table td,.table th{vertical-align:middle}
  .form-hint{font-size:.85rem;color:#6c757d}
</style>
@endpush

@section('content')
@php
  $current = Route::currentRouteName();
  $currency = $currency ?? currency_symbol();
@endphp

<div class="content">

  {{-- TABS --}}
  <div class="page-header-sticky mb-3">
    <ul class="nav nav-pills gap-2 flex-nowrap py-2" role="tablist" aria-label="Product sections">
      <li class="nav-item">
        <a class="nav-link {{ $current==='products.show' ? 'active':'btn-outline-secondary' }}" href="{{ route('products.show',$product) }}">
          <i class="fa-regular fa-circle-question me-1"></i> About
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $current==='products.pricing' ? 'active':'btn-outline-secondary' }}" href="{{ route('products.pricing',$product) }}">
          <i class="fa-solid fa-tags me-1"></i> Price & Inventory
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $current==='products.variations' ? 'active':'btn-outline-secondary' }}" href="{{ route('products.variations',$product) }}">
          <i class="fa-solid fa-layer-group me-1"></i> Variations
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $current==='products.details' ? 'active':'btn-outline-secondary' }}" href="{{ route('products.details',$product) }}">
          <i class="fa-regular fa-rectangle-list me-1"></i> Details
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $current==='products.shipping' ? 'active':'btn-outline-secondary' }}" href="{{ route('products.shipping',$product) }}">
          <i class="fa-solid fa-truck me-1"></i> Shipping
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $current==='products.settings' ? 'active':'btn-outline-secondary' }}" href="{{ route('products.settings',$product) }}">
          <i class="fa-solid fa-gear me-1"></i> Settings
        </a>
      </li>
    </ul>
  </div>

  {{-- ALERTS --}}
  @if(session('success'))
    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- HEADER --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">{{ $product->name ?? 'Product' }} — Edit Shipping</h2>
    <a href="{{ route('products.show',$product) }}" class="btn btn-outline-dark btn-sm">Back</a>
  </div>

  {{-- PROFILE INFO FORM --}}
  <form id="shipping-info-form" method="POST" action="{{ route('products.shipping.update',$product) }}" class="row g-3 mb-4" novalidate>
    @csrf
    @method('PATCH')

    <div class="col-md-4">
      <label class="form-label">Ship-from country</label>
      <select name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
        @foreach($countries as $c)
          <option value="{{ $c->id }}" @selected(old('country_id', $currentProfile->country_id) == $c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
      @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
      <label class="form-label">Postal code</label>
      <input type="text" name="origin_postal_code" class="form-control @error('origin_postal_code') is-invalid @enderror"
             value="{{ old('origin_postal_code', $currentProfile->origin_postal_code) }}" autocomplete="postal-code">
      @error('origin_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
      <label class="form-label">Processing time</label>
      <select name="processing_time_id" id="processing-time-select" class="form-select @error('processing_time_id') is-invalid @enderror">
        <option value="">Select…</option>
        @foreach($processingTimes as $pt)
          <option value="{{ $pt->id }}" @selected(old('processing_time_id', $currentProfile->processing_time_id) == $pt->id)>{{ $pt->days }} day(s)</option>
        @endforeach
        @php
          $showCustomProcessing = old('processing_time_id')==='custom'
            || (is_null($currentProfile->processing_time_id) && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max));
        @endphp
        <option value="custom" @selected($showCustomProcessing)>Custom</option>
      </select>
      @error('processing_time_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div id="processing-custom-indicator" class="form-text text-success" style="display: {{ $showCustomProcessing && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max) ? 'block' : 'none' }};">
        @if($showCustomProcessing && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max))
          Custom set: {{ (int)$currentProfile->processing_custom_min }}–{{ (int)$currentProfile->processing_custom_max }} days ✓
        @endif
      </div>
    </div>

    {{-- Custom processing window (auto toggled) --}}
    <div class="col-md-2 processing-custom-wrap" style="display: {{ $showCustomProcessing ? 'block' : 'none' }};">
      <label class="form-label">Min days</label>
      <input type="number" min="1" name="processing_custom_min"
             class="form-control @error('processing_custom_min') is-invalid @enderror"
             value="{{ old('processing_custom_min', $currentProfile->processing_custom_min) }}">
      @error('processing_custom_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 processing-custom-wrap" style="display: {{ $showCustomProcessing ? 'block' : 'none' }};">
      <label class="form-label">Max days</label>
      <input type="number" min="1" name="processing_custom_max"
             class="form-control @error('processing_custom_max') is-invalid @enderror"
             value="{{ old('processing_custom_max', $currentProfile->processing_custom_max) }}">
      @error('processing_custom_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-hint">Shown if “Custom” is selected.</div>
    </div>

    <div id="processing-warning" class="col-12 d-none">
      <div class="alert alert-warning mb-0" role="alert">
        For custom processing time, please enter both Min and Max days.
      </div>
    </div>

    <div class="col-12 text-end">
      <button class="btn btn-primary">
        <i class="fa-regular fa-floppy-disk me-1"></i> Save Info
      </button>
    </div>
  </form>

  {{-- SHIPPING ROWS --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Standard shipping</h5>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addRowModal">
      <i class="fa-solid fa-plus me-1"></i> Add row
    </button>
  </div>

  @if(($shippingProfiles ?? collect())->isEmpty())
    <div class="border rounded p-4 mb-4 text-center">
      <p class="mb-1">No shipping rows yet.</p>
      <p class="text-muted mb-3">Create rules for destinations, couriers, delivery windows, and charges.</p>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRowModal">Add your first shipping row</button>
    </div>
  @else
    <div class="table-responsive">
      <table class="table table-bordered mb-4 align-middle">
        <thead class="table-light">
          <tr>
            <th>Location</th>
            <th>Service</th>
            <th>Days</th>
            <th>Charge</th>
            <th>One item</th>
            <th>Additional</th>
            <th class="text-center" style="width:110px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($shippingProfiles as $row)
            @php
              $countryName = '';
              if ($row->dest_country_id) {
                $country = $countries->firstWhere('id', $row->dest_country_id);
                $countryName = $country ? $country->name : 'Selected country';
              }
            @endphp
            <tr class="{{ $row->charge_type==='free' ? 'charge-free' : '' }}">
              <td>
                @if($row->dest_location_type==='everywhere_else')
                  Everywhere
                @elseif($row->dest_country_id)
                  Ship to {{ $countryName }}
                @else
                  &mdash;
                @endif
              </td>
              <td>{{ $row->service }}</td>
              <td>{{ $row->days_min }} &ndash; {{ $row->days_max }}</td>
              <td>{{ ucfirst($row->charge_type) }}</td>
              <td>{{ $currency }} {{ number_format((float)$row->base_rate, 2) }}</td>
              <td>{{ $currency }} {{ number_format((float)$row->additional_rate, 2) }}</td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRowModal{{ $row->id }}" title="Edit">✎</button>
                <form method="POST" action="{{ route('products.shipping.rows.destroy',[$product,$row]) }}" class="d-inline" onsubmit="return confirm('Delete this shipping row?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger" title="Delete">×</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

{{-- ADD ROW MODAL --}}
<div class="modal fade" id="addRowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('products.shipping.rows.store',$product) }}" class="modal-content" novalidate>
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Add shipping row</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{-- Inherit profile-level values --}}
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
            <option value="country" selected>Country</option>
            <option value="everywhere_else">Everywhere</option>
          </select>
        </div>

        <div class="mb-3" id="add-country-wrap">
          <label class="form-label">Country</label>
          <select name="row[country_id]" class="form-select">
            <option value="">Select…</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
          <div class="form-hint">Shown only when “Country” is chosen.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Service</label>
          @php $couriers = couriers_list(); @endphp
          <select name="row[service]" class="form-select service-select" id="add-service-select" data-target="#add-service-other-wrap" required>
            @if(!empty($couriers))
              <optgroup label="Common Couriers">
                @foreach($couriers as $c)
                  <option>{{ $c }}</option>
                @endforeach
              </optgroup>
            @endif
            <optgroup label="Generic Services">
              <option>Courier</option>
              <option>Postal service</option>
              <option>Express</option>
              <option>Manual</option>
              <option>Other</option>
            </optgroup>
          </select>
        </div>

        <div class="mb-3" id="add-service-other-wrap" style="display:none;">
          <label class="form-label">Courier name</label>
          <input type="text" name="row[service_other]" class="form-control" placeholder="Enter courier (e.g., DHL, Rider, etc.)" maxlength="100">
          <div class="form-hint">Shown when Service is Other/Manual.</div>
        </div>

        <div class="row gx-2 mb-3">
          <div class="col">
            <label class="form-label">Min days</label>
            <input type="number" name="row[days_min]" class="form-control" min="1" value="1" required>
          </div>
          <div class="col">
            <label class="form-label">Max days</label>
            <input type="number" name="row[days_max]" class="form-control" min="1" value="1" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Charge type</label>
          <select name="row[charge_type]" class="form-select charge-type-select" data-one="#add-price-one" data-add="#add-price-additional">
            <option value="fixed" selected>Fixed</option>
            <option value="free">Free</option>
          </select>
        </div>

        <div class="row gx-2 mb-3">
          <div class="col">
            <label class="form-label">One item</label>
            <div class="input-group">
              <span class="input-group-text">{{ $currency }}</span>
              <input id="add-price-one" type="number" name="row[price_one]" step="0.01" class="form-control" value="0.00" required>
            </div>
          </div>
          <div class="col">
            <label class="form-label">Additional</label>
            <div class="input-group">
              <span class="input-group-text">{{ $currency }}</span>
              <input id="add-price-additional" type="number" name="row[price_additional]" step="0.01" class="form-control" value="0.00" required>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-plus me-1"></i> Add row
        </button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT ROW MODALS --}}
@foreach($shippingProfiles as $row)
  @php
    $standardServices = [
      'Other','Manual','Postal service','Courier','Express',
      'DHL','FedEx','UPS','USPS','Royal Mail','DPD','Evri','GLS',
      'Canada Post','Australia Post','PostNL','La Poste','SEUR','Correos','Aramex','TNT'
    ];
    $customServiceVal = in_array($row->service, $standardServices, true) ? '' : $row->service;
    $showCustom = $row->service==='Other' || $row->service==='Manual' || ($customServiceVal !== '');
    $couriers = couriers_list();
  @endphp
  <div class="modal fade" id="editRowModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="{{ route('products.shipping.rows.update',[$product,$row]) }}" class="modal-content" novalidate>
        @csrf
        @method('PATCH')

        <div class="modal-header">
          <h5 class="modal-title">Edit shipping row</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          {{-- Hidden profile-level fields (preserved) --}}
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
              <option value="country" @selected($row->dest_location_type==='country')>Country</option>
              <option value="everywhere_else" @selected($row->dest_location_type==='everywhere_else')>Everywhere</option>
            </select>
          </div>

          <div class="mb-3" id="country-wrap-{{ $row->id }}">
            <label class="form-label">Country</label>
            <select name="row[country_id]" class="form-select">
              <option value="">Select…</option>
              @foreach($countries as $c)
                <option value="{{ $c->id }}" @selected($c->id==$row->dest_country_id)>{{ $c->name }}</option>
              @endforeach
            </select>
            <div class="form-hint">Shown only when “Country” is chosen.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Service</label>
            <select name="row[service]" class="form-select edit-service-select" data-target="#service-other-wrap-{{ $row->id }}" required>
              @if(!empty($couriers))
                <optgroup label="Common Couriers">
                  @foreach($couriers as $c)
                    <option @selected($row->service===$c)>{{ $c }}</option>
                  @endforeach
                </optgroup>
              @endif
              <optgroup label="Generic Services">
                <option @selected($row->service==='Courier')>Courier</option>
                <option @selected($row->service==='Postal service')>Postal service</option>
                <option @selected($row->service==='Express')>Express</option>
                <option @selected($row->service==='Manual')>Manual</option>
                <option @selected($row->service==='Other')>Other</option>
              </optgroup>
            </select>
          </div>

          <div class="mb-3" id="service-other-wrap-{{ $row->id }}" style="display: {{ $showCustom ? 'block' : 'none' }};">
            <label class="form-label">Courier name</label>
            <input type="text" name="row[service_other]" class="form-control" value="{{ $customServiceVal }}" placeholder="Enter courier (e.g., DHL, Rider, etc.)" maxlength="100">
            <div class="form-hint">Shown when Service is Other/Manual.</div>
          </div>

          <div class="row gx-2 mb-3">
            <div class="col">
              <label class="form-label">Min days</label>
              <input type="number" name="row[days_min]" class="form-control" min="1" value="{{ (int)$row->days_min }}" required>
            </div>
            <div class="col">
              <label class="form-label">Max days</label>
              <input type="number" name="row[days_max]" class="form-control" min="1" value="{{ (int)$row->days_max }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Charge type</label>
            <select name="row[charge_type]" class="form-select charge-type-select" data-one="#price-one-{{ $row->id }}" data-add="#price-additional-{{ $row->id }}">
              <option value="fixed" @selected($row->charge_type==='fixed')>Fixed</option>
              <option value="free"  @selected($row->charge_type==='free')>Free</option>
            </select>
          </div>

          <div class="row gx-2 mb-3">
            <div class="col">
              <label class="form-label">One item</label>
              <div class="input-group">
                <span class="input-group-text">{{ $currency }}</span>
                <input id="price-one-{{ $row->id }}" type="number" name="row[price_one]" step="0.01" class="form-control" value="{{ number_format((float)$row->base_rate, 2, '.', '') }}" required>
              </div>
            </div>
            <div class="col">
              <label class="form-label">Additional</label>
              <div class="input-group">
                <span class="input-group-text">{{ $currency }}</span>
                <input id="price-additional-{{ $row->id }}" type="number" name="row[price_additional]" step="0.01" class="form-control" value="{{ number_format((float)$row->additional_rate, 2, '.', '') }}" required>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-regular fa-floppy-disk me-1"></i> Save changes
          </button>
        </div>
      </form>
    </div>
  </div>
@endforeach
@endsection

@push('scripts')
<script>
/** Safe QS */
const $ = (sel, root=document) => root.querySelector(sel);
const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

/** Toggle show/hide of country selectors based on location type */
function toggleCountry(selectEl, wrapSelector){
  const wrap = typeof wrapSelector === 'string' ? $(wrapSelector) : wrapSelector;
  if(!selectEl || !wrap) return;
  wrap.style.display = (selectEl.value === 'country') ? 'block' : 'none';
}

/** Bind add-row location chooser */
(function bindAddLocation(){
  const sel = $('#add-location-type');
  const wrap = $('#add-country-wrap');
  if(!sel || !wrap) return;
  toggleCountry(sel, wrap);
  sel.addEventListener('change', e => toggleCountry(e.target, wrap));
})();

/** Bind edit-row location choosers */
(function bindEditLocations(){
  $$('.edit-location-type').forEach(sel => {
    const target = sel.getAttribute('data-target'); // e.g. #country-wrap-123
    toggleCountry(sel, target);
    sel.addEventListener('change', e => toggleCountry(e.target, target));
  });
})();

/** Toggle “Other/Manual” courier free text input */
function bindServiceToggle(select){
  const targetSel = select.getAttribute('data-target');
  const wrap = targetSel ? $(targetSel) : null;
  const toggle = () => {
    const v = (select.value || '').toLowerCase();
    if (wrap) wrap.style.display = (v === 'other' || v === 'manual') ? 'block' : 'none';
  };
  select.addEventListener('change', toggle);
  toggle(); // init
}
$$('.service-select, .edit-service-select').forEach(bindServiceToggle);

/** Disable price inputs when “Free” is selected */
function bindChargeToggle(select){
  const oneSel = select.getAttribute('data-one');
  const addSel = select.getAttribute('data-add');
  const one = oneSel ? $(oneSel) : null;
  const add = addSel ? $(addSel) : null;
  const setState = (isFree) => {
    [one, add].forEach(inp => {
      if(!inp) return;
      inp.readOnly = isFree;
      inp.required = !isFree;
      if(isFree) inp.value = '0.00';
      inp.closest('.input-group')?.classList.toggle('opacity-50', isFree);
    });
  };
  const toggle = () => setState((select.value || '').toLowerCase() === 'free');
  select.addEventListener('change', toggle);
  toggle(); // init
}
$$('.charge-type-select').forEach(bindChargeToggle);

/** Processing time: reveal custom min/max */
(function bindProcessingCustom(){
  const sel = $('#processing-time-select');
  if(!sel) return;
  const wraps = $$('.processing-custom-wrap');
  const setVisible = (show) => wraps.forEach(w => { if(w) w.style.display = show ? 'block' : 'none'; });
  const onChange = () => {
    const isCustom = sel.value === 'custom';
    setVisible(isCustom);
    updateProcessingIndicator();
  };
  sel.addEventListener('change', onChange);
  onChange(); // init
})();

/** Show a green indicator when custom min/max are set */
function updateProcessingIndicator(){
  const sel = $('#processing-time-select');
  const ind = $('#processing-custom-indicator');
  const minEl = $('input[name="processing_custom_min"]');
  const maxEl = $('input[name="processing_custom_max"]');
  if(!sel || !ind || !minEl || !maxEl) return;
  const isCustom = sel.value === 'custom';
  const min = parseInt(minEl.value, 10);
  const max = parseInt(maxEl.value, 10);
  const valid = isCustom && Number.isInteger(min) && Number.isInteger(max) && min > 0 && max > 0;
  ind.style.display = valid ? 'block' : 'none';
  if (valid) ind.textContent = `Custom set: ${min}–${max} days ✓`;
}
['input','change'].forEach(ev => {
  const minEl = $('input[name="processing_custom_min"]');
  const maxEl = $('input[name="processing_custom_max"]');
  minEl && minEl.addEventListener(ev, updateProcessingIndicator);
  maxEl && maxEl.addEventListener(ev, updateProcessingIndicator);
});

/** Client-side guard: require min/max when custom selected */
(function bindInfoFormValidate(){
  const form = $('#shipping-info-form');
  if(!form) return;
  form.addEventListener('submit', function(e){
    const sel = $('#processing-time-select');
    const warnWrap = $('#processing-warning');
    const minEl = $('input[name="processing_custom_min"]');
    const maxEl = $('input[name="processing_custom_max"]');
    if (sel && sel.value === 'custom') {
      const min = parseInt(minEl?.value || '', 10);
      const max = parseInt(maxEl?.value || '', 10);
      if (!Number.isInteger(min) || !Number.isInteger(max)) {
        e.preventDefault();
        if (warnWrap) warnWrap.classList.remove('d-none');
        warnWrap?.scrollIntoView({behavior:'smooth', block:'center'});
      }
    }
  });
})();
</script>
@endpush
