@extends('theme.'.theme().'.layouts.app')
@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <h3 class="mb-4">Addresses</h3>
    @if(count($addresses) > 0)
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200">
            @foreach($addresses as $address)
                <li class="px-4 py-3">
                    <h5>{{ $address->label }}</h5>
                    <p>{{ $address->address }}</p>
                    <p>{{ $address->city }}, {{ $address->state }}, {{ $address->country }}</p>
                    <p>ZIP: {{ $address->zip }}</p>
                    <a href="{{ route('address.edit', $address->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400 px-3 py-1.5 text-xs">Edit</a>
                    <form action="{{ route('address.delete', $address->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-rose-600 text-white hover:bg-rose-500 px-3 py-1.5 text-xs">Delete</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @else
        <p>No addresses found.</p>
    @endif
@endsection



