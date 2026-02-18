{{-- resources/views/cart/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Your Cart')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 pb-28 sm:px-6 md:pb-0">
    <div id="flash-container" class="mb-4 space-y-2">
      @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{!! session('success') !!}</div>
      @endif
      @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
      @endif
    </div>

    @php
      $cart     = session('cart', []);
      $currency = get_currency();

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
      <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <h2 class="text-2xl font-bold text-slate-900">Your cart is empty</h2>
        <p class="mt-2 text-sm text-slate-500">Add products to continue checkout.</p>
        <a href="{{ route('listings') }}" class="mt-5 inline-flex rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
          Continue Shopping
        </a>
      </div>
    @else
      <div class="mb-4 hidden overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Product</th>
              <th class="px-4 py-3 text-left font-semibold">Variation</th>
              <th class="px-4 py-3 text-right font-semibold">Price</th>
              <th class="px-4 py-3 text-center font-semibold">Quantity</th>
              <th class="px-4 py-3 text-left font-semibold">Shipping</th>
              <th class="px-4 py-3 text-right font-semibold">Line Total</th>
              <th class="px-4 py-3 text-center font-semibold">Remove</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 text-slate-700">
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
            <tr
              data-row-id="{{ $rowId }}"
              data-unit-price="{{ $unitPrice }}"
              data-quantity="{{ $qty }}"
              data-is-digital="{{ $isDigital ? 1 : 0 }}"
            >
              <td class="px-4 py-3 align-top">
                <div class="flex items-start gap-3">
                  @if($photoUrl)
                    <img src="{{ $photoUrl }}" width="60" height="60" class="rounded-lg border border-slate-200 object-cover" alt="">
                  @endif
                  <div>
                    <p class="font-semibold text-slate-900">
                      {{ $item['name'] }}
                      @if($isDigital)
                        <span class="ml-1 rounded-full border border-slate-300 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">Digital</span>
                      @endif
                    </p>
                    @if (!empty($item['variation_summary']))
                      <p class="mt-0.5 text-xs text-slate-500">{{ $item['variation_summary'] }}</p>
                    @endif
                  </div>
                </div>
              </td>

              <td class="px-4 py-3 align-top text-slate-600">{{ $item['variation_summary'] ?? '-' }}</td>
              <td class="px-4 py-3 align-top text-right font-medium">{{ $currency }} {{ number_format($unitPrice,2) }}</td>

              <td class="px-4 py-3 align-top">
                <div class="flex items-center justify-center gap-2">
                  <button class="js-qty-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40" data-action="decrease" @disabled($qty<=1)>&minus;</button>
                  <span class="quantity min-w-[20px] text-center font-semibold">{{ $qty }}</span>
                  <button class="js-qty-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" data-action="increase">+</button>
                </div>
              </td>

              <td class="px-4 py-3 align-top">
                @if($isDigital)
                  <p class="text-xs text-slate-500">No shipping (digital)</p>
                @else
                  @if($profilesC->isNotEmpty())
                    <select name="shipping_profile_ids[{{ $rowId }}]" class="js-shipping-select w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:outline-none">
                      @foreach($profilesC as $p)
                        @php
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
                    <p class="js-ship-by-hint mt-1 text-xs text-slate-500">
                      @if($minD && $maxD)
                        Ships within {{ $minD }}-{{ $maxD }} days ({{ $startLbl }} - {{ $endLbl }})
                      @elseif($minD)
                        Ships within {{ $minD }} days (by {{ $startLbl }})
                      @elseif($maxD)
                        Ships by {{ $maxD }} days (by {{ $endLbl }})
                      @endif
                    </p>
                  @else
                    <p class="text-xs text-slate-500">No shipping profiles ({{ $currency }} 0.00)</p>
                  @endif
                @endif
              </td>

              <td class="line-total px-4 py-3 align-top text-right font-semibold text-slate-900">{{ $currency }} {{ number_format($lineTotal,2) }}</td>
              <td class="px-4 py-3 align-top text-center">
                <form action="{{ route('cart.remove') }}" method="POST" class="js-remove-form inline">
                  @csrf
                  <input type="hidden" name="row_id" value="{{ $rowId }}">
                  <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white hover:bg-rose-500">&times;</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
          <tfoot class="bg-slate-50 text-slate-700">
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
              <th colspan="5" class="px-4 py-3 text-right font-semibold">Total:</th>
              <th colspan="2" id="grand-total" class="px-4 py-3 text-left text-base font-bold text-slate-900">{{ $currency }} {{ number_format($grand,2) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="space-y-3 md:hidden">
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
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
               data-row-id="{{ $rowId }}"
               data-unit-price="{{ $unitPrice }}"
               data-quantity="{{ $qty }}"
               data-is-digital="{{ $isDigital ? 1 : 0 }}">
            <div class="flex items-start gap-3">
              @if($photoUrl)
                <img src="{{ $photoUrl }}" class="h-[72px] w-[72px] rounded-lg border border-slate-200 object-cover" alt="">
              @endif
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="font-semibold text-slate-900">{{ $item['name'] }}</p>
                    @if (!empty($item['variation_summary']))
                      <p class="text-xs text-slate-500">{{ $item['variation_summary'] }}</p>
                    @endif
                    @if($isDigital)
                      <span class="mt-1 inline-flex rounded-full border border-slate-300 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">Digital</span>
                    @endif
                  </div>
                  <div class="text-right">
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Unit</p>
                    <p class="text-sm font-semibold text-slate-800">{{ $currency }} {{ number_format($unitPrice,2) }}</p>
                  </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <button class="js-qty-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40" data-action="decrease" @disabled($qty<=1)>&minus;</button>
                    <span class="quantity min-w-[20px] text-center font-semibold">{{ $qty }}</span>
                    <button class="js-qty-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" data-action="increase">+</button>
                  </div>
                  <div class="text-right">
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Line Total</p>
                    <p class="line-total text-sm font-semibold text-slate-900">{{ $currency }} {{ number_format($lineTotal,2) }}</p>
                  </div>
                </div>

                <div class="mt-3">
                  @if($isDigital)
                    <p class="text-xs text-slate-500">No shipping (digital)</p>
                  @else
                    @if($profilesC->isNotEmpty())
                      <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Shipping</label>
                      <select name="shipping_profile_ids[{{ $rowId }}]" class="js-shipping-select w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:outline-none">
                        @foreach($profilesC as $p)
                          @php
                            $minD = (int)($p['processing_min_days'] ?? $p['proc_min'] ?? 0);
                            $maxD = (int)($p['processing_max_days'] ?? $p['proc_max'] ?? 0);
                            $base = (float)($p['base_rate'] ?? 0);
                            $label = $p['label'] ?? ($p['name'] ?? 'Shipping');
                            if (!empty($p['pickup_available']) && $base <= 0) {
                                $label = 'Pickup / Collect in person';
                            }
                          @endphp
                          <option value="{{ $p['id'] }}"
                                  data-base-rate="{{ (float)($p['base_rate'] ?? 0) }}"
                                  data-additional-rate="{{ (float)($p['additional_rate'] ?? 0) }}"
                                  data-proc-min="{{ $minD }}"
                                  data-proc-max="{{ $maxD }}"
                                  @selected($selectedId === (int)$p['id'])>
                            {{ $label }} ({{ $currency }} {{ number_format($base,2) }})
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
                      <p class="js-ship-by-hint mt-1 text-xs text-slate-500">
                        @if($minD && $maxD)
                          Ships within {{ $minD }}-{{ $maxD }} days ({{ $startLbl }} - {{ $endLbl }})
                        @elseif($minD)
                          Ships within {{ $minD }} days (by {{ $startLbl }})
                        @elseif($maxD)
                          Ships by {{ $maxD }} days (by {{ $endLbl }})
                        @endif
                      </p>
                    @else
                      <p class="text-xs text-slate-500">No shipping profiles ({{ $currency }} 0.00)</p>
                    @endif
                  @endif
                </div>

                <div class="mt-3 text-right">
                  <form action="{{ route('cart.remove') }}" method="POST" class="js-remove-form inline">
                    @csrf
                    <input type="hidden" name="row_id" value="{{ $rowId }}">
                    <button class="inline-flex rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Remove</button>
                  </form>
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

        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">Total</span>
            <span id="grand-total-mobile" class="text-sm font-bold text-slate-900">{{ $currency }} {{ number_format($grand,2) }}</span>
          </div>
        </div>
      </div>

      <form id="shipping-form" method="POST" action="{{ route('cart.shipping') }}">
        @csrf
      </form>

      <div class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <a href="{{ route('listings') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 md:w-auto">Continue Shopping</a>
        <a href="{{ route('cart.checkout') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 md:w-auto">Proceed to Checkout</a>
      </div>

      <div class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 p-3 shadow-[0_-8px_20px_rgba(15,23,42,0.08)] backdrop-blur md:hidden">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-1">
          <div>
            <p class="text-[11px] uppercase tracking-wide text-slate-400">Total</p>
            <p id="grand-total-sticky" class="text-sm font-bold text-slate-900">{{ $currency }} {{ number_format($grand ?? 0,2) }}</p>
          </div>
          <a href="{{ route('cart.checkout') }}" class="inline-flex flex-1 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">Checkout</a>
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
    if (!$row || qty <= 0) return 0;
    const sel = $row.querySelector('.js-shipping-select');
    if (!sel || !sel.selectedOptions.length) return 0;
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
        txt = `Ships within ${min}-${max} days (${sLbl} - ${eLbl})`;
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
    const map = {
      success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
      danger: 'border-rose-200 bg-rose-50 text-rose-800',
      warning: 'border-amber-200 bg-amber-50 text-amber-800'
    };
    const cls = map[type] || map.warning;
    flash.innerHTML = `<div class="rounded-xl border px-4 py-3 text-sm ${cls}">${msg}</div>`;
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
          if (json && json.cart) syncRowFromCartSnapshot($row, rowId, json.cart);
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

  document.querySelectorAll('.js-shipping-select').forEach(sel=>{
    sel.addEventListener('change',()=>{
      const $row  = getRow(sel);
      const rowId = $row.dataset.rowId;

      refreshRow($row);
      refreshGrand();

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

  refreshGrand();
});
</script>
@endpush
