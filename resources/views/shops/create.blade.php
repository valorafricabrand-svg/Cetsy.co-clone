{{-- resources/views/shops/create.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
    <div class="col-span-12 lg:col-span-10 lg:col-start-2">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
          <h2 class="text-lg font-semibold mb-0 text-center">Create Your Shop</h2>
        </div>
        <div class="p-4 sm:p-5">

          {{-- Flash Success --}}
          @if(session('success'))
            <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 alert-dismissible" role="alert">
              {{ session('success') }}
              <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
            </div>
          @endif

          {{-- Validation Errors --}}
          @if($errors->any())
            <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form 
            action="{{ route('seller.shops.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
            x-data="{ name: '{{ old('name') }}', slug: '{{ old('slug') }}' }"
            @input.debounce.300ms="slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');"
            class="needs-validation" 
            novalidate
          >
            @csrf

            {{-- 1) Shop Preferences --}}
            <h5 class="mt-4">1. Shop Preferences</h5>
            <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-12">
              <div class="col-span-12 md:col-span-4">
                <label for="language" class="mb-1 block text-sm font-medium text-slate-700">Language <span class="text-rose-600">*</span></label>
                <select name="language" id="language" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                  <option value="" disabled selected>Select language</option>
                  <option value="English" {{ old('language')=='English'?'selected':'' }}>English</option>
                </select>
                <div class="mt-1 text-xs text-rose-600">Please select a language.</div>
              </div>
              <div class="col-span-12 md:col-span-4">
                <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Country <span class="text-rose-600">*</span></label>
                <select name="country" id="country" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                  <option value="" disabled selected>Select country</option>
                  @foreach($countries as $country)
                    <option value="{{ $country->id }}" {{ old('country')==$country->id?'selected':'' }}>{{ $country->name }}</option>
                  @endforeach
                </select>
                <div class="mt-1 text-xs text-rose-600">Please select a country.</div>
              </div>

  <div class="col-span-12 md:col-span-4">
  <label for="currency" class="mb-1 block text-sm font-medium text-slate-700">
    Currency <span class="text-rose-600">*</span>
  </label>
  <select
    id="currency"
    name="currency"
    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('currency') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
    required
  >
    <option value="" disabled {{ old('currency') ? '' : 'selected' }}>
      Select a currency
    </option>
    @foreach(currencies() as $code => $name)
      <option
        value="{{ $code }}"
        {{ old('currency') === $code ? 'selected' : '' }}
      >
        ({{ $code }})
      </option>
    @endforeach
  </select>

  @error('currency')
    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
  @else
    <div class="mt-1 text-xs text-rose-600">Please select a currency.</div>
  @enderror
</div>



            </div>

            {{-- 2) Name & Slug --}}
            <h5 class="mt-4">2. Name Your Shop</h5>
            <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-12">
              <div class="col-span-12 md:col-span-8">
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Shop Name <span class="text-rose-600">*</span></label>
                <input 
                  id="name" name="name" type="text"
                  x-model="name"
                  required
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="e.g. MyCraftShop"
                >
                <div class="mt-1 text-xs text-rose-600">Please enter your shop name.</div>
              </div>
              <div class="col-span-12 md:col-span-4">
                <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">Slug (URL Identifier)</label>
                <div class="flex w-full flex-col items-stretch sm:flex-row">
                  <span class="inline-flex items-center rounded-t-xl border border-slate-300 bg-slate-100 px-3 py-2 text-xs text-slate-600 sm:rounded-l-xl sm:rounded-tr-none sm:text-sm">{{ url('shop') }}/</span>
                  <input 
                    id="slug" name="slug" type="text"
                    x-model="slug"
                    readonly
                    class="w-full rounded-b-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-100 sm:rounded-r-xl sm:rounded-bl-none"
                  >
                </div>
                <div class="mt-1 text-xs text-slate-500">Auto-generated from your shop name.</div>
              </div>
            </div>

           

            {{-- 3) Shop Images --}}
            <h5 class="mt-4">3. Shop Images</h5>
            <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-12">
              <div class="col-span-12 md:col-span-6">
                <label for="logo" class="mb-1 block text-sm font-medium text-slate-700">Logo (optional)</label>
                <input 
                  id="logo" name="logo" type="file"
                  accept="image/*"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                >
                <div class="mt-1 text-xs text-slate-500">Upload your shop logo. Recommended size: 200x200 pixels.</div>
              </div>
              <div class="col-span-12 md:col-span-6">
                <label for="featured_image" class="mb-1 block text-sm font-medium text-slate-700">Featured Image (optional)</label>
                <input 
                  id="featured_image" name="featured_image" type="file"
                  accept="image/*"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                >
                <div class="mt-1 text-xs text-slate-500">This image will be displayed prominently on your shop page. Recommended size: 1200x400 pixels.</div>
              </div>
            </div>

            {{-- 4) Share Your Billing Info --}}
            <h5 class="mt-4">4. Share Your Billing Info</h5>
            <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-12">
              <div class="col-span-12">
                <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Street Address <span class="text-rose-600">*</span></label>
                <input 
                  id="address" name="address" type="text"
                  value="{{ old('address') }}"
                  required
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="123 Main St"
                >
                <div class="mt-1 text-xs text-rose-600">Please enter your address.</div>
              </div>
              <div class="col-span-12 md:col-span-6">
                <label for="city" class="mb-1 block text-sm font-medium text-slate-700">City <span class="text-rose-600">*</span></label>
                <input 
                  id="city" name="city" type="text"
                  value="{{ old('city') }}"
                  required
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Anytown"
                >
                <div class="mt-1 text-xs text-rose-600">Please enter your city.</div>
              </div>
              <div class="col-span-12 md:col-span-6">
                <label for="postal" class="mb-1 block text-sm font-medium text-slate-700">Postal Code <span class="text-rose-600">*</span></label>
                <input 
                  id="postal" name="postal" type="text"
                  value="{{ old('postal') }}"
                  required
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="12345"
                >
                <div class="mt-1 text-xs text-rose-600">Please enter your postal code.</div>
              </div>
            </div>

            {{-- Submit --}}
            <div class="text-right mb-3">
              <button 
                type="submit"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5"
              >
                Finish & Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Ensure jQuery is loaded before Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('#currency').select2({
    placeholder: 'Select a currency',
    width: '100%',
    minimumResultsForSearch: 0 // Always show search box
  });
  $('#country').select2({
    placeholder: 'Select country',
    width: '100%',
    minimumResultsForSearch: 0 // Always show search box
  });
});
</script>

@endsection



