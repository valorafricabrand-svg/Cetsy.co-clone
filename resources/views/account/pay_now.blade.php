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
  $depositNeeded = max(0, $grandTotal - $walletBalance);
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
              @endunless
            @endif

            {{-- ── M-Pesa STK deposit ───────────────────────── --}}
            @if($depositNeeded > 0)
              <div id="mpesa-section" class="mb-4">
                <div class="alert alert-success small d-flex align-items-center mb-3">
                  <i class="fa fa-mobile me-2"></i>
                  <span><strong>M-Pesa STK Push:</strong> We’ll send a prompt to your phone.</span>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-7">
                    <label for="mpesa_phone" class="form-label">M-Pesa Phone (Safaricom)</label>
                    <input type="text" id="mpesa_phone" class="form-control" placeholder="e.g. 07XXXXXXXX" maxlength="12">
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">KES Amount (auto)</label>
                    <input type="text" id="mpesa_kes_preview" class="form-control" disabled>
                  </div>
                </div>

                <div class="d-grid">
                  <button id="btn-start-stk" class="btn btn-success">
                    <span class="spinner d-none" id="stk-spinner"></span>
                    Pay with M-Pesa
                  </button>
                </div>

                <div class="small text-muted mt-2">
                  By continuing you agree that M-Pesa transaction charges (if any) are borne by you.
                </div>
              </div>
            @endif

            {{-- ── 2️⃣  PayPal / Card button ─────────────────── --}}
            <div id="paypal-button-container" class="text-center mb-3"></div>

            {{-- Error placeholder --}}
            <div id="generic-result" class="text-center mt-3 fw-semibold"></div>

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
    @if($depositNeeded > 0)
    const USD_TO_KES = {{ (float) (env('USD_TO_KES', 130)) }};
    const depositUsd = {{ $depositNeeded }};
    const $result = $('#generic-result');
    const $phone = $('#mpesa_phone');
    const $kesPreview = $('#mpesa_kes_preview');
    const $stkBtn = $('#btn-start-stk');
    const $stkSpinner = $('#stk-spinner');

    function updateKesPreview(){
        const kes = Math.ceil(depositUsd * USD_TO_KES);
        $kesPreview.val('KES ' + kes.toFixed(2));
    }
    updateKesPreview();

    $stkBtn.on('click', function(){
        const phone = ($phone.val() || '').trim();
        if(!phone){
            $result.addClass('text-danger').text('Please enter your Safaricom phone number.');
            return;
        }
        $stkBtn.prop('disabled', true);
        $stkSpinner.removeClass('d-none');
        $result.removeClass('text-danger text-success').text('');

        $.post("{{ route('wallet.deposit.mpesa.stk') }}", {
            _token: '{{ csrf_token() }}',
            phone: phone,
            usd_amount: depositUsd
        }, function(resp){
            if(resp.success){
                $result.addClass('text-success').text('STK Push sent. Once complete, use the wallet option above to finish payment.');
            }else{
                $result.addClass('text-danger').text(resp.message || 'Failed to start STK Push.');
            }
        }).fail(function(xhr){
            $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        }).always(function(){
            $stkBtn.prop('disabled', false);
            $stkSpinner.addClass('d-none');
        });
    });
    @endif
});
</script>
@endsection
