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
</style>

@php
  $user      = auth()->user();
  $cart      = session('cart', []);
  $currency  = get_currency();

  // Prefetch product types once (digital/physical) to decide shipping visibility/costs
  $productIds   = collect($cart)->pluck('product_id')->filter()->unique()->all();
  $productTypes = $productIds
      ? \App\Models\Product::whereIn('id', $productIds)->pluck('type','id')->toArray()
      : [];

  // Helper to read the selected profile snapshot from session (for PHYSICAL only)
  $getSelectedProfile = function(array $row) {
      $profiles = collect($row['shipping_profiles'] ?? []);
      $selId    = (int)($row['selected_shipping_profile_id'] ?? 0);
      $selected = $profiles->firstWhere('id', $selId);
      if (!$selected && $profiles->isNotEmpty()) {
          $selected = $profiles->first();
      }
      // Always return a consistent array shape
      return $selected ?: ['name' => 'Standard', 'base_rate' => 0];
  };

  // Subtotal (products only)
  $subtotal = collect($cart)->sum(fn($i) => ((float)($i['price'] ?? 0)) * (int)($i['quantity'] ?? 1));

  // Shipping total: zero for digital rows, selected profile * qty for physical rows
  $totalShipping = collect($cart)->sum(function($i) use ($getSelectedProfile, $productTypes) {
      $qty  = (int)($i['quantity'] ?? 1);
      $pid  = (int)($i['product_id'] ?? 0);
      $type = $i['product_type'] ?? ($productTypes[$pid] ?? null); // prefer cart snapshot if ever stored
      $isDigital = ($type === 'digital');

      if ($isDigital) return 0.0;

      $prof = $getSelectedProfile($i);
      return (float)($prof['base_rate'] ?? 0) * $qty;
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

            <ul class="list-group mb-3">
              @foreach ($cart as $row)
                @php
                  $qty   = (int)($row['quantity'] ?? 1);
                  $unit  = (float)($row['price'] ?? 0);
                  $pid   = (int)($row['product_id'] ?? 0);
                  $type  = $row['product_type'] ?? ($productTypes[$pid] ?? null);
                  $isDigital = ($type === 'digital');

                  // Physical: use selected shipping profile snapshot; Digital: rate=0 and show "No shipping"
                  $prof  = $isDigital ? ['name' => 'No shipping (digital)', 'base_rate' => 0] : $getSelectedProfile($row);
                  $rate  = (float)($prof['base_rate'] ?? 0);

                  // Label (match cart/index formatting) — only meaningful for physical
                  if ($isDigital) {
                      $label = 'No shipping (digital)';
                  } else {
                      $label = ($prof['dest_location_type'] ?? null) === 'everywhere_else'
                               ? 'Everywhere'
                               : (!empty($prof['dest_country_name'])
                                    ? 'Ship to '.$prof['dest_country_name']
                                    : ($prof['name'] ?? 'Shipping'));
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
                      {{ $label }} ({{ $currency }} {{ number_format($rate,2) }})
                    </small>
                    <div class="mt-1">× {{ $qty }}</div>
                  </div>
                  <div>{{ $currency }} {{ number_format(($unit + $rate) * $qty,2) }}</div>
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
</script>
@endsection
