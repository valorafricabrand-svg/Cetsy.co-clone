@extends('layouts.app')

@section('title', 'Deposit Funds')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');

    .wallet-deposit {
        --ink: #0b1220;
        --muted: #64748b;
        --brand: #0ea5a4;
        --brand-strong: #0f766e;
        --accent: #f59e0b;
        --panel: #ffffff;
        --panel-soft: #f8fafc;
        --line: rgba(15, 23, 42, 0.10);
        --shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        font-family: "Instrument Sans", "Space Grotesk", sans-serif;
        color: var(--ink);
        position: relative;
        padding: 2.5rem 0 3rem;
        background:
            radial-gradient(900px 460px at 5% -10%, rgba(14, 165, 164, 0.16), transparent 55%),
            radial-gradient(700px 360px at 95% -5%, rgba(37, 99, 235, 0.18), transparent 52%);
    }
    .wallet-deposit::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image: radial-gradient(rgba(15, 23, 42, 0.05) 1px, transparent 1px);
        background-size: 18px 18px;
        opacity: 0.35;
    }
    .wallet-deposit .deposit-shell {
        position: relative;
        z-index: 1;
    }
    .wallet-deposit__card {
        border-radius: 18px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow);
        background: var(--panel);
        overflow: hidden;
        animation: liftIn 0.5s ease both;
    }
    .wallet-deposit__header {
        text-align: center;
        margin-bottom: 1.25rem;
        animation: fadeIn 0.6s ease both;
    }
    .wallet-deposit__eyebrow {
        font-size: 0.72rem;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .wallet-deposit__title {
        font-family: "Space Grotesk", sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }
    .wallet-deposit__subtitle {
        color: var(--muted);
        margin-bottom: 0;
    }
    .wallet-balance {
        background: linear-gradient(135deg, #0f766e, #2563eb);
        color: #fff;
        border-radius: 14px;
        padding: 1rem 1.2rem;
        display: grid;
        gap: 0.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 18px 30px rgba(37, 99, 235, 0.25);
        animation: liftIn 0.6s ease both;
    }
    .wallet-balance__label {
        text-transform: uppercase;
        letter-spacing: 0.2em;
        font-size: 0.7rem;
        opacity: 0.85;
    }
    .wallet-balance__value {
        font-family: "Space Grotesk", sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
    }
    .wallet-balance__meta {
        font-size: 0.85rem;
        opacity: 0.85;
    }
    .deposit-amount {
        background: var(--panel-soft);
        border-radius: 14px;
        padding: 1rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        margin-bottom: 1.25rem;
        animation: fadeIn 0.65s ease both;
    }
    .deposit-amount .form-control {
        border-radius: 10px;
        border: 1px solid rgba(15, 23, 42, 0.18);
        padding: 0.75rem 0.9rem;
        font-weight: 600;
    }
    .deposit-amount .form-text {
        color: var(--muted);
    }
    .payment-toggle {
        margin-bottom: 1.25rem;
        display: grid;
        gap: 0.6rem;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        animation: fadeIn 0.7s ease both;
    }
    .payment-toggle .btn {
        border-radius: 999px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink);
        font-weight: 600;
        padding: 0.55rem 0.9rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .payment-toggle .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }
    .payment-toggle .btn.is-active {
        background: var(--ink);
        color: #fff;
        border-color: var(--ink);
    }
    .payment-panel {
        background: var(--panel-soft);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 16px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        animation: fadeIn 0.5s ease both;
    }
    .payment-panel .text-muted {
        color: var(--muted) !important;
    }
    .payment-panel .btn {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.7rem 1rem;
    }
    .mpesa-callout {
        border-radius: 14px;
        border: 1px solid rgba(16, 185, 129, 0.25);
        background: rgba(16, 185, 129, 0.08);
    }
    .wallet-result {
        min-height: 1.4rem;
        color: var(--ink);
    }
    .d-none { display: none !important; }
    .opacity-50 { opacity: .5; }
    .spinner {
        display:inline-block; width:1.25rem; height:1.25rem; border:2px solid #ddd; border-top-color:#000;
        border-radius:50%; animation:spin .6s linear infinite; vertical-align:middle; margin-right:.5rem;
    }
    @keyframes spin { to { transform: rotate(360deg);} }
    @keyframes liftIn { from { opacity: 0; transform: translateY(12px);} to { opacity: 1; transform: translateY(0);} }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px);} to { opacity: 1; transform: translateY(0);} }
    @media (max-width: 575px) {
        .wallet-deposit { padding: 2rem 0; }
        .wallet-deposit__card .card-body { padding: 1.25rem !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .wallet-deposit__card,
        .wallet-deposit__header,
        .wallet-balance,
        .deposit-amount,
        .payment-toggle,
        .payment-panel { animation: none; }
        .payment-toggle .btn { transition: none; }
    }
</style>
@endsection

@section('content')
<div class="content wallet-deposit">
    <div class="row justify-content-center deposit-shell">
        <div class="col-md-8 col-lg-7 col-xl-6">

            <div class="card border-0 wallet-deposit__card mt-3">
                <div class="card-body p-4 p-md-5">

                    <div class="wallet-deposit__header">
                        <div class="wallet-deposit__eyebrow">Wallet top-up</div>
                        <h2 class="wallet-deposit__title">Deposit funds to wallet</h2>
                        <p class="wallet-deposit__subtitle">Top up securely with card or mobile money in seconds.</p>
                    </div>

                    {{-- Wallet Balance --}}
                    <div class="wallet-balance">
                        <div class="wallet-balance__label">Current balance</div>
                        <div class="wallet-balance__value">USD {{ number_format($balance, 2) }}</div>
                        <div class="wallet-balance__meta">Funds are instantly available for purchases.</div>
                    </div>

                    {{-- Deposit Amount (USD for wallet) --}}
                    <div class="deposit-amount">
                        <label for="deposit_amount" class="form-label">Deposit Amount (USD)</label>
                        <input type="number" id="deposit_amount" class="form-control" min="1" step="0.01" required placeholder="Enter amount e.g. 25.00"
                               value="{{ isset($defaultAmount) && $defaultAmount ? number_format((float)$defaultAmount, 2, '.', '') : '' }}">
                        <div class="form-text">Your wallet is in USD. For M-Pesa we'll auto-convert to KES using our configured rate.</div>
                    </div>

                    @php
                      $paypalAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('paypal') : true;
                      $stripeAvailable = function_exists('payment_gateway_available')
                          ? payment_gateway_available('stripe')
                          : (!empty(config('services.stripe.secret')) || (function_exists('setting') && !empty(setting('stripe_secret'))));
                      $paystackAvailable = function_exists('payment_gateway_available')
                          ? payment_gateway_available('paystack')
                          : (!empty(config('services.paystack.secret')) || (function_exists('setting') && !empty(setting('paystack_secret'))));
                      $mpesaAvailable  = function_exists('payment_gateway_available') ? payment_gateway_available('mpesa') : true;

                      $availableMethods = [];
                      if ($paypalAvailable) $availableMethods[] = 'paypal';
                      if ($stripeAvailable) $availableMethods[] = 'stripe';
                      if ($paystackAvailable) $availableMethods[] = 'paystack';
                      if ($mpesaAvailable)  $availableMethods[] = 'mpesa';

                      $defaultGateway = function_exists('payment_default_gateway') ? payment_default_gateway() : 'paypal';
                      if (!empty($preferredMethod) && in_array($preferredMethod, $availableMethods, true)) {
                        $defaultGateway = $preferredMethod;
                      }
                      if (!in_array($defaultGateway, $availableMethods, true)) {
                        $defaultGateway = $availableMethods[0] ?? '';
                      }
                    @endphp

                    {{-- Payment Method Toggle --}}
                    @if(empty($availableMethods))
                      <div class="alert alert-warning text-center">
                        No payment gateways are enabled/configured. Please contact support.
                      </div>
                    @else
                      <div class="payment-toggle">
                          @if($paypalAvailable)
                            <button type="button" id="btn-paypal" class="btn" data-method-toggle="paypal" aria-pressed="false">Pay with PayPal / Card</button>
                          @endif
                          @if($stripeAvailable)
                            <button type="button" id="btn-stripe" class="btn" data-method-toggle="stripe" aria-pressed="false">Pay with Stripe</button>
                          @endif
                          @if($paystackAvailable)
                            <button type="button" id="btn-paystack" class="btn" data-method-toggle="paystack" aria-pressed="false">Pay with Paystack</button>
                          @endif
                          @if($mpesaAvailable)
                            <button type="button" id="btn-mpesa"  class="btn" data-method-toggle="mpesa" aria-pressed="false">Pay with M-Pesa (STK)</button>
                          @endif
                      </div>
                    @endif

                    {{-- PayPal Section --}}
                    @if($paypalAvailable)
                      <div id="paypal-section" class="payment-panel d-none">
                          <p class="text-muted text-center">Proceed securely using PayPal or card.</p>
                          <div id="paypal-button-container" class="text-center"></div>
                      </div>
                    @endif

                    {{-- Stripe Section --}}
                    @if($stripeAvailable)
                      <div id="stripe-section" class="payment-panel d-none">
                          <p class="text-muted text-center">Pay securely by card using Stripe checkout.</p>
                          <div class="d-grid">
                              <button type="button" id="btn-stripe-checkout" class="btn btn-dark">
                                  Continue to Stripe
                              </button>
                          </div>
                      </div>
                    @endif

                    {{-- Paystack Section --}}
                    @if($paystackAvailable)
                      <div id="paystack-section" class="payment-panel d-none">
                          <p class="text-muted text-center">Pay securely via Paystack checkout.</p>
                          <div class="d-grid">
                              <button type="button" id="btn-paystack-checkout" class="btn btn-success">
                                  Continue to Paystack
                              </button>
                          </div>
                      </div>
                    @endif

                    {{-- M-Pesa Section --}}
                    @if($mpesaAvailable)
                    <div id="mpesa-section" class="payment-panel d-none">
                        <div class="alert mpesa-callout">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-mobile me-2"></i>
                                <div>
                                    <strong>M-Pesa STK Push:</strong> We'll send a prompt to your phone. Enter your PIN to approve.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="mpesa_phone" class="form-label">M-Pesa Phone (Safaricom)</label>
                                <input type="text" id="mpesa_phone" class="form-control" placeholder="e.g. 07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX" maxlength="13">
                                <div class="form-text">We'll normalize to <code>2547XXXXXXXX</code>.</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">KES Amount (auto)</label>
                                <input type="text" id="mpesa_kes_preview" class="form-control" disabled value="KES 0.00">
                                <div class="form-text">Calculated from USD amount x rate.</div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button id="btn-start-stk" class="btn btn-success">
                                <span class="spinner d-none" id="stk-spinner"></span>
                                Send STK Push
                            </button>
                        </div>

                        <div class="small text-muted mt-2">
                            By continuing you agree that M-Pesa transaction charges (if any) are borne by you.
                        </div>

                        {{-- Live status area when polling --}}
                        <div id="stk-live-status" class="alert alert-light border mt-3 d-none"></div>
                    </div>
                    @endif

                    {{-- Result Message --}}
                    <div id="generic-result" class="wallet-result text-center mt-3 fw-semibold" role="status" aria-live="polite"></div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
@if(!empty($availableMethods) && $paypalAvailable)
@php $ppClient = config('services.paypal.client_id') ?: (function_exists('setting') ? (setting('paypal_client_id') ?: 'sb') : 'sb'); @endphp
<script src="https://www.paypal.com/sdk/js?client-id={{ $ppClient }}&currency=USD"></script>
@endif
<script>
(function(){
    const $paypalSection = $('#paypal-section');
    const $mpesaSection  = $('#mpesa-section');
    const $stripeSection = $('#stripe-section');
    const $paystackSection = $('#paystack-section');
    const $methodButtons = $('[data-method-toggle]');
    const $result        = $('#generic-result');
    const $amount        = $('#deposit_amount');
    const $phoneInput    = $('#mpesa_phone');
    const $kesPreview    = $('#mpesa_kes_preview');
    const $stkBtn        = $('#btn-start-stk');
    const $stkSpinner    = $('#stk-spinner');
    const $liveStatus    = $('#stk-live-status');

    const USD_TO_KES = {{ (float) (env('USD_TO_KES', 130)) }}; // configure in .env
    const REDIRECT_URL = @json(($redirectTo ?? null) ?: route('wallet.index'));

    function redirectSuccess() {
        try {
            const u = new URL(REDIRECT_URL, window.location.origin);
            u.searchParams.set('success', '1');
            window.location.href = u.toString();
        } catch (e) {
            // Fallback: best-effort append
            const sep = REDIRECT_URL.includes('?') ? '&' : '?';
            window.location.href = REDIRECT_URL + sep + 'success=1';
        }
    }

    const AVAILABLE_METHODS = @json($availableMethods);
    const DEFAULT_METHOD = @json($defaultGateway);

    function resolveDefault(){
        if (AVAILABLE_METHODS.includes(DEFAULT_METHOD)) return DEFAULT_METHOD;
        return AVAILABLE_METHODS[0] || null;
    }

    function setActive(method){
        if (!$methodButtons.length) return;
        $methodButtons.removeClass('is-active').attr('aria-pressed', 'false');
        $methodButtons.filter(`[data-method-toggle="${method}"]`).addClass('is-active').attr('aria-pressed', 'true');
    }

    function show(method){
        if (!AVAILABLE_METHODS.includes(method)) {
            method = resolveDefault();
        }
        if (!method) return;
        setActive(method);
        $result.removeClass('text-danger text-success').text('');
        $paypalSection.addClass('d-none');
        $mpesaSection.addClass('d-none');
        if ($stripeSection.length) { $stripeSection.addClass('d-none'); }
        if ($paystackSection.length) { $paystackSection.addClass('d-none'); }

        if(method === 'paypal'){
            $paypalSection.removeClass('d-none');
        } else if (method === 'mpesa') {
            $mpesaSection.removeClass('d-none');
            updateKesPreview();
        } else if (method === 'stripe' && $stripeSection.length) {
            $stripeSection.removeClass('d-none');
        } else if (method === 'paystack' && $paystackSection.length) {
            $paystackSection.removeClass('d-none');
        }
    }

    function updateKesPreview(){
        const usd = parseFloat($amount.val() || '0');
        const kes = Math.ceil(usd * USD_TO_KES);
        $kesPreview.val('KES ' + kes.toFixed(2));
    }

    // Normalize Kenyan phone to 2547XXXXXXXX
    function normalizeMsisdn(raw) {
        if (!raw) return null;
        let p = String(raw).replace(/\D/g, '');
        if (p.startsWith('0') && p.length === 10) p = '254' + p.substring(1);
        else if (p.startsWith('7') && p.length === 9) p = '254' + p;
        if (/^2547\d{8}$/.test(p)) return p;
        return null;
    }

    // Toggle buttons
    $('#btn-paypal').on('click', () => show('paypal'));
    $('#btn-stripe').on('click', () => show('stripe'));
    $('#btn-paystack').on('click', () => show('paystack'));
    $('#btn-mpesa').on('click', () => show('mpesa'));

    // Default view
    show(resolveDefault());

    // Stripe checkout (hosted)
    const $stripeCheckoutBtn = $('#btn-stripe-checkout');
    if ($stripeCheckoutBtn.length) {
        $stripeCheckoutBtn.on('click', function(){
            const amount = $amount.val();
            if (!amount || parseFloat(amount) <= 0) {
                $result.addClass('text-danger').text('Please enter a valid USD amount before proceeding.');
                return;
            }
            $stripeCheckoutBtn.prop('disabled', true).addClass('disabled');
            $result.removeClass('text-danger text-success').text('Redirecting to Stripe...');
            $.post(@json(route('wallet.deposit.stripe.session')), {
                _token: @json(csrf_token()),
                amount: amount,
                currency: 'USD',
                redirect_to: REDIRECT_URL
            }, function(resp){
                if (resp?.success && resp?.url) {
                    window.location = resp.url;
                } else {
                    $stripeCheckoutBtn.prop('disabled', false).removeClass('disabled');
                    $result.addClass('text-danger').text(resp?.message || 'Unable to start Stripe checkout.');
                }
            }).fail(function(xhr){
                $stripeCheckoutBtn.prop('disabled', false).removeClass('disabled');
                $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
            });
        });
    }

    // Paystack checkout (hosted)
    const $paystackCheckoutBtn = $('#btn-paystack-checkout');
    if ($paystackCheckoutBtn.length) {
        $paystackCheckoutBtn.on('click', function(){
            const amount = $amount.val();
            if (!amount || parseFloat(amount) <= 0) {
                $result.addClass('text-danger').text('Please enter a valid USD amount before proceeding.');
                return;
            }
            $paystackCheckoutBtn.prop('disabled', true).addClass('disabled');
            $result.removeClass('text-danger text-success').text('Redirecting to Paystack...');
            $.post(@json(route('wallet.deposit.paystack.session')), {
                _token: @json(csrf_token()),
                amount: amount,
                currency: 'USD',
                redirect_to: REDIRECT_URL
            }, function(resp){
                if (resp?.success && resp?.url) {
                    window.location = resp.url;
                } else {
                    $paystackCheckoutBtn.prop('disabled', false).removeClass('disabled');
                    $result.addClass('text-danger').text(resp?.message || 'Unable to start Paystack checkout.');
                }
            }).fail(function(xhr){
                $paystackCheckoutBtn.prop('disabled', false).removeClass('disabled');
                $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
            });
        });
    }

    // Keep KES preview in sync
    $amount.on('input', updateKesPreview);

    // PayPal buttons (unchanged)
    @if(!empty($availableMethods) && $paypalAvailable)
    paypal.Buttons({
        createOrder: function(data, actions) {
            const amount = $amount.val();
            if (!amount || parseFloat(amount) <= 0) {
                $result.addClass('text-danger').text('Please enter a valid USD amount before proceeding.');
                return;
            }
            return actions.order.create({
                purchase_units: [{ amount: { value: amount } }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                const amount = $amount.val();
                $.post("{{ route('wallet.deposit.paypal') }}", {
                    _token: '{{ csrf_token() }}',
                    amount: amount,
                    method: 'paypal',
                    order_id: details.id || null
                }, function(resp) {
                    if (resp.success) {
                        redirectSuccess();
                    } else {
                        $result.addClass('text-danger').text(resp.message || 'Deposit failed. Please try again later.');
                    }
                }).fail(function(xhr) {
                    $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.error ?? 'Unknown error'));
                });
            });
        },
        onError: function(err) {
            $result.addClass('text-danger').text('PayPal error: ' + (err?.message || 'Unknown error'));
        }
    }).render('#paypal-button-container');
    @endif

    // === M-Pesa STK push with live polling & redirect ===
    let pollTimer = null;
    const POLL_INTERVAL_MS = 3000;
    const MAX_POLLS = 40; // ~2 minutes

    function startPolling(ref) {
        let attempts = 0;
        clearInterval(pollTimer);

        $liveStatus.removeClass('d-none alert-danger alert-success alert-warning').addClass('alert').html(`
            <i class="fa fa-sync-alt fa-spin me-2"></i>
            Waiting for M-Pesa confirmation... (this can take up to 2 minutes)
        `);

        pollTimer = setInterval(function(){
            attempts++;
            $.get("{{ route('wallet.deposit.mpesa.status', '__REF__') }}".replace('__REF__', encodeURIComponent(ref)), function(resp){
                const msg = resp?.message || '';
                if (resp?.status === 'success') {
                    clearInterval(pollTimer);
                    $liveStatus.removeClass('alert-warning alert-danger').addClass('alert-success').html(`
                        <i class="fa fa-check-circle me-2"></i>
                        Payment successful! Redirecting to your wallet...
                    `);
                    setTimeout(() => redirectSuccess(), 1200);
                    return;
                }
                if (resp?.status === 'failed') {
                    clearInterval(pollTimer);
                    $liveStatus.removeClass('alert-warning alert-success').addClass('alert-danger').html(`
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        Payment failed: ${msg || 'Unknown error'}.
                    `);
                    return;
                }
                // keep spinning; optionally render intermediate messages
            }).fail(function(){
                // network hiccup; continue polling unless max reached
            });

            if (attempts >= MAX_POLLS) {
                clearInterval(pollTimer);
                $liveStatus.removeClass('alert-success alert-danger').addClass('alert-warning').html(`
                    <i class="fa fa-hourglass-half me-2"></i>
                    It's taking longer than expected to confirm. If you've approved the prompt, please check your wallet shortly.
                `);
            }
        }, POLL_INTERVAL_MS);
    }

    $stkBtn.on('click', function(){
        const usd = parseFloat($amount.val() || '0');
        if (!usd || usd <= 0) {
            $result.addClass('text-danger').text('Please enter a valid USD amount before proceeding.');
            return;
        }
        const normalized = normalizeMsisdn($phoneInput.val());
        if (!normalized) {
            $result.addClass('text-danger').text('Enter a valid Safaricom number (07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX).');
            return;
        }

        $stkBtn.prop('disabled', true);
        $stkSpinner.removeClass('d-none');
        $result.removeClass('text-danger text-success').text('');
        $liveStatus.addClass('d-none').empty();

        $.post("{{ route('wallet.deposit.mpesa.stk') }}", {
            _token: '{{ csrf_token() }}',
            phone: normalized,
            usd_amount: usd
        }, function(resp){
            if (resp.success && resp.ref) {
                $result.addClass('text-success').text('STK Push sent. Check your phone and approve.');
                startPolling(resp.ref);
            } else {
                $result.addClass('text-danger').text(resp.message || 'Failed to start STK Push.');
            }
        }).fail(function(xhr){
            $result.addClass('text-danger').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        }).always(function(){
            $stkBtn.prop('disabled', false);
            $stkSpinner.addClass('d-none');
        });
    });
})();
</script>
@endsection
