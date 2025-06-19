{{-- resources/views/listings/checkout.blade.php --}}
@extends('layouts.app')
@section('title','Process Payment')

{{-- -------------------------------------------------------------------
 |  INLINE STYLES  (Bootstrap 5.3 utilities everywhere else)
-------------------------------------------------------------------- --}}
@section('styles')
<style>
    .checkout-page   { background:#f8f9fa;min-height:100vh }
    .card.glass      { backdrop-filter:blur(6px);background:rgba(255,255,255,.85);border-radius:12px }
    @media (prefers-color-scheme:dark){
        .card.glass{background:rgba(35,35,35,.55);}
    }
    .btn-primary     { background:#0275d8;border-color:#0275d8 }
    .btn-primary:hover{ background:#025aa5;border-color:#025aa5 }
</style>
@endsection

{{-- -------------------------------------------------------------------
 |  PHP HELPERS  – amount formatted exactly as PayPal expects
-------------------------------------------------------------------- --}}
@php
    $currency = $order->currency ?? 'USD';
    $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
                    'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

    $rawAmount    = (float) $order->category?->listing_fee;
    $paypalAmount = in_array($currency, $zeroDecimal)
        ? (string) intval(round($rawAmount))          // e.g. 70 → "70"
        : number_format($rawAmount, 2, '.', '');      // e.g. 70 → "70.00"
@endphp

{{-- -------------------------------------------------------------------
 |  MAIN CONTENT
-------------------------------------------------------------------- --}}
@section('content')
<div class="content checkout-page d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="card shadow-sm border-0 glass">
                    <div class="card-body p-5">

                        <h2 class="text-center fw-semibold mb-3">Process Your Payment</h2>
                        <p class="text-muted text-center mb-4">
                            Pay securely with PayPal or major credit / debit cards.
                        </p>

                        {{-- Amount display --}}
                        <div class="alert alert-info text-center fw-semibold">
                            Amount&nbsp;:&nbsp; {{ $currency }} {{ number_format($rawAmount, 2) }}
                        </div>

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

{{-- -------------------------------------------------------------------
 |  SCRIPTS  – jQuery + PayPal SDK + button config
-------------------------------------------------------------------- --}}
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

{{-- Sandbox key “sb” for dev; replace with real in .env / config --}}
<script src="https://www.paypal.com/sdk/js?client-id=sb&currency={{ $currency }}"></script>

<script>
$(function () {

    paypal.Buttons({

        style:{
            layout :'vertical',
            color  :'blue',
            shape  :'rect',
            label  :'paypal'
        },

        /* 1️⃣ Create PayPal order */
        createOrder: (_, actions) => {
            return actions.order.create({
                purchase_units:[{
                    amount:{ value:'{{ $paypalAmount }}' }
                }]
            });
        },

        /* 2️⃣ Capture & redirect */
        onApprove: (_, actions) => {
            $('#generic-result').empty();
            return actions.order.capture().then(() => {
                window.location = @json(route('success_deposit_fee',$order->id));
            });
        },

        /* 3️⃣ Error handler */
        onError: err => {
            console.error(err);
            $('#generic-result').text('PayPal error: ' + err.message);
        }

    }).render('#paypal-button-container');

});
</script>
@endsection
