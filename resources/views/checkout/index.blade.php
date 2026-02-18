{{-- resources/views/checkout/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title','Checkout')

@section('main')
@php
  $user      = auth()->user();
  $cart      = session('cart', []);
  $currency  = get_currency();

  $savedShipping = null;
  $savedBilling  = null;
  try {
    if ($user) {
      $savedShipping = \App\Models\Address::where('user_id',$user->id)->where('type','shipping')->first();
      $savedBilling  = \App\Models\Address::where('user_id',$user->id)->where('type','billing')->first();
    }
  } catch (\Throwable $e) { /* ignore */ }

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

  $getSelectedProfile = function(array $row) {
      $profiles = collect($row['shipping_profiles'] ?? []);
      $selId    = (int)($row['selected_shipping_profile_id'] ?? 0);
      $selected = $profiles->firstWhere('id', $selId);
      if (!$selected && $profiles->isNotEmpty()) {
          $selected = $profiles->first();
      }
      return $selected ?: ['name' => 'Standard', 'base_rate' => 0, 'additional_rate' => 0];
  };

  $subtotal = collect($cart)->sum(fn($i) => ((float)($i['price'] ?? 0)) * (int)($i['quantity'] ?? 1));

  $totalShipping = collect($cart)->sum(function($i) use ($getSelectedProfile, $productTypes, $shippingCalculator) {
      $qty  = (int)($i['quantity'] ?? 1);
      $pid  = (int)($i['product_id'] ?? 0);
      $type = $i['product_type'] ?? ($productTypes[$pid] ?? null);
      $isDigital = ($type === 'digital');

      if ($isDigital) return 0.0;

      $prof = $getSelectedProfile($i);
      $baseRate = (float)($prof['base_rate'] ?? 0);
      $addRate  = (float)($prof['additional_rate'] ?? 0);
      return $shippingCalculator($baseRate, $addRate, $qty);
  });

  $grandTotal = $subtotal + $totalShipping;

  $labelClass = 'mb-1 block text-sm font-semibold text-slate-700';
  $inputClass = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none';
  $errorClass = 'mt-1 text-xs font-medium text-rose-600';
@endphp

<section class="checkout-page bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 pb-28 sm:px-6 md:pb-8">
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold">There were some problems with your input:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('error_exception'))
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold">Technical error:</p>
        <pre class="mt-2 overflow-x-auto whitespace-pre-wrap rounded-lg bg-white/70 p-3 text-xs">{{ session('error_exception') }}</pre>
      </div>
    @endif

    @if (!empty($cart))
      <form id="checkout-form" action="{{ route('store_order') }}" method="POST" class="grid gap-6 lg:grid-cols-12" novalidate>
        @csrf

        <div class="space-y-4 lg:col-span-7">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h2 class="text-xl font-bold text-slate-900">Billing and Shipping Details</h2>
            <p class="mt-1 text-sm text-slate-500">Review your contact details and delivery address before placing the order.</p>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="{{ $labelClass }}">Full Name <span class="text-rose-600">*</span></label>
                <input name="full_name" type="text" class="{{ $inputClass }}" value="{{ old('full_name', $user->name ?? '') }}" required>
                @error('full_name')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
              </div>

              <div>
                <label class="{{ $labelClass }}">Email <span class="text-rose-600">*</span></label>
                <input name="email" type="email" class="{{ $inputClass }}" value="{{ old('email', $user->email ?? '') }}" required>
                @error('email')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
              </div>

              <div>
                <label class="{{ $labelClass }}">Phone <span class="text-rose-600">*</span></label>
                <input name="phone" type="tel" class="{{ $inputClass }}" value="{{ old('phone', $user->phone ?? '') }}" required>
                @error('phone')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
              </div>

              <div>
                <label class="{{ $labelClass }}">Country <span class="text-rose-600">*</span></label>
                <select name="shipping_country" class="{{ $inputClass }}" required>
                  <option value="">Select Country</option>
                  @foreach(\App\Models\Country::orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}" @selected(old('shipping_country', optional($savedShipping)->country_id ?? ($user->country_id ?? '')) == $c->id)>
                      {{ $c->name }}
                    </option>
                  @endforeach
                </select>
                @error('shipping_country')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
              </div>

              <div class="md:col-span-2">
                <label class="{{ $labelClass }}">Address Line 1 <span class="text-rose-600">*</span></label>
                <input name="shipping_address_1" type="text" class="{{ $inputClass }}" value="{{ old('shipping_address_1', optional($savedShipping)->address_1) }}" required>
                @error('shipping_address_1')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
              </div>

              <div class="md:col-span-2">
                <label class="{{ $labelClass }}">Address Line 2</label>
                <input name="shipping_address_2" type="text" class="{{ $inputClass }}" value="{{ old('shipping_address_2', optional($savedShipping)->address_2) }}">
              </div>

              <div>
                <label class="{{ $labelClass }}">City/Town <span class="text-rose-600">*</span></label>
                <input name="shipping_city" type="text" class="{{ $inputClass }}" value="{{ old('shipping_city', optional($savedShipping)->city) }}" required>
                @error('shipping_city')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
              </div>

              <div>
                <label class="{{ $labelClass }}">State/Province</label>
                <input name="shipping_state" type="text" class="{{ $inputClass }}" value="{{ old('shipping_state', optional($savedShipping)->state) }}">
              </div>

              <div>
                <label class="{{ $labelClass }}">Postal/ZIP Code</label>
                <input name="shipping_postal_code" type="text" class="{{ $inputClass }}" value="{{ old('shipping_postal_code', optional($savedShipping)->zip) }}">
              </div>

              <div class="md:col-span-2 border-t border-slate-200 pt-4">
                <input type="hidden" name="billing_same_as_shipping" value="0">
                <label for="billing_same" class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-700">
                  <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" id="billing_same" name="billing_same_as_shipping" value="1" {{ old('billing_same_as_shipping','1')=='1' ? 'checked' : '' }} onchange="toggleBillingAddress(this.checked)">
                  Billing address same as shipping
                </label>
              </div>

              <div id="billing_address_fields" class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4 {{ old('billing_same_as_shipping','1')=='1' ? 'hidden' : '' }}">
                <h3 class="text-base font-semibold text-slate-900">Billing Address</h3>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label class="{{ $labelClass }}">Country</label>
                    <select name="billing_country" class="{{ $inputClass }}">
                      <option value="">Select Country</option>
                      @foreach(\App\Models\Country::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" @selected(old('billing_country', optional($savedBilling)->country_id)==$c->id)>{{ $c->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Address Line 1</label>
                    <input name="billing_address_1" type="text" class="{{ $inputClass }}" value="{{ old('billing_address_1', optional($savedBilling)->address_1) }}">
                  </div>

                  <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Address Line 2</label>
                    <input name="billing_address_2" type="text" class="{{ $inputClass }}" value="{{ old('billing_address_2', optional($savedBilling)->address_2) }}">
                  </div>

                  <div>
                    <label class="{{ $labelClass }}">City/Town</label>
                    <input name="billing_city" type="text" class="{{ $inputClass }}" value="{{ old('billing_city', optional($savedBilling)->city) }}">
                  </div>

                  <div>
                    <label class="{{ $labelClass }}">State/Province</label>
                    <input name="billing_state" type="text" class="{{ $inputClass }}" value="{{ old('billing_state', optional($savedBilling)->state) }}">
                  </div>

                  <div>
                    <label class="{{ $labelClass }}">Postal/ZIP Code</label>
                    <input name="billing_postal_code" type="text" class="{{ $inputClass }}" value="{{ old('billing_postal_code', optional($savedBilling)->zip) }}">
                  </div>
                </div>
              </div>

              <div class="md:col-span-2 border-t border-slate-200 pt-4">
                <label class="{{ $labelClass }}">Order Notes</label>
                <textarea name="order_notes" rows="3" class="{{ $inputClass }}">{{ old('order_notes') }}</textarea>
              </div>

              <div class="md:col-span-2">
                <label class="{{ $labelClass }}">Promo Code</label>
                <input name="promo_code" type="text" class="{{ $inputClass }}" value="{{ old('promo_code') }}">
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-4 lg:col-span-5">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-24 md:p-5">
            <h2 class="text-xl font-bold text-slate-900">Your Order</h2>

            <ul class="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-slate-50">
              @foreach ($cart as $row)
                @php
                  $qty   = (int)($row['quantity'] ?? 1);
                  $unit  = (float)($row['price'] ?? 0);
                  $pid   = (int)($row['product_id'] ?? 0);
                  $type  = $row['product_type'] ?? ($productTypes[$pid] ?? null);
                  $isDigital = ($type === 'digital');

                  $prof  = $isDigital ? ['name' => 'No shipping (digital)', 'base_rate' => 0, 'additional_rate' => 0] : $getSelectedProfile($row);
                  $baseRate  = $isDigital ? 0.0 : (float)($prof['base_rate'] ?? 0);
                  $addRate   = $isDigital ? 0.0 : (float)($prof['additional_rate'] ?? 0);
                  $shipTotal = $isDigital ? 0.0 : $shippingCalculator($baseRate, $addRate, $qty);
                  $lineTotal = ($unit * $qty) + $shipTotal;

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
                <li class="flex items-start justify-between gap-3 px-4 py-3">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">
                      {{ $row['name'] }}
                      @if($isDigital)
                        <span class="ml-1 rounded-full border border-slate-300 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">Digital</span>
                      @endif
                    </p>
                    @if (!empty($row['variation_summary']))
                      <p class="mt-1 text-xs text-slate-500">{{ $row['variation_summary'] }}</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-600">{{ $label }} ({{ $currency }} {{ number_format($shipTotal,2) }})</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">x {{ $qty }}</p>
                  </div>
                  <div class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ $currency }} {{ number_format($lineTotal,2) }}</div>
                </li>
              @endforeach
            </ul>

            <div class="mt-4 space-y-2 text-sm">
              <div class="flex items-center justify-between text-slate-700">
                <span>Subtotal</span>
                <span class="font-semibold">{{ $currency }} {{ number_format($subtotal,2) }}</span>
              </div>
              <div class="flex items-center justify-between text-slate-700">
                <span>Shipping</span>
                <span class="font-semibold">{{ $currency }} {{ number_format($totalShipping,2) }}</span>
              </div>
              <div class="border-t border-slate-200 pt-3">
                <div class="flex items-center justify-between text-base font-bold text-slate-900">
                  <span>Total</span>
                  <span>{{ $currency }} {{ number_format($grandTotal,2) }}</span>
                </div>
              </div>
            </div>

            <button type="submit" class="mt-5 hidden w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-emerald-300 md:inline-flex">
              Place Your Order
            </button>
          </div>
        </div>
      </form>
    @else
      <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <h2 class="text-2xl font-bold text-slate-900">Your cart is empty</h2>
        <p class="mt-2 text-sm text-slate-500">Looks like you have not added anything yet.</p>
        <a href="{{ route('listings') }}" class="mt-5 inline-flex rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
          Continue Shopping
        </a>
      </div>
    @endif
  </div>
</section>

@if (!empty($cart))
  <div class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 p-3 shadow-[0_-8px_20px_rgba(15,23,42,0.08)] backdrop-blur md:hidden">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-1 sm:px-4">
      <div>
        <p class="text-[11px] uppercase tracking-wide text-slate-400">Total</p>
        <p id="grand-total-sticky-checkout" class="text-sm font-bold text-slate-900">{{ $currency }} {{ number_format($grandTotal,2) }}</p>
      </div>
      <button id="checkout-sticky-submit" type="submit" form="checkout-form" class="inline-flex flex-1 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-emerald-300" data-submit-checkout>
        Place Order
      </button>
    </div>
  </div>
@endif

<script>
  function toggleBillingAddress(checked) {
    const box = document.getElementById('billing_address_fields');
    if (!box) return;
    box.classList.toggle('hidden', !!checked);
  }

  document.addEventListener('DOMContentLoaded', function(){
    const billingSame = document.getElementById('billing_same');
    if (billingSame) {
      toggleBillingAddress(billingSame.checked);
    }

    const form = document.getElementById('checkout-form');
    if (!form) return;

    form.addEventListener('submit', function(){
      const btns = form.querySelectorAll('button[type="submit"], button[data-submit-checkout]');
      btns.forEach(function(btn){
        btn.disabled = true;
      });
    });

    const stickyBtn = document.getElementById('checkout-sticky-submit');
    if (stickyBtn) {
      stickyBtn.addEventListener('click', function(e){
        e.preventDefault();
        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit();
        } else {
          form.submit();
        }
      });
    }
  });
</script>
@endsection
