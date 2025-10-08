{{-- resources/views/shipping_profiles/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Shipping Profiles</h2>
        <a href="{{ route('seller.shipping_profiles.create') }}" class="btn btn-primary">Add Shipping Profile</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($profiles->count())
        <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Shipping to Country</th>
                    <th>Base Rate ({{ get_currency() }})</th>
                    <th>Delivery Days</th>
                    <th>Processing Time</th>
                    <th>Pickup Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profiles as $profile)
                    <tr>
                        <td>{{ $profile->name }}</td>
                        <td>{{ $profile->destination_label }}</td>
                        <td>{{ number_format($profile->base_rate, 2) }}</td>
                        <td>{{ $profile->delivery_days }}</td>
                        <td>
                            @if($profile->processingTime)
                                {{ $profile->processingTime->label }}
                                ({{ $profile->processingTime->days }} day{{ $profile->processingTime->days > 1 ? 's' : '' }})
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td>
                            @if($profile->pickup_available)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('seller.shipping_profiles.edit', $profile) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('seller.shipping_profiles.destroy', $profile) }}"
                                  method="POST"
                                  class="d-inline-block"
                                  onsubmit="return confirm('Delete this shipping profile?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{ $profiles->links() }}
    @else
        <p>No shipping profiles found. <a href="{{ route('seller.shipping_profiles.create') }}">Create one now.</a></p>
    @endif
</div>
@endsection

