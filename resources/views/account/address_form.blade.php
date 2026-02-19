@extends('theme.'.theme().'.layouts.app')

@section('main')
@php
    $isEdit = ($formMode ?? 'create') === 'edit';
    $formAction = $isEdit
        ? route('account.addresses.update', $address->id)
        : route('account.addresses.store');
@endphp

<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 lg:col-span-9">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <div>
                        <h3 class="text-2xl font-semibold text-slate-900">{{ $isEdit ? 'Edit Address' : 'Add Address' }}</h3>
                        <p class="mt-1 text-slate-500">Save your shipping or billing address details.</p>
                    </div>
                    <a href="{{ route('account.addresses') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                        Back to Addresses
                    </a>
                </div>

                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        <div class="font-semibold">Please fix the highlighted fields.</div>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <form action="{{ $formAction }}" method="POST" class="grid grid-cols-12 gap-4">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <div class="col-span-12 md:col-span-4">
                            <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Address Type</label>
                            <select id="type" name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="shipping" @selected(old('type', $address->type ?? 'shipping') === 'shipping')>Shipping</option>
                                <option value="billing" @selected(old('type', $address->type ?? 'shipping') === 'billing')>Billing</option>
                            </select>
                        </div>

                        <div class="col-span-12 md:col-span-8">
                            <label for="label" class="mb-1 block text-sm font-medium text-slate-700">Label</label>
                            <input id="label" name="label" type="text" value="{{ old('label', $address->label) }}" placeholder="e.g. Home, Office" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label for="full_name" class="mb-1 block text-sm font-medium text-slate-700">Full Name</label>
                            <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $address->full_name) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $address->phone) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $address->email) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label for="country_id" class="mb-1 block text-sm font-medium text-slate-700">Country</label>
                            <select id="country_id" name="country_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Select country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" @selected((string) old('country_id', $address->country_id) === (string) $country->id)>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-12">
                            <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Country Name (optional override)</label>
                            <input id="country" name="country" type="text" value="{{ old('country', $address->country) }}" placeholder="Used when country list is unavailable" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12">
                            <label for="address_1" class="mb-1 block text-sm font-medium text-slate-700">Address Line 1</label>
                            <input id="address_1" name="address_1" type="text" value="{{ old('address_1', $address->address_1 ?: $address->address) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12">
                            <label for="address_2" class="mb-1 block text-sm font-medium text-slate-700">Address Line 2</label>
                            <input id="address_2" name="address_2" type="text" value="{{ old('address_2', $address->address_2) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-4">
                            <label for="city" class="mb-1 block text-sm font-medium text-slate-700">City</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $address->city) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-4">
                            <label for="state" class="mb-1 block text-sm font-medium text-slate-700">State/Province</label>
                            <input id="state" name="state" type="text" value="{{ old('state', $address->state) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12 md:col-span-4">
                            <label for="zip" class="mb-1 block text-sm font-medium text-slate-700">ZIP/Postal Code</label>
                            <input id="zip" name="zip" type="text" value="{{ old('zip', $address->zip) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="col-span-12">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', (bool) $address->is_default)) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                Set as default for this address type
                            </label>
                        </div>

                        <div class="col-span-12 flex flex-wrap items-center gap-2 pt-1">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                {{ $isEdit ? 'Update Address' : 'Save Address' }}
                            </button>
                            <a href="{{ route('account.addresses') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
