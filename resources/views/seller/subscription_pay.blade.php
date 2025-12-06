{{-- resources/views/seller/subscription_pay.blade.php --}}
@extends('layouts.app')
@section('title','Process Subscription Payment')

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
    $currency = 'USD';
    $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
                    'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

    // Get the selected plan from session or request
    $plan = session('selected_subscription_plan', request('plan', 'monthly'));
    
    // Calculate subscription fee based on plan
    if ($plan === 'yearly') {
        $rawAmount = (float) config('subscription.yearly_fee', 50);
        $planName = 'Yearly';
        $duration = '1 year';
    } else {
        $rawAmount = (float) config('subscription.monthly_fee', 5);
        $planName = 'Monthly';
        $duration = '1 month';
    }
    
    $paypalAmount = in_array($currency, $zeroDecimal)
        ? (string) intval(round($rawAmount))          // e.g. 70 → "70"
        : number_format($rawAmount, 2, '.', '');      // e.g. 70 → "70.00"

    // Shopper wallet balance & ability to cover
    $walletBalance = wallet();
    $canPayWithWallet = $walletBalance >= $rawAmount;
    $topUpNeeded = max(0, (float)$rawAmount - (float)$walletBalance);
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

                        <h2 class="text-center fw-semibold mb-3">Process Subscription Payment</h2>
                        <p class="text-muted text-center mb-4">
                            Pay securely with PayPal, a major card, or your in-site wallet.
                        </p>

                        {{-- Plan and Amount display --}}
                        <div class="alert alert-info">
                            <div class="text-center mb-2">
                                <div class="fw-semibold">{{ $planName }} Plan</div>
                                <div class="small text-muted">Duration: {{ $duration }}</div>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between">
                                    <span>Plan Amount</span>
                                    <span>{{ $currency }} {{ number_format($rawAmount, 2) }}</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span>Wallet Balance</span>
                                    <span>{{ $currency }} {{ number_format((float)$walletBalance, 2) }}</span>
                                </li>
                                <li class="d-flex justify-content-between fw-semibold">
                                    <span>Amount Due</span>
                                    <span class="{{ $canPayWithWallet ? 'text-success' : 'text-danger' }}">{{ $currency }} {{ number_format($rawAmount, 2) }}</span>
                                </li>
                                @unless($canPayWithWallet)
                                    <li class="d-flex justify-content-between">
                                        <span>Top Up Needed</span>
                                        <span class="text-danger">{{ $currency }} {{ number_format($topUpNeeded, 2) }}</span>
                                    </li>
                                @endunless
                            </ul>
                        </div>

                        {{-- ── 1️⃣  WALLET OPTION ─────────────────────────── --}}
                        @if($walletBalance > 0)
                          <form action="{{ route('seller.subscription.wallet.pay') }}"
                                method="POST"
                                class="d-grid gap-2 mb-3">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $plan }}">
                            <button type="submit"
                                    class="btn btn-primary {{ $canPayWithWallet ? '' : 'disabled' }}"
                                    @disabled(!$canPayWithWallet)>
                              Pay via Wallet
                            </button>
                          </form>

                          @unless($canPayWithWallet)
                            <div class="alert alert-warning small d-flex align-items-center gap-2 mb-3">
                              <i class="fas fa-exclamation-circle"></i>
                              <span>
                                Wallet balance is insufficient — please deposit funds, then click
                                "Pay via Wallet" again.
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

{{-- Use project PayPal credentials (fall back to sandbox key locally) --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') ?: 'sb' }}&currency={{ $currency }}"></script>

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
                window.location = @json(route('seller.subscription.success', auth()->id())) + '?plan={{ $plan }}';
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
