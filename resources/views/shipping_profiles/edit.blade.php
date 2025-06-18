@extends('layouts.app')

@section('content')
<div class="content">
    <h2>Edit Shipping Profile</h2>

    <form method="POST" action="{{ route('shipping_profiles.update', $shippingProfile) }}">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="name" class="form-label">Profile Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $shippingProfile->name) }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="country" class="form-label">Country (ISO Code) <span class="text-danger">*</span></label>
            <input type="text" id="country" name="country" maxlength="3" class="form-control @error('country') is-invalid @enderror"
                value="{{ old('country', $shippingProfile->country) }}" required>
            @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="form-text text-muted">Use 2 or 3 letter ISO country code, e.g. KE, UG, TZ</small>
        </div>

        <div class="mb-3">
            <label for="base_rate" class="form-label">Base Rate (KES) <span class="text-danger">*</span></label>
            <input type="number" id="base_rate" name="base_rate" min="0" step="0.01"
                class="form-control @error('base_rate') is-invalid @enderror" value="{{ old('base_rate', $shippingProfile->base_rate) }}" required>
            @error('base_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="delivery_days" class="form-label">Estimated Delivery Days <span class="text-danger">*</span></label>
            <input type="number" id="delivery_days" name="delivery_days" min="0"
                class="form-control @error('delivery_days') is-invalid @enderror" value="{{ old('delivery_days', $shippingProfile->delivery_days) }}" required>
            @error('delivery_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" id="pickup_available" name="pickup_available" class="form-check-input" {{ old('pickup_available', $shippingProfile->pickup_available) ? 'checked' : '' }}>
            <label for="pickup_available" class="form-check-label">Pickup Available</label>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="{{ route('shipping_profiles.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
