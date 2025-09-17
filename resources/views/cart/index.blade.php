{{-- resources/views/cart/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Your Cart')
@section('main')
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
    @endphp
    @if($cart === [])
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>
    @else
      <div class="table-responsive mb-4">
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
              $rate       = $isDigital ? 0.0 : (float)($selected['base_rate'] ?? 0.0);
              $lineTotal  = ($unitPrice + $rate) * $qty;
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
                          @selected((int)$p['id'] === $selectedId)
                        >
                          {{ $label }} ({{ $currency }} {{ number_format((float)$p['base_rate'],2) }})
                        </option>
                      @endforeach
                    </select>
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
            <tr>
              <th colspan="5" class="text-end">Total:</th>
              <th colspan="2" id="grand-total">{{ $currency }} {{ number_format($grand,2) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>
      {{-- Passive fallback form (not used by JS directly, kept for PE) --}}
      <form id="shipping-form" method="POST" action="{{ route('cart.shipping') }}">
        @csrf
      </form>
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Continue Shopping</a>
        <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
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
  function rowTotal($tr){
    const unit      = +$tr.dataset.unitPrice;
    const qty       = +$tr.dataset.quantity;
    const isDigital = ($tr.dataset.isDigital === '1');
    let rate = 0;
    if (!isDigital) {
      const sel = $tr.querySelector('.js-shipping-select');
      rate = sel && sel.selectedOptions.length ? +(sel.selectedOptions[0].dataset.baseRate || 0) : 0;
    }
    return (unit + rate) * qty;
  }
  function refreshRow($tr){
    $tr.querySelector('.line-total').textContent = money(rowTotal($tr));
  }
  function refreshGrand(){
    let sum=0;
    document.querySelectorAll('tbody tr').forEach($tr => sum += rowTotal($tr));
    document.getElementById('grand-total').textContent = money(sum);
  }
  function notify(type,msg){
    flash.innerHTML=`<div class="alert alert-${type}">${msg}</div>`;
  }
  function syncRowFromCartSnapshot($tr, rowId, snapshot) {
    if (!snapshot || !snapshot[rowId]) {
      $tr.remove();
      return false;
    }
    const entry = snapshot[rowId];
    const qty   = parseInt(entry.quantity ?? 1, 10);
    $tr.dataset.quantity = qty;
    $tr.querySelector('.quantity').textContent = qty;
    $tr.querySelectorAll('.js-qty-btn[data-action="decrease"]').forEach(btn => btn.disabled = qty <= 1);
    refreshRow($tr);
    return true;
  }
  /* quantity buttons */
  document.querySelectorAll('.js-qty-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const $tr   = btn.closest('tr');
      const rowId = $tr.dataset.rowId;
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
            syncRowFromCartSnapshot($tr, rowId, json.cart);
          }
          refreshGrand();
          notify('danger', message);
          return;
        }
        syncRowFromCartSnapshot($tr, rowId, json.cart);
        refreshGrand();
        notify('success', json.message || 'Quantity updated.');
      })
      .catch(() => notify('danger','Failed to update quantity.'));
    });
  });
  /* shipping select (only on physical rows) */
  document.querySelectorAll('.js-shipping-select').forEach(sel=>{
    sel.addEventListener('change',()=>{
      const $tr  = sel.closest('tr');
      const rowId= $tr.dataset.rowId;
      // UI first
      refreshRow($tr); refreshGrand();
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
