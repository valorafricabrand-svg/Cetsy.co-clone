@extends('layouts.app')

@section('title', 'Wallet Transactions')

@section('content')
<div class="content">
    <div class="row gx-4 gy-4">
        <div class="col-12">

            {{-- Header --}}
          {{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="h5 fw-semibold mb-0">Wallet Overview</h2>
    <div class="d-flex align-items-center gap-2">
        {{-- View Payouts --}}
        @if(auth()->user()->isSeller())
        <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-sync-alt me-1"></i> View Payouts
        </a>
        @endif
        <a href="{{ route('wallet.deposit.form') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Deposit Funds
        </a>

        <button class="btn btn-primary btn-lg mt-3 mt-md-0"
                data-bs-toggle="modal"
                data-bs-target="#payoutModal"
                @disabled($balance < $minAmount || ($paymentMethods?->count() ?? 0) === 0)>
            Request&nbsp;Payout
        </button>

        
        <a href="{{ route('wallet.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </a>
    </div>
</div>


            {{-- Summary Card --}}
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-wallet fa-2x text-success me-3"></i>
                            <div>
                                <div class="text-muted small">Available Balance</div>
                                <div class="fs-4 fw-bold">
                                    USD {{ number_format($balance, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-pause-circle fa-2x text-warning me-3"></i>
                            <div>
                                <div class="text-muted small">On Hold</div>
                                <div class="fs-4 fw-bold">
                                    USD {{ number_format($onHold, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>






@if(($paymentMethods?->count() ?? 0) === 0)
  <div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
      Add a payout method to request payouts.
      <a href="{{ route('seller.payment-methods.index') }}" class="alert-link">Manage methods</a>.
    </div>
  </div>
@endif

{{-- Payout modal --}}
<div class="modal fade" id="payoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
      action="{{ route('seller.payouts.store') }}"
      class="modal-content needs-validation"
      novalidate>
    @csrf


    <div class="modal-header">
        <h5 class="modal-title">Request Payout</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        {{-- amount --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Amount</label>
            <input type="number"
                   name="amount"
                   class="form-control @error('amount') is-invalid @enderror"
                   step="0.01"
                   min="{{ number_format($minAmount, 2, '.', '') }}"
                    max="{{ number_format($maxPayout, 2, '.', '') }}"
                   value="{{ old('amount') }}"
                   required>
            <div class="invalid-feedback">@error('amount') {{ $message }} @else Required @enderror</div>
            <small class="text-muted d-block">Available: {{ get_currency() }} {{ number_format($balance,2) }}</small>
            <small class="text-muted d-block">Max request (before fee deducted): {{ get_currency() }} {{ number_format($maxPayout,2) }}</small>
            <small class="text-muted d-block">
              Fee rate: {{ number_format($feeRate * 100, 2) }}% &middot; Minimum: {{ get_currency() }} {{ number_format($minAmount,2) }}
            </small>
            <small class="text-muted d-block">
              Estimated fee: <span id="payoutFee">0.00</span> &middot; You receive: <span id="payoutNet">0.00</span>
            </small>
        </div>

        {{-- method --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Method</label>
            <select name="method"
                    class="form-select @error('method') is-invalid @enderror"
                    required>
                <option hidden value="">Choose&hellip;</option>
                @forelse($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod->id }}" {{ old('method') == $paymentMethod->id ? 'selected' : '' }}>
                        {{ $paymentMethod->paymentType->name }} - {{ $paymentMethod->account_name }}
                    </option>
                @empty
                    <option value="">No payment methods found</option>
                @endforelse
            </select>
            <div class="invalid-feedback">@error('method') {{ $message }} @else Required @enderror</div>
            @if(($paymentMethods?->count() ?? 0) === 0)
              <div class="form-text">
                No payout methods yet. <a href="{{ route('seller.payment-methods.index') }}">Add one</a> to continue.
              </div>
            @endif
        </div>

        {{-- Inline: Add new payout method --}}
        <div class="mb-3">
          <a class="small" data-bs-toggle="collapse" href="#addPayoutMethod" role="button" aria-expanded="false" aria-controls="addPayoutMethod">
            + Add a new payout method
          </a>
          <div class="collapse mt-2" id="addPayoutMethod">
            <div class="card card-body">
              <form action="{{ route('seller.payment-methods.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <select name="payment_type_id" class="form-select" required>
                      <option hidden value="">Choose&hellip;</option>
                      @foreach($paymentTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Account Name</label>
                    <input type="text" class="form-control" name="account_name" required>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Account Number</label>
                    <input type="text" class="form-control" name="account_number" required>
                  </div>
                </div>
                <div class="text-end mt-3">
                  <button class="btn btn-outline-primary btn-sm">Save Method</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        
    </div>

    <div class="modal-footer">
        <button class="btn btn-primary">
            Submit&nbsp;Request
        </button>
    </div>
</form>

    </div>
</div>


            {{-- Filters --}}
            <form method="GET" action="{{ route('wallet.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
            </select>
        </div>
                <div class="col-md-3">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To date">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>

            {{-- Transactions Table --}}
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Credit (USD)</th>
                            <th class="text-end">Debit (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                                <td>{{ $transaction->description ?? 'Transaction' }}</td>
                                <td>{{ ucfirst(str_replace('_',' ', $transaction->status ?? 'completed')) }}</td>
                                <td class="text-end text-success">
                                    {{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
      
                  {{ $transactions->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const input = document.querySelector('#payoutModal input[name="amount"]');
    if(!input) return;
    const feeRate = {{ json_encode($feeRate) }};
    const feeEl = document.getElementById('payoutFee');
    const netEl = document.getElementById('payoutNet');
    const fmt = (n) => (isFinite(n) ? Number(n).toFixed(2) : '0.00');
    function recalc(){
      const amt = parseFloat(input.value || '0');
      const fee = Math.round((amt * feeRate) * 100) / 100;
      const net = Math.max(0, amt - fee);
      if (feeEl) feeEl.textContent = fmt(fee);
      if (netEl) netEl.textContent = fmt(net);
    }
    input.addEventListener('input', recalc);
    recalc();
  })();
</script>
@endpush
