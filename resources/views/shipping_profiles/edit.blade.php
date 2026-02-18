{{-- resources/views/shipping_profiles/edit.blade.php --}}

@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
    <h2>Edit Shipping Profile</h2>

    <form action="{{ route('seller.shipping_profiles.update', $shippingProfile) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Profile Name --}}
        <div class="mb-3">
            <label for="profile_name" class="mb-1 block text-sm font-medium text-slate-700">Name <span class="text-rose-600">*</span></label>
            <input
              type="text"
              id="profile_name"
              name="name"
              value="{{ old('name', $shippingProfile->name) }}"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
              required
            >
            @error('name')
                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- Shipping to Country (nullable; blank = Worldwide) --}}
        <div class="mb-3">
            <label for="country_id" class="mb-1 block text-sm font-medium text-slate-700">Shipping to Country <small class="text-slate-500">(leave blank for Worldwide)</small></label>
            <select
              id="country_id"
              name="country_id"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('country_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
            >
                <option value="">Worldwide</option>
                @foreach($countries as $country)
                    <option
                      value="{{ $country->id }}"
                      {{ old('country_id', $shippingProfile->country_id) == $country->id ? 'selected' : '' }}
                    >
                      {{ $country->name }} ({{ $country->iso_code }})
                    </option>
                @endforeach
            </select>
            @error('country_id')
                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- Base Rate & Delivery Days --}}
        <div class="grid grid-cols-12 gap-4 gap-3">
            <div class="md:col-span-6">
                <label for="base_rate" class="mb-1 block text-sm font-medium text-slate-700">Base Rate ({{ get_currency() }}) <span class="text-rose-600">*</span></label>
                <input
                  type="number"
                  id="base_rate"
                  name="base_rate"
                  value="{{ old('base_rate', $shippingProfile->base_rate) }}"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('base_rate') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                  required
                >
                @error('base_rate')
                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                @enderror
            </div>
            <div class="md:col-span-6">
                <label for="delivery_days" class="mb-1 block text-sm font-medium text-slate-700">Delivery Days <span class="text-rose-600">*</span></label>
                <input
                  type="number"
                  id="delivery_days"
                  name="delivery_days"
                  value="{{ old('delivery_days', $shippingProfile->delivery_days) }}"
                  min="0"
                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('delivery_days') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                  required
                >
                @error('delivery_days')
                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Processing Time --}}
        <div class="mb-3 mt-4">
            <label for="processing_time_id" class="mb-1 block text-sm font-medium text-slate-700">Processing Time <span class="text-rose-600">*</span></label>
            <select
              id="processing_time_id"
              name="processing_time_id"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_time_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
              required
            >
                <option value="">Select processing time</option>
                @foreach($processingTimes as $pt)
                    <option
                      value="{{ $pt->id }}"
                      {{ old('processing_time_id', $shippingProfile->processing_time_id) == $pt->id ? 'selected' : '' }}
                    >
                      {{ $pt->name }} ({{ $pt->days }} day{{ $pt->days > 1 ? 's' : '' }})
                    </option>
                @endforeach
            </select>
            @error('processing_time_id')
                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- Pickup Available --}}
        <input type="hidden" name="pickup_available" value="0">
        <div class="form-check form-switch mt-3">
            <input
              class="form-check-input @error('pickup_available') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
              type="checkbox"
              id="pickup_available"
              name="pickup_available"
              value="1"
              {{ old('pickup_available', $shippingProfile->pickup_available) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="pickup_available">
                Pickup Available
            </label>
            @error('pickup_available')
                <div class="mt-1 text-xs text-rose-600 block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Form Actions --}}
        <div class="mt-4">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Update Profile</button>
            <a href="{{ route('seller.shipping_profiles.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500 ml-2">Cancel</a>
        </div>
    </form>
</div>
@endsection


