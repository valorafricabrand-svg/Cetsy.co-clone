@extends('theme.'.theme().'.layouts.app')

@section('title', 'KYC Verification')

@section('main')
@php
    $shop = auth()->user()->shop;
    $brandColor = optional($shop)->primary_color;
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        $brandColor = '#0f766e';
    }

    $statusTone = match ((string) ($kyc->status ?? '')) {
        'pending' => 'border-amber-200 bg-amber-50 text-amber-800',
        'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'needs_correction' => 'border-orange-200 bg-orange-50 text-orange-800',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };

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
                            <p class="mt-1 text-sm text-slate-500">Verify your identity to unlock all seller features.</p>
                        </div>
                        @if($kyc && $kyc->status !== 'rejected')
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusTone }}">
                                {{ ucfirst($kyc->status) }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-5 space-y-4">
                        @if(session('success'))
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($kyc && $kyc->status !== 'rejected')
                            @if($kyc->status === 'pending')
                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    Your KYC is under review.
                                </div>
                            @elseif($kyc->status === 'approved')
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                    Your KYC is approved. You can now access all seller features.
                                </div>
                            @elseif($kyc->status === 'needs_correction')
                                <div class="rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                                    Action required: your KYC needs corrections. Review notes and resubmit.
                                </div>
                                <div>
                                    <a href="{{ route('seller.kyc.info') }}"
                                       class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-white"
                                       style="background-color: {{ $brandColor }}">
                                        <i class="fas fa-edit mr-2"></i>
                                        Fix and Resubmit
                                    </a>
                                </div>
                            @endif

                            @if($kyc->admin_notes)
                                <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                    <span class="font-semibold">Admin notes:</span> {{ $kyc->admin_notes }}
                                </div>
                            @endif
                        @endif

                        @if(!$kyc || $kyc->status === 'rejected')
                            <form action="{{ route('seller.kyc.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="first_name" class="text-sm font-semibold text-slate-700">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="{{ $inputClass }}" placeholder="e.g., Alan" value="{{ old('first_name', $kyc->first_name ?? '') }}" required>
                                        @error('first_name')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="last_name" class="text-sm font-semibold text-slate-700">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="{{ $inputClass }}" placeholder="e.g., Smith" value="{{ old('last_name', $kyc->last_name ?? '') }}" required>
                                        @error('last_name')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                                    <input type="email" id="email" name="email" class="{{ $inputClass }} bg-slate-50" value="{{ old('email', $kyc->email ?? auth()->user()->email) }}" required readonly>
                                    <p class="mt-1 text-xs text-slate-500">Uses your account email. Contact support to change.</p>
                                    @error('email')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="text-sm font-semibold text-slate-700">Phone</label>
                                    <input type="text" id="phone" name="phone" class="{{ $inputClass }}" placeholder="e.g., +2547XXXXXXXX" value="{{ old('phone', $kyc->phone ?? '') }}" required>
                                    @error('phone')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="id_type" class="text-sm font-semibold text-slate-700">ID Type</label>
                                        @php $selectedIdType = old('id_type', $kyc->id_type ?? ''); @endphp
                                        <select id="id_type" name="id_type" class="{{ $inputClass }}" required>
                                            <option value="" disabled {{ $selectedIdType ? '' : 'selected' }}>Select ID Type</option>
                                            <option value="national_id" {{ $selectedIdType === 'national_id' ? 'selected' : '' }}>National ID</option>
                                            <option value="passport" {{ $selectedIdType === 'passport' ? 'selected' : '' }}>Passport</option>
                                            <option value="driver_license" {{ $selectedIdType === 'driver_license' ? 'selected' : '' }}>Driver's License</option>
                                        </select>
                                        @error('id_type')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="id_number" class="text-sm font-semibold text-slate-700">ID Number</label>
                                        <input type="text" id="id_number" name="id_number" class="{{ $inputClass }}" placeholder="e.g., 12345678" value="{{ old('id_number', $kyc->id_number ?? '') }}" required>
                                        @error('id_number')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label for="id_front" class="text-sm font-semibold text-slate-700">ID Front (PDF/JPG/PNG)</label>
                                        <input class="{{ $inputClass }}" type="file" id="id_front" name="id_front" accept=".pdf,.jpg,.jpeg,.png" {{ $kyc?->id_front ? '' : 'required' }}>
                                        <p class="mt-1 text-xs text-slate-500">Max size 2MB. Clear front side.</p>
                                        <div class="mt-2">
                                            @if(!empty($kyc?->id_front))
                                                <img id="preview-id_front" src="{{ Storage::url($kyc->id_front) }}" alt="ID front preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover" style="display:inline-block;">
                                            @else
                                                <img id="preview-id_front" alt="ID front preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover">
                                            @endif
                                        </div>
                                        @error('id_front')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="id_back" class="text-sm font-semibold text-slate-700">ID Back (PDF/JPG/PNG)</label>
                                        <input class="{{ $inputClass }}" type="file" id="id_back" name="id_back" accept=".pdf,.jpg,.jpeg,.png" {{ $kyc?->id_back ? '' : 'required' }}>
                                        <p class="mt-1 text-xs text-slate-500">Max size 2MB. Clear back side.</p>
                                        <div class="mt-2">
                                            @if(!empty($kyc?->id_back))
                                                <img id="preview-id_back" src="{{ Storage::url($kyc->id_back) }}" alt="ID back preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover" style="display:inline-block;">
                                            @else
                                                <img id="preview-id_back" alt="ID back preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover">
                                            @endif
                                        </div>
                                        @error('id_back')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="selfie" class="text-sm font-semibold text-slate-700">Selfie</label>
                                        <input class="{{ $inputClass }}" type="file" id="selfie" name="selfie" accept=".jpg,.jpeg,.png" {{ $kyc?->selfie ? '' : 'required' }}>
                                        <p class="mt-1 text-xs text-slate-500">Max size 2MB. Hold ID near your face.</p>
                                        <div class="mt-2">
                                            @if(!empty($kyc?->selfie))
                                                <img id="preview-selfie" src="{{ Storage::url($kyc->selfie) }}" alt="Selfie preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover" style="display:inline-block;">
                                            @else
                                                <img id="preview-selfie" alt="Selfie preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover">
                                            @endif
                                        </div>
                                        @error('selfie')<p class="js-field-error mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: {{ $brandColor }}" type="submit">
                                    Submit KYC
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const firstError = document.querySelector('.js-field-error');
    if (firstError) {
      firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function bindPreview(inputId, imgId) {
      const input = document.getElementById(inputId);
      const img = document.getElementById(imgId);
      if (!input || !img) return;

      input.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        if (file.type.startsWith('image/')) {
          img.src = URL.createObjectURL(file);
          img.classList.remove('hidden');
          img.style.display = 'inline-block';
        } else {
          img.classList.add('hidden');
          img.style.display = 'none';
          img.removeAttribute('src');
        }
      });
    }

    bindPreview('id_front', 'preview-id_front');
    bindPreview('id_back', 'preview-id_back');
    bindPreview('selfie', 'preview-selfie');
  });
</script>
@endpush

@endsection
