@extends('layouts.app')
@section('title','Subscription Payment')

@section('styles')
<style>
  .subpay{
    --sub-brand: #198754;
    --sub-text: #0f172a;
    --sub-muted: #64748b;
    --sub-border: rgba(15, 23, 42, .10);
    --sub-shadow: 0 16px 40px rgba(15, 23, 42, .08);
  }
  .subpay-hero{
    border: 1px solid var(--sub-border);
    border-radius: 1rem;
    background: linear-gradient(135deg, rgba(25,135,84,.10), rgba(32,201,151,.08));
    box-shadow: var(--sub-shadow);
  }
  .subpay-card{
    border-radius: 1rem;
    border: 1px solid var(--sub-border);
    box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
  }
  .subpay-badge{
    border-radius: 999px;
    padding: .35rem .65rem;
    font-weight: 700;
    border: 1px solid rgba(25,135,84,.22);
    background: rgba(25,135,84,.10);
    color: var(--sub-text);
  }
  .subpay-muted{ color: var(--sub-muted); }
  .subpay-kpi{
    border-radius: .9rem;
    border: 1px solid rgba(0,0,0,.08);
    background: rgba(255,255,255,.85);
  }
  .subpay-kpi .label{ color: var(--sub-muted); font-size: .85rem; }
  .subpay-kpi .value{ font-weight: 800; color: var(--sub-text); }
  .subpay-divider{ height: 1px; background: rgba(15, 23, 42, .08); }
  .subpay-methods .btn{ border-radius: .85rem; }
  .subpay-result{ min-height: 1.25rem; }
  .subpay-paypal{ border-radius: 1rem; border: 1px solid rgba(0,0,0,.08); padding: 1rem; background: rgba(248,250,252,.9); }
</style>
@endsection

@php
    $currency = 'USD';
    $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
                    'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

    $plan = session('selected_subscription_plan', request('plan', 'monthly'));

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
        ? (string) intval(round($rawAmount))
        : number_format($rawAmount, 2, '.', '');

    $walletBalance = wallet();
    $canPayWithWallet = $walletBalance >= $rawAmount;
    $topUpNeeded = max(0, (float) $rawAmount - (float) $walletBalance);

    $paypalAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('paypal') : true;
    $stripeAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('stripe') : true;
    $mpesaAvailable  = function_exists('payment_gateway_available') ? payment_gateway_available('mpesa') : true;

    $redirectToAfterTopup = route('seller.subscription.pay', ['plan' => $plan, 'autopay' => 1], false);
@endphp

@section('content')
<div class="content subpay">
  <div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">

      <div class="subpay-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3">
          <div>
            <div class="subpay-badge d-inline-flex align-items-center gap-2 mb-2">
              <i class="bi bi-shield-lock-fill"></i> Secure checkout
            </div>
            <h1 class="h3 mb-1" style="color: var(--sub-text)">Pay for {{ $planName }} plan</h1>
            <div class="subpay-muted">Complete payment using wallet, Stripe, M‑Pesa, or PayPal (when enabled).</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('seller.subscription') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back
            </a>
          </div>
        </div>
      </div>

      <div class="row g-3 g-lg-4">
        <div class="col-lg-5">
          <div class="card subpay-card border-0">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="small text-uppercase fw-semibold subpay-muted">Plan</div>
                  <div class="h5 mb-1">{{ $planName }} subscription</div>
                  <div class="subpay-muted small">Duration: {{ $duration }}</div>
                </div>
                <span class="badge rounded-pill text-bg-success">{{ $currency }}</span>
              </div>

              <div class="subpay-divider my-3"></div>

              <div class="subpay-kpi p-3 mb-2">
                <div class="label">Plan amount</div>
                <div class="value">{{ $currency }} {{ number_format($rawAmount, 2) }}</div>
              </div>

              <div class="subpay-kpi p-3 mb-2">
                <div class="label">Wallet balance</div>
                <div class="value">{{ $currency }} {{ number_format((float) $walletBalance, 2) }}</div>
              </div>

              <div class="subpay-kpi p-3">
                <div class="label">Amount due</div>
                <div class="value {{ $canPayWithWallet ? 'text-success' : 'text-danger' }}">
                  {{ $currency }} {{ number_format($rawAmount, 2) }}
                </div>
                @unless($canPayWithWallet)
                  <div class="small text-danger mt-1">Top up needed: {{ $currency }} {{ number_format($topUpNeeded, 2) }}</div>
                @endunless
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card subpay-card border-0">
            <div class="card-body p-4 p-md-5">
              <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                <div>
                  <div class="small text-uppercase fw-semibold subpay-muted">Payment</div>
                  <div class="h5 mb-0">Choose a method</div>
                </div>
                <span class="badge rounded-pill {{ $canPayWithWallet ? 'text-bg-success' : 'text-bg-warning' }}">
                  {{ $canPayWithWallet ? 'Wallet ready' : 'Top up required' }}
                </span>
              </div>

              <form id="wallet-pay-form" action="{{ route('seller.subscription.wallet.pay') }}" method="POST" class="mb-3">
                @csrf
                <input type="hidden" name="plan" value="{{ $plan }}">
                <button type="submit" class="btn btn-success w-100 py-2 {{ $canPayWithWallet ? '' : 'disabled' }}" @disabled(!$canPayWithWallet)>
                  <i class="bi bi-wallet2 me-1"></i> Pay with Wallet
                </button>
                @unless($canPayWithWallet)
                  <div class="small subpay-muted mt-2">
                    Wallet balance is not enough. Top up then this button will auto‑enable.
                  </div>
                @endunless
              </form>

              @unless($canPayWithWallet)
                <div class="subpay-methods d-grid gap-2 mb-3">
                  @if($stripeAvailable)
                    <button type="button" id="btn-sub-stripe" class="btn btn-dark py-2">
                      <i class="bi bi-credit-card-2-front me-1"></i> Pay with Stripe
                    </button>
                  @endif
                  @if($mpesaAvailable)
                    <button type="button" id="btn-sub-mpesa-toggle" class="btn btn-outline-success py-2">
                      <i class="bi bi-phone-vibrate me-1"></i> Pay with M‑Pesa (STK)
                    </button>
                  @endif
                  <a href="{{ route('wallet.deposit.form', ['redirect_to' => $redirectToAfterTopup, 'amount' => $topUpNeeded], false) }}"
                     class="btn btn-outline-secondary py-2">
                    <i class="bi bi-plus-circle me-1"></i> Top up wallet (all methods)
                  </a>
                </div>

                @if($mpesaAvailable)
                  <div id="mpesa-sub-section" class="subpay-kpi p-3 d-none mb-3">
                    <div class="fw-semibold mb-1">M‑Pesa STK Push</div>
                    <div class="small subpay-muted mb-2">Enter Safaricom number (07XXXXXXXX / 2547XXXXXXXX).</div>
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
                    <div id="sub-stk-live-status" class="alert mt-3 d-none mb-0"></div>
                  </div>
                @endif
              @endunless

              @if($paypalAvailable)
                <div class="subpay-divider my-4"></div>
                <div class="fw-semibold mb-2">PayPal / Card</div>
                <div class="subpay-paypal">
                  <div id="paypal-button-container" class="text-center"></div>
                </div>
              @else
                <div class="alert alert-warning mt-3 mb-0">
                  PayPal is currently disabled. Please use wallet, Stripe, or M‑Pesa.
                </div>
              @endif

              <div id="generic-result" class="subpay-result text-center mt-3 fw-semibold text-danger"></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

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
    $('#btn-sub-mpesa-toggle').on('click', function(){
        $('#mpesa-sub-section').toggleClass('d-none');
        updateKesPreview();
    });

    const USD_TO_KES = parseFloat(@json((string) (float) env('USD_TO_KES', 130)));
    function normalizeMsisdn(raw) {
        if (!raw) return null;
        let p = String(raw).replace(/\D/g, '');
        if (p.startsWith('0') && p.length === 10) p = '254' + p.substring(1);
        else if (p.startsWith('7') && p.length === 9) p = '254' + p;
        if (/^2547\d{8}$/.test(p)) return p;
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
             .html('<i class="fa fa-sync-alt fa-spin me-2"></i>Waiting for M‑Pesa confirmation... (this can take up to 2 minutes)');

        subPollTimer = setInterval(function(){
            attempts++;
            $.get(@json(route('wallet.deposit.mpesa.status', '__REF__')).replace('__REF__', encodeURIComponent(ref)), function(resp){
                const msg = resp?.message || '';
                if (resp?.status === 'success') {
                    clearInterval(subPollTimer);
                    $live.removeClass('alert-warning alert-danger').addClass('alert-success')
                         .html('<i class="fa fa-check-circle me-2"></i>Top up successful. Activating your subscription...');
                    setTimeout(() => document.getElementById('wallet-pay-form')?.submit(), 1200);
                    return;
                }
                if (resp?.status === 'failed') {
                    clearInterval(subPollTimer);
                    $live.removeClass('alert-warning alert-success').addClass('alert-danger')
                         .html('<i class="fa fa-exclamation-triangle me-2"></i>Payment failed: ' + (msg || 'Unknown error') + '.');
                }
            });

            if (attempts >= MAX_POLLS) {
                clearInterval(subPollTimer);
                $live.removeClass('alert-success alert-danger').addClass('alert-warning')
                     .html('<i class="fa fa-hourglass-half me-2"></i>It\'s taking longer than expected to confirm. If you\'ve approved the prompt, please try again shortly.');
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
        style:{ layout :'vertical', color  :'blue', shape  :'rect', label  :'paypal' },
        createOrder: (_, actions) => {
            return actions.order.create({ purchase_units:[{ amount:{ value:'{{ $paypalAmount }}' } }] });
        },
        onApprove: (_, actions) => {
            $('#generic-result').empty();
            return actions.order.capture().then(() => {
                window.location = @json(route('seller.subscription.success', auth()->id())) + '?plan={{ $plan }}';
            });
        },
        onError: err => {
            console.error(err);
            $('#generic-result').text('PayPal error: ' + err.message);
        }
    }).render('#paypal-button-container');
@endif
});
</script>
@endsection

