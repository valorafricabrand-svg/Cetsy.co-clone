{{-- resources/views/shipping_profiles/_create_modal.blade.php --}}

<div class="modal" id="newProfileModal" tabindex="-1"
     aria-labelledby="newProfileLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form  action="{{ route('seller.shipping_profiles.store') }}"
           method="POST" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
      @csrf

      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h5 class="text-base font-semibold text-slate-900" id="newProfileLabel">Add Shipping Profile</h5>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
      </div>

      <div class="px-4 py-4">
        {{-- NAME --}}
        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">Name <span class="text-rose-600">*</span></label>
          <input  type="text" name="name" value="{{ old('name') }}"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
          @error('name') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>

        {{-- SHIP-TO COUNTRY --}}
        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">
            Ships to {{ setting('region') }} <small class="text-slate-500">(leave blank for Worldwide)</small>
          </label>
          <select name="country_id"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('country_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
            <option value="">Worldwide</option>
            @foreach($countries as $country)
              <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>
                {{ $country->name }}
              </option>
            @endforeach
          </select>
          @error('country_id') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>

        {{-- BASE RATE & DELIVERY DAYS --}}
        <div class="grid grid-cols-12 gap-4 gap-3">
          <div class="col-span-12 md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">
              Base Rate ({{ get_currency() }}) <span class="text-rose-600">*</span>
            </label>
            <input  type="number" name="base_rate" min="0" step="0.01"
                    value="{{ old('base_rate') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('base_rate') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
            @error('base_rate') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
          </div>
          <div class="col-span-12 md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Delivery Days <span class="text-rose-600">*</span></label>
            <input  type="number" name="delivery_days" min="0"
                    value="{{ old('delivery_days') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('delivery_days') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
            @error('delivery_days') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- PROCESSING TIME --}}
        <div class="mb-3 mt-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">Processing Time <span class="text-rose-600">*</span></label>
          <select name="processing_time_id"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_time_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                  required>
            <option value="">Select processing time</option>
            @foreach($processingTimes as $pt)
              <option value="{{ $pt->id }}" @selected(old('processing_time_id') == $pt->id)>
                {{ $pt->label }} ({{ $pt->days }} day{{ $pt->days>1?'s':'' }})
              </option>
            @endforeach
          </select>
          @error('processing_time_id') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>

        {{-- PICKUP SWITCH --}}
        <input type="hidden" name="pickup_available" value="0">
        <div class="form-check form-switch mt-3">
          <input  type="checkbox"
                  class="form-check-input @error('pickup_available') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                  id="pickup_available"
                  name="pickup_available"
                  value="1"
                  {{ old('pickup_available') ? 'checked' : '' }}>
          <label class="form-check-label" for="pickup_available">Pickup available</label>
          @error('pickup_available') <div class="mt-1 text-xs text-rose-600 block">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Create Profile</button>
      </div>
    </form>
  </div>
</div>



