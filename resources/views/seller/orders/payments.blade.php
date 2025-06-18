{{-- resources/views/seller/orders/payments.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Payments')

@section('content')
<div class="content">

    {{-- ───────────── PAGE TITLE  ───────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success mb-0">
            <i class="bi bi-credit-card-2-back me-1"></i>
            Payments
        </h2>
    </div>

    @if ($payments->isNotEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-semibold">
                Payment History ({{ $payments->count() }})
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Status</th>
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

                            $symbol = config('app.currency_symbol','KES');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            {{-- Copy-to-clipboard helper via tooltip --}}
                            <td>
                                <span class="text-nowrap"
                                      data-bs-toggle="tooltip"
                                      data-bs-placement="top"
                                      data-bs-title="Click to copy"
                                      style="cursor:pointer"
                                      onclick="navigator.clipboard.writeText('{{ $payment->local_transaction_id }}')">
                                    {{ Str::limit($payment->local_transaction_id, 18, '…') }}
                                </span>
                            </td>

                            <td class="text-end">
                                {{ $symbol }} {{ number_format($payment->total_amount, 2) }}
                            </td>

                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    @switch($payment->payment_method)
                                        @case('mpesa')   <i class="bi bi-phone"></i> @break
                                        @case('paypal')  <i class="bi bi-paypal"></i> @break
                                        @default         <i class="bi bi-cash-stack"></i>
                                    @endswitch
                                    {{ ucfirst($payment->payment_method) }}
                                </span>
                            </td>

                            <td>
    <span class="badge {{ $payment->status_badge_class }}">
        {{ ucfirst($payment->status_label) }}
    </span>
</td>

                            <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-4">
            <i class="bi bi-exclamation-circle me-1"></i>
            No payments recorded for this order.
        </div>
    @endif
</div>
@endsection

@push('scripts')
{{-- Enable Bootstrap tooltips once per page --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.forEach(tt => new bootstrap.Tooltip(tt));
});
</script>
@endpush
