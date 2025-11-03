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
  .spinner{display:inline-block;width:1.25rem;height:1.25rem;border:2px solid #ddd;border-top-color:#000;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:.5rem}
  @keyframes spin{to{transform:rotate(360deg)}}
  .d-none{display:none!important}
</style>
@endsection

{{-- ──────────────────────────────────────────────
|  PHP HELPERS – numeric-safe amounts & previews
└───────────────────────────────────────────── --}}
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
              We’ll automatically use your wallet first, then charge only the remainder.
            </p>

            {{-- ── Amount breakdown ───────────────────────────── --}}
            <ul class="list-group mb-4">
              <li class="list-group-item d-flex justify-content-between">
                <span>Order&nbsp;Total</span>
                <span>{{ $currency }} {{ number_format($orderTotal, 2) }}</span>
              </li>

              @if($walletApplied > 0)
                <li class="list-group-item d-flex justify-content-between">
                  <span>Wallet&nbsp;Applied</span>
                  <span>- {{ $currency }} {{ number_format($walletApplied, 2) }}</span>
                </li>
              @endif

              {{-- No fee shown here; PayPal fee shown near PayPal button only --}}

              <li class="list-group-item d-flex justify-content-between fw-bold">
                <span>Amount&nbsp;Due&nbsp;Now</span>
                <span>{{ $currency }} {{ $amountDueNowDisplay }}</span>
              </li>
            </ul>

            {{-- ── WALLET OPTION (visible only if wallet fully covers) ───────── --}}
            <form id="wallet-pay-form"
                  action="{{ route('order.wallet.pay', $order->id) }}"
                  method="POST"
                  class="d-grid gap-2 mb-3 {{ $canPayWithWalletOnly ? '' : 'd-none' }}">
              @csrf
              <button type="submit" id="wallet-pay-btn" class="btn btn-primary">
                Pay via Wallet
                <small class="fw-normal">(Balance: {{ $currency }} {{ number_format($walletBalance,2) }})</small>
              </button>
            </form>

            {{-- ── M-Pesa STK deposit (top-up shortfall, then auto-finish via wallet) ─ --}}
            @if($shortfallBase > 0)
              <div id="mpesa-section" class="mb-4">
                <div class="alert alert-success small d-flex align-items-center mb-3">
                  <i class="fa fa-mobile me-2"></i>
                  <span><strong>M-Pesa STK Push:</strong> We’ll send a prompt to your phone. Approve with your M-Pesa PIN.</span>
                </div>

                <div class="row g-3 mb-2">
                  <div class="col-md-7">
                    <label for="mpesa_phone" class="form-label">M-Pesa Phone (Safaricom)</label>
                    <input type="text" id="mpesa_phone" class="form-control" placeholder="07XXXXXXXX / 7XXXXXXXX / 2547XXXXXXXX" maxlength="13" autocomplete="tel">
                    <div class="form-text">We’ll normalize to <code>2547XXXXXXXX</code>.</div>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">KES Amount (auto)</label>
                    <input type="text" id="mpesa_kes_preview" class="form-control" disabled
                           value="KES {{ number_format($previewKes, 2) }}">
                    <div class="form-text">Rate used: {{ number_format($usdToKesRate, 2) }} KES / USD</div>
                  </div>
                </div>

                <div class="d-grid">
                  <button id="btn-start-stk" class="btn btn-success">
                    <span class="spinner d-none" id="stk-spinner"></span>
                    Pay with M-Pesa
                  </button>
                </div>

                <div id="stk-live-status" class="alert alert-light border mt-3 d-none" aria-live="polite"></div>
              </div>
            @endif

            {{-- ── PayPal / Card (charges only amountDueNow) ───────────── --}}
            @if($shortfallBase > 0)
              <div class="text-center small text-muted mb-2">
                Paying with PayPal adds an online fee of {{ $currency }} {{ number_format($paypalFeeShort, 2) }} ({{ number_format($transactionFeePercentDisplay, 2) }}%).
              </div>
              <div id="paypal-button-container" class="text-center mb-3"></div>
            @endif

            {{-- Error / result placeholder --}}
            <div id="generic-result" class="text-center mt-3 fw-semibold"></div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

{{-- ──────────────────────────────────────────────
|  SCRIPTS – jQuery + PayPal SDK + STK listener
└───────────────────────────────────────────── --}}
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

@if($shortfallBase > 0)
  {{-- Load PayPal only when we actually need to charge --}}
  <script src="https://www.paypal.com/sdk/js?client-id={{ setting('paypal_client_id') }}&currency={{ $currency }}"></script>
@endif

<script>
$(function () {
    const $result       = $('#generic-result');
    const $walletForm   = $('#wallet-pay-form');
    const $walletBtn    = $('#wallet-pay-btn');

    // ========= PayPal (only if shortfall exists) =========
    @if($shortfallBase > 0)
    const PAYPAL_AMOUNT_STR = @json($paypalAmountStr);  // exact "Amount Due Now" (includes fee)
    const SHORTFALL_BASE    = Number(@json((float) $shortfallBase)); // credit to wallet (excludes fee)
    const PAYPAL_FEE        = Number(@json((float) $paypalFeeShort)); // fee component (PayPal only)
    const CURRENCY          = @json($currency);

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
    @endif

    // ========= M-Pesa STK (shortfall top-up, then wallet auto-finish) =========
    @if($shortfallBase > 0)
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
      $liveStatus.removeClass('d-none alert-danger alert-success alert-warning').addClass('alert')
                 .html(`<i class="fa fa-sync-alt fa-spin me-2"></i> Waiting for M-Pesa confirmation...`);
      pollTimer=setInterval(function(){
        attempts++;
        $.get("{{ route('wallet.deposit.mpesa.status', '__REF__') }}".replace('__REF__', encodeURIComponent(ref)), function(resp){
          if(resp?.status==='success'){
            clearInterval(pollTimer);
            $liveStatus.removeClass('alert-warning alert-danger').addClass('alert-success')
                       .html(`<i class="fa fa-check-circle me-2"></i> Payment confirmed! Finalizing your order...`);
            if(!autoPayInProgress && $walletForm.length){
              autoPayInProgress = true;
              $walletBtn.prop('disabled', true).addClass('disabled');
              setTimeout(()=> $walletForm.trigger('submit'), 600);
            }
            return;
          }
          if(resp?.status==='failed'){
            clearInterval(pollTimer);
            $liveStatus.removeClass('alert-warning alert-success').addClass('alert-danger')
                       .html(`<i class="fa fa-exclamation-triangle me-2"></i> Payment failed: ${resp?.message || 'Unknown error'}`);
            return;
          }
        }).fail(()=>{ /* keep polling on transient errors */ });

        if(attempts>=MAX_POLLS){
          clearInterval(pollTimer);
          $liveStatus.removeClass('alert-success alert-danger').addClass('alert-warning')
                     .html(`<i class="fa fa-hourglass-half me-2"></i> Still waiting on M-Pesa. If you approved the prompt, try again shortly.`);
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
});
</script>
@endsection
