{{-- resources/views/listings/checkout.blade.php --}}
@extends('layouts.app')
@section('title','Process Payment')

{{-- ──────────────────────────────────────────────
|  INLINE STYLES
└───────────────────────────────────────────── --}}
@section('styles')
<style>
  .checkout-page{background:#f8f9fa;min-height:100vh}
  .card.glass   {backdrop-filter:blur(6px);background:rgba(255,255,255,.85);border-radius:12px}
  @media (prefers-color-scheme:dark){.card.glass{background:rgba(35,35,35,.55)}}
  .btn-primary  {background:#0275d8;border-color:#0275d8}
  .btn-primary:hover{background:#025aa5;border-color:#025aa5}
</style>
@endsection

{{-- ──────────────────────────────────────────────
|  PHP HELPERS – amounts exactly as PayPal needs
└───────────────────────────────────────────── --}}
@php
  $currency    = $order->currency ?? 'USD';

  // PayPal "zero-decimal" currencies
  $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
                  'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

  // Base order total
  $orderTotal  = (float) $order->total_amount;

  // PayPal fee (e.g. 3.98 %)
  $transactionFeePercent = setting('paypal_transaction_fee_percent');
  $transactionFee        = round($orderTotal * $transactionFeePercent, 2);

  // Grand total the buyer pays
  $grandTotal            = $orderTotal + $transactionFee;

  // PayPal-formatted amount string
  $paypalAmount = in_array($currency,$zeroDecimal)
      ? (string) intval(round($grandTotal))
      : number_format($grandTotal, 2, '.', '');

  // Shopper wallet balance & ability to cover
  $walletBalance = wallet();
  $canPayWithWallet = $walletBalance >= $grandTotal;
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
              Pay securely with PayPal, a major card, or your in-site wallet.
            </p>

            {{-- ── Amount breakdown ───────────────────────────── --}}
            <ul class="list-group mb-4">
              <li class="list-group-item d-flex justify-content-between">
                <span>Order&nbsp;Total</span>
                <span>{{ $currency }} {{ number_format($orderTotal,2) }}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>PayPal&nbsp;Transaction&nbsp;Fee&nbsp;
                  <small class="text-muted">({{ $transactionFeePercent * 100 }} %)</small></span>
                <span>{{ $currency }} {{ number_format($transactionFee,2) }}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between fw-bold">
                <span>Amount&nbsp;Charged</span>
                <span>{{ $currency }} {{ number_format($grandTotal,2) }}</span>
              </li>
            </ul>

            {{-- ── 1️⃣  WALLET OPTION ─────────────────────────── --}}
            @if($walletBalance > 0)
              <form action="{{ route('order.wallet.pay', $order->id) }}"
                    method="POST"
                    class="d-grid gap-2 mb-3">
                @csrf
                <button type="submit"
                        class="btn btn-primary {{ $canPayWithWallet ? '' : 'disabled' }}"
                        @disabled(!$canPayWithWallet)>
                  Pay via Wallet
                  <small class="fw-normal">
                    ({{ $currency }} {{ number_format($walletBalance,2) }})
                  </small>
                </button>
              </form>

              @unless($canPayWithWallet)
                <div class="alert alert-warning small d-flex align-items-center gap-2 mb-3">
                  <i class="fas fa-exclamation-circle"></i>
                  <span>
                    Wallet balance is insufficient — please deposit funds, then click
                    “Pay via Wallet” again.
                  </span>
                </div>
                <a href="{{ route('wallet.deposit.form') }}"
                   class="btn btn-success d-flex align-items-center gap-2 mb-4">
                  <i class="fas fa-plus"></i>
                  Deposit&nbsp;Funds
                </a>
              @endunless
            @endif

            {{-- ── 2️⃣  PayPal / Card button ─────────────────── --}}
            <div id="paypal-button-container" class="text-center mb-3"></div>

            {{-- Error placeholder --}}
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

{{-- Sandbox key “sb” for dev; replace with live client-ID in production --}}
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
      createOrder: (_, actions) => actions.order.create({
          purchase_units:[{
              amount:{ value:'{{ $paypalAmount }}' }
          }]
      }),

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
