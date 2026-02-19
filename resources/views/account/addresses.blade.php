@extends('theme.'.theme().'.layouts.app')
@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 lg:col-span-9">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <div>
                        <h3 class="text-2xl font-semibold text-slate-900">Addresses</h3>
                        <p class="mt-1 text-slate-500">Manage your saved delivery addresses.</p>
                    </div>
                    <a href="{{ route('account.addresses.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                        Add Address
                    </a>
                </div>

                @if(count($addresses) > 0)
                    <ul class="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @foreach($addresses as $address)
                            <li class="px-4 py-4 sm:px-5">
                                <h5 class="text-base font-semibold text-slate-900">
                                    {{ $address->label ?: ucfirst($address->type ?: 'Address') }}
                                    @if($address->is_default)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Default</span>
                                    @endif
                                </h5>
                                <p class="mt-1 text-sm text-slate-700">{{ $address->address ?: trim(($address->address_1 ?? '').' '.($address->address_2 ?? '')) ?: 'Address not set' }}</p>
                                <p class="text-sm text-slate-600">{{ $address->city ?: 'City' }}, {{ $address->state ?: 'State' }}, {{ $address->country ?: 'Country' }}</p>
                                <p class="text-sm text-slate-600">ZIP: {{ $address->zip }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('account.addresses.edit', $address->id) }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-slate-900 transition hover:bg-amber-400">Edit</a>
                                    <form action="{{ route('account.addresses.destroy', $address->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this address?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">Delete</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                        No addresses found. Add one to speed up checkout.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
