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
  /* Mobile sticky bar for quick action */
  .checkout-sticky-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 1049; background: #ffffff; border-top: 1px solid rgba(0,0,0,.1); box-shadow: 0 -6px 18px rgba(0,0,0,.06); padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); }
  .checkout-sticky-bar .price { font-weight: 700; }
  @media (min-width: 768px) { .checkout-sticky-bar { display: none !important; } }
  @media (max-width: 767.98px) { .checkout-page .container { padding-bottom: 100px; } }
</style>

@php
  $user      = auth()->user();
  $cart      = session('cart', []);
  $currency  = get_currency();
  // Saved addresses (if any) to prefill form
  $savedShipping = null; $savedBilling = null;
  try {
    if ($user) {
      $savedShipping = \App\Models\Address::where('user_id',$user->id)->where('type','shipping')->first();
      $savedBilling  = \App\Models\Address::where('user_id',$user->id)->where('type','billing')->first();
    }
  } catch (\Throwable $e) { /* ignore */ }

  // Prefetch product types once (digital/physical) to decide shipping visibility/costs
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

  // Helper to read the selected profile snapshot from session (for PHYSICAL only)
  $getSelectedProfile = function(array $row) {
      $profiles = collect($row['shipping_profiles'] ?? []);
      $selId    = (int)($row['selected_shipping_profile_id'] ?? 0);
      $selected = $profiles->firstWhere('id', $selId);
      if (!$selected && $profiles->isNotEmpty()) {
          $selected = $profiles->first();
      }
      // Always return a consistent array shape
      return $selected ?: ['name' => 'Standard', 'base_rate' => 0, 'additional_rate' => 0];
  };

  // Subtotal (products only)
  $subtotal = collect($cart)->sum(fn($i) => ((float)($i['price'] ?? 0)) * (int)($i['quantity'] ?? 1));

  // Shipping total: zero for digital rows, selected profile * qty for physical rows
  $totalShipping = collect($cart)->sum(function($i) use ($getSelectedProfile, $productTypes, $shippingCalculator) {
      $qty  = (int)($i['quantity'] ?? 1);
      $pid  = (int)($i['product_id'] ?? 0);
      $type = $i['product_type'] ?? ($productTypes[$pid] ?? null); // prefer cart snapshot if ever stored
      $isDigital = ($type === 'digital');

      if ($isDigital) return 0.0;

      $prof = $getSelectedProfile($i);
      $baseRate = (float)($prof['base_rate'] ?? 0);
      $addRate  = (float)($prof['additional_rate'] ?? 0);
      return $shippingCalculator($baseRate, $addRate, $qty);
  });

  $grandTotal = $subtotal + $totalShipping;
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

    {{-- Exception Detail (optional debug) --}}
    @if (session('error_exception'))
      <div class="alert alert-danger">
        <strong>Technical error:</strong>
        <pre class="mb-0">{{ session('error_exception') }}</pre>
      </div>
    @endif

    @if (!empty($cart))
      <form id="checkout-form" action="{{ route('store_order') }}" method="POST" class="row gy-5 gy-lg-0" novalidate>
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
                    @selected(old('shipping_country', optional($savedShipping)->country_id ?? ($user->country_id ?? '')) == $c->id)>
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
                     value="{{ old('shipping_address_1', optional($savedShipping)->address_1) }}" required>
              @error('shipping_address_1')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- Address Line 2 --}}
            <div class="col-12">
              <label class="form-label">Address Line 2</label>
              <input name="shipping_address_2" type="text" class="form-control"
                     value="{{ old('shipping_address_2', optional($savedShipping)->address_2) }}">
            </div>

            {{-- City --}}
            <div class="col-md-6">
              <label class="form-label">City/Town <span class="text-danger">*</span></label>
              <input name="shipping_city" type="text" class="form-control"
                     value="{{ old('shipping_city', optional($savedShipping)->city) }}" required>
              @error('shipping_city')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            {{-- State --}}
            <div class="col-md-6">
              <label class="form-label">State/Province</label>
              <input name="shipping_state" type="text" class="form-control"
                     value="{{ old('shipping_state', optional($savedShipping)->state) }}">
            </div>

            {{-- Postal Code --}}
            <div class="col-md-6">
              <label class="form-label">Postal/ZIP Code</label>
              <input name="shipping_postal_code" type="text" class="form-control"
                     value="{{ old('shipping_postal_code', optional($savedShipping)->zip) }}">
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
                    <option value="{{ $c->id }}" @selected(old('billing_country', optional($savedBilling)->country_id)==$c->id)>{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 mt-2">
                <label class="form-label">Address Line 1</label>
                <input name="billing_address_1" type="text" class="form-control"
                       value="{{ old('billing_address_1', optional($savedBilling)->address_1) }}">
              </div>

              <div class="col-12 mt-2">
                <label class="form-label">Address Line 2</label>
                <input name="billing_address_2" type="text" class="form-control"
                       value="{{ old('billing_address_2', optional($savedBilling)->address_2) }}">
              </div>

              <div class="col-md-6 mt-2">
                <label class="form-label">City/Town</label>
                <input name="billing_city" type="text" class="form-control"
                       value="{{ old('billing_city', optional($savedBilling)->city) }}">
              </div>

              <div class="col-md-6 mt-2">
                <label class="form-label">State/Province</label>
                <input name="billing_state" type="text" class="form-control"
                       value="{{ old('billing_state', optional($savedBilling)->state) }}">
              </div>

              <div class="col-md-6 mt-2">
                <label class="form-label">Postal/ZIP Code</label>
                <input name="billing_postal_code" type="text" class="form-control"
                       value="{{ old('billing_postal_code', optional($savedBilling)->zip) }}">
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

            <ul class="list-group mb-3">
              @foreach ($cart as $row)
                @php
                  $qty   = (int)($row['quantity'] ?? 1);
                  $unit  = (float)($row['price'] ?? 0);
                  $pid   = (int)($row['product_id'] ?? 0);
                  $type  = $row['product_type'] ?? ($productTypes[$pid] ?? null);
                  $isDigital = ($type === 'digital');

                  // Physical: use selected shipping profile snapshot; Digital: rate=0 and show "No shipping"
                  $prof  = $isDigital ? ['name' => 'No shipping (digital)', 'base_rate' => 0, 'additional_rate' => 0] : $getSelectedProfile($row);
                  $baseRate  = $isDigital ? 0.0 : (float)($prof['base_rate'] ?? 0);
                  $addRate   = $isDigital ? 0.0 : (float)($prof['additional_rate'] ?? 0);
                  $shipTotal = $isDigital ? 0.0 : $shippingCalculator($baseRate, $addRate, $qty);
                  $lineTotal = ($unit * $qty) + $shipTotal;

                  // Label (match cart/index formatting) — only meaningful for physical
                  if ($isDigital) {
                      $label = 'No shipping (digital)';
                  } else {
                      $service = $prof['service'] ?? null;
                      $dest    = $prof['dest_country_name'] ?? null;
                      if (!empty($prof['pickup_available']) && $baseRate <= 0) {
                          $label = 'Pickup / Collect in person';
                      } elseif ($service && $dest) {
                          $label = $service.' to '.$dest;
                      } elseif ($service && ($prof['dest_location_type'] ?? null) === 'everywhere_else') {
                          $label = $service.' (Worldwide)';
                      } elseif ($service) {
                          $label = $service;
                      } elseif (($prof['dest_location_type'] ?? null) === 'everywhere_else') {
                          $label = 'Everywhere';
                      } elseif ($dest) {
                          $label = 'Ship to '.$dest;
                      } else {
                          $label = $prof['name'] ?? 'Shipping';
                      }
                  }
                @endphp
                <li class="list-group-item d-flex justify-content-between">
                  <div>
                    <div class="fw-semibold">
                      {{ $row['name'] }}
                      @if($isDigital)
                        <span class="badge bg-secondary ms-1">Digital</span>
                      @endif
                    </div>
                    @if (!empty($row['variation_summary']))
                      <small class="text-muted">{{ $row['variation_summary'] }}</small><br>
                    @endif
                    <small>
                      {{ $label }} ({{ $currency }} {{ number_format($shipTotal,2) }})
                    </small>
                    <div class="mt-1">× {{ $qty }}</div>
                  </div>
                  <div>{{ $currency }} {{ number_format($lineTotal,2) }}</div>
                </li>
              @endforeach
            </ul>

            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal</span>
              <strong>{{ $currency }} {{ number_format($subtotal,2) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Shipping</span>
              <strong>{{ $currency }} {{ number_format($totalShipping,2) }}</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
              <span><strong>Total</strong></span>
              <strong>{{ $currency }} {{ number_format($grandTotal,2) }}</strong>
            </div>

            <div class="d-grid mt-4">
              <button type="submit" class="btn btn-success btn-lg w-100 w-md-auto">Place Your Order</button>
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
  // Submit protection and mobile sticky submit (bind after DOM is ready)
  document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('checkout-form');
    if (!form) return;
    form.addEventListener('submit', function(){
      const btns = form.querySelectorAll('button[type="submit"], button[data-submit-checkout]');
      btns.forEach(b => { b.disabled = true; b.classList.add('disabled'); });
    });
    const stickyBtn = document.getElementById('checkout-sticky-submit');
    if (stickyBtn) {
      stickyBtn.addEventListener('click', function(e){ e.preventDefault(); form.requestSubmit(); });
    }
  });
</script>

<!-- Sticky footer summary (mobile) -->
<div class="checkout-sticky-bar d-md-none">
  <div class="container d-flex align-items-center justify-content-between gap-3">
    <div>
      <div class="small text-muted">Total</div>
      <div id="grand-total-sticky-checkout" class="price">{{ $currency }} {{ number_format($grandTotal,2) }}</div>
    </div>
    <button id="checkout-sticky-submit" type="submit" form="checkout-form" class="btn btn-success btn-lg flex-grow-1" data-submit-checkout>
      Place Order
    </button>
  </div>
</div>
@endsection




