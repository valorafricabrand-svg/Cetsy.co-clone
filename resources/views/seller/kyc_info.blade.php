@extends('theme.'.theme().'.layouts.app')

@section('title', 'KYC - Step 1 of 2')

@section('main')
@php
    $shop = auth()->user()->shop;
    $brandColor = optional($shop)->primary_color;
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        $brandColor = '#0f766e';
    }

    $inputClass = 'mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100';
@endphp

<section class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            @include('seller.partials.sidebar')

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">KYC Verification</h1>
                            <p class="mt-1 text-sm text-slate-500">Step 1 of 2: personal details.</p>
                        </div>
                        <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Step 1 of 2</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @if($errors->any())
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                <p class="font-semibold">There were problems with your details:</p>
                                <ul class="mt-1 list-disc pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('seller.kyc.info.submit') }}" method="POST" class="space-y-4">
                            @csrf

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700" for="first_name">First Name</label>
                                    <input class="{{ $inputClass }}" id="first_name" name="first_name" value="{{ old('first_name', $step1['first_name'] ?? ($kyc->first_name ?? '')) }}" required>
                                    @error('first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700" for="last_name">Last Name</label>
                                    <input class="{{ $inputClass }}" id="last_name" name="last_name" value="{{ old('last_name', $step1['last_name'] ?? ($kyc->last_name ?? '')) }}" required>
                                    @error('last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700" for="email">Email</label>
                                <input class="{{ $inputClass }} bg-slate-50" id="email" name="email" type="email" value="{{ old('email', $step1['email'] ?? ($kyc->email ?? auth()->user()->email)) }}" required readonly>
                                <p class="mt-1 text-xs text-slate-500">Uses your account email. Contact support to change.</p>
                                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700" for="phone">Phone</label>
                                <input class="{{ $inputClass }}" id="phone" name="phone" value="{{ old('phone', $step1['phone'] ?? ($kyc->phone ?? '')) }}" placeholder="e.g., +2547XXXXXXXX" required>
                                @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700" for="id_type">ID Type</label>
                                    @php $selectedIdType = old('id_type', $step1['id_type'] ?? ($kyc->id_type ?? '')); @endphp
                                    <select id="id_type" name="id_type" class="{{ $inputClass }}" required>
                                        <option value="" disabled {{ $selectedIdType ? '' : 'selected' }}>Select ID Type</option>
                                        <option value="national_id" {{ $selectedIdType === 'national_id' ? 'selected' : '' }}>National ID</option>
                                        <option value="passport" {{ $selectedIdType === 'passport' ? 'selected' : '' }}>Passport</option>
                                        <option value="driver_license" {{ $selectedIdType === 'driver_license' ? 'selected' : '' }}>Driver's License</option>
                                    </select>
                                    @error('id_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700" for="id_number">ID Number</label>
                                    <input class="{{ $inputClass }}" id="id_number" name="id_number" value="{{ old('id_number', $step1['id_number'] ?? ($kyc->id_number ?? '')) }}" placeholder="e.g., 12345678" required>
                                    @error('id_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: {{ $brandColor }}" type="submit">
                                    Continue to Documents
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
