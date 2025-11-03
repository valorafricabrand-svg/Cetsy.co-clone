{{-- resources/views/theme/{{ theme() }}/partials/_tab_shipping.blade.php --}}

<div class="tab-pane fade" id="shipping-pane" role="tabpanel">
  @if(!empty($etaLabel))
    <div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
      <i class="fa-solid fa-truck-fast text-success"></i>
      <div>
        <div class="fw-semibold text-dark">Estimated delivery</div>
        <div class="small text-muted">{{ $etaLabel }} @if(!empty($procLabel) || !empty($transitLabel)) (Processing {{ $procLabel ?? '-' }}, Transit {{ $transitLabel ?? '-' }}) @endif</div>
      </div>
    </div>
  @endif

  {{-- Policies removed per request --}}
</div>
