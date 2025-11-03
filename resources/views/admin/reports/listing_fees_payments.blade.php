{{-- resources/views/admin/reports/listing_fees_payments.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4 mb-0">Listing Fee — Payments ({{ $label }})</h2>
@endsection

@section('content')
<div class="content">
  <div class="mb-3">
    <a href="{{ route('admin.reports.listing-fees') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Listing Fees</a>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <form method="GET" action="{{ route('admin.reports.listing-fees.payments', $ms->format('Y-m')) }}" class="card shadow-sm mb-3">
        <div class="card-body row g-3 align-items-end">
          <div class="col-12 col-md-4">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select">
              <option value="">All</option>
              @foreach(($paymentMethods ?? []) as $pm)
                <option value="{{ $pm }}" @selected(($filters['payment_method'] ?? '') === $pm)>{{ ucfirst($pm) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-8 d-flex gap-2">
            <button class="btn btn-primary mt-auto">Filter</button>
            <a href="{{ route('admin.reports.listing-fees.payments', $ms->format('Y-m')) }}" class="btn btn-outline-secondary mt-auto">Reset</a>
            <a href="{{ route('admin.reports.listing-fees.export', $ms->format('Y-m')) }}?payment_method={{ urlencode($filters['payment_method'] ?? '') }}" class="btn btn-success mt-auto">Download CSV</a>
          </div>
        </div>
      </form>
      <div class="text-muted">Month: <strong>{{ $label }}</strong></div>
      <div class="fw-semibold">Total: {{ get_currency() }} {{ number_format($total, 2) }}</div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Shop</th>
            <th class="d-none d-md-table-cell">Owner</th>
            <th class="text-end">Amount</th>
            <th class="d-none d-lg-table-cell">Method</th>
            <th class="d-none d-lg-table-cell">Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $p)
            <tr>
              <td>
                @if(!empty($p->shop))
                  <a href="{{ route('shop.show', $p->shop->id) }}" class="text-decoration-none" target="_blank">{{ $p->shop->name }}</a>
                @else
                  &mdash;
                @endif
              </td>
              <td class="d-none d-md-table-cell">{{ optional(optional($p->shop)->user)->name ?? '—' }}</td>
              <td class="text-end">{{ get_currency() }} {{ number_format((float)$p->total_amount, 2) }}</td>
              <td class="d-none d-lg-table-cell">{{ strtoupper($p->payment_method ?? '-') }}</td>
              <td class="d-none d-lg-table-cell">{{ optional($p->created_at)->format('Y-m-d') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-muted">No listing fee payments found for this month.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

