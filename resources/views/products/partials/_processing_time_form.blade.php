{{-- resources/views/products/partials/_processing_time_form.blade.php --}}
@php
  // Decide if "Custom" should be shown as selected and fields visible
  $showCustomProcessing = old('processing_time_id')==='custom'
    || (is_null($currentProfile->processing_time_id)
        && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max));
@endphp

<div class="col-md-4">
  <label class="form-label">Processing time</label>
  <select name="processing_time_id" id="processing-time-select" class="form-select @error('processing_time_id') is-invalid @enderror">
    <option value="">Select…</option>
    @foreach(($processingTimes ?? collect()) as $pt)
      @php
        $label = isset($pt->days)
          ? ($pt->days.' day(s)')
          : trim(($pt->start_day ?? '').'–'.($pt->end_day ?? '').' day(s)');
      @endphp
      <option value="{{ $pt->id }}" @selected(old('processing_time_id', $currentProfile->processing_time_id) == $pt->id)>
        {{ $label ?: 'Processing preset' }}
      </option>
    @endforeach
    <option value="custom" @selected($showCustomProcessing)>Custom</option>
  </select>
  @error('processing_time_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const sel = document.getElementById('processing-time-select');
    if (!sel) return;
    function toggleCustom(){
      const show = sel.value === 'custom';
      document.querySelectorAll('.processing-custom-wrap').forEach(el=>{
        el.style.display = show ? 'block' : 'none';
      });
    }
    sel.addEventListener('change', toggleCustom);
  });
  </script>
@endpush

