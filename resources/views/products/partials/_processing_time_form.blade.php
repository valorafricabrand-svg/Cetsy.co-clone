{{-- resources/views/products/partials/_processing_time_form.blade.php --}}
@php
  // Decide if "Custom" should be shown as selected and fields visible
  $showCustomProcessing = old('processing_time_id')==='custom'
    || (is_null($currentProfile->processing_time_id)
        && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max));
@endphp

<div class="md:col-span-4">
  <label class="mb-1 block text-sm font-medium text-slate-700">Processing time</label>
  <select name="processing_time_id" id="processing-time-select" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_time_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
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
  @error('processing_time_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
</div>

{{-- Custom processing window (auto toggled) --}}
<div class="md:col-span-2 processing-custom-wrap" style="display: {{ $showCustomProcessing ? 'block' : 'none' }};">
  <label class="mb-1 block text-sm font-medium text-slate-700">Min days</label>
  <input type="number" min="1" name="processing_custom_min"
         class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_custom_min') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
         value="{{ old('processing_custom_min', $currentProfile->processing_custom_min) }}">
  @error('processing_custom_min')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
</div>
<div class="md:col-span-2 processing-custom-wrap" style="display: {{ $showCustomProcessing ? 'block' : 'none' }};">
  <label class="mb-1 block text-sm font-medium text-slate-700">Max days</label>
  <input type="number" min="1" name="processing_custom_max"
         class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_custom_max') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
         value="{{ old('processing_custom_max', $currentProfile->processing_custom_max) }}">
  @error('processing_custom_max')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
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


