@extends('layouts.app')

@section('title', 'Deposit Funds')

@section('styles')
<style>
    .payment-toggle {
        margin-bottom: 1.25rem;
        text-align: center;
    }
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
                                <input type="text" id="mpesa_phone" class="form-control" placeholder="e.g. 07XXXXXXXX" maxlength="12">
                                <div class="form-text">Use Safaricom number (Kenya). We’ll normalize to 2547… format.</div>
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
    const $phone         = $('#mpesa_phone');
    const $kesPreview    = $('#mpesa_kes_preview');
    const $stkBtn        = $('#btn-start-stk');
    const $stkSpinner    = $('#stk-spinner');

    const USD_TO_KES = {{ (float) (env('USD_TO_KES', 130)) }}; // configure in .env

    function show(method){
        $result.removeClass('text-danger text-success').text('');
        if(method === 'paypal'){
            $paypalSection.removeClass('d-none');
            $mpesaSection.addClass('d-none');
        }else{
            // mpesa
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

    // Toggle buttons
    $('#btn-paypal').on('click', () => show('paypal'));
    $('#btn-mpesa').on('click', () => show('mpesa'));

    // Default view
    show('paypal');

    // Keep KES preview in sync
    $amount.on('input', updateKesPreview);

    // PayPal buttons
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
                        window.location.href = "{{ route('wallet.index') }}?success=1";
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

    // M-Pesa STK push
    $stkBtn.on('click', function(){
        const usd = parseFloat($amount.val() || '0');
        if (!usd || usd <= 0) {
            $result.addClass('text-danger').text('Please enter a valid USD amount before proceeding.');
            return;
        }
        const phone = ($phone.val() || '').trim();
        if (!phone) {
            $result.addClass('text-danger').text('Please enter your Safaricom phone number.');
            return;
        }

        $stkBtn.prop('disabled', true);
        $stkSpinner.removeClass('d-none');
        $result.removeClass('text-danger text-success').text('');

        $.post("{{ route('wallet.deposit.mpesa.stk') }}", {
            _token: '{{ csrf_token() }}',
            phone: phone,
            usd_amount: usd
        }, function(resp){
            if (resp.success) {
                $result.addClass('text-success').text('STK Push sent. Please check your phone and enter your M-Pesa PIN to approve.');
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
