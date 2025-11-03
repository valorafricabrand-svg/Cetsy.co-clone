{{-- resources/views/theme/{{ theme() }}/partials/_tab_shipping.blade.php --}}

<div class="tab-pane fade" id="shipping-pane" role="tabpanel">
  @php
    // Compute processing time min/max
    $procMin = null; $procMax = null;
    try {
      if (!empty($product->processing_time_id)) {
        $pt = \App\Models\ProcessingTime::find($product->processing_time_id);
        if ($pt) {
          if (isset($pt->days) && is_numeric($pt->days)) { $procMin = $procMax = (int)$pt->days; }
          else {
            $procMin = is_numeric($pt->start_day ?? null) ? (int)$pt->start_day : null;
            $procMax = is_numeric($pt->end_day   ?? null) ? (int)$pt->end_day   : null;
          }
        }
      }
      $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
      if (($procMin === null && $procMax === null) && $rows->isNotEmpty()) {
        $minRow = $rows->min(fn($r) => (int) ($r->processing_custom_min ?? PHP_INT_MAX));
        if (is_int($minRow) && $minRow !== PHP_INT_MAX) { $procMin = $minRow; }
        $rowPtId = $rows->firstWhere('processing_time_id', '!=', null)->processing_time_id ?? null;
        if ($rowPtId && ($pt2 = \App\Models\ProcessingTime::find($rowPtId))) {
          if ($procMin === null && isset($pt2->days) && is_numeric($pt2->days)) { $procMin = (int)$pt2->days; }
          if (isset($pt2->start_day) && is_numeric($pt2->start_day)) { $procMin = $procMin ?? (int)$pt2->start_day; }
          if (isset($pt2->end_day)   && is_numeric($pt2->end_day))   { $procMax = (int)$pt2->end_day; }
        }
      }

      // Transit days from shipping rows (use default profile_name group if present)
      $daysMin = null; $daysMax = null;
      if ($rows->isNotEmpty()) {
        $defaultGroup = optional($rows->firstWhere('is_default', true))->profile_name
                       ?? optional($rows->first())->profile_name;
        $groupRows = $defaultGroup ? $rows->where('profile_name', $defaultGroup) : collect();
        if ($groupRows->isNotEmpty()) {
          $daysMin = $groupRows->min(function($r){ return is_numeric($r->days_min ?? null) ? (int)$r->days_min : PHP_INT_MAX; });
          $daysMax = $groupRows->max(function($r){ return is_numeric($r->days_max ?? null) ? (int)$r->days_max : 0; });
          if ($daysMin === PHP_INT_MAX) { $daysMin = null; }
          if ($daysMax === 0) { $daysMax = null; }
        }
      }

      // Combine processing + transit to estimated delivery window
      $etaStart = null; $etaEnd = null; $etaLabel = null; $procLabel = null; $transitLabel = null;
      if ($procMin !== null && $procMax !== null) {
        $procLabel = ($procMin === $procMax) ? ($procMin.' day'.($procMin==1?'':'s')) : ($procMin.'-'.$procMax.' days');
      } elseif ($procMin !== null) {
        $procLabel = $procMin.' day'.($procMin==1?'':'s');
      }
      if ($daysMin !== null && $daysMax !== null) {
        $transitLabel = ($daysMin === $daysMax) ? ($daysMin.' day'.($daysMin==1?'':'s')) : ($daysMin.'-'.$daysMax.' days');
      } elseif ($daysMin !== null) {
        $transitLabel = $daysMin.' day'.($daysMin==1?'':'s');
      }

      if ($procMin !== null || $daysMin !== null || $procMax !== null || $daysMax !== null) {
        $minTotal = (int) (($procMin ?? 0) + ($daysMin ?? 0));
        $maxTotal = ($procMax !== null || $daysMax !== null)
          ? (int) (($procMax ?? ($procMin ?? 0)) + ($daysMax ?? ($daysMin ?? 0)))
          : null;
        $base = now();
        $fmt = function($d){ return $d ? $d->format('M j') : null; };
        $etaStart = $minTotal > 0 ? $base->copy()->addDays($minTotal) : null;
        $etaEnd   = $maxTotal !== null ? $base->copy()->addDays($maxTotal) : null;
        if ($etaStart && $etaEnd) { $etaLabel = $fmt($etaStart).' - '.$fmt($etaEnd); }
        elseif ($etaStart)        { $etaLabel = $fmt($etaStart); }
        elseif ($etaEnd)          { $etaLabel = $fmt($etaEnd); }
      }
  @endphp

  @if($etaLabel)
    <div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
      <i class="fa-solid fa-truck-fast text-success"></i>
      <div>
        <div class="fw-semibold">Estimated delivery</div>
        <div class="small text-muted">{{ $etaLabel }} @if($procLabel || $transitLabel) - (Processing {{ $procLabel ?? '-' }}, Transit {{ $transitLabel ?? '-' }}) @endif</div>
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
