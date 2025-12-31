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

    $paypalAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('paypal') : true;
    $stripeAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('stripe') : true;
    $mpesaAvailable  = function_exists('payment_gateway_available') ? payment_gateway_available('mpesa') : true;

    $redirectToAfterTopup = route('seller.subscription.pay', ['plan' => $plan, 'autopay' => 1], false);
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
                            Pay securely with wallet, Stripe, M-Pesa, or PayPal (when enabled).
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
                          <form id="wallet-pay-form" action="{{ route('seller.subscription.wallet.pay') }}"
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

                            <div class="d-grid gap-2 mb-3">
                              @if($stripeAvailable)
                                <button type="button" id="btn-sub-stripe" class="btn btn-dark">
                                  Pay with Stripe
                                </button>
                              @endif
                              @if($mpesaAvailable)
                                <button type="button" id="btn-sub-mpesa-toggle" class="btn btn-outline-success">
                                  Pay with M-Pesa (STK)
                                </button>
                              @endif
                              <a href="{{ route('wallet.deposit.form', ['redirect_to' => $redirectToAfterTopup, 'amount' => $topUpNeeded], false) }}"
                                 class="btn btn-outline-secondary">
                                Top up wallet (all methods)
                              </a>
                            </div>

                            @if($mpesaAvailable)
                              <div id="mpesa-sub-section" class="border rounded p-3 mb-3 d-none">
                                <div class="mb-2 fw-semibold">M-Pesa STK Push</div>
                                <div class="mb-2 small text-muted">Enter Safaricom number (07XXXXXXXX / 2547XXXXXXXX).</div>
                                <div class="row g-2 align-items-end">
                                  <div class="col-12 col-md-7">
                                    <label class="form-label mb-1">Phone</label>
                                    <input type="text" id="mpesa_sub_phone" class="form-control" placeholder="07XXXXXXXX">
                                  </div>
                                  <div class="col-12 col-md-5">
                                    <label class="form-label mb-1">KES (estimate)</label>
                                    <input type="text" id="mpesa_sub_kes_preview" class="form-control" readonly>
                                  </div>
                                </div>
                                <div class="d-grid mt-3">
                                  <button type="button" id="btn-sub-start-stk" class="btn btn-success">
                                    <span id="sub-stk-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                    Send STK Push
                                  </button>
                                </div>
                                <div id="sub-stk-live-status" class="alert mt-3 d-none"></div>
                              </div>
                            @endif
                          @endunless

                        {{-- ── 2️⃣  PayPal / Card button ─────────────────── --}}
                        @if($paypalAvailable)
                          <div id="paypal-button-container" class="text-center mb-3"></div>
                        @else
                          <div class="alert alert-warning text-center mb-3">
                            PayPal is currently disabled. Please use your wallet or contact support.
                          </div>
                        @endif

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
@if($paypalAvailable)
@php $ppClient = config('services.paypal.client_id') ?: (function_exists('setting') ? (setting('paypal_client_id') ?: 'sb') : 'sb'); @endphp
<script src="https://www.paypal.com/sdk/js?client-id={{ $ppClient }}&currency={{ $currency }}"></script>
@endif

<script>
$(function () {
    const canPayWithWallet = @json($canPayWithWallet);
    const shouldAutoPay = @json((bool) request()->query('autopay'));
    if (shouldAutoPay && canPayWithWallet) {
        const form = document.getElementById('wallet-pay-form');
        if (form) form.submit();
        return;
    }

    const topUpNeeded = Number(@json($topUpNeeded));
    const redirectToAfterTopup = @json($redirectToAfterTopup);

    @if($stripeAvailable)
    // Stripe topup for subscription (then auto-pay via wallet on return)
    $('#btn-sub-stripe').on('click', function(){
        if (!topUpNeeded || topUpNeeded <= 0) return;
        const $btn = $(this);
        $btn.prop('disabled', true).addClass('disabled').text('Redirecting to Stripe...');
        $('#generic-result').removeClass('text-danger text-success').text('');

        $.post(@json(route('wallet.deposit.stripe.session')), {
            _token: @json(csrf_token()),
            amount: topUpNeeded,
            currency: 'USD',
            redirect_to: redirectToAfterTopup
        }, function(resp){
            if (resp?.success && resp?.url) {
                window.location = resp.url;
            } else {
                $btn.prop('disabled', false).removeClass('disabled').text('Pay with Stripe');
                $('#generic-result').addClass('text-danger').text(resp?.message || 'Unable to start Stripe checkout.');
            }
        }).fail(function(xhr){
            $btn.prop('disabled', false).removeClass('disabled').text('Pay with Stripe');
            $('#generic-result').addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        });
    });
    @endif

    @if($mpesaAvailable)
    // M-Pesa STK topup for subscription (then auto-pay via wallet)
    $('#btn-sub-mpesa-toggle').on('click', function(){
        $('#mpesa-sub-section').toggleClass('d-none');
        updateKesPreview();
    });

    const USD_TO_KES = parseFloat(@json((string) (float) env('USD_TO_KES', 130)));
    function normalizeMsisdn(raw) {
        if (!raw) return null;
        let p = String(raw).replace(/\\D/g, '');
        if (p.startsWith('0') && p.length === 10) p = '254' + p.substring(1);
        else if (p.startsWith('7') && p.length === 9) p = '254' + p;
        if (/^2547\\d{8}$/.test(p)) return p;
        return null;
    }
    function updateKesPreview(){
        const kes = Math.ceil((topUpNeeded || 0) * USD_TO_KES);
        $('#mpesa_sub_kes_preview').val('KES ' + kes.toFixed(2));
    }

    let subPollTimer = null;
    const POLL_INTERVAL_MS = 3000;
    const MAX_POLLS = 40;
    function startPolling(ref) {
        let attempts = 0;
        clearInterval(subPollTimer);
        const $live = $('#sub-stk-live-status');
        $live.removeClass('d-none alert-danger alert-success alert-warning')
             .addClass('alert alert-warning')
             .html('<i class=\"fa fa-sync-alt fa-spin me-2\"></i>Waiting for M-Pesa confirmation... (this can take up to 2 minutes)');

        subPollTimer = setInterval(function(){
            attempts++;
            $.get(@json(route('wallet.deposit.mpesa.status', '__REF__')).replace('__REF__', encodeURIComponent(ref)), function(resp){
                const msg = resp?.message || '';
                if (resp?.status === 'success') {
                    clearInterval(subPollTimer);
                    $live.removeClass('alert-warning alert-danger').addClass('alert-success')
                         .html('<i class=\"fa fa-check-circle me-2\"></i>Top up successful. Activating your subscription...');
                    setTimeout(() => document.getElementById('wallet-pay-form')?.submit(), 1200);
                    return;
                }
                if (resp?.status === 'failed') {
                    clearInterval(subPollTimer);
                    $live.removeClass('alert-warning alert-success').addClass('alert-danger')
                         .html('<i class=\"fa fa-exclamation-triangle me-2\"></i>Payment failed: ' + (msg || 'Unknown error') + '.');
                }
            });

            if (attempts >= MAX_POLLS) {
                clearInterval(subPollTimer);
                $live.removeClass('alert-success alert-danger').addClass('alert-warning')
                     .html('<i class=\"fa fa-hourglass-half me-2\"></i>It\\'s taking longer than expected to confirm. If you\\'ve approved the prompt, please try again shortly.');
            }
        }, POLL_INTERVAL_MS);
    }

    $('#btn-sub-start-stk').on('click', function(){
        if (!topUpNeeded || topUpNeeded <= 0) return;
        const phone = normalizeMsisdn($('#mpesa_sub_phone').val());
        if (!phone) {
            $('#generic-result').addClass('text-danger').text('Enter a valid Safaricom number (07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX).');
            return;
        }
        const $btn = $(this);
        $('#sub-stk-spinner').removeClass('d-none');
        $btn.prop('disabled', true);
        $('#generic-result').removeClass('text-danger text-success').text('');

        $.post(@json(route('wallet.deposit.mpesa.stk')), {
            _token: @json(csrf_token()),
            phone: phone,
            usd_amount: topUpNeeded
        }, function(resp){
            if (resp?.success && resp?.ref) {
                $('#generic-result').addClass('text-success').text('STK Push sent. Check your phone and approve.');
                startPolling(resp.ref);
            } else {
                $('#generic-result').addClass('text-danger').text(resp?.message || 'Failed to start STK Push.');
            }
        }).fail(function(xhr){
            $('#generic-result').addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        }).always(function(){
            $('#sub-stk-spinner').addClass('d-none');
            $btn.prop('disabled', false);
        });
    });
    @endif

@if($paypalAvailable)
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
@endif

});
</script>
@endsection
