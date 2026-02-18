{{-- resources/views/products/_shipping_row_fields.blade.php --}}
{{-- Shared shipping row fields for both Add & Edit modals --}}

<div class="mb-3">
  <label class="mb-1 block text-sm font-medium text-slate-700">Location type</label>
  <select name="row[location_type]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" x-model="row.location_type">
    <option value="country">Country</option>
    <option value="everywhere_else">Everywhere else</option>
  </select>
</div>

<div class="mb-3" x-show="row.location_type==='country'">
  <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
  <select name="row[country_id]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" x-model.number="row.country_id">
    <option value="">Selectâ€¦</option>
    @foreach($countries as $c)
      <option value="{{ $c->id }}">{{ $c->name }}</option>
    @endforeach
  </select>
</div>

<div class="mb-3">
  <label class="mb-1 block text-sm font-medium text-slate-700">Service</label>
  @php($couriers = couriers_list())
  <select name="row[service]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" x-model="row.service">
    @if(!empty($couriers))
      <optgroup label="Common Couriers">
        @foreach($couriers as $c)
          <option value="{{ $c }}">{{ $c }}</option>
        @endforeach
      </optgroup>
    @endif
    <optgroup label="Generic Services">
      <option value="Courier">Courier</option>
      <option value="Postal service">Postal service</option>
      <option value="Express">Express</option>
      <option value="Manual">Manual</option>
      <option value="Other">Other</option>
    </optgroup>
  </select>
</div>

<div class="mb-3" x-show="row.service==='Other' || row.service==='Manual'">
  <label class="mb-1 block text-sm font-medium text-slate-700">Courier name</label>
  <input type="text" name="row[service_other]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Enter courier (e.g., DHL, Rider, etc.)">
  <div class="mt-1 text-xs text-slate-500">Shown when Service is Other/Manual.</div>
  
</div>

<div class="grid grid-cols-12 gap-4 gap-x-2 mb-3">
  <div class="col-span-12">
    <label class="mb-1 block text-sm font-medium text-slate-700">Min days</label>
    <input type="number" name="row[days_min]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" x-model.number="row.days_min">
  </div>
  <div class="col-span-12">
    <label class="mb-1 block text-sm font-medium text-slate-700">Max days</label>
    <input type="number" name="row[days_max]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" x-model.number="row.days_max">
  </div>
</div>

<div class="mb-3">
  <label class="mb-1 block text-sm font-medium text-slate-700">Charge type</label>
  <select name="row[charge_type]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" x-model="row.charge_type">
    <option value="fixed">Fixed</option>
    <option value="free">Free</option>
  </select>
</div>

<div class="grid grid-cols-12 gap-4 gap-x-2 mb-3">
  <div class="col-span-12">
    <label class="mb-1 block text-sm font-medium text-slate-700">One item</label>
    <input type="number" name="row[price_one]" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" x-model.number="row.price_one">
  </div>
  <div class="col-span-12">
    <label class="mb-1 block text-sm font-medium text-slate-700">Additional</label>
    <input type="number" name="row[price_additional]" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" x-model.number="row.price_additional">
  </div>
</div>

