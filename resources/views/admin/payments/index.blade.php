@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="content py-4">
    <div class="container-xxl">

        <h2 class="mb-4">Payments</h2>

        {{-- simple status filter --}}
        <form class="mb-3" method="GET">
            <div class="input-group w-auto">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['pending','completed','failed','refunded'] as $s)
                        <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-outline-secondary">Filter</button>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Payer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->customer->name ?? $payment->customer_id }}</td>
                                <td>{{ get_currency() }} {{ number_format($payment->total_amount,2) }}</td>
                                <td class="text-capitalize">{{ $payment->payment_status }}</td>
                                <td>{{ $payment->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-outline-secondary btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No payments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
{{ $payments->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>
</div>
@endsection 