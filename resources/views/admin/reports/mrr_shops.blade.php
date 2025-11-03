{{-- resources/views/admin/reports/mrr_shops.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4 mb-0">MRR — Shops ({{ $label }})</h2>
@endsection

@section('content')
<div class="content">
  <div class="mb-3">
    <a href="{{ route('admin.reports.mrr') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to MRR</a>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
  <form method="GET" action="{{ route('admin.reports.mrr.shops', $ms->format('Y-m')) }}" class="card shadow-sm mb-3">
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
      @if(!empty($plans))
      <div class="col-12 col-md-4">
        <label class="form-label">Plan</label>
        <select name="plan" class="form-select">
          <option value="">All</option>
          @foreach(($plans ?? []) as $pl)
            <option value="{{ $pl }}" @selected(($filters['plan'] ?? '') === $pl)>{{ $pl }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="col-12 col-md-4 d-flex gap-2">
        <button class="btn btn-primary mt-auto">Filter</button>
        <a href="{{ route('admin.reports.mrr.shops', $ms->format('Y-m')) }}" class="btn btn-outline-secondary mt-auto">Reset</a>
        <a href="{{ route('admin.reports.mrr.shops.export', $ms->format('Y-m')) }}?payment_method={{ urlencode($filters['payment_method'] ?? '') }}&plan={{ urlencode($filters['plan'] ?? '') }}" class="btn btn-success mt-auto">Download CSV</a>
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
          <th class="d-none d-lg-table-cell">Start</th>
          <th class="d-none d-lg-table-cell">End</th>
          <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($subs as $s)
          <tr>
            <td>
              @if(!empty($s->shop))
                <a href="{{ route('shop.show', $s->shop->slug) }}" class="text-decoration-none" target="_blank">{{ $s->shop->name }}</a>
              @else
                &mdash;
              @endif
            </td>
            <td class="d-none d-md-table-cell">{{ $s->user->name ?? '—' }}</td>
            <td class="text-end">{{ get_currency() }} {{ number_format((float)$s->amount, 2) }}</td>
            <td class="d-none d-lg-table-cell">{{ optional($s->start_date)->format('Y-m-d') }}</td>
            <td class="d-none d-lg-table-cell">{{ optional($s->end_date)->format('Y-m-d') ?? '—' }}</td>
            <td>
              <span class="badge bg-{{ $s->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($s->status ?? 'n/a') }}</span>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-muted">No active subscriptions found for this month.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection


