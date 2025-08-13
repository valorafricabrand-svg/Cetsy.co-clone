{{-- resources/views/checkout/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title','Checkout')

@section('main')
<style>
  .form-control, .form-select {
    background: #fff;
    color: #000;
    border: 1px solid #ced4da;
  }
  .form-control:focus, .form-select:focus {
    background: #fff;
    color: #000;
    border-color: #80bdff;
    box-shadow: 0 0 0 .2rem rgba(38,143,255,.25);
  }
  .text-danger { font-size: .875rem; }
  @media (min-width: 992px) {
    .sticky-summary { position: sticky; top: 100px; z-index: 10; }
  }
  .list-group-item .thumb {
    width: 56px; height: 56px; object-fit: cover; border-radius: .5rem;
  }
</style>

@php
  $user      = auth()->user();
  $cart      = session('cart', []);
  $currency  = get_currency();

  // Totals (server-side initial)
  $subtotal      = collect($cart)->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 0));
  $totalShipping = collect($cart)->sum(function($i){
    $prof = collect($i['shipping_profiles'] ?? [])
              ->firstWhere('id', $i['selected_shipping_profile_id'] ?? null);
    return (($prof['base_rate'] ?? 0) * ($i['quantity'] ?? 0));
  });
@endphp

<section class="checkout-page py-5" style="margin-top:100px;">
  <div class="container">
    {{-- Validation Errors --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>There were some problems with your input:</strong>
        <ul class="mb-0 mt-2">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Exception Detail --}}
    @if (session('error_exception'))
      <div class="alert alert-danger">
        <strong>Technical error:</strong>
        <pre class="mb-0">{{ session('error_exception') }}</pre>
      </div>
    @endif

    @if (!empty($cart))
      <form action="{{ route('store_order') }}" method="POST" class="row gy-5 gy-lg-0" novalidate>
        @csrf

        {{-- Billing & Shipping Details --}}
        <div class="col-lg-7 order-lg-1">
          <h4 class="mb-4">Billing &amp; Shipping Details</h4>
          <div class="row g-3">
            {{-- Full Name --}}
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input name="full_name" type="text" class="form-control"
                     value="{{ old('full_name', $user->name ?? '') }}" required>
              @error('full_name')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input name="email" type="email" class="form-control"
                     value="{{ old('email', $user->email ?? '') }}" required>
              @error('email')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- Phone --}}
            <div class="col-md-6">
              <label class="form-label">Phone <span class="text-danger">*</span></label>
              <input name="phone" type="tel" class="form-control"
                     value="{{ old('phone', $user->phone ?? '') }}" required>
              @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- Shipping Country --}}
            <div class="col-md-6">
              <label class="form-label">Country <span class="text-danger">*</span></label>
              <select name="shipping_country" class="form-select" required>
                <option value="">Select Country</option>
                @foreach(\App\Models\Country::orderBy('name')->get() as $c)
                  <option value="{{ $c->id }}"
                    @selected(old('shipping_country', $user->country_id ?? '') == $c->id)>
                    {{ $c->name }}
                  </option>
                @endforeach
              </select>
              @error('shipping_country')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- Address Line 1 --}}
            <div class="col-12">
              <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
              <input name="shipping_address_1" type="text" class="form-control"
                     value="{{ old('shipping_address_1') }}" required>
              @error('shipping_address_1')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- Address Line 2 --}}
            <div class="col-12">
              <label class="form-label">Address Line 2</label>
              <input name="shipping_address_2" type="text" class="form-control"
                     value="{{ old('shipping_address_2') }}">
            </div>

            {{-- City --}}
            <div class="col-md-6">
              <label class="form-label">City/Town <span class="text-danger">*</span></label>
              <input name="shipping_city" type="text" class="form-control"
                     value="{{ old('shipping_city') }}" required>
              @error('shipping_city')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- State --}}
            <div class="col-md-6">
              <label class="form-label">State/Province</label>
              <input name="shipping_state" type="text" class="form-control"
                     value="{{ old('shipping_state') }}">
            </div>

            {{-- Postal Code --}}
            <div class="col-md-6">
              <label class="form-label">Postal/ZIP Code</label>
              <input name="shipping_postal_code" type="text" class="form-control"
                     value="{{ old('shipping_postal_code') }}">
            </div>

            {{-- Billing same as shipping --}}
            <div class="col-12 mt-3">
              <input type="hidden" name="billing_same_as_shipping" value="0">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="billing_same"
                       name="billing_same_as_shipping" value="1"
                       {{ old('billing_same_as_shipping','1')=='1' ? 'checked' : '' }}
                       onchange="toggleBillingAddress(this.checked)">
                <label class="form-check-label" for="billing_same">
                  Billing address same as shipping
                </label>
              </div>
            </div>

            {{-- Billing Address Fields --}}
            <div id="billing_address_fields"
                 style="display:{{ old('billing_same_as_shipping','1')=='1' ? 'none' : 'block' }};">
              <hr class="my-4"><h5>Billing Address</h5>

              <div class="col-md-6">
                <label class="form-label">Country</label>
                <select name="billing_country" class="form-select">
                  <option value="">Select Country</option>
                  @foreach(\App\Models\Country::orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}" @selected(old('billing_country')==$c->id)>{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 mt-2">
                <label class="form-label">Address Line 1</label>
                <input name="billing_address_1" type="text" class="form-control"
                       value="{{ old('billing_address_1') }}">
              </div>

              <div class="col-12 mt-2">
                <label class="form-label">Address Line 2</label>
                <input name="billing_address_2" type="text" class="form-control"
                       value="{{ old('billing_address_2') }}">
              </div>

              <div class="col-md-6 mt-2">
                <label class="form-label">City/Town</label>
                <input name="billing_city" type="text" class="form-control"
                       value="{{ old('billing_city') }}">
              </div>

              <div class="col-md-6 mt-2">
                <label class="form-label">State/Province</label>
                <input name="billing_state" type="text" class="form-control"
                       value="{{ old('billing_state') }}">
              </div>

              <div class="col-md-6 mt-2">
                <label class="form-label">Postal/ZIP Code</label>
                <input name="billing_postal_code" type="text" class="form-control"
                       value="{{ old('billing_postal_code') }}">
              </div>
            </div>

            {{-- Order Notes --}}
            <hr class="my-4">
            <div class="col-12">
              <label class="form-label">Order Notes</label>
              <textarea name="order_notes" rows="3" class="form-control">{{ old('order_notes') }}</textarea>
            </div>

            {{-- Promo Code --}}
            <div class="col-12">
              <label class="form-label">Promo Code</label>
              <input name="promo_code" type="text" class="form-control"
                     value="{{ old('promo_code') }}">
            </div>
          </div>
        </div>

        {{-- Order Summary --}}
        <div class="col-lg-5 order-lg-2">
          <div class="sticky-summary bg-white p-4 shadow-sm rounded">
            <h4 class="mb-4">Your Order</h4>

            <ul class="list-group mb-3" id="js-order-lines">
              @foreach ($cart as $row)
                @php
                  $qty       = (int) ($row['quantity'] ?? 0);
                  $unit      = (float) ($row['price'] ?? 0);
                  $photo     = $row['photo'] ?? null;
                  $profiles  = collect($row['shipping_profiles'] ?? []);
                  $selected  = $row['selected_shipping_profile_id'] ?? null;
                  $selProf   = $profiles->firstWhere('id', $selected);
                  $rate      = (float) ($selProf['base_rate'] ?? 0);
                @endphp
                <li class="list-group-item" data-row-id="{{ $row['row_id'] }}"
                    data-unit="{{ $unit }}" data-qty="{{ $qty }}">
                  <div class="d-flex align-items-start gap-3">
                    @if($photo)
                      <img src="{{ $photo }}" alt="{{ $row['name'] }}" class="thumb">
                    @endif
                    <div class="flex-grow-1">
                      <div class="fw-semibold">{{ $row['name'] }}</div>
                      @if (!empty($row['variation_summary']))
                        <small class="text-muted d-block">{{ $row['variation_summary'] }}</small>
                      @endif

                      {{-- Shipping profile selector (per item) --}}
                      <div class="mt-2">
                        <label class="form-label small mb-1">Shipping method</label>
                        <select class="form-select form-select-sm js-ship-select"
                                data-row-id="{{ $row['row_id'] }}">
                          @foreach($profiles as $p)
                            <option value="{{ $p['id'] }}"
                                    data-rate="{{ (float)($p['base_rate'] ?? 0) }}"
                                    @selected($p['id'] == $selected)>
                              {{ $p['name'] }} — {{ $currency }} {{ number_format((float)($p['base_rate'] ?? 0), 2) }}
                            </option>
                          @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                          Rate: <span class="js-ship-rate">{{ $currency }} {{ number_format($rate,2) }}</span>
                          <span class="ms-2">× {{ $qty }}</span>
                        </small>
                      </div>
                    </div>

                    <div class="text-end">
                      <div class="fw-semibold">{{ $currency }} <span class="js-line-total">{{ number_format(($unit * $qty),2) }}</span></div>
                      <small class="text-muted">Item × {{ $qty }}</small>
                    </div>
                  </div>
                </li>
              @endforeach
            </ul>

            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal</span>
              <strong>{{ $currency }} <span id="js-subtotal">{{ number_format($subtotal,2) }}</span></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Shipping</span>
              <strong>{{ $currency }} <span id="js-shipping">{{ number_format($totalShipping,2) }}</span></strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
              <span><strong>Total</strong></span>
              <strong>{{ $currency }} <span id="js-total">{{ number_format($subtotal + $totalShipping,2) }}</span></strong>
            </div>

            <div class="d-grid mt-4">
              <button type="submit" class="btn btn-success btn-lg">Place Your Order</button>
            </div>
          </div>
        </div>
      </form>
    @else
      <div class="text-center py-5">
        <h2>Your cart is empty</h2>
        <p class="lead">Looks like you haven’t added anything yet.</p>
        <a href="{{ route('listings') }}" class="btn btn-primary">Continue Shopping</a>
      </div>
    @endif
  </div>
</section>

<script>
  function toggleBillingAddress(checked) {
    document.getElementById('billing_address_fields')
            .style.display = checked ? 'none' : 'block';
  }

  (function(){
    const currency = @json($currency);
    const csrf     = @json(csrf_token());
    const endpoint = @json(route('cart.shipping'));

    const orderLines = document.getElementById('js-order-lines');
    if (!orderLines) return;

    const subtotalNode = document.getElementById('js-subtotal');
    const shippingNode = document.getElementById('js-shipping');
    const totalNode    = document.getElementById('js-total');

    function money(n){ return Number(n).toFixed(2); }

    function recalcTotals(){
      let subtotal = 0, shipping = 0;

      orderLines.querySelectorAll('li.list-group-item').forEach(li => {
        const unit = parseFloat(li.getAttribute('data-unit')) || 0;
        const qty  = parseInt(li.getAttribute('data-qty')) || 0;

        subtotal += unit * qty;

        const shipRateText = li.querySelector('.js-ship-rate')?.textContent || '';
        // Extract number from "<CUR> 123.45"
        const shipRate = parseFloat(shipRateText.replace(/[^\d.]/g,'')) || 0;
        shipping += shipRate * qty;

        const lineTotalNode = li.querySelector('.js-line-total');
        if (lineTotalNode) lineTotalNode.textContent = money(unit * qty);
      });

      if (subtotalNode) subtotalNode.textContent = money(subtotal);
      if (shippingNode) shippingNode.textContent = money(shipping);
      if (totalNode) totalNode.textContent = money(subtotal + shipping);
    }

    async function persistShipping(rowId, selectedProfileId){
      // Build payload: shipping_profile_ids[rowId] = selectedProfileId
      const form = new FormData();
      form.append(`shipping_profile_ids[${rowId}]`, selectedProfileId);

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          body: form
        });
        // We don't depend on response to update UI, but log for debugging
        // const data = await res.json();
      } catch (e) {
        console.error('Failed to persist shipping selection', e);
      }
    }

    orderLines.querySelectorAll('.js-ship-select').forEach(sel => {
      sel.addEventListener('change', function(){
        const rowId = this.getAttribute('data-row-id');
        const opt   = this.selectedOptions[0];
        const rate  = parseFloat(opt.getAttribute('data-rate')) || 0;

        // Update the visible rate for this row
        const li = this.closest('li.list-group-item');
        const rateNode = li.querySelector('.js-ship-rate');
        if (rateNode) rateNode.textContent = `${currency} ${money(rate)}`;

        // Recalculate totals client-side
        recalcTotals();

        // Persist selection to session
        persistShipping(rowId, this.value);
      });
    });

    // Initial calc (useful if DOM changed)
    recalcTotals();
  })();
</script>
@endsection
