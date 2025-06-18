{{-- resources/views/components/analytics/card.blade.php --}}
@props(['title','value','icon'])
<div class="col-md-4">
    <div class="card h-100 shadow-sm border-0 text-center">
        <div class="card-body py-4">
            <i class="{{ $icon }} fs-1 text-primary mb-3"></i>
            <h6 class="text-muted">{{ $title }}</h6>
            <p class="display-6 fs-3 fw-semibold mb-0">{{ $value }}</p>
        </div>
    </div>
</div>
