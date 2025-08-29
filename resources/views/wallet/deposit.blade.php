@extends('layouts.app')

@section('title', 'Deposit Funds')

@section('styles')
<style>
    .payment-toggle { margin-bottom: 1.25rem; text-align: center; }
    .payment-toggle .btn { min-width: 180px; }
    .payment-toggle .btn:not(:last-child) { margin-right: .5rem; }
    .d-none { display: none !important; }
    .opacity-50 { opacity: .5; }
    .spinner {
        display:inline-block; width:1.25rem; height:1.25rem; border:2px solid #ddd; border-top-color:#000;
        border-radius:50%; animation:spin .6s linear infinite; vertical-align:middle; margin-right:.5rem;
    }
    @keyframes spin { to { transform: rotate(360deg);} }
</style>
@endsection

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <div class="card shadow-sm border-0 mt-5">
                <div class="card-body p-4">

                    <h2 class="h5 fw-semibold mb-4 text-center">Deposit Funds to Wallet</h2>

                    {{-- Wallet Balance --}}
                    <div class="alert alert-info text-center">
                        Current Wallet Balance: <strong>USD {{ number_format($balance, 2) }}</strong>
                    </div>

                    {{-- Deposit Amount (USD for wallet) --}}
                    <div class="mb-3">
                        <label for="deposit_amount" class="form-label">Deposit Amount (USD)</label>
                        <input type="number" id="deposit_amount" class="form-control" min="1" step="0.01" required placeholder="Enter amount e.g. 25.00">
                        <div class="form-text">Your wallet is in USD. For M-Pesa we’ll auto-convert to KES using our configured rate.</div>
                    </div>

                    {{-- Payment Method Toggle --}}
                    <div class="payment-toggle">
                        <button type="button" id="btn-paypal" class="btn btn-outline-primary">Pay with PayPal / Card</button>
                        <button type="button" id="btn-mpesa"  class="btn btn-outline-success">Pay with M-Pesa (STK)</button>
                    </div>

                    {{-- PayPal Section --}}
                    <div id="paypal-section" class="mb-3 d-none">
                        <p class="text-muted text-center">Proceed securely using PayPal or card.</p>
                        <div id="paypal-button-container" class="text-center"></div>
                    </div>

                    {{-- M-Pesa Section --}}
                    <div id="mpesa-section" class="mb-3 d-none">
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-mobile me-2"></i>
                                <div>
                                    <strong>M-Pesa STK Push:</strong> We’ll send a prompt to your phone. Enter your PIN to approve.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="mpesa_phone" class="form-label">M-Pesa Phone (Safaricom)</label>
                                <input type="text" id="mpesa_phone" class="form-control" placeholder="e.g. 07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX" maxlength="13">
                                <div class="form-text">We’ll normalize to <code>2547XXXXXXXX</code>.</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">KES Amount (auto)</label>
                                <input type="text" id="mpesa_kes_preview" class="form-control" disabled value="KES 0.00">
                                <div class="form-text">Calculated from USD amount × rate.</div>
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

                    {{-- Result Message --}}
                    <div id="generic-result" class="text-center mt-3 fw-semibold"></div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id','sb') }}&currency=USD"></script>
<script>
(function(){
    const $paypalSection = $('#paypal-section');
    const $mpesaSection  = $('#mpesa-section');
    const $result        = $('#generic-result');
    const $amount        = $('#deposit_amount');
    const $phoneInput    = $('#mpesa_phone');
    const $kesPreview    = $('#mpesa_kes_preview');
    const $stkBtn        = $('#btn-start-stk');
    const $stkSpinner    = $('#stk-spinner');
    const $liveStatus    = $('#stk-live-status');

    const USD_TO_KES = {{ (float) (env('USD_TO_KES', 130)) }}; // configure in .env
    const REDIRECT_URL = "{{ route('wallet.index') }}";

    function show(method){
        $result.removeClass('text-danger text-success').text('');
        if(method === 'paypal'){
            $paypalSection.removeClass('d-none');
            $mpesaSection.addClass('d-none');
        }else{
            $mpesaSection.removeClass('d-none');
            $paypalSection.addClass('d-none');
            updateKesPreview();
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
    $('#btn-mpesa').on('click', () => show('mpesa'));

    // Default view
    show('paypal');

    // Keep KES preview in sync
    $amount.on('input', updateKesPreview);

    // PayPal buttons (unchanged)
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
                        window.location.href = REDIRECT_URL + '?success=1';
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
                    setTimeout(() => window.location.href = REDIRECT_URL + '?success=1', 1200);
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
