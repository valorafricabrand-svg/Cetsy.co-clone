{{-- resources/views/products/shipping.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title', ($product->name ?? 'Product') . ' | Edit Shipping')

@push('styles')
<style>
  .shipping-rows-table{
    min-width:760px;
  }
  .shipping-row-card__actions{
    display:flex;
    flex-direction:column;
    gap:.5rem;
  }
  @media (min-width: 640px){
    .shipping-row-card__actions{
      flex-direction:row;
      align-items:center;
    }
  }
</style>
@endpush

@section('main')
@php
  $current = Route::currentRouteName();
  $currency = $currency ?? currency_symbol();
@endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6" x-data="{ addRowOpen: false, editRowOpen: null }" @keydown.escape.window="addRowOpen = false; editRowOpen = null">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Shipping</h1>
              <p class="mt-1 text-sm text-slate-500">Set ship-from details, pickup availability, processing windows, and destination-based rates for this listing.</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
              <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                <i class="fas fa-external-link-alt mr-2"></i> View Public Listing
              </a>
              <a href="{{ route('products.show',$product) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                <i class="fas fa-arrow-left mr-2"></i> Back to Listing
              </a>
            </div>
          </div>
        </div>

        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

  {{-- ALERTS --}}
  @if(session('success'))
    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800" role="alert">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800" role="alert">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- PROFILE INFO FORM --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
      <h2 class="text-lg font-bold text-slate-900">Shipping Profile</h2>
      <p class="mt-1 text-sm text-slate-500">This information powers shipping choices buyers will see at checkout.</p>
    </div>
    <div class="p-4 sm:p-5">
      <form id="shipping-info-form" method="POST" action="{{ route('products.shipping.update',$product) }}" class="grid grid-cols-12 gap-3" novalidate>
        @csrf
        @method('PATCH')

        <div class="col-span-12 md:col-span-4">
          <label class="mb-1 block text-sm font-medium text-slate-700">Ship-from country</label>
          <select name="country_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('country_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
            @foreach($countries as $c)
              <option value="{{ $c->id }}" @selected(old('country_id', $currentProfile->country_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
          @error('country_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>

        <div class="col-span-12 md:col-span-4">
          <label class="mb-1 block text-sm font-medium text-slate-700">Postal code</label>
          <input type="text" name="origin_postal_code" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('origin_postal_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                 value="{{ old('origin_postal_code', $currentProfile->origin_postal_code) }}" autocomplete="postal-code" required>
          @error('origin_postal_code')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>

        <div class="col-span-12 md:col-span-4">
          <label class="mb-1 block text-sm font-medium text-slate-700">Processing time</label>
          <select name="processing_time_id" id="processing-time-select" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_time_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
            <option value="">Select...</option>
            @foreach($processingTimes as $pt)
              <option value="{{ $pt->id }}" @selected(old('processing_time_id', $currentProfile->processing_time_id) == $pt->id)>{{ $pt->days }} day(s)</option>
            @endforeach
            @php
              $showCustomProcessing = old('processing_time_id')==='custom'
                || (is_null($currentProfile->processing_time_id) && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max));
            @endphp
            <option value="custom" @selected($showCustomProcessing)>Custom</option>
          </select>
          @error('processing_time_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
          <div id="processing-custom-indicator" class="mt-1 text-xs text-slate-500 text-emerald-600" style="display: {{ $showCustomProcessing && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max) ? 'block' : 'none' }};">
            @if($showCustomProcessing && ($currentProfile->processing_custom_min || $currentProfile->processing_custom_max))
              Custom set: {{ (int)$currentProfile->processing_custom_min }}-{{ (int)$currentProfile->processing_custom_max }} days 
            @endif
          </div>
        </div>

        <div class="col-span-12 sm:col-span-6 md:col-span-2 processing-custom-wrap" style="display: {{ $showCustomProcessing ? 'block' : 'none' }};">
          <label class="mb-1 block text-sm font-medium text-slate-700">Min days</label>
          <input type="number" min="1" name="processing_custom_min"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_custom_min') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                 value="{{ old('processing_custom_min', $currentProfile->processing_custom_min) }}">
          @error('processing_custom_min')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>
        <div class="col-span-12 sm:col-span-6 md:col-span-2 processing-custom-wrap" style="display: {{ $showCustomProcessing ? 'block' : 'none' }};">
          <label class="mb-1 block text-sm font-medium text-slate-700">Max days</label>
          <input type="number" min="1" name="processing_custom_max"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_custom_max') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                 value="{{ old('processing_custom_max', $currentProfile->processing_custom_max) }}">
          @error('processing_custom_max')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
          <div class="text-xs text-slate-500">Shown if "Custom" is selected.</div>
        </div>

        <div id="processing-warning" class="col-span-12 hidden">
          <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mb-0" role="alert">
            For custom processing time, please enter both Min and Max days.
          </div>
        </div>

        <div class="col-span-12">
          <div class="rounded-2xl border border-slate-200 bg-slate-50">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
              <div>
                <h6 class="mb-1 text-sm font-semibold text-slate-900">Pickup available</h6>
                <p class="mb-0 text-xs text-slate-500">
                  Let buyers know they can collect this item in person for this specific listing.
                </p>
              </div>
              <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="hidden" name="pickup_available" value="0">
                <input
                  class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 @error('pickup_available') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                  type="checkbox"
                  id="pickup_available"
                  name="pickup_available"
                  value="1"
                  {{ old('pickup_available', $product->pickup_available) ? 'checked' : '' }}>
                <span>Enabled</span>
              </label>
            </div>
          </div>
          @error('pickup_available')<div class="text-rose-600 text-xs mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-span-12 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
            <i class="fa-regular fa-floppy-disk mr-1"></i> Save Info
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- SHIPPING ROWS --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h5 class="mb-0 text-lg font-bold text-slate-900">Standard Shipping Rows</h5>
          <p class="mt-1 text-xs text-slate-500">
            Tip: add separate rows for local and international services so buyers can choose the option that fits them best in the cart.
          </p>
        </div>
        <button type="button" class="inline-flex w-full items-center justify-center rounded-xl border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 sm:w-auto" @click="addRowOpen = true">
          <i class="fa-solid fa-plus mr-2"></i> Add row
        </button>
      </div>
    </div>

    <div class="p-4 sm:p-5">
      @if(($shippingProfiles ?? collect())->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center">
          <p class="mb-1 font-semibold text-slate-900">No shipping rows yet.</p>
          <p class="mb-3 text-sm text-slate-500">Create rules for destinations, couriers, delivery windows, and charges.</p>
          <button type="button" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto" @click="addRowOpen = true">Add your first shipping row</button>
        </div>
      @else
        <div class="space-y-3 md:hidden">
          @foreach($shippingProfiles as $row)
            @php
              $countryName = '';
              if ($row->dest_country_id) {
                $country = $countries->firstWhere('id', $row->dest_country_id);
                $countryName = $country ? $country->name : 'Selected country';
              }
              $rowLocation = $row->dest_location_type==='everywhere_else'
                ? 'Everywhere'
                : ($row->dest_country_id ? 'Ship to '.$countryName : 'Unspecified');
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
              <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                  <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Destination</div>
                  <div class="mt-1 text-sm font-semibold text-slate-900">{{ $rowLocation }}</div>
                </div>
                <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $row->charge_type==='free' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-700' }}">
                  {{ ucfirst($row->charge_type) }}
                </span>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">Service</div>
                  <div class="mt-1 text-sm font-medium text-slate-900">{{ $row->service }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">Days</div>
                  <div class="mt-1 text-sm font-medium text-slate-900">{{ $row->days_min }} - {{ $row->days_max }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">One item</div>
                  <div class="mt-1 text-sm font-medium text-slate-900">{{ $currency }} {{ number_format((float)$row->base_rate, 2) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                  <div class="text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">Additional</div>
                  <div class="mt-1 text-sm font-medium text-slate-900">{{ $currency }} {{ number_format((float)$row->additional_rate, 2) }}</div>
                </div>
              </div>

              <div class="shipping-row-card__actions mt-4">
                <button type="button" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto" @click="editRowOpen = {{ (int) $row->id }}" title="Edit">Edit</button>
                <form method="POST" action="{{ route('products.shipping.rows.destroy',[$product,$row]) }}" onsubmit="return confirm('Delete this shipping row?');">
                  @csrf
                  @method('DELETE')
                  <button class="inline-flex w-full items-center justify-center rounded-xl border border-rose-600 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50" title="Delete">Delete</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>

        <div class="hidden md:block overflow-x-auto">
          <table class="shipping-rows-table min-w-full divide-y divide-slate-200 border border-slate-200 text-sm align-middle">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-3 py-3 text-left">Location</th>
                <th class="px-3 py-3 text-left">Service</th>
                <th class="px-3 py-3 text-left">Days</th>
                <th class="px-3 py-3 text-left">Charge</th>
                <th class="px-3 py-3 text-left">One item</th>
                <th class="px-3 py-3 text-left">Additional</th>
                <th class="px-3 py-3 text-center" style="width:150px;">Actions</th>
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
                  <td class="px-3 py-3">
                    @if($row->dest_location_type==='everywhere_else')
                      Everywhere
                    @elseif($row->dest_country_id)
                      Ship to {{ $countryName }}
                    @else
                      &mdash;
                    @endif
                  </td>
                  <td class="px-3 py-3">{{ $row->service }}</td>
                  <td class="px-3 py-3">{{ $row->days_min }} &ndash; {{ $row->days_max }}</td>
                  <td class="px-3 py-3">{{ ucfirst($row->charge_type) }}</td>
                  <td class="px-3 py-3">{{ $currency }} {{ number_format((float)$row->base_rate, 2) }}</td>
                  <td class="px-3 py-3">{{ $currency }} {{ number_format((float)$row->additional_rate, 2) }}</td>
                  <td class="px-3 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" @click="editRowOpen = {{ (int) $row->id }}" title="Edit">Edit</button>
                      <form method="POST" action="{{ route('products.shipping.rows.destroy',[$product,$row]) }}" class="inline-block" onsubmit="return confirm('Delete this shipping row?');">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex items-center justify-center rounded-xl border border-rose-600 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50" title="Delete">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

{{-- ADD ROW MODAL --}}
<div x-cloak x-show="addRowOpen" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
  <div class="absolute inset-0 bg-slate-900/50" @click="addRowOpen = false"></div>
  <div class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl">
    <form method="POST" action="{{ route('products.shipping.rows.store',$product) }}" class="rounded-t-3xl border border-slate-200 bg-white shadow-xl sm:rounded-2xl" novalidate>
      @csrf
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h5 class="text-base font-semibold text-slate-900">Add shipping row</h5>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="addRowOpen = false" aria-label="Close">&times;</button>
      </div>

      <div class="px-4 py-4">
        {{-- Inherit profile-level values --}}
        <input type="hidden" name="profile_name"          value="{{ $currentProfile->profile_name }}">
        <input type="hidden" name="set_default"           value="{{ $currentProfile->is_default?1:0 }}">
        <input type="hidden" name="country_id"            value="{{ $currentProfile->country_id }}">
        <input type="hidden" name="origin_postal_code"    value="{{ $currentProfile->origin_postal_code }}">
        <input type="hidden" name="processing_time_id"    value="{{ $currentProfile->processing_time_id }}">
        <input type="hidden" name="processing_custom_min" value="{{ $currentProfile->processing_custom_min }}">
        <input type="hidden" name="processing_custom_max" value="{{ $currentProfile->processing_custom_max }}">

        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">Location type</label>
          <select name="row[location_type]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" id="add-location-type" required>
            <option value="country" selected>Country</option>
            <option value="everywhere_else">Everywhere</option>
          </select>
        </div>

        <div class="mb-3" id="add-country-wrap">
          <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
          <select name="row[country_id]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Select...</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
          <div class="text-xs text-slate-500">Shown only when "Country" is chosen.</div>
        </div>

        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">Service</label>
          @php $couriers = couriers_list(); @endphp
          <select name="row[service]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 service-select" id="add-service-select" data-ui-target="#add-service-other-wrap" required>
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
          <label class="mb-1 block text-sm font-medium text-slate-700">Courier name</label>
          <input type="text" name="row[service_other]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Enter courier (e.g., DHL, Rider, etc.)" maxlength="100">
          <div class="text-xs text-slate-500">Shown when Service is Other/Manual.</div>
        </div>

        <div class="mb-3 grid grid-cols-12 gap-3">
          <div class="col-span-12 sm:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Min days</label>
            <input type="number" name="row[days_min]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" value="1" required>
          </div>
          <div class="col-span-12 sm:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Max days</label>
            <input type="number" name="row[days_max]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" value="1" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">Charge type</label>
          <select name="row[charge_type]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 charge-type-select" data-one="#add-price-one" data-add="#add-price-additional">
            <option value="fixed" selected>Fixed</option>
            <option value="free">Free</option>
          </select>
        </div>

        <div class="mb-3 grid grid-cols-12 gap-3">
          <div class="col-span-12 sm:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">One item</label>
            <div class="flex w-full items-stretch">
              <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">{{ $currency }}</span>
              <input id="add-price-one" type="number" name="row[price_one]" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="0.00" required>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Additional</label>
            <div class="flex w-full items-stretch">
              <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">{{ $currency }}</span>
              <input id="add-price-additional" type="number" name="row[price_additional]" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="0.00" required>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col-reverse items-stretch gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:justify-end">
        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" @click="addRowOpen = false">Cancel</button>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
          <i class="fa-solid fa-plus mr-1"></i> Add row
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
  <div x-cloak x-show="editRowOpen === {{ (int) $row->id }}" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
    <div class="absolute inset-0 bg-slate-900/50" @click="editRowOpen = null"></div>
    <div class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl">
      <form method="POST" action="{{ route('products.shipping.rows.update',[$product,$row]) }}" class="rounded-t-3xl border border-slate-200 bg-white shadow-xl sm:rounded-2xl" novalidate>
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900">Edit shipping row</h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="editRowOpen = null" aria-label="Close">&times;</button>
        </div>

        <div class="px-4 py-4">
          {{-- Hidden profile-level fields (preserved) --}}
          <input type="hidden" name="profile_name"          value="{{ $row->profile_name }}">
          <input type="hidden" name="set_default"           value="{{ $row->is_default?1:0 }}">
          <input type="hidden" name="country_id"            value="{{ $row->country_id }}">
          <input type="hidden" name="origin_postal_code"    value="{{ $row->origin_postal_code }}">
          <input type="hidden" name="processing_time_id"    value="{{ $row->processing_time_id }}">
          <input type="hidden" name="processing_custom_min" value="{{ $row->processing_custom_min }}">
          <input type="hidden" name="processing_custom_max" value="{{ $row->processing_custom_max }}">

          <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">Location type</label>
            <select name="row[location_type]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 edit-location-type" data-ui-target="#country-wrap-{{ $row->id }}" required>
              <option value="country" @selected($row->dest_location_type==='country')>Country</option>
              <option value="everywhere_else" @selected($row->dest_location_type==='everywhere_else')>Everywhere</option>
            </select>
          </div>

          <div class="mb-3" id="country-wrap-{{ $row->id }}">
            <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
            <select name="row[country_id]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
              <option value="">Select...</option>
              @foreach($countries as $c)
                <option value="{{ $c->id }}" @selected($c->id==$row->dest_country_id)>{{ $c->name }}</option>
              @endforeach
            </select>
            <div class="text-xs text-slate-500">Shown only when "Country" is chosen.</div>
          </div>

          <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">Service</label>
            <select name="row[service]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 edit-service-select" data-ui-target="#service-other-wrap-{{ $row->id }}" required>
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
            <label class="mb-1 block text-sm font-medium text-slate-700">Courier name</label>
            <input type="text" name="row[service_other]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="{{ $customServiceVal }}" placeholder="Enter courier (e.g., DHL, Rider, etc.)" maxlength="100">
            <div class="text-xs text-slate-500">Shown when Service is Other/Manual.</div>
          </div>

          <div class="mb-3 grid grid-cols-12 gap-3">
            <div class="col-span-12 sm:col-span-6">
              <label class="mb-1 block text-sm font-medium text-slate-700">Min days</label>
              <input type="number" name="row[days_min]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" value="{{ (int)$row->days_min }}" required>
            </div>
            <div class="col-span-12 sm:col-span-6">
              <label class="mb-1 block text-sm font-medium text-slate-700">Max days</label>
              <input type="number" name="row[days_max]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" value="{{ (int)$row->days_max }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">Charge type</label>
            <select name="row[charge_type]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 charge-type-select" data-one="#price-one-{{ $row->id }}" data-add="#price-additional-{{ $row->id }}">
              <option value="fixed" @selected($row->charge_type==='fixed')>Fixed</option>
              <option value="free"  @selected($row->charge_type==='free')>Free</option>
            </select>
          </div>

          <div class="mb-3 grid grid-cols-12 gap-3">
            <div class="col-span-12 sm:col-span-6">
              <label class="mb-1 block text-sm font-medium text-slate-700">One item</label>
              <div class="flex w-full items-stretch">
                <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">{{ $currency }}</span>
                <input id="price-one-{{ $row->id }}" type="number" name="row[price_one]" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="{{ number_format((float)$row->base_rate, 2, '.', '') }}" required>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6">
              <label class="mb-1 block text-sm font-medium text-slate-700">Additional</label>
              <div class="flex w-full items-stretch">
                <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">{{ $currency }}</span>
                <input id="price-additional-{{ $row->id }}" type="number" name="row[price_additional]" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="{{ number_format((float)$row->additional_rate, 2, '.', '') }}" required>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col-reverse items-stretch gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:justify-end">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" @click="editRowOpen = null">Cancel</button>
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
            <i class="fa-regular fa-floppy-disk mr-1"></i> Save changes
          </button>
        </div>
      </form>
    </div>
  </div>
@endforeach
      </div>
      </div>
    </div>
  </div>
</section>
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
    const target = sel.getAttribute('data-ui-target'); // e.g. #country-wrap-123
    toggleCountry(sel, target);
    sel.addEventListener('change', e => toggleCountry(e.target, target));
  });
})();

/** Toggle "Other/Manual" courier free text input */
function bindServiceToggle(select){
  const targetSel = select.getAttribute('data-ui-target');
  const wrap = targetSel ? $(targetSel) : null;
  const toggle = () => {
    const v = (select.value || '').toLowerCase();
    if (wrap) wrap.style.display = (v === 'other' || v === 'manual') ? 'block' : 'none';
  };
  select.addEventListener('change', toggle);
  toggle(); // init
}
$$('.service-select, .edit-service-select').forEach(bindServiceToggle);

/** Disable price inputs when "Free" is selected */
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
      inp.closest('.flex')?.classList.toggle('opacity-50', isFree);
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
  if (valid) ind.textContent = `Custom set: ${min}-${max} days `;
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
        if (warnWrap) warnWrap.classList.remove('hidden');
        warnWrap?.scrollIntoView({behavior:'smooth', block:'center'});
      }
    }
  });
})();
</script>
@endpush
