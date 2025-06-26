{{-- resources/views/listings/checkout.blade.php --}}
@extends('layouts.app')
@section('title','Process Payment')

{{-- ──────────────────────────────────────────────
|  INLINE STYLES
└───────────────────────────────────────────── --}}
@section('styles')
<style>
  .checkout-page {background:#f8f9fa;min-height:100vh}
  .card.glass    {backdrop-filter:blur(6px);background:rgba(255,255,255,.85);border-radius:12px}
  @media (prefers-color-scheme:dark){.card.glass{background:rgba(35,35,35,.55)}}
  .btn-primary   {background:#0275d8;border-color:#0275d8}
  .btn-primary:hover{background:#025aa5;border-color:#025aa5}
</style>
@endsection

{{-- ──────────────────────────────────────────────
|  PHP HELPERS – amounts exactly as PayPal needs
└───────────────────────────────────────────── --}}
@php
  $currency   = $order->currency ?? 'USD';

  // PayPal "zero-decimal" currencies
  $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
                  'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

  // Base order total
  $orderTotal = (float) $order->total_amount;

  // PayPal fee (example 3.98 % -> 0.0398) – adjust if your account has a better rate
  $transactionFeePercent = setting('paypal_transaction_fee_percent');
  $transactionFee        = round($orderTotal * $transactionFeePercent, 2);

  // Grand total charged to the buyer
  $grandTotal            = $orderTotal + $transactionFee;

  // Amount string PayPal expects
  $paypalAmount = in_array($currency, $zeroDecimal)
      ? (string) intval(round($grandTotal))
      : number_format($grandTotal, 2, '.', '');
@endphp

{{-- ──────────────────────────────────────────────
|  MAIN CONTENT
└───────────────────────────────────────────── --}}
@section('content')
<div class="content checkout-page d-flex align-items-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">

        <div class="card shadow-sm border-0 glass">
          <div class="card-body p-5">

            <h2 class="text-center fw-semibold mb-3">Process Your Payment</h2>
            <p class="text-muted text-center mb-4">
              Pay securely with PayPal or a major credit / debit card.
            </p>

            {{-- ── Amount breakdown ───────────────────────────── --}}
            <ul class="list-group mb-4">
              <li class="list-group-item d-flex justify-content-between">
                <span>Order&nbsp;Total</span>
                <span>{{ $currency }} {{ number_format($orderTotal,2) }}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>PayPal&nbsp;Transaction&nbsp;Fee&nbsp;<small class="text-muted">(3.98 %)</small></span>
                <span>{{ $currency }} {{ number_format($transactionFee,2) }}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between fw-bold">
                <span>Amount&nbsp;Charged</span>
                <span>{{ $currency }} {{ number_format($grandTotal,2) }}</span>
              </li>
            </ul>

            {{-- PayPal / Card button --}}
            <div id="paypal-button-container" class="text-center mb-3"></div>

            {{-- Result / error placeholder --}}
            <div id="generic-result" class="text-center mt-3 fw-semibold text-danger"></div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

{{-- ──────────────────────────────────────────────
|  SCRIPTS – jQuery + PayPal SDK
└───────────────────────────────────────────── --}}
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

{{-- Sandbox key “sb” for dev; replace with real client-ID in production --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ setting('paypal_client_id') }}&currency={{ $currency }}"></script>

<script>
$(function () {

  paypal.Buttons({

    /* Styling */
    style:{
      layout :'vertical',
      color  :'blue',
      shape  :'rect',
      label  :'paypal'
    },

    /* 1️⃣  Create PayPal order */
    createOrder: (_, actions) => {
      return actions.order.create({
        purchase_units:[{
          amount:{ value:'{{ $paypalAmount }}' }
        }]
      });
    },

    /* 2️⃣  Capture & redirect */
    onApprove: (_, actions) => {
      $('#generic-result').empty();
      return actions.order.capture().then(() => {
        window.location = @json(route('success_deposit',$order->id));
      });
    },

    /* 3️⃣  Error handler */
    onError: err => {
      console.error(err);
      $('#generic-result').text('PayPal error: ' + (err.message || 'Unexpected error'));
    }

  }).render('#paypal-button-container');

});
</script>
@endsection
