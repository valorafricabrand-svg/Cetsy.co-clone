{{-- resources/views/shipping_profiles/create.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="content">
    <h2>Create Shipping Profile</h2>

    <form action="{{ route('shipping_profiles.store') }}" method="POST" class="mt-4">
        @csrf

        {{-- Profile Name --}}
        <div class="mb-3">
            <label for="profile_name" class="form-label">Name <span class="text-danger">*</span></label>
            <input
                type="text"
                id="profile_name"
                name="name"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Shipping to Country --}}
        <div class="mb-3">
            <label for="country_id" class="form-label">Shipping to Country <span class="text-danger">*</span></label>
            <select
                id="country_id"
                name="country_id"
                class="form-select @error('country_id') is-invalid @enderror"
                required
            >
                <option value="">Select country</option>
                @foreach($countries as $country)
                    <option
                        value="{{ $country->id }}"
                        {{ old('country_id') == $country->id ? 'selected' : '' }}
                    >
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
            @error('country_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Base Rate & Delivery Days --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="base_rate" class="form-label">Base Rate ({{ get_currency() }}) <span class="text-danger">*</span></label>
                <input
                    type="number"
                    id="base_rate"
                    name="base_rate"
                    value="{{ old('base_rate') }}"
                    min="0"
                    step="0.01"
                    class="form-control @error('base_rate') is-invalid @enderror"
                    required
                >
                @error('base_rate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="delivery_days" class="form-label">Delivery Days <span class="text-danger">*</span></label>
                <input
                    type="number"
                    id="delivery_days"
                    name="delivery_days"
                    value="{{ old('delivery_days') }}"
                    min="0"
                    class="form-control @error('delivery_days') is-invalid @enderror"
                    required
                >
                @error('delivery_days')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Pickup Available --}}
        {{-- Hidden fallback so unchecked submits 0 --}}
        <input type="hidden" name="pickup_available" value="0">
        <div class="form-check form-switch mb-4">
            <input
                class="form-check-input @error('pickup_available') is-invalid @enderror"
                type="checkbox"
                id="pickup_available"
                name="pickup_available"
                value="1"
                {{ old('pickup_available') ? 'checked' : '' }}
            >
            <label class="form-check-label" for="pickup_available">
                Pickup Available
            </label>
            @error('pickup_available')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Form Actions --}}
        <button type="submit" class="btn btn-success">Create Profile</button>
        <a href="{{ route('shipping_profiles.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
