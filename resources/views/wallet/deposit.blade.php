@extends('layouts.app')

@section('title', 'Deposit Funds')

@section('styles')
<style>
    #paypal-section {
        display: none;
    }

    .payment-toggle {
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .payment-toggle .btn {
        min-width: 180px;
    }

    .payment-toggle .btn:not(:last-child) {
        margin-right: 0.5rem;
    }

    .btn[disabled] {
        pointer-events: none;
        opacity: 0.65;
    }
</style>
@endsection

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm border-0 mt-5">
                <div class="card-body p-4">

                    <h2 class="h5 fw-semibold mb-4 text-center">Deposit Funds to Wallet</h2>

                    {{-- Wallet Balance --}}
                    <div class="alert alert-info text-center">
                        Current Wallet Balance: <strong>USD {{ number_format($balance, 2) }}</strong>
                    </div>

                    {{-- Deposit Amount --}}
                    <div class="mb-3">
                        <label for="deposit_amount" class="form-label">Deposit Amount (USD)</label>
                        <input type="number" id="deposit_amount" class="form-control" min="1" step="0.01" required placeholder="Enter amount e.g. 25.00">
                    </div>

                    {{-- Payment Method Toggle --}}
                    <div class="payment-toggle">
                        <button type="button" id="btn-paypal" class="btn btn-primary">Pay with PayPal / Card</button>
                    </div>

                    {{-- PayPal Section --}}
                    <div id="paypal-section" class="mb-3">
                        <p class="text-muted text-center">Proceed securely using PayPal or card.</p>
                        <div id="paypal-button-container" class="text-center"></div>
                    </div>

                    {{-- Result Message --}}
                    <div id="generic-result" class="text-center mt-3 text-danger fw-semibold"></div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>

<script>
$(document).ready(function() {
    // Default view
    $('#btn-paypal').on('click', function() {
        $('#paypal-section').fadeIn(200);
        $('#generic-result').empty();
    }).trigger('click');

    paypal.Buttons({
        createOrder: function(data, actions) {
            const amount = $('#deposit_amount').val();
            if (!amount || parseFloat(amount) <= 0) {
                $('#generic-result').html('<span class="text-danger">Please enter a valid deposit amount before proceeding.</span>');
                return;
            }

            return actions.order.create({
                purchase_units: [{
                    amount: { value: amount }
                }]
            });
        },

        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                const amount = $('#deposit_amount').val();
                $.post("{{ route('wallet.deposit.paypal') }}", {
                    _token: '{{ csrf_token() }}',
                    amount: amount,
                    method: 'paypal'
                }, function(response) {
                    if (response.success) {
                        window.location.href = "{{ route('wallet.index') }}?success=1";
                    } else {
                        $('#generic-result').html('<span class="text-danger">Deposit failed. Please try again later.</span>');
                    }
                }).fail(function(xhr) {
                    $('#generic-result').html('<span class="text-danger">Server error: ' + (xhr.responseJSON?.error ?? 'Unknown error') + '</span>');
                });
            });
        },

        onError: function(err) {
            $('#generic-result').html('<span class="text-danger">PayPal error: ' + err.message + '</span>');
        }

    }).render('#paypal-button-container');
});
</script>
@endsection
