@extends('theme.'.theme().'.layouts.app')
@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper p-4">
    <h3 class="mb-4 text-2xl font-semibold text-slate-900">Addresses</h3>
    @if(count($addresses) > 0)
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200">
            @foreach($addresses as $address)
                <li class="px-4 py-3">
                    <h5 class="text-base font-semibold text-slate-900">{{ $address->label }}</h5>
                    <p class="mt-1 text-sm text-slate-700">{{ $address->address }}</p>
                    <p class="text-sm text-slate-600">{{ $address->city }}, {{ $address->state }}, {{ $address->country }}</p>
                    <p class="text-sm text-slate-600">ZIP: {{ $address->zip }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('address.edit', $address->id) }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-slate-900 transition hover:bg-amber-400">Edit</a>
                    <form action="{{ route('address.delete', $address->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">Delete</button>
                    </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">No addresses found.</div>
    @endif
 </div>
@endsection



