@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800']) }}>
        <div class="inline-flex items-start gap-2">
            <i class="fa-solid fa-circle-check mt-0.5 text-emerald-600" aria-hidden="true"></i>
            <span>{{ $status }}</span>
        </div>
    </div>
@endif
