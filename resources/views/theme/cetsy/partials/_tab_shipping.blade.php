{{-- resources/views/theme/{{ theme() }}/partials/_tab_shipping.blade.php --}}

<div class="listing-tab-pane hidden" id="shipping-pane" role="tabpanel">
  @if(!empty($etaLabel))
    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
      <i class="fa-solid fa-truck-fast mt-0.5 text-emerald-600"></i>
      <div>
        <div class="text-sm font-semibold text-slate-900">Estimated delivery</div>
        <div class="text-sm text-slate-600">
          {{ $etaLabel }}
          @if(!empty($procLabel) || !empty($transitLabel))
            (Processing {{ $procLabel ?? '-' }}, Transit {{ $transitLabel ?? '-' }})
          @endif
        </div>
      </div>
    </div>
  @endif

  {{-- Policies removed per request --}}
</div>
