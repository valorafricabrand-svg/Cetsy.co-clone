@extends('layouts.app')
@section('title', 'Payment Details')

@section('content')
<div class="content py-4">
    <div class="container-xxl">
        <h2 class="mb-4">Payment #{{ $payment->id }}</h2>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Payer</dt>
                    <dd class="col-sm-9">{{ $payment->customer->name ?? $payment->customer_id }}</dd>

                    <dt class="col-sm-3">Amount</dt>
                    <dd class="col-sm-9">{{ get_currency() }} {{ number_format($payment->total_amount, 2) }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9 text-capitalize">{{ $payment->payment_status }}</dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ $payment->created_at->format('d M Y, H:i') }}</dd>

                    <dt class="col-sm-3">Reference</dt>
                    <dd class="col-sm-9">{{ $payment->reference ?? '-' }}</dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $payment->description ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
    </div>
</div>
@endsection 