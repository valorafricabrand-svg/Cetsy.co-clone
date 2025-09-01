{{-- resources/views/components/analytics/card.blade.php --}}
@props(['title','value','icon'])
<div class="col-12 col-sm-6 col-xl-4">
    <div class="card h-100 shadow-sm border-0 rounded-3 glass text-center">
        <div class="card-body py-4 d-flex flex-column align-items-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle analytics-icon mb-3 flex-shrink-0">
                <i class="{{ $icon }} fs-4"></i>
            </div>
            <h6 class="text-muted mb-1">{{ $title }}</h6>
            <p class="fs-3 fw-semibold mb-0">{{ $value }}</p>
        </div>
    </div>
</div>
