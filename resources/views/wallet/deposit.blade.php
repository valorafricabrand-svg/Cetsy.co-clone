@extends('theme.'.theme().'.layouts.app')

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
    .payment-layout {
        display: flex;
        border-radius: 16px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        overflow: hidden;
        background: var(--panel);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }
    .payment-menu {
        width: 200px;
        background: #f1f5f9;
        border-right: 1px solid rgba(15, 23, 42, 0.08);
        padding: 1rem;
    }
    .payment-menu__title {
        font-size: 0.7rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    .payment-option {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        color: var(--ink);
        padding: 0.65rem 0.75rem;
        border-radius: 12px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        position: relative;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }
    .payment-option i { width: 18px; text-align: center; }
    .payment-option + .payment-option { margin-top: 0.5rem; }
    .payment-option:hover { background: #e2e8f0; }
    .payment-option.is-active {
        background: #ffffff;
        border-color: rgba(15, 23, 42, 0.12);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        color: var(--brand-strong);
    }
    .payment-option.is-active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0.4rem;
        bottom: 0.4rem;
        width: 3px;
        background: var(--brand);
        border-radius: 4px;
    }
    .payment-content {
        flex: 1;
        padding: 1.25rem 1.5rem;
        background: var(--panel);
    }
    .method-panel { display: none; }
    .method-panel.is-active { display: block; }
    .payment-content .payment-panel { margin-bottom: 0; }
    .mpesa-callout {
        border-radius: 14px;
        border: 1px solid rgba(16, 185, 129, 0.25);
        background: rgba(16, 185, 129, 0.08);
    }
    .wallet-result {
        min-height: 1.4rem;
        color: var(--ink);
    }
    .wallet-result--error { color: #dc2626; }
    .wallet-result--success { color: #059669; }
    .wallet-result--info { color: #334155; }
    .wallet-live-status {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    .wallet-live-status--neutral {
        background: #f8fafc;
        border-color: rgba(15, 23, 42, 0.10);
        color: #334155;
    }
    .wallet-live-status--warning {
        background: #fffbeb;
        border-color: #fcd34d;
        color: #92400e;
    }
    .wallet-live-status--success {
        background: #ecfdf5;
        border-color: #6ee7b7;
        color: #065f46;
    }
    .wallet-live-status--error {
        background: #fff1f2;
        border-color: #fda4af;
        color: #9f1239;
    }
    .is-disabled { opacity: .6; pointer-events: none; }
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
    @media (max-width: 768px) {
        .payment-layout { flex-direction: column; }
        .payment-menu {
            width: 100%;
            border-right: 0;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.5rem;
        }
        .payment-menu__title { grid-column: 1 / -1; margin-bottom: 0.25rem; }
        .payment-option + .payment-option { margin-top: 0; }
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

@section('main')
<div class="content wallet-deposit">
    <div class="grid grid-cols-12 gap-4 justify-center deposit-shell">
        <div class="md:col-span-9 lg:col-span-9 xl:col-span-8">

            <div class="mt-3 rounded-2xl border border-slate-200 bg-white shadow-sm wallet-deposit__card">
                <div class="p-4 sm:p-5">

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
                        <label for="deposit_amount" class="mb-1 block text-sm font-medium text-slate-700">Deposit Amount (USD)</label>
                        <input type="number" id="deposit_amount" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" min="1" step="0.01" required placeholder="Enter amount e.g. 25.00"
                               value="{{ isset($defaultAmount) && $defaultAmount ? number_format((float)$defaultAmount, 2, '.', '') : '' }}">
                        <div class="mt-1 text-xs text-slate-500">Your wallet is in USD. For M-Pesa we'll auto-convert to KES using our configured rate.</div>
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

                    {{-- Payment Methods --}}
                    @if(empty($availableMethods))
                      <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 text-center">
                        No payment gateways are enabled/configured. Please contact support.
                      </div>
                    @else
                      <div class="payment-layout">
                        <aside class="payment-menu">
                          <div class="payment-menu__title">Pay with</div>
                          @if($paypalAvailable)
                            <button type="button" class="payment-option {{ $defaultGateway === 'paypal' ? 'is-active' : '' }}" data-method="paypal" aria-pressed="{{ $defaultGateway === 'paypal' ? 'true' : 'false' }}">
                              <i class="fab fa-paypal"></i>
                              <span>PayPal</span>
                            </button>
                          @endif
                          @if($stripeAvailable)
                            <button type="button" class="payment-option {{ $defaultGateway === 'stripe' ? 'is-active' : '' }}" data-method="stripe" aria-pressed="{{ $defaultGateway === 'stripe' ? 'true' : 'false' }}">
                              <i class="fa fa-credit-card"></i>
                              <span>Card (Stripe)</span>
                            </button>
                          @endif
                          @if($paystackAvailable)
                            <button type="button" class="payment-option {{ $defaultGateway === 'paystack' ? 'is-active' : '' }}" data-method="paystack" aria-pressed="{{ $defaultGateway === 'paystack' ? 'true' : 'false' }}">
                              <i class="fa fa-check-circle"></i>
                              <span>Paystack</span>
                            </button>
                          @endif
                          @if($mpesaAvailable)
                            <button type="button" class="payment-option {{ $defaultGateway === 'mpesa' ? 'is-active' : '' }}" data-method="mpesa" aria-pressed="{{ $defaultGateway === 'mpesa' ? 'true' : 'false' }}">
                              <i class="fa fa-mobile"></i>
                              <span>M-Pesa</span>
                            </button>
                          @endif
                        </aside>

                        <div class="payment-content">
                          @if($paypalAvailable)
                            <div id="paypal-section" class="payment-panel method-panel {{ $defaultGateway === 'paypal' ? 'is-active' : '' }}" data-method="paypal">
                              <p class="text-slate-500 text-center">Proceed securely using PayPal or card.</p>
                              <div id="paypal-button-container" class="text-center"></div>
                            </div>
                          @endif

                          @if($stripeAvailable)
                            <div id="stripe-section" class="payment-panel method-panel {{ $defaultGateway === 'stripe' ? 'is-active' : '' }}" data-method="stripe">
                              <p class="text-slate-500 text-center">Pay securely by card using Stripe checkout.</p>
                              <div class="grid">
                                  <button type="button" id="btn-stripe-checkout" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-900 text-white hover:bg-slate-700">
                                      Continue to Stripe
                                  </button>
                              </div>
                            </div>
                          @endif

                          @if($paystackAvailable)
                            <div id="paystack-section" class="payment-panel method-panel {{ $defaultGateway === 'paystack' ? 'is-active' : '' }}" data-method="paystack">
                              <p class="text-slate-500 text-center">Pay securely via Paystack checkout.</p>
                              <div class="grid">
                                  <button type="button" id="btn-paystack-checkout" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                      Continue to Paystack
                                  </button>
                              </div>
                            </div>
                          @endif

                          @if($mpesaAvailable)
                          <div id="mpesa-section" class="payment-panel method-panel {{ $defaultGateway === 'mpesa' ? 'is-active' : '' }}" data-method="mpesa">
                              <div class="rounded-xl border px-4 py-3 text-sm mpesa-callout">
                                  <div class="flex items-center">
                                      <i class="fa fa-mobile mr-2"></i>
                                      <div>
                                          <strong>M-Pesa STK Push:</strong> We'll send a prompt to your phone. Enter your PIN to approve.
                                      </div>
                                  </div>
                              </div>

                              <div class="grid grid-cols-12 gap-3">
                                  <div class="md:col-span-7">
                                      <label for="mpesa_phone" class="mb-1 block text-sm font-medium text-slate-700">M-Pesa Phone (Safaricom)</label>
                                      <input type="text" id="mpesa_phone" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. 07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX" maxlength="13">
                                      <div class="mt-1 text-xs text-slate-500">We'll normalize to <code>2547XXXXXXXX</code>.</div>
                                  </div>
                                  <div class="md:col-span-5">
                                      <label class="mb-1 block text-sm font-medium text-slate-700">KES Amount (auto)</label>
                                      <input type="text" id="mpesa_kes_preview" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" disabled value="KES 0.00">
                                      <div class="mt-1 text-xs text-slate-500">Calculated from USD amount x rate.</div>
                                  </div>
                              </div>

                              <div class="mt-3 grid">
                                  <button id="btn-start-stk" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                      <span class="spinner hidden" id="stk-spinner"></span>
                                      Send STK Push
                                  </button>
                              </div>

                              <div class="text-xs text-slate-500 mt-2">
                                  By continuing you agree that M-Pesa transaction charges (if any) are borne by you.
                              </div>

                              {{-- Live status area when polling --}}
                              <div id="stk-live-status" class="wallet-live-status wallet-live-status--neutral mt-3 hidden"></div>
                          </div>
                          @endif

                          <div id="generic-result" class="wallet-result text-center mt-3 font-semibold" role="status" aria-live="polite"></div>
                        </div>
                      </div>
                    @endif

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
    const $methodButtons = $('.payment-option');
    const $methodPanels  = $('.method-panel');
    const $result        = $('#generic-result');
    const $amount        = $('#deposit_amount');
    const $phoneInput    = $('#mpesa_phone');
    const $kesPreview    = $('#mpesa_kes_preview');
    const $stkBtn        = $('#btn-start-stk');
    const $stkSpinner    = $('#stk-spinner');
    const $liveStatus    = $('#stk-live-status');
    let renderPaypalButtons = function () {};

    const USD_TO_KES = {{ (float) (env('USD_TO_KES', 130)) }}; // configure in .env
    const REDIRECT_URL = @json(($redirectTo ?? null) ?: route('wallet.index'));
    const RESULT_BASE_CLASSES = 'wallet-result--error wallet-result--success wallet-result--info';

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
        $methodButtons.filter('[data-method="' + method + '"]').addClass('is-active').attr('aria-pressed', 'true');
    }

    function show(method){
        if (!AVAILABLE_METHODS.includes(method)) {
            method = resolveDefault();
        }
        if (!method) return;
        setActive(method);
        $result.removeClass(RESULT_BASE_CLASSES).text('');
        $methodPanels.removeClass('is-active');
        const $panel = $methodPanels.filter('[data-method="' + method + '"]');
        if ($panel.length) {
            $panel.addClass('is-active');
        }
        if (method === 'mpesa') {
            updateKesPreview();
        }
        if (method === 'paypal') {
            renderPaypalButtons();
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

    function setResult(kind, message) {
        $result.removeClass(RESULT_BASE_CLASSES).text('');
        if (!message) return;
        if (kind === 'success') $result.addClass('wallet-result--success');
        else if (kind === 'error') $result.addClass('wallet-result--error');
        else $result.addClass('wallet-result--info');
        $result.text(message);
    }

    @if(!empty($availableMethods) && $paypalAvailable)
    let paypalRendered = false;
    renderPaypalButtons = function () {
        if (paypalRendered || typeof paypal === 'undefined') return;
        paypalRendered = true;
        paypal.Buttons({
            createOrder: function(data, actions) {
                const amount = $amount.val();
                if (!amount || parseFloat(amount) <= 0) {
                    setResult('error', 'Please enter a valid USD amount before proceeding.');
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
                            setResult('error', resp.message || 'Deposit failed. Please try again later.');
                        }
                    }).fail(function(xhr) {
                        setResult('error', 'Server error: ' + (xhr.responseJSON?.error ?? 'Unknown error'));
                    });
                });
            },
            onError: function(err) {
                setResult('error', 'PayPal error: ' + (err?.message || 'Unknown error'));
            }
        }).render('#paypal-button-container');
    };
    @endif

    $methodButtons.on('click', function(){
        show($(this).data('method'));
    });

    // Default view
    show(resolveDefault());

    // Stripe checkout (hosted)
    const $stripeCheckoutBtn = $('#btn-stripe-checkout');
    if ($stripeCheckoutBtn.length) {
        $stripeCheckoutBtn.on('click', function(){
            const amount = $amount.val();
            if (!amount || parseFloat(amount) <= 0) {
                setResult('error', 'Please enter a valid USD amount before proceeding.');
                return;
            }
            $stripeCheckoutBtn.prop('disabled', true).addClass('is-disabled');
            setResult('info', 'Redirecting to Stripe...');
            $.post(@json(route('wallet.deposit.stripe.session')), {
                _token: @json(csrf_token()),
                amount: amount,
                currency: 'USD',
                redirect_to: REDIRECT_URL
            }, function(resp){
                if (resp?.success && resp?.url) {
                    window.location = resp.url;
                } else {
                    $stripeCheckoutBtn.prop('disabled', false).removeClass('is-disabled');
                    setResult('error', resp?.message || 'Unable to start Stripe checkout.');
                }
            }).fail(function(xhr){
                $stripeCheckoutBtn.prop('disabled', false).removeClass('is-disabled');
                setResult('error', 'Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
            });
        });
    }

    // Paystack checkout (hosted)
    const $paystackCheckoutBtn = $('#btn-paystack-checkout');
    if ($paystackCheckoutBtn.length) {
        $paystackCheckoutBtn.on('click', function(){
            const amount = $amount.val();
            if (!amount || parseFloat(amount) <= 0) {
                setResult('error', 'Please enter a valid USD amount before proceeding.');
                return;
            }
            $paystackCheckoutBtn.prop('disabled', true).addClass('is-disabled');
            setResult('info', 'Redirecting to Paystack...');
            $.post(@json(route('wallet.deposit.paystack.session')), {
                _token: @json(csrf_token()),
                amount: amount,
                currency: 'USD',
                redirect_to: REDIRECT_URL
            }, function(resp){
                if (resp?.success && resp?.url) {
                    window.location = resp.url;
                } else {
                    $paystackCheckoutBtn.prop('disabled', false).removeClass('is-disabled');
                    setResult('error', resp?.message || 'Unable to start Paystack checkout.');
                }
            }).fail(function(xhr){
                $paystackCheckoutBtn.prop('disabled', false).removeClass('is-disabled');
                setResult('error', 'Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
            });
        });
    }

    // Keep KES preview in sync
    $amount.on('input', updateKesPreview);

    // === M-Pesa STK push with live polling & redirect ===
    let pollTimer = null;
    const POLL_INTERVAL_MS = 3000;
    const MAX_POLLS = 40; // ~2 minutes

    function startPolling(ref) {
        let attempts = 0;
        clearInterval(pollTimer);

        $liveStatus
            .removeClass('hidden wallet-live-status--error wallet-live-status--success wallet-live-status--warning')
            .addClass('wallet-live-status--neutral')
            .html(`
            <i class="fa fa-sync-alt fa-spin mr-2"></i>
            Waiting for M-Pesa confirmation... (this can take up to 2 minutes)
        `);

        pollTimer = setInterval(function(){
            attempts++;
            $.get("{{ route('wallet.deposit.mpesa.status', '__REF__') }}".replace('__REF__', encodeURIComponent(ref)), function(resp){
                const msg = resp?.message || '';
                if (resp?.status === 'success') {
                    clearInterval(pollTimer);
                    $liveStatus
                        .removeClass('wallet-live-status--neutral wallet-live-status--warning wallet-live-status--error')
                        .addClass('wallet-live-status--success')
                        .html(`
                        <i class="fa fa-check-circle mr-2"></i>
                        Payment successful! Redirecting to your wallet...
                    `);
                    setTimeout(() => redirectSuccess(), 1200);
                    return;
                }
                if (resp?.status === 'failed') {
                    clearInterval(pollTimer);
                    $liveStatus
                        .removeClass('wallet-live-status--neutral wallet-live-status--warning wallet-live-status--success')
                        .addClass('wallet-live-status--error')
                        .html(`
                        <i class="fa fa-exclamation-triangle mr-2"></i>
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
                $liveStatus
                    .removeClass('wallet-live-status--neutral wallet-live-status--success wallet-live-status--error')
                    .addClass('wallet-live-status--warning')
                    .html(`
                    <i class="fa fa-hourglass-half mr-2"></i>
                    It's taking longer than expected to confirm. If you've approved the prompt, please check your wallet shortly.
                `);
            }
        }, POLL_INTERVAL_MS);
    }

    $stkBtn.on('click', function(){
        const usd = parseFloat($amount.val() || '0');
        if (!usd || usd <= 0) {
            setResult('error', 'Please enter a valid USD amount before proceeding.');
            return;
        }
        const normalized = normalizeMsisdn($phoneInput.val());
        if (!normalized) {
            setResult('error', 'Enter a valid Safaricom number (07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX).');
            return;
        }

        $stkBtn.prop('disabled', true);
        $stkSpinner.removeClass('hidden');
        setResult('info', '');
        $liveStatus.addClass('hidden').empty();

        $.post("{{ route('wallet.deposit.mpesa.stk') }}", {
            _token: '{{ csrf_token() }}',
            phone: normalized,
            usd_amount: usd
        }, function(resp){
            if (resp.success && resp.ref) {
                setResult('success', 'STK Push sent. Check your phone and approve.');
                startPolling(resp.ref);
            } else {
                setResult('error', resp.message || 'Failed to start STK Push.');
            }
        }).fail(function(xhr){
            setResult('error', 'Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
        }).always(function(){
            $stkBtn.prop('disabled', false);
            $stkSpinner.addClass('hidden');
        });
    });
})();
</script>
@endsection




