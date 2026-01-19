{{-- resources/views/seller/orders/payments.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Payments')

@section('content')
<div class="content">

    {{-- ───────────── PAGE TITLE & FILTERS  ───────────── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h2 class="text-success mb-0">
            <i class="bi bi-credit-card-2-back me-1"></i>
            Payments
        </h2>

        {{-- Filters --}}
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="status" class="form-label mb-0 small text-muted">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="success" {{ request('status')=='success'?'selected':'' }}>Success</option>
                    <option value="failed"  {{ request('status')=='failed' ?'selected':'' }}>Failed</option>
                </select>
            </div>

            <div class="col-auto">
                <label for="method" class="form-label mb-0 small text-muted">Method</label>
                <select name="method" id="method" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="mpesa"  {{ request('method')=='mpesa' ?'selected':'' }}>M-Pesa</option>
                    <option value="paypal" {{ request('method')=='paypal'?'selected':'' }}>PayPal</option>
                    <option value="stripe" {{ request('method')=='stripe'?'selected':'' }}>Stripe</option>
                    <option value="paystack" {{ request('method')=='paystack'?'selected':'' }}>Paystack</option>
                    <option value="cash"   {{ request('method')=='cash'  ?'selected':'' }}>Cash</option>
                    <option value="card"   {{ request('method')=='card'  ?'selected':'' }}>Card</option>
                </select>
            </div>

            <div class="col-auto">
                <label for="per_page" class="form-label mb-0 small text-muted">Per Page</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm">
                    @foreach ([20,50,100] as $size)
                        <option value="{{ $size }}" {{ request('per_page',20)==$size?'selected':'' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-success">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
                @if(request()->hasAny(['status','method','per_page']))
                    <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    @if ($payments->isNotEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
                <span>Payment History ({{ $payments->total() }})</span>
                <small class="text-muted">
                    Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Paid&nbsp;On</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach ($payments as $payment)
                        @php
                            $badge = [
                                'pending' => 'secondary',
                                'success' => 'success',
                                'failed'  => 'danger',
                            ][$payment->status] ?? 'dark';

                            $symbol = currency_symbol();
                        @endphp
                        <tr>
                            <td>{{ $payment->id }}</td>

                            {{-- Copy-to-clipboard helper via tooltip --}}
                            <td>
                                <span class="text-nowrap"
                                      data-bs-toggle="tooltip"
                                      data-bs-placement="top"
                                      data-bs-title="Click to copy"
                                      style="cursor:pointer"
                                      onclick="navigator.clipboard.writeText('{{ $payment->local_transaction_id }}')">
                                    {{ \Illuminate\Support\Str::limit($payment->local_transaction_id, 18, '…') }}
                                </span>
                            </td>

                            <td class="text-end">{{ money($payment->total_amount) }}</td>

                            <td>
                                <span class="d-inline-flex align-items-center gap-1 text-capitalize">
                                    @switch($payment->payment_method)
                                        @case('mpesa')   <i class="bi bi-phone"></i> @break
                                        @case('paypal')  <i class="bi bi-paypal"></i> @break
                                        @case('stripe')  <i class="bi bi-credit-card-2-front"></i> @break
                                        @case('paystack')  <i class="bi bi-credit-card-2-front"></i> @break
                                        @case('card')    <i class="bi bi-credit-card-2-front"></i> @break
                                        @default         <i class="bi bi-cash-stack"></i>
                                    @endswitch
                                    {{ $payment->payment_method }}
                                </span>
                            </td>

                            

                            <td>{{ optional($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer">
                      {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <div class="alert alert-info mt-4">
            <i class="bi bi-exclamation-circle me-1"></i>
            No payments recorded yet.
        </div>
    @endif
</div>
@endsection

