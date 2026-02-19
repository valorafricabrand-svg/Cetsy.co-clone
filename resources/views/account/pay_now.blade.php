{{-- resources/views/listings/checkout.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Process Payment')

{{-- ----------------------------------------------
|  INLINE STYLES
---------------------------------------------- --}}
@section('styles')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');

  .checkout-page{
    --ink:#0f172a;
    --muted:#64748b;
    --brand:#0ea5a4;
    --brand-strong:#0f766e;
    --accent:#f59e0b;
    --card:#ffffff;
    --card-soft:#f8fafc;
    --line:rgba(15,23,42,.12);
    --shadow:0 22px 60px rgba(15,23,42,.12);
    font-family:"Instrument Sans","Space Grotesk",sans-serif;
    color:var(--ink);
    min-height:100vh;
    position:relative;
    padding:2.5rem 0 3rem;
    background:
      radial-gradient(900px 500px at 10% -20%, rgba(14,165,164,.18), transparent 55%),
      radial-gradient(700px 420px at 90% -15%, rgba(59,130,246,.18), transparent 55%);
  }
  .checkout-page::after{
    content:"";
    position:absolute;
    inset:0;
    pointer-events:none;
    background-image:radial-gradient(rgba(15,23,42,.05) 1px, transparent 1px);
    background-size:18px 18px;
    opacity:.35;
  }
  .checkout-page .paynow-shell{position:relative;z-index:1;}
  .paynow-card{
    border-radius:18px;
    border:1px solid var(--line);
    box-shadow:var(--shadow);
    background:var(--card);
    overflow:hidden;
    animation:liftIn .5s ease both;
  }
  .paynow-header{text-align:center;margin-bottom:1.5rem;animation:fadeIn .6s ease both;}
  .paynow-eyebrow{
    font-size:.72rem;
    letter-spacing:.22em;
    text-transform:uppercase;
    color:var(--muted);
    font-weight:600;
    margin-bottom:.5rem;
  }
  .paynow-title{
    font-family:"Space Grotesk",sans-serif;
    font-size:1.6rem;
    font-weight:700;
    margin-bottom:.35rem;
  }
  .paynow-subtitle{color:var(--muted);margin-bottom:0;}
  .summary-list{
    border-radius:14px;
    border:1px solid rgba(15,23,42,.08);
    overflow:hidden;
    background:var(--card-soft);
    animation:fadeIn .65s ease both;
  }
  .summary-list .list-group-item{
    border-color:rgba(15,23,42,.08);
    background:transparent;
    font-weight:500;
    padding:.75rem 1rem;
  }
  .summary-list .summary-row--total{
    background:rgba(14,165,164,.10);
    font-weight:700;
  }
  .method-card{
    background:var(--card-soft);
    border:1px solid rgba(15,23,42,.08);
    border-radius:16px;
    padding:1rem;
    margin-bottom:1rem;
    box-shadow:0 12px 26px rgba(15,23,42,.08);
    animation:fadeIn .7s ease both;
  }
  .method-title{
    font-weight:600;
    margin-bottom:.25rem;
  }
  .method-copy{color:var(--muted);margin-bottom:.75rem;}
  .method-alert{
    border-radius:12px;
    border:1px solid rgba(16,185,129,.25);
    background:rgba(16,185,129,.10);
    padding:.5rem .75rem;
  }
  .fee-note{
    background:rgba(245,158,11,.12);
    border:1px solid rgba(245,158,11,.25);
    color:#92400e;
    border-radius:12px;
    padding:.5rem .75rem;
    margin-bottom:.75rem;
  }
  .payment-layout{
    display:flex;
    border-radius:16px;
    border:1px solid rgba(15,23,42,.12);
    overflow:hidden;
    background:var(--card);
    box-shadow:0 12px 30px rgba(15,23,42,.08);
  }
  .payment-menu{
    width:200px;
    background:#f1f5f9;
    border-right:1px solid rgba(15,23,42,.08);
    padding:1rem;
  }
  .payment-menu__title{
    font-size:.7rem;
    letter-spacing:.18em;
    text-transform:uppercase;
    color:var(--muted);
    font-weight:600;
    margin-bottom:.75rem;
  }
  .payment-option{
    width:100%;
    border:1px solid transparent;
    background:transparent;
    color:var(--ink);
    padding:.65rem .75rem;
    border-radius:12px;
    text-align:left;
    display:flex;
    align-items:center;
    gap:.5rem;
    font-weight:600;
    position:relative;
    transition:background .2s ease, border-color .2s ease, color .2s ease;
  }
  .payment-option i{width:18px;text-align:center;}
  .payment-option + .payment-option{margin-top:.5rem;}
  .payment-option:hover{background:#e2e8f0;}
  .payment-option.is-active{
    background:#ffffff;
    border-color:rgba(15,23,42,.12);
    box-shadow:0 8px 20px rgba(15,23,42,.08);
    color:var(--brand-strong);
  }
  .payment-option.is-active::before{
    content:"";
    position:absolute;
    left:0;
    top:.4rem;
    bottom:.4rem;
    width:3px;
    background:var(--brand);
    border-radius:4px;
  }
  .payment-content{
    flex:1;
    padding:1.25rem 1.5rem;
    background:var(--card);
  }
  .method-panel{display:none;}
  .method-panel.is-active{display:block;}
  .payment-content .method-card{margin-bottom:0;}
  .paynow-result{min-height:1.4rem;}
  .spinner{display:inline-block;width:1.25rem;height:1.25rem;border:2px solid #ddd;border-top-color:#000;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:.5rem}
  @keyframes spin{to{transform:rotate(360deg)}}
  @keyframes liftIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
  @keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
  .alert{
    border-radius:12px;
    border:1px solid transparent;
    padding:.6rem .8rem;
    font-size:.82rem;
    font-weight:500;
  }
  .alert-light{background:#f8fafc;border-color:#cbd5e1;color:#334155;}
  .alert-success{background:#dcfce7;border-color:#86efac;color:#166534;}
  .alert-warning{background:#fef3c7;border-color:#fcd34d;color:#92400e;}
  .alert-danger{background:#fee2e2;border-color:#fca5a5;color:#991b1b;}
  .text-success{color:#15803d;}
  .text-danger{color:#b91c1c;}
  .disabled{opacity:.6;pointer-events:none;}
  .d-none{display:none!important}
  @media (max-width: 576px){
    .checkout-page{padding:2rem 0;}
    .paynow-card > div{padding:1.5rem!important;}
  }
  @media (max-width: 768px){
    .payment-layout{flex-direction:column;}
    .payment-menu{
      width:100%;
      border-right:0;
      border-bottom:1px solid rgba(15,23,42,.08);
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));
      gap:.5rem;
    }
    .payment-menu__title{grid-column:1 / -1;margin-bottom:.25rem;}
    .payment-option + .payment-option{margin-top:0;}
  }
  @media (prefers-reduced-motion: reduce){
    .paynow-card,.paynow-header,.summary-list,.method-card{animation:none;}
  }
</style>
@endsection

{{-- ----------------------------------------------
|  PHP HELPERS - numeric-safe amounts & previews
---------------------------------------------- --}}
@php
  $currency    = $order->currency ?? 'USD';

  // Currencies without minor units (PayPal)
  $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

  // Numeric-safe caster (handles "1,234.56" or "USD 12.00")
  $toFloat = function ($v) {
      if (is_numeric($v)) return (float)$v;
      $clean = preg_replace('/[^\d\.\-]/', '', (string)$v);
      return (float)($clean === '' ? 0 : $clean);
  };

  // Order total
  $orderTotal  = (float) $order->total_amount;

  // Wallet balance (may come formatted from helper)
  $walletBalanceRaw = wallet();
  $walletBalance    = $toFloat($walletBalanceRaw);

  // Transaction fee percent: allow either 3.98 or 0.0398 in settings
  $feeSettingRaw          = $toFloat(setting('paypal_transaction_fee_percent') ?? 0);
  $transactionFeePercent  = $feeSettingRaw > 1 ? ($feeSettingRaw / 100) : $feeSettingRaw; // normalize to decimal
  $transactionFeePercentDisplay = $transactionFeePercent * 100; // for UI

  // Hidden auto-apply wallet (cap at order total)
  $walletApplied   = min($walletBalance, $orderTotal);

  // Shortfall the user must cover via online method (before fees)
  $shortfallBase   = max(0, $orderTotal - $walletApplied);

  // Online fee only applies to PayPal (on the shortfall only)
  $paypalFeeShort  = round($shortfallBase * $transactionFeePercent, 2);

  // Amount due now (for wallet/MPesa): shortfall only
  $amountDueNow    = $shortfallBase;

  // Display string for "Amount Due Now" respecting zero-decimal currencies
  $amountDueNowDisplay = in_array($currency, $zeroDecimal)
      ? (string) intval(round($amountDueNow))
      : number_format($amountDueNow, 2, '.', '');

  // PayPal createOrder amount (shortfall + fee) must be a string
  $paypalGross     = $shortfallBase + $paypalFeeShort;
  $paypalAmountStr = in_array($currency, $zeroDecimal)
      ? (string) intval(round($paypalGross))
      : number_format($paypalGross, 2, '.', '');

  // KES preview for M-Pesa (convert only the shortfall base; fees are PayPal-specific)
  $usdToKesRate = (float) env('USD_TO_KES', 130);
  $previewKes   = (int) ceil($shortfallBase * $usdToKesRate);

  // If wallet fully covers, allow wallet button; otherwise hide wallet UI but we still auto-use wallet in background
  $canPayWithWalletOnly = $walletApplied >= $orderTotal;

  // Payment gateway availability (admin-configurable)
  $paypalAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('paypal') : true;
  $stripeAvailable = function_exists('payment_gateway_available')
      ? payment_gateway_available('stripe')
      : (!empty(config('services.stripe.secret')) || (function_exists('setting') && !empty(setting('stripe_secret'))));
  $paystackAvailable = function_exists('payment_gateway_available')
      ? payment_gateway_available('paystack')
      : (!empty(config('services.paystack.secret')) || (function_exists('setting') && !empty(setting('paystack_secret'))));
  $mpesaAvailable  = function_exists('payment_gateway_available') ? payment_gateway_available('mpesa') : true;

  $availableMethods = [];
  if ($shortfallBase > 0) {
      if ($mpesaAvailable) $availableMethods[] = 'mpesa';
      if ($paypalAvailable) $availableMethods[] = 'paypal';
      if ($stripeAvailable) $availableMethods[] = 'stripe';
      if ($paystackAvailable) $availableMethods[] = 'paystack';
  }
  $defaultMethod = $availableMethods[0] ?? null;
@endphp

{{-- ----------------------------------------------
|  MAIN CONTENT
---------------------------------------------- --}}
@section('main')
<div class="checkout-page">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 paynow-shell">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>
      <div class="col-span-12 lg:col-span-9">
        <div class="mx-auto w-full max-w-4xl">

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 paynow-card">
          <div class="p-5">

            <div class="paynow-header">
              <div class="paynow-eyebrow">Order payment</div>
              <h2 class="paynow-title">Process your payment</h2>
              <p class="paynow-subtitle">We'll automatically use your wallet first, then charge only the remainder.</p>
            </div>

            {{-- -- Amount breakdown ----------------------------- --}}
            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 mb-4 summary-list">
              <li class="px-4 py-3 flex justify-between summary-row">
                <span>Order&nbsp;Total</span>
                <span>{{ $currency }} {{ number_format($orderTotal, 2) }}</span>
              </li>

              @if($walletApplied > 0)
                <li class="px-4 py-3 flex justify-between summary-row">
                  <span>Wallet&nbsp;Applied</span>
                  <span>- {{ $currency }} {{ number_format($walletApplied, 2) }}</span>
                </li>
              @endif

              {{-- No fee shown here; PayPal fee shown near PayPal button only --}}

              <li class="px-4 py-3 flex justify-between summary-row summary-row--total">
                <span>Amount&nbsp;Due&nbsp;Now</span>
                <span>{{ $currency }} {{ $amountDueNowDisplay }}</span>
              </li>
            </ul>

            {{-- -- WALLET OPTION (visible only if wallet fully covers) --------- --}}
            <form id="wallet-pay-form"
                  action="{{ route('order.wallet.pay', $order->id) }}"
                  method="POST"
                  class="grid gap-2 mb-3 wallet-pay {{ $canPayWithWalletOnly ? '' : 'd-none' }}">
              @csrf
              <input type="hidden" name="method" id="pay-method" value="wallet">
              <button type="submit" id="wallet-pay-btn" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                Pay via Wallet
                <small class="font-normal">(Balance: {{ $currency }} {{ number_format($walletBalance,2) }})</small>
              </button>
            </form>

            @if($shortfallBase > 0)
              @if(count($availableMethods))
                <div class="payment-layout">
                  <aside class="payment-menu">
                    <div class="payment-menu__title">Pay with</div>
                    @if($mpesaAvailable)
                      <button type="button" class="payment-option {{ $defaultMethod === 'mpesa' ? 'is-active' : '' }}" data-method="mpesa" aria-pressed="{{ $defaultMethod === 'mpesa' ? 'true' : 'false' }}">
                        <i class="fa fa-mobile"></i>
                        <span>M-Pesa</span>
                      </button>
                    @endif
                    @if($paypalAvailable)
                      <button type="button" class="payment-option {{ $defaultMethod === 'paypal' ? 'is-active' : '' }}" data-method="paypal" aria-pressed="{{ $defaultMethod === 'paypal' ? 'true' : 'false' }}">
                        <i class="fab fa-paypal"></i>
                        <span>PayPal</span>
                      </button>
                    @endif
                    @if($stripeAvailable)
                      <button type="button" class="payment-option {{ $defaultMethod === 'stripe' ? 'is-active' : '' }}" data-method="stripe" aria-pressed="{{ $defaultMethod === 'stripe' ? 'true' : 'false' }}">
                        <i class="fa fa-credit-card"></i>
                        <span>Card (Stripe)</span>
                      </button>
                    @endif
                    @if($paystackAvailable)
                      <button type="button" class="payment-option {{ $defaultMethod === 'paystack' ? 'is-active' : '' }}" data-method="paystack" aria-pressed="{{ $defaultMethod === 'paystack' ? 'true' : 'false' }}">
                        <i class="fa fa-check-circle"></i>
                        <span>Paystack</span>
                      </button>
                    @endif
                  </aside>

                  <div class="payment-content">
                    @if($mpesaAvailable)
                      <div class="method-panel {{ $defaultMethod === 'mpesa' ? 'is-active' : '' }}" data-method="mpesa">
                        <div id="mpesa-section" class="method-card method-card--mpesa">
                          <div class="method-title">M-Pesa STK Push</div>
                          <div class="method-alert text-xs flex items-center mb-3">
                            <i class="fa fa-mobile mr-2"></i>
                            <span>We'll send a prompt to your phone. Approve with your M-Pesa PIN.</span>
                          </div>

                          <div class="grid grid-cols-12 gap-4 gap-3 mb-2">
                            <div class="md:col-span-7">
                              <label for="mpesa_phone" class="mb-1 block text-sm font-medium text-slate-700">M-Pesa Phone (Safaricom)</label>
                              <input type="text" id="mpesa_phone" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="07XXXXXXXX / 7XXXXXXXX / 2547XXXXXXXX" maxlength="13" autocomplete="tel">
                              <div class="mt-1 text-xs text-slate-500">We'll normalize to <code>2547XXXXXXXX</code>.</div>
                            </div>
                            <div class="md:col-span-5">
                              <label class="mb-1 block text-sm font-medium text-slate-700">KES Amount (auto)</label>
                              <input type="text" id="mpesa_kes_preview" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" disabled
                                     value="KES {{ number_format($previewKes, 2) }}">
                              <div class="mt-1 text-xs text-slate-500">Rate used: {{ number_format($usdToKesRate, 2) }} KES / USD</div>
                            </div>
                          </div>

                          <div class="grid">
                            <button id="btn-start-stk" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                              <span class="spinner d-none" id="stk-spinner"></span>
                              Pay with M-Pesa
                            </button>
                          </div>

                          <div id="stk-live-status" class="rounded-xl border px-4 py-3 text-sm alert-light mt-3 d-none" aria-live="polite"></div>
                        </div>
                      </div>
                    @endif

                    @if($paypalAvailable)
                      <div class="method-panel {{ $defaultMethod === 'paypal' ? 'is-active' : '' }}" data-method="paypal">
                        <div class="method-card">
                          <div class="method-title">PayPal</div>
                          <p class="method-copy">Pay with your PayPal balance or card.</p>
                          <div class="fee-note text-xs">
                            Paying with PayPal adds an online fee of {{ $currency }} {{ number_format($paypalFeeShort, 2) }} ({{ number_format($transactionFeePercentDisplay, 2) }}%).
                          </div>
                          <div id="paypal-button-container" class="text-center"></div>
                        </div>
                      </div>
                    @endif

                    @if($stripeAvailable)
                      <div class="method-panel {{ $defaultMethod === 'stripe' ? 'is-active' : '' }}" data-method="stripe">
                        <div class="method-card">
                          <div class="method-title">Card via Stripe</div>
                          <p class="method-copy">Pay securely with Stripe Checkout.</p>
                          <div class="grid">
                            <button id="btn-stripe" type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-900 text-white hover:bg-slate-700">Pay with Stripe</button>
                          </div>
                        </div>
                      </div>
                    @endif

                    @if($paystackAvailable)
                      <div class="method-panel {{ $defaultMethod === 'paystack' ? 'is-active' : '' }}" data-method="paystack">
                        <div class="method-card">
                          <div class="method-title">Paystack</div>
                          <p class="method-copy">Pay securely with Paystack checkout.</p>
                          <div class="grid">
                            <button id="btn-paystack" type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Pay with Paystack</button>
                          </div>
                        </div>
                      </div>
                    @endif

                    <div id="generic-result" class="paynow-result text-center mt-3 font-semibold" role="status" aria-live="polite"></div>
                  </div>
                </div>
              @else
                <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mb-0">
                  No payment methods are currently available. Please contact support.
                </div>
              @endif
            @endif

          </div>
        </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection

{{-- ----------------------------------------------
|  SCRIPTS - jQuery + PayPal SDK + STK listener
---------------------------------------------- --}}
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

@if($shortfallBase > 0 && $paypalAvailable)
  {{-- Load PayPal only when we actually need to charge --}}
  @php $ppClient = config('services.paypal.client_id') ?: (function_exists('setting') ? (setting('paypal_client_id') ?: 'sb') : 'sb'); @endphp
  <script src="https://www.paypal.com/sdk/js?client-id={{ $ppClient }}&currency={{ $currency }}"></script>
@endif

<script>
$(function () {
    const $result       = $('#generic-result');
    const $walletForm   = $('#wallet-pay-form');
    const $walletBtn    = $('#wallet-pay-btn');
    const $methodInput  = $('#pay-method');
    const $methodButtons = $('.payment-option');
    const $methodPanels  = $('.method-panel');
    let renderPaypalButtons = function () {};

    // Auto-pay after returning from Stripe success route (flash session)
    const AUTO_PAY = @json(session('autopay') ?? null);
    if (AUTO_PAY && $walletForm.length && $walletForm.is(':visible')) {
        $methodInput.val(String(AUTO_PAY));
        $walletBtn.prop('disabled', true).addClass('disabled');
        setTimeout(() => $walletForm.trigger('submit'), 350);
    }

    // ========= PayPal (only if shortfall exists) =========
    @if($shortfallBase > 0 && $paypalAvailable)
    const PAYPAL_AMOUNT_STR = @json($paypalAmountStr);  // exact "Amount Due Now" (includes fee)
    const SHORTFALL_BASE    = Number(@json((float) $shortfallBase)); // credit to wallet (excludes fee)
    const PAYPAL_FEE        = Number(@json((float) $paypalFeeShort)); // fee component (PayPal only)
    const CURRENCY          = @json($currency);
    let paypalRendered = false;

    renderPaypalButtons = function () {
      if (paypalRendered || typeof paypal === 'undefined') return;
      paypalRendered = true;

      paypal.Buttons({
        style:{ layout:'vertical', color:'blue', shape:'rect', label:'paypal' },

        createOrder: (_, actions) => actions.order.create({
            purchase_units:[{ amount:{ value: PAYPAL_AMOUNT_STR } }]
        }),

        onApprove: (_, actions) => {
            $result.removeClass('text-danger text-success').text('');
            return actions.order.capture().then((details) => {
                // Credit wallet with the shortfall base (fee is NOT credited)
                $.post("{{ route('wallet.deposit.paypal') }}", {
                    _token : '{{ csrf_token() }}',
                    amount : SHORTFALL_BASE,
                    fee    : PAYPAL_FEE,
                    gross  : PAYPAL_AMOUNT_STR,
                    currency: CURRENCY,
                    method : 'paypal',
                    order_id: details?.id || null
                }, function(resp){
                    if (resp?.success) {
                        // Auto-finish via wallet (hidden form)
                        if ($walletForm.length) {
                            $methodInput.val('paypal');
                            $walletBtn.prop('disabled', true).addClass('disabled');
                            setTimeout(() => $walletForm.trigger('submit'), 500);
                        } else {
                            window.location = @json(route('buyer.orders.show', $order->id));
                        }
                    } else {
                        $result.addClass('text-danger').text(resp?.message || 'Unable to credit wallet. Please contact support.');
                    }
                }).fail(function(xhr){
                    $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.error ?? 'Unknown error'));
                });
            });
        },

        onError: err => {
            console.error(err);
            $result.addClass('text-danger').text('PayPal error: ' + (err?.message || 'Unexpected error'));
        }
      }).render('#paypal-button-container');
    };
    @endif

    function setActiveMethod(method) {
      if (!method || !$methodButtons.length) return;
      $methodButtons.removeClass('is-active').attr('aria-pressed', 'false');
      $methodPanels.removeClass('is-active');
      const $button = $methodButtons.filter('[data-method="' + method + '"]');
      const $panel  = $methodPanels.filter('[data-method="' + method + '"]');
      if (!$button.length || !$panel.length) return;
      $button.addClass('is-active').attr('aria-pressed', 'true');
      $panel.addClass('is-active');
      $result.removeClass('text-danger text-success');
      if (method === 'paypal') {
        renderPaypalButtons();
      }
    }

    const DEFAULT_METHOD = @json($defaultMethod);
    if ($methodButtons.length) {
      setActiveMethod(DEFAULT_METHOD || $methodButtons.first().data('method'));
    }

    $methodButtons.on('click', function(){
      setActiveMethod($(this).data('method'));
    });

    // ========= M-Pesa STK (shortfall top-up, then wallet auto-finish) =========
    @if($shortfallBase > 0 && $mpesaAvailable)
    const USD_TO_KES = Number(@json((float) env('USD_TO_KES', 130)));
    const DEPOSIT_USD = Number(@json((float) $shortfallBase)); // deposit base only

    const $phoneInput   = $('#mpesa_phone');
    const $kesPreview   = $('#mpesa_kes_preview');
    const $stkBtn       = $('#btn-start-stk');
    const $stkSpinner   = $('#stk-spinner');
    const $liveStatus   = $('#stk-live-status');

    const toNumber = v => {
      const n = Number(String(v).replace(/,/g, ''));
      return Number.isFinite(n) ? n : 0;
    };
    function calcKes(usd, rate){ usd=toNumber(usd); rate=toNumber(rate); if(usd<=0||rate<=0) return 0; return Math.ceil(usd*rate); }
    function formatKes(kes){ return 'KES ' + toNumber(kes).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function updateKesPreview(){ $kesPreview.val(formatKes(calcKes(DEPOSIT_USD, USD_TO_KES))); }
    updateKesPreview();

    function normalizeMsisdn(raw){
      if(!raw) return null;
      let p = String(raw).replace(/\D/g,'');
      if (p.startsWith('0') && p.length===10) p = '254'+p.substring(1);
      else if (p.startsWith('7') && p.length===9) p = '254'+p;
      else if (p.startsWith('254') && p.length>12) p = p.substring(0,12);
      return /^2547\d{8}$/.test(p) ? p : null;
    }

    let pollTimer=null, autoPayInProgress=false;
    const POLL_INTERVAL_MS=3000, MAX_POLLS=40;

    function startPolling(ref){
      let attempts=0;
      clearInterval(pollTimer);
      $liveStatus.removeClass('d-none hidden alert-danger alert-success alert-warning').addClass('alert')
                 .html(`<i class="fa fa-sync-alt fa-spin mr-2"></i> Waiting for M-Pesa confirmation...`);
      pollTimer=setInterval(function(){
        attempts++;
        $.get("{{ route('wallet.deposit.mpesa.status', '__REF__') }}".replace('__REF__', encodeURIComponent(ref)), function(resp){
          if(resp?.status==='success'){
            clearInterval(pollTimer);
            $liveStatus.removeClass('alert-warning alert-danger').addClass('alert-success')
                       .html(`<i class="fa fa-check-circle mr-2"></i> Payment confirmed! Finalizing your order...`);
            if(!autoPayInProgress && $walletForm.length){
              autoPayInProgress = true;
              $methodInput.val('mpesa');
              $walletBtn.prop('disabled', true).addClass('disabled');
              setTimeout(()=> $walletForm.trigger('submit'), 600);
            }
            return;
          }
          if(resp?.status==='failed'){
            clearInterval(pollTimer);
            $liveStatus.removeClass('alert-warning alert-success').addClass('alert-danger')
                       .html(`<i class="fa fa-exclamation-triangle mr-2"></i> Payment failed: ${resp?.message || 'Unknown error'}`);
            return;
          }
        }).fail(()=>{ /* keep polling on transient errors */ });

        if(attempts>=MAX_POLLS){
          clearInterval(pollTimer);
          $liveStatus.removeClass('alert-success alert-danger').addClass('alert-warning')
                     .html(`<i class="fa fa-hourglass-half mr-2"></i> Still waiting on M-Pesa. If you approved the prompt, try again shortly.`);
        }
      }, POLL_INTERVAL_MS);
    }

    $stkBtn.on('click', function(){
      const normalized = normalizeMsisdn($phoneInput.val());
      if(!normalized){
        $result.removeClass('text-success').addClass('text-danger')
               .text('Enter a valid Safaricom number (07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX).');
        $phoneInput.focus();
        return;
      }
      $stkBtn.prop('disabled', true);
      $stkSpinner.removeClass('d-none');
      $result.removeClass('text-danger text-success').text('');
      $liveStatus.addClass('d-none').empty();

      $.post("{{ route('wallet.deposit.mpesa.stk') }}", {
        _token: '{{ csrf_token() }}',
        phone: normalized,
        usd_amount: DEPOSIT_USD
      }, function(resp){
        if(resp.success && resp.ref){
          $result.removeClass('text-danger').addClass('text-success')
                 .text('STK Push sent. Check your phone and approve.');
          startPolling(resp.ref);
        }else{
          $result.removeClass('text-success').addClass('text-danger')
                 .text(resp.message || 'Failed to start STK Push.');
        }
      }).fail(function(xhr){
        $result.removeClass('text-success').addClass('text-danger')
               .text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
      }).always(function(){
        $stkBtn.prop('disabled', false);
        $stkSpinner.addClass('d-none');
      });
    });
    @endif

    // ========= Stripe Checkout (hosted) =========
    @if($shortfallBase > 0 && $stripeAvailable)
    const $stripeBtn = $('#btn-stripe');
    $stripeBtn.on('click', function(){
        $stripeBtn.prop('disabled', true).addClass('disabled');
        $result.removeClass('text-danger text-success').text('Redirecting to Stripe...');
        $.post(@json(route('order.stripe.session', $order->id)), { _token: @json(csrf_token()) }, function(resp){
            if (resp?.success && resp?.url) {
                window.location = resp.url;
            } else {
                $stripeBtn.prop('disabled', false).removeClass('disabled');
                $result.addClass('text-danger').text(resp?.message || 'Unable to start Stripe checkout.');
            }
        }).fail(function(xhr){
            $stripeBtn.prop('disabled', false).removeClass('disabled');
            $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        });
    });
    @endif

    // ========= Paystack Checkout (hosted) =========
    @if($shortfallBase > 0 && $paystackAvailable)
    const $paystackBtn = $('#btn-paystack');
    $paystackBtn.on('click', function(){
        $paystackBtn.prop('disabled', true).addClass('disabled');
        $result.removeClass('text-danger text-success').text('Redirecting to Paystack...');
        $.post(@json(route('order.paystack.session', $order->id)), { _token: @json(csrf_token()) }, function(resp){
            if (resp?.success && resp?.url) {
                window.location = resp.url;
            } else {
                $paystackBtn.prop('disabled', false).removeClass('disabled');
                $result.addClass('text-danger').text(resp?.message || 'Unable to start Paystack checkout.');
            }
        }).fail(function(xhr){
            $paystackBtn.prop('disabled', false).removeClass('disabled');
            $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        });
    });
    @endif
});
</script>
@endsection




