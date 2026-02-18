@extends('theme.'.theme().'.layouts.app')

@section('title', 'KYC - Step 2 of 2')

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
                            <p class="mt-1 text-sm text-slate-500">Step 2 of 2: upload your documents.</p>
                        </div>
                        <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Step 2 of 2</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @if(session('error'))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                <p class="font-semibold">There were problems with your submission:</p>
                                <ul class="mt-1 list-disc pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                            <span class="font-semibold">Heads up:</span> Accepted formats: PDF/JPG/PNG for ID and JPG/PNG for selfie. Max 2MB each.
                        </div>

                        <form action="{{ route('seller.kyc.documents.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <input type="hidden" name="first_name" value="{{ $step1['first_name'] ?? ($kyc->first_name ?? '') }}">
                            <input type="hidden" name="last_name" value="{{ $step1['last_name'] ?? ($kyc->last_name ?? '') }}">
                            <input type="hidden" name="email" value="{{ $step1['email'] ?? ($kyc->email ?? (auth()->user()->email ?? '')) }}">
                            <input type="hidden" name="phone" value="{{ $step1['phone'] ?? ($kyc->phone ?? '') }}">
                            <input type="hidden" name="id_type" value="{{ $step1['id_type'] ?? ($kyc->id_type ?? '') }}">
                            <input type="hidden" name="id_number" value="{{ $step1['id_number'] ?? ($kyc->id_number ?? '') }}">

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label for="id_front" class="text-sm font-semibold text-slate-700">ID Front (PDF/JPG/PNG)</label>
                                    <input class="{{ $inputClass }}" type="file" id="id_front" name="id_front" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <p class="mt-1 text-xs text-slate-500">Clear image of the front side.</p>
                                    <div class="mt-2">
                                        <img id="preview-id_front" alt="ID front preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover">
                                    </div>
                                    @error('id_front')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="id_back" class="text-sm font-semibold text-slate-700">ID Back (PDF/JPG/PNG)</label>
                                    <input class="{{ $inputClass }}" type="file" id="id_back" name="id_back" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <p class="mt-1 text-xs text-slate-500">Clear image of the back side.</p>
                                    <div class="mt-2">
                                        <img id="preview-id_back" alt="ID back preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover">
                                    </div>
                                    @error('id_back')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="selfie" class="text-sm font-semibold text-slate-700">Selfie</label>
                                    <input class="{{ $inputClass }}" type="file" id="selfie" name="selfie" accept=".jpg,.jpeg,.png" required>
                                    <p class="mt-1 text-xs text-slate-500">Hold your ID next to your face.</p>
                                    <div class="mt-2">
                                        <img id="preview-selfie" alt="Selfie preview" class="hidden max-h-28 rounded-lg border border-slate-200 object-cover">
                                    </div>
                                    @error('selfie')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-2">
                                <a class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('seller.kyc.info') }}">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </a>
                                <button class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: {{ $brandColor }}" type="submit">
                                    Submit KYC
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
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
