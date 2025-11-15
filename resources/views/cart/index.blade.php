{{-- resources/views/cart/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Your Cart')
@section('main')
@push('styles')
<style>
  /* Sticky checkout summary on mobile */
  .cart-sticky-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 1049; background: #ffffff; border-top: 1px solid rgba(0,0,0,.1); box-shadow: 0 -6px 18px rgba(0,0,0,.06); padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); }
  .cart-sticky-bar .price { font-weight: 700; }
  @media (min-width: 768px) { .cart-sticky-bar { display: none !important; } }
  @media (max-width: 767.98px) { .cart-page .container { padding-bottom: 100px; } }
</style>
@endpush
<section class="cart-page py-5 bg-light">
  <div class="container">
    {{-- Flash --}}
    <div id="flash-container">
      @if(session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
    </div>
    @php
      $cart     = session('cart', []);
      $currency = get_currency();
      // Prefetch product types (to detect digital vs physical) with one query
      $productIds   = collect($cart)->pluck('product_id')->filter()->unique()->all();
      $productTypes = $productIds
        ? \App\Models\Product::whereIn('id', $productIds)->pluck('type','id')->toArray()
        : [];
      $shippingCalculator = static function ($baseRate, $additionalRate, $quantity) {
        $quantity = max(0, (int) $quantity);
        if ($quantity < 1) {
          return 0.0;
        }
        $base       = (float) $baseRate;
        $additional = (float) $additionalRate;
        return $base + max($quantity - 1, 0) * $additional;
      };
    @endphp
    @if($cart === [])
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ route('listings') }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>
    @else
      <div class="table-responsive mb-4 d-none d-md-block">
        <table class="table table-bordered align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Product</th>
              <th>Variation</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Shipping</th>
              <th>Line&nbsp;Total</th>
              <th>Remove</th>
            </tr>
          </thead>
          <tbody>
          @foreach($cart as $rowId => $item)
            @php
              $qty       = (int)($item['quantity'] ?? 1);
              $unitPrice = (float)($item['price'] ?? 0);
              // Determine digital/physical
              $typeFromCart = $item['product_type'] ?? null; // if you ever stored it
              $typeFromDb   = $productTypes[$item['product_id']] ?? null;
              $isDigital    = ($typeFromCart ?? $typeFromDb) === 'digital';
              // Use profiles snapshot from session for physical products
              $profilesC  = collect($item['shipping_profiles'] ?? []);
              $selectedId = (int)($item['selected_shipping_profile_id'] ?? 0);
              // Rate is zero for digital items (hidden UI), otherwise selected profile's base_rate
              $selected   = $profilesC->firstWhere('id', $selectedId);
              $baseRate   = $isDigital ? 0.0 : (float)($selected['base_rate'] ?? 0.0);
              $addRate    = $isDigital ? 0.0 : (float)($selected['additional_rate'] ?? 0.0);
              $shipTotal  = $isDigital ? 0.0 : $shippingCalculator($baseRate, $addRate, $qty);
              $lineTotal  = ($unitPrice * $qty) + $shipTotal;
              $photoUrl   = $item['photo'] ?? null; // absolute URL already stored by controller
            @endphp
            <tr
              data-row-id="{{ $rowId }}"
              data-unit-price="{{ $unitPrice }}"
              data-quantity="{{ $qty }}"
              data-is-digital="{{ $isDigital ? 1 : 0 }}"
            >
              {{-- Product --}}
              <td class="text-start">
                <div class="d-flex gap-3 align-items-center">
                  @if($photoUrl)
                    <img src="{{ $photoUrl }}" width="60" height="60" class="rounded object-fit-cover" alt="">
                  @endif
                  <div class="text-start">
                    <div class="fw-semibold">
                      {{ $item['name'] }}
                      @if($isDigital)
                        <span class="badge bg-secondary ms-1">Digital</span>
                      @endif
                    </div>
                    @if (!empty($item['variation_summary']))
                      <small class="text-muted">{{ $item['variation_summary'] }}</small>
                    @endif
                  </div>
                </div>
              </td>
              {{-- Variation --}}
              <td>{{ $item['variation_summary'] ?? '—' }}</td>
              {{-- Unit Price --}}
              <td>{{ $currency }} {{ number_format($unitPrice,2) }}</td>
              {{-- Quantity --}}
              <td>
                <div class="d-flex gap-1 justify-content-center align-items-center">
                  <button
                    class="btn btn-sm btn-outline-secondary js-qty-btn"
                    data-action="decrease"
                    @disabled($qty<=1)
                  >&minus;</button>
                  <span class="quantity">{{ $qty }}</span>
                  <button
                    class="btn btn-sm btn-outline-secondary js-qty-btn"
                    data-action="increase"
                  >+</button>
                </div>
              </td>
              {{-- Shipping (hidden/disabled for digital) --}}
              <td>
                @if($isDigital)
                  <div class="small text-muted">No shipping (digital)</div>
                @else
                  @if($profilesC->isNotEmpty())
                    <select
                      name="shipping_profile_ids[{{ $rowId }}]"
                      class="form-select form-select-sm js-shipping-select"
                    >
                      @foreach($profilesC as $p)
                        @php
                          // Label rule you requested
                          $label = ($p['dest_location_type'] ?? null) === 'everywhere_else'
                                   ? 'Everywhere'
                                   : (!empty($p['dest_country_name'])
                                        ? 'Ship to '.$p['dest_country_name']
                                        : $p['name']);
                        @endphp
                        <option
                          value="{{ $p['id'] }}"
                          data-base-rate="{{ (float)$p['base_rate'] }}"
                          data-additional-rate="{{ (float)($p['additional_rate'] ?? 0) }}"
                          @selected((int)$p['id'] === $selectedId)
                        >
                          {{ $label }} ({{ $currency }} {{ number_format((float)$p['base_rate'],2) }})
                        </option>
                      @endforeach
                    </select>
                    @php
                      $sel = $profilesC->firstWhere('id', $selectedId) ?: $profilesC->first();
                      $minD = isset($sel['proc_min']) ? (int)$sel['proc_min'] : null;
                      $maxD = isset($sel['proc_max']) ? (int)$sel['proc_max'] : null;
                      $placedAt = now();
                      $startLbl = $minD ? ($placedAt->copy()->addDays($minD)->isSameDay($placedAt) ? 'today' : $placedAt->copy()->addDays($minD)->format('M j')) : null;
                      $endLbl   = $maxD ? ($placedAt->copy()->addDays($maxD)->isSameDay($placedAt) ? 'today' : $placedAt->copy()->addDays($maxD)->format('M j')) : null;
                    @endphp
                    <div class="small text-muted js-ship-by-hint mt-1">
                      @if($minD && $maxD)
                        Ships within {{ $minD }}–{{ $maxD }} days ({{ $startLbl }} – {{ $endLbl }})
                      @elseif($minD)
                        Ships within {{ $minD }} days (by {{ $startLbl }})
                      @elseif($maxD)
                        Ships by {{ $maxD }} days (by {{ $endLbl }})
                      @endif
                    </div>
                  @else
                    <div class="small text-muted">No shipping profiles ({{ $currency }} 0.00)</div>
                  @endif
                @endif
              </td>
              {{-- Line total --}}
              <td class="line-total">{{ $currency }} {{ number_format($lineTotal,2) }}</td>
              {{-- Remove --}}
              <td>
                <form action="{{ route('cart.remove') }}" method="POST" class="d-inline js-remove-form">
                  @csrf
                  <input type="hidden" name="row_id" value="{{ $rowId }}">
                  <button class="btn btn-sm btn-danger">&times;</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
          <tfoot class="table-light">
            @php
              $grand = collect($cart)->sum(function($i) use ($productTypes, $shippingCalculator) {
                $qty   = (int)($i['quantity'] ?? 1);
                $unit  = (float)($i['price'] ?? 0);
                $typeFromCart = $i['product_type'] ?? null;
                $typeFromDb   = $productTypes[$i['product_id']] ?? null;
                $isDigital    = ($typeFromCart ?? $typeFromDb) === 'digital';
                $profiles = collect($i['shipping_profiles'] ?? []);
                $selId    = (int)($i['selected_shipping_profile_id'] ?? 0);
                $selected = $profiles->firstWhere('id',$selId);
                $baseRate = $isDigital ? 0.0 : (float)($selected['base_rate'] ?? 0.0);
                $addRate  = $isDigital ? 0.0 : (float)($selected['additional_rate'] ?? 0.0);
                $shipping = $isDigital ? 0.0 : $shippingCalculator($baseRate, $addRate, $qty);
                return ($unit * $qty) + $shipping;
              });
            @endphp
            <tr>
              <th colspan="5" class="text-end">Total:</th>
              <th colspan="2" id="grand-total">{{ $currency }} {{ number_format($grand,2) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Mobile/Card layout -->
      <div class="d-block d-md-none mb-4">
        @foreach($cart as $rowId => $item)
          @php
            $qty       = (int)($item['quantity'] ?? 1);
            $unitPrice = (float)($item['price'] ?? 0);
            $typeFromCart = $item['product_type'] ?? null;
            $typeFromDb   = $productTypes[$item['product_id']] ?? null;
            $isDigital    = ($typeFromCart ?? $typeFromDb) === 'digital';
            $profilesC  = collect($item['shipping_profiles'] ?? []);
            $selectedId = (int)($item['selected_shipping_profile_id'] ?? 0);
            $selected   = $profilesC->firstWhere('id', $selectedId);
            $baseRate   = $isDigital ? 0.0 : (float)($selected['base_rate'] ?? 0.0);
            $addRate    = $isDigital ? 0.0 : (float)($selected['additional_rate'] ?? 0.0);
            $shipTotal  = $isDigital ? 0.0 : $shippingCalculator($baseRate, $addRate, $qty);
            $lineTotal  = ($unitPrice * $qty) + $shipTotal;
            $photoUrl   = $item['photo'] ?? null;
          @endphp
          <div class="card shadow-sm mb-3"
               data-row-id="{{ $rowId }}"
               data-unit-price="{{ $unitPrice }}"
               data-quantity="{{ $qty }}"
               data-is-digital="{{ $isDigital ? 1 : 0 }}">
            <div class="card-body">
              <div class="d-flex align-items-start gap-3">
                @if($photoUrl)
                  <img src="{{ $photoUrl }}" class="rounded" alt="" style="width:72px;height:72px;object-fit:cover;flex:0 0 auto;">
                @endif
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div class="fw-semibold">{{ $item['name'] }}</div>
                      @if (!empty($item['variation_summary']))
                        <div class="text-muted small">{{ $item['variation_summary'] }}</div>
                      @endif
                      @if($isDigital)
                        <span class="badge bg-secondary mt-1">Digital</span>
                      @endif
                    </div>
                    <div class="text-end ms-2">
                      <div class="small text-muted">Unit</div>
                      <div>{{ $currency }} {{ number_format($unitPrice,2) }}</div>
                    </div>
                  </div>

                  <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="d-flex align-items-center gap-2">
                      <button class="btn btn-sm btn-outline-secondary js-qty-btn" data-action="decrease" @disabled($qty<=1)>&minus;</button>
                      <span class="quantity">{{ $qty }}</span>
                      <button class="btn btn-sm btn-outline-secondary js-qty-btn" data-action="increase">+</button>
                    </div>
                    <div class="text-end">
                      <div class="small text-muted">Line Total</div>
                      <div class="fw-semibold line-total">{{ $currency }} {{ number_format($lineTotal,2) }}</div>
                    </div>
                  </div>

                  <div class="mt-3">
                    @if($isDigital)
                      <div class="small text-muted">No shipping (digital)</div>
                    @else
                      @if($profilesC->isNotEmpty())
                        <label class="form-label small text-muted mb-1">Shipping</label>
                        <select name="shipping_profile_ids[{{ $rowId }}]" class="form-select form-select-sm js-shipping-select">
                          @foreach($profilesC as $p)
                            @php
                              $minD = (int)($p['processing_min_days'] ?? 0);
                              $maxD = (int)($p['processing_max_days'] ?? 0);
                            @endphp
                              <option value="{{ $p['id'] }}"
                                     data-base-rate="{{ (float)($p['base_rate'] ?? 0) }}"
                                     data-additional-rate="{{ (float)($p['additional_rate'] ?? 0) }}"
                                     data-proc-min="{{ $minD }}"
                                     data-proc-max="{{ $maxD }}"
                                     @selected($selectedId === (int)$p['id'])>
                              {{ $p['name'] ?? 'Shipping' }} ({{ $currency }} {{ number_format((float)($p['base_rate'] ?? 0),2) }})
                            </option>
                          @endforeach
                        </select>
                        @php
                          $placedAt = now();
                          $minD = (int)($selected['processing_min_days'] ?? 0);
                          $maxD = (int)($selected['processing_max_days'] ?? 0);
                          $startLbl = $minD ? ($placedAt->copy()->addDays($minD)->isSameDay($placedAt) ? 'today' : $placedAt->copy()->addDays($minD)->format('M j')) : null;
                          $endLbl   = $maxD ? ($placedAt->copy()->addDays($maxD)->isSameDay($placedAt) ? 'today' : $placedAt->copy()->addDays($maxD)->format('M j')) : null;
                        @endphp
                        <div class="small text-muted js-ship-by-hint mt-1">
                          @if($minD && $maxD)
                            Ships within {{ $minD }}–{{ $maxD }} days ({{ $startLbl }} – {{ $endLbl }})
                          @elseif($minD)
                            Ships within {{ $minD }} days (by {{ $startLbl }})
                          @elseif($maxD)
                            Ships by {{ $maxD }} days (by {{ $endLbl }})
                          @endif
                        </div>
                      @else
                        <div class="small text-muted">No shipping profiles ({{ $currency }} 0.00)</div>
                      @endif
                    @endif
                  </div>

                  <div class="mt-3 text-end">
                    <form action="{{ route('cart.remove') }}" method="POST" class="d-inline js-remove-form">
                      @csrf
                      <input type="hidden" name="row_id" value="{{ $rowId }}">
                      <button class="btn btn-sm btn-danger">Remove</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach

        @php
          $grand = collect($cart)->sum(function($i) use ($productTypes) {
            $qty   = (int)($i['quantity'] ?? 1);
            $unit  = (float)($i['price'] ?? 0);
            $typeFromCart = $i['product_type'] ?? null;
            $typeFromDb   = $productTypes[$i['product_id']] ?? null;
            $isDigital    = ($typeFromCart ?? $typeFromDb) === 'digital';
            $profiles = collect($i['shipping_profiles'] ?? []);
            $selId    = (int)($i['selected_shipping_profile_id'] ?? 0);
            $rate     = $isDigital ? 0.0 : (float) optional($profiles->firstWhere('id',$selId))['base_rate'] ?? 0.0;
            return ($unit + $rate) * $qty;
          });
        @endphp
        <div class="card shadow-sm">
          <div class="card-body d-flex justify-content-between">
            <strong>Total</strong>
            <span id="grand-total-mobile">{{ $currency }} {{ number_format($grand,2) }}</span>
          </div>
        </div>
      </div>
      {{-- Passive fallback form (not used by JS directly, kept for PE) --}}
      <form id="shipping-form" method="POST" action="{{ route('cart.shipping') }}">
        @csrf
      </form>
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
        <a href="{{ route('listings') }}" class="btn btn-outline-secondary w-100 w-md-auto">Continue Shopping</a>
        <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 w-md-auto">Proceed to Checkout</a>
      </div>

      <!-- Sticky footer summary (mobile) -->
      <div class="cart-sticky-bar d-md-none">
        <div class="container d-flex align-items-center justify-content-between gap-3">
          <div>
            <div class="small text-muted">Total</div>
            <div id="grand-total-sticky" class="price">{{ $currency }} {{ number_format($grand ?? 0,2) }}</div>
          </div>
          <a href="{{ route('cart.checkout') }}" class="btn btn-primary btn-lg flex-grow-1">Checkout</a>
        </div>
      </div>
    @endif
  </div>
</section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const CSRF  = '{{ csrf_token() }}';
  const cur   = '{{ $currency }}';
  const flash = document.getElementById('flash-container');
  function money(n){ return cur+' '+(+n).toFixed(2); }
  function getRow(el){ return el.closest('[data-row-id]'); }
  function shippingTotal($row, qty) {
    if (!$row || qty <= 0) {
      return 0;
    }
    const sel = $row.querySelector('.js-shipping-select');
    if (!sel || !sel.selectedOptions.length) {
      return 0;
    }
    const opt        = sel.selectedOptions[0];
    const base       = Number(opt.dataset.baseRate || 0);
    const additional = Number(opt.dataset.additionalRate || 0);
    return base + Math.max(qty - 1, 0) * additional;
  }
  function rowTotal($row){
    const unit = Number($row.dataset.unitPrice || 0);
    const qty  = parseInt($row.dataset.quantity || '0', 10) || 0;
    const isDigital = ($row.dataset.isDigital === '1');
    const shipping = isDigital ? 0 : shippingTotal($row, qty);
    return (unit * qty) + shipping;
  }
  function refreshRow($row){
    const lt = $row.querySelector('.line-total');
    if (lt) lt.textContent = money(rowTotal($row));
    const hint = $row.querySelector('.js-ship-by-hint');
    const sel  = $row.querySelector('.js-shipping-select');
    if (hint && sel && sel.selectedOptions.length) {
      const opt   = sel.selectedOptions[0];
      const min   = parseInt(opt.getAttribute('data-proc-min') || '', 10);
      const max   = parseInt(opt.getAttribute('data-proc-max') || '', 10);
      const today = new Date();
      const fmt = (d)=> d.toLocaleString('en-US',{month:'short'})+' '+d.getDate();
      const add = (n)=> { const d=new Date(); d.setDate(d.getDate()+n); return d; };
      let txt='';
      if (!isNaN(min) && !isNaN(max)){
        const s=add(min), e=add(max);
        const sLbl = (s.toDateString()===today.toDateString()) ? 'today' : fmt(s);
        const eLbl = (e.toDateString()===today.toDateString()) ? 'today' : fmt(e);
        txt = `Ships within ${min}–${max} days (${sLbl} – ${eLbl})`;
      } else if (!isNaN(min)){
        const s=add(min); const sLbl=(s.toDateString()===today.toDateString())?'today':fmt(s);
        txt = `Ships within ${min} days (by ${sLbl})`;
      } else if (!isNaN(max)){
        const e=add(max); const eLbl=(e.toDateString()===today.toDateString())?'today':fmt(e);
        txt = `Ships by ${max} days (by ${eLbl})`;
      }
      hint.textContent = txt;
    }
  }
  function refreshGrand(){
    let sum=0;
    const rows = Array.from(document.querySelectorAll('[data-row-id]')).filter(el => el.offsetParent !== null);
    rows.forEach($row => sum += rowTotal($row));
    const gt = document.getElementById('grand-total');
    if (gt) gt.textContent = money(sum);
    const gtm = document.getElementById('grand-total-mobile');
    if (gtm) gtm.textContent = money(sum);
    const gts = document.getElementById('grand-total-sticky');
    if (gts) gts.textContent = money(sum);
  }
  function notify(type,msg){
    flash.innerHTML=`<div class="alert alert-${type}">${msg}</div>`;
  }
  function syncRowFromCartSnapshot($row, rowId, snapshot) {
    if (!snapshot || !snapshot[rowId]) {
      $row.remove();
      return false;
    }
    const entry = snapshot[rowId];
    const qty   = parseInt(entry.quantity ?? 1, 10);
    $row.dataset.quantity = qty;
    const qEl = $row.querySelector('.quantity');
    if (qEl) qEl.textContent = qty;
    $row.querySelectorAll('.js-qty-btn[data-action="decrease"]').forEach(btn => btn.disabled = qty <= 1);
    refreshRow($row);
    return true;
  }
  /* quantity buttons */
  document.querySelectorAll('.js-qty-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const $row  = getRow(btn);
      const rowId = $row.dataset.rowId;
      const act   = btn.dataset.action;
      fetch('{{ route("cart.update") }}',{
        method:'POST',
        headers:{
          'X-CSRF-TOKEN':CSRF,'Accept':'application/json',
          'Content-Type':'application/json'
        },
        body:JSON.stringify({row_id:rowId,action:act})
      })
      .then(async response => {
        const json = await response.json().catch(() => null);
        if (!response.ok || !json || json.success === false) {
          const message = (json && json.message) ? json.message : 'Failed to update quantity.';
          if (json && json.cart) {
            syncRowFromCartSnapshot($row, rowId, json.cart);
          }
          refreshGrand();
          notify('danger', message);
          return;
        }
        syncRowFromCartSnapshot($row, rowId, json.cart);
        refreshGrand();
        notify('success', json.message || 'Quantity updated.');
      })
      .catch(() => notify('danger','Failed to update quantity.'));
    });
  });
  /* shipping select (only on physical rows) */
  document.querySelectorAll('.js-shipping-select').forEach(sel=>{
    sel.addEventListener('change',()=>{
      const $row  = getRow(sel);
      const rowId = $row.dataset.rowId;
      // UI first
      refreshRow($row); refreshGrand();
      // Persist selection to session
      fetch('{{ route("cart.shipping") }}',{
        method:'POST',
        headers:{
          'X-CSRF-TOKEN':CSRF,'Accept':'application/json',
          'Content-Type':'application/json'
        },
        body:JSON.stringify({shipping_profile_ids:{[rowId]:sel.value}})
      })
      .then(r=>r.json())
      .then(j=>notify('success', j.message || 'Shipping updated.'))
      .catch(()=>notify('danger','Failed to update shipping.'));
    });
  });
  // initial totals
  refreshGrand();
});
</script>
@endpush
