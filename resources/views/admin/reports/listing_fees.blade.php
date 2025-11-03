{{-- resources/views/admin/reports/listing_fees.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4 mb-0">Listing Fee Revenue</h2>
@endsection

@section('content')
<div class="content">
  <form method="GET" action="{{ route('admin.reports.listing-fees') }}" class="card shadow-sm mb-3">
    <div class="card-body row g-3 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label">From</label>
        <input type="month" name="from" value="{{ $fromYm ?? now()->subMonths(11)->format('Y-m') }}" class="form-control">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">To</label>
        <input type="month" name="to" value="{{ $toYm ?? now()->format('Y-m') }}" class="form-control">
      </div>
      <div class="col-12 col-md-4 d-flex gap-2">
        <button class="btn btn-primary mt-auto">Apply</button>
        <a href="{{ route('admin.reports.listing-fees') }}" class="btn btn-outline-secondary mt-auto">Reset</a>
      </div>
    </div>
  </form>

  <div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted small">Current Month</div>
          <div class="fs-3 fw-bold">{{ get_currency() }} {{ number_format($currentTotal, 2) }}</div>
          <div class="mt-2">
            <a href="{{ route('admin.reports.listing-fees.payments', 'current') }}" class="btn btn-sm btn-outline-primary">View payments</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-light fw-semibold">Last 12 Months</div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Month</th>
            <th class="text-end">Revenue</th>
          </tr>
        </thead>
        <tbody>
          @foreach($months as $m)
            <tr>
              <td>
                <a href="{{ route('admin.reports.listing-fees.payments', $m['ym']) }}" class="text-decoration-none">
                  {{ $m['label'] }}
                </a>
              </td>
              <td class="text-end">{{ get_currency() }} {{ number_format($m['amount'], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

