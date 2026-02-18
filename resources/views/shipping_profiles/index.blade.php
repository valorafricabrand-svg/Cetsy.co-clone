{{-- resources/views/shipping_profiles/index.blade.php --}}

@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
    <div class="flex justify-between items-center mb-3">
        <h2>Shipping Profiles</h2>
        <a href="{{ route('seller.shipping_profiles.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Add Shipping Profile</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">{{ session('error') }}</div>
    @endif

    @if($profiles->count())
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm border border-slate-200 mb-0">
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
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success">Yes</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('seller.shipping_profiles.edit', $profile) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400">Edit</a>
                            <form action="{{ route('seller.shipping_profiles.destroy', $profile) }}"
                                  method="POST"
                                  class="inline-block"
                                  onsubmit="return confirm('Delete this shipping profile?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-rose-600 text-white hover:bg-rose-500">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{ $profiles->onEachSide(1)->links('pagination::tailwind') }}
    @else
        <p>No shipping profiles found. <a href="{{ route('seller.shipping_profiles.create') }}">Create one now.</a></p>
    @endif
</div>
@endsection


