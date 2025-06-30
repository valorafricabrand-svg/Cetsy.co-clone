{{-- resources/views/listings/checkout.blade.php --}}
@extends('layouts.app')
@section('title', 'Process Payment')

{{-- -------------------------------------------------------------------
 |  INLINE STYLES  (Bootstrap 5.3 utilities everywhere else)
-------------------------------------------------------------------- --}}
@section('styles')
<style>
    :root{
        --accent:#0275d8;
        --accent-dark:#025aa5;
    }
    .checkout-page{
        background:#f8f9fa;
        min-height:100vh;
        display:flex;
        align-items:center;
    }
    .card.glass{
        backdrop-filter:blur(6px);
        background:rgba(255,255,255,.85);
        border-radius:1rem;
    }
    @media (prefers-color-scheme:dark){
        .checkout-page{background:#121212;}
        .card.glass{background:rgba(35,35,35,.55);}
    }
    .btn-primary{
        background:var(--accent);
        border-color:var(--accent);
    }
    .btn-primary:hover,
    .btn-primary:focus{
        background:var(--accent-dark);
        border-color:var(--accent-dark);
    }
</style>
@endsection

{{-- -------------------------------------------------------------------
 |  PHP HELPERS  – amount exactly as PayPal & humans expect
-------------------------------------------------------------------- --}}
@php
    $currency       = $order->currency ?? 'USD';
    $zeroDecimal    = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
                       'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

    $rawAmount      = (float) ($order->category?->listing_fee ?? 0);
    $paypalAmount   = in_array($currency, $zeroDecimal)
        ? (string) intval(round($rawAmount))          // 70 → "70"
        : number_format($rawAmount, 2, '.', '');      // 70 → "70.00"

    $displayAmount  = in_array($currency, $zeroDecimal)
        ? number_format($rawAmount, 0)
        : number_format($rawAmount, 2);

    /** ----------------------------------------------------------------
     *  USER WALLET
     *  wallet() already returns the numeric balance
     *  -------------------------------------------------------------- */
    $walletBalance  = auth()->check()
        ? (float) (wallet() ?? 0)
        : 0.0;
@endphp

{{-- -------------------------------------------------------------------
 |  MAIN CONTENT
-------------------------------------------------------------------- --}}
@section('content')
<div class="checkout-page w-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-6 col-xl-5">

                <div class="card shadow-sm border-0 glass">
                    <div class="card-body p-5">

                        <h1 class="h3 fw-bold text-center mb-3">Complete Your Payment</h1>
                        <p class="text-center text-body-secondary mb-4">
                            Secure checkout powered by PayPal.<br class="d-none d-md-block">
                            Pay with your PayPal balance or any major debit / credit card.
                        </p>

                        {{-- Amount display --}}
                        <div class="alert alert-info text-center fw-semibold" role="alert">
                            Amount&nbsp;:&nbsp; {{ $currency }} {{ $displayAmount }}
                        </div>

                        {{-- 1️⃣  WALLET BUTTON (visible only if balance > 0) --}}
                        @if($walletBalance > 0)
                            <form
                                action="{{ route('listing.wallet.pay', $order->id) }}"
                                method="POST"
                                class="d-grid gap-2 mb-3"
                            >
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    Pay via Wallet
                                    <small class="fw-normal">
                                        ({{ $currency }} {{ number_format($walletBalance, 2) }})
                                    </small>
                                </button>
                            </form>
                        @endif

                        {{-- 2️⃣  PAYPAL BUTTON --}}
                        <div id="paypal-button-container" class="d-grid gap-2 mb-3"></div>

                        {{-- Result / error placeholder --}}
                        <div id="generic-result" class="text-center fw-semibold"></div>

                        {{-- Optional cancel link --}}
                        <p class="text-center mt-4 small">
                            <a href="{{ url()->previous() }}" class="text-decoration-none text-muted">
                                <i class="bi bi-arrow-left-circle"></i> Cancel&nbsp;and&nbsp;return
                            </a>
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

{{-- -------------------------------------------------------------------
 |  SCRIPTS  – PayPal SDK + button config (vanilla JS, no jQuery)
-------------------------------------------------------------------- --}}
@section('scripts')
<script
    src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id', 'sb') }}&currency={{ $currency }}"
    data-sdk-integration-source="button-factory"
    data-partner-attribution-id="LaravelApp"
></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const resultBlock = document.getElementById('generic-result');

    paypal.Buttons({

        style: {
            layout : 'vertical',
            color  : 'blue',
            shape  : 'rect',
            label  : 'paypal',
            tagline: false
        },

        /* 1️⃣  Create the PayPal order */
        createOrder: (_data, actions) => actions.order.create({
            purchase_units: [{
                amount: { value: '{{ $paypalAmount }}' }
            }]
        }),

        /* 2️⃣  Capture & redirect */
        onApprove: (_data, actions) => {
            resultBlock.textContent = '';
            return actions.order.capture()
                .then(() => window.location.href = @json(route('success_deposit_fee', $order->id)));
        },

        /* 3️⃣  Handle cancellations */
        onCancel: () => {
            resultBlock.className = 'text-warning fw-semibold';
            resultBlock.textContent = 'Payment cancelled – you can try again.';
        },

        /* 4️⃣  Handle errors */
        onError: err => {
            console.error(err);
            resultBlock.className = 'text-danger fw-semibold';
            resultBlock.textContent = 'Unable to process payment: ' + (err?.message ?? 'Unknown error');
        }

    }).render('#paypal-button-container');
});
</script>
@endsection
