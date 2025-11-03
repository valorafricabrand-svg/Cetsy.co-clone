{{-- resources/views/theme/{{ theme() }}/partials/_tab_shipping.blade.php --}}

<div class="tab-pane fade" id="shipping-pane" role="tabpanel">
  @if(!empty($etaLabel))
    <div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
      <i class="fa-solid fa-truck-fast text-success"></i>
      <div>
        <div class="fw-semibold">Estimated delivery</div>
        <div class="small text-muted">{{ $etaLabel }} @if(!empty($procLabel) || !empty($transitLabel)) (Processing {{ $procLabel ?? '-' }}, Transit {{ $transitLabel ?? '-' }}) @endif</div>
      </div>
    </div>
  @endif

  <h5 class="fw-semibold mb-3">Shipping policies</h5>
  <p class="small text-muted">
    {{ $shopPolicies->shipping ?? 'Shipping details coming soon.' }}
  </p>

  <h5 class="fw-semibold mt-4 mb-3">Returns & exchanges</h5>
  <p class="small text-muted">
    {{ $shopPolicies->returns ?? 'Returns policy coming soon.' }}
  </p>
</div>
