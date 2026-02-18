{{-- resources/views/components/analytics/card.blade.php --}}
@props([
  'title',
  'value',
  'icon',
  // optional props
  'delta' => null,        // numeric delta percent (positive/negative)
  'sparkId' => null,      // optional canvas id for sparkline
])
<article class="glass h-full rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
    <div class="mx-auto mb-3 inline-flex items-center justify-center rounded-full analytics-icon">
        <i class="{{ $icon }} text-xl"></i>
    </div>
    <h3 class="text-sm font-medium text-slate-500">{{ $title }}</h3>
    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $value }}</p>
    @if(!is_null($delta))
        @php $up = $delta >= 0; $cls = $up ? 'text-emerald-600' : 'text-rose-600'; @endphp
        <div class="mt-1 text-xs font-semibold {{ $cls }}">
            <i class="fas {{ $up ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ number_format($delta, 1) }}%
        </div>
    @endif
    @if($sparkId)
        <div class="mt-3 h-9 w-full">
            <canvas id="{{ $sparkId }}" height="36"></canvas>
        </div>
    @endif
</article>
