{{-- resources/views/theme/{{ theme() }}/partials/_tab_shipping.blade.php --}}

<div class="tab-pane fade" id="shipping-pane" role="tabpanel">
  <h5 class="fw-semibold mb-3">Shipping policies</h5>
  <p class="small text-muted">
    {{ $shopPolicies->shipping ?? 'Shipping details coming soon.' }}
  </p>

  <h5 class="fw-semibold mt-4 mb-3">Returns & exchanges</h5>
  <p class="small text-muted">
    {{ $shopPolicies->returns ?? 'Returns policy coming soon.' }}
  </p>
</div>
