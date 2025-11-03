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
            <td>{{ $s->shop->name ?? '—' }}</td>
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

