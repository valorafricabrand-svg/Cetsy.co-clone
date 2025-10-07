{{-- resources/views/admin/payouts/index.blade.php --}}
@extends('layouts.app')
@section('title','Payout Requests')

@section('content')
<div class="content py-4">
  <div class="container-xxl">

    <h2 class="mb-3">Payout Requests</h2>

    @if(isset($metrics))
      <div class="row g-3 mb-4">
        @php $cur = get_currency(); @endphp
        @foreach([
          'pending' => ['label' => 'Pending', 'bg' => 'warning'],
          'approved'=> ['label' => 'Approved', 'bg' => 'info'],
          'sent'    => ['label' => 'Sent', 'bg' => 'primary'],
          'paid'    => ['label' => 'Paid', 'bg' => 'success'],
          'failed'  => ['label' => 'Failed', 'bg' => 'danger'],
          'rejected'=> ['label' => 'Rejected', 'bg' => 'secondary']
        ] as $key => $cfg)
          <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm border-0">
              <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="badge bg-{{ $cfg['bg'] }}">{{ $cfg['label'] }}</span>
                  <span class="small text-muted">{{ $metrics[$key]['count'] ?? 0 }}</span>
                </div>
                <div class="mt-2 fw-semibold">{{ $cur }} {{ number_format(($metrics[$key]['amount'] ?? 0), 2) }}</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- Filters toolbar --}}
    <form class="card shadow-sm border-0 mb-3" method="GET">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md-3">
            <label class="form-label">Search</label>
            <input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Payout ID, user ID, name or email">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">All</option>
              @foreach(['otp_pending','pending','approved','sent','rejected','failed','paid','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status')=== $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Payment Type</label>
            <select name="payment_type" class="form-select">
              <option value="">All</option>
              @isset($paymentTypes)
                @foreach($paymentTypes as $t)
                  <option value="{{ $t->id }}" {{ (string)request('payment_type') === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
              @endisset
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">From</label>
            <input type="date" class="form-control" name="from" value="{{ request('from') }}">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">To</label>
            <input type="date" class="form-control" name="to" value="{{ request('to') }}">
          </div>
          <div class="col-6 col-md-1">
            <label class="form-label">Min</label>
            <input type="number" step="0.01" class="form-control" name="min" value="{{ request('min') }}">
          </div>
          <div class="col-6 col-md-1">
            <label class="form-label">Max</label>
            <input type="number" step="0.01" class="form-control" name="max" value="{{ request('max') }}">
          </div>
          <div class="col-12 col-md-12 text-end">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.payouts.index') }}">Reset</a>
          </div>
        </div>
      </div>
    </form>

    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Seller</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Method</th>
              <th>Requested</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payouts as $p)
              <tr>
                <td>{{ $p->id }}</td>
                <td>{{ optional($p->user)->name ?? ('User #'.$p->user_id) }}</td>
                <td>{{ get_currency() }} {{ number_format($p->amount,2) }}</td>
                <td>
                  @php
                    $badge = 'secondary';
                    if ($p->status === 'pending') $badge='warning';
                    elseif ($p->status === 'approved') $badge='info';
                    elseif ($p->status === 'sent') $badge='primary';
                    elseif ($p->status === 'paid') $badge='success';
                    elseif (in_array($p->status, ['rejected','failed'])) $badge='danger';
                  @endphp
                  <span class="badge text-bg-{{ $badge }} text-uppercase">{{ $p->status }}</span>
                </td>
                <td>{{ optional(optional($p->paymentMethod)->paymentType)->name ?? '—' }}</td>
                <td>{{ $p->created_at->format('d M Y') }}</td>
                <td class="text-end">
                  <a href="{{ route('admin.payouts.show',$p) }}" class="btn btn-outline-secondary btn-sm">View</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">No payout requests found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">{{ $payouts->links() }}</div>
    </div>

  </div>
</div>
@endsection
