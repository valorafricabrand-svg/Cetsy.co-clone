{{-- resources/views/admin/reports/transaction_fees.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4 mb-0">Transaction Fees</h2>
@endsection

@section('content')
<div class="content">
  <form method="GET" action="{{ route('admin.reports.transaction-fees') }}" class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-2">
          <label class="form-label">From</label>
          <input type="date" class="form-control" name="from" value="{{ request('from') }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">To</label>
          <input type="date" class="form-control" name="to" value="{{ request('to') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Seller</label>
          <select name="user_id" class="form-select">
            <option value="">All sellers</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Order ID</label>
          <input type="number" class="form-control" name="order_id" value="{{ request('order_id') }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">Reference</label>
          <input type="text" class="form-control" name="reference" value="{{ request('reference') }}">
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button class="btn btn-primary w-100">Filter</button>
        </div>
      </div>
    </div>
  </form>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">Total fees: <strong>{{ get_currency() }} {{ number_format($totalFees, 2) }}</strong></div>
    <a class="btn btn-outline-success btn-sm" href="{{ route('admin.reports.transaction-fees.export', request()->all()) }}">Download CSV</a>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Seller</th>
            <th>Order</th>
            <th>Reference</th>
            <th class="text-end">Fee (debit)</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
        @forelse($fees as $f)
          <tr>
            <td>{{ $f->id }}</td>
            <td><small class="text-muted">{{ $f->created_at->format('Y-m-d H:i') }}</small></td>
            <td>{{ optional($f->user)->name }}</td>
            <td>{{ data_get($f->meta,'order_id') }}</td>
            <td>{{ $f->reference }}</td>
            <td class="text-end">{{ get_currency() }} {{ number_format((float)$f->debit, 2) }}</td>
            <td>{{ $f->description }}</td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted py-4">No fees match your filter.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $fees->links('pagination::bootstrap-5') }}</div>
</div>
@endsection

