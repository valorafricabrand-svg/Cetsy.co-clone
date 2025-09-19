{{-- resources/views/components/analytics/card.blade.php --}}
@props([
  'title',
  'value',
  'icon',
  // optional props
  'delta' => null,        // numeric delta percent (positive/negative)
  'sparkId' => null,      // optional canvas id for sparkline
])
<div class="col-12 col-sm-6 col-xl-4">
    <div class="card h-100 shadow-sm border-0 rounded-3 glass text-center">
        <div class="card-body py-4 d-flex flex-column align-items-center w-100">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle analytics-icon mb-3 flex-shrink-0">
                <i class="{{ $icon }} fs-4"></i>
            </div>
            <h6 class="text-muted mb-1">{{ $title }}</h6>
            <p class="fs-3 fw-semibold mb-1">{{ $value }}</p>
            @if(!is_null($delta))
                @php $up = $delta >= 0; $cls = $up ? 'text-success' : 'text-danger'; @endphp
                <div class="small {{ $cls }} mb-2">
                    <i class="fas {{ $up ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>{{ number_format($delta, 1) }}%
                </div>
            @endif
            @if($sparkId)
                <div class="w-100" style="height:36px">
                    <canvas id="{{ $sparkId }}" height="36"></canvas>
                </div>
            @endif
        </div>
    </div>
    </div>
