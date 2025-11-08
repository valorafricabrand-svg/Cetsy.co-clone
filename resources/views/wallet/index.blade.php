@extends('layouts.app')

@section('title', 'Wallet Transactions')

@section('content')
<div class="content">
    <div class="row gx-4 gy-4">
        <div class="col-12">

            {{-- Header --}}
          {{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <h2 class="h5 fw-semibold mb-0">Wallet Overview</h2>
    <div class="d-flex align-items-center gap-2">
        {{-- View Payouts --}}
        @if(auth()->user()->isSeller())
        <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-receipt me-1"></i> View Payouts
        </a>
        @endif
        <a href="{{ route('wallet.deposit.form') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Deposit Funds
        </a>

        @if(auth()->user()->isSeller())
          <button class="btn btn-primary btn-lg mt-3 mt-md-0"
                  data-bs-toggle="modal"
                  data-bs-target="#payoutModal"
                  @disabled($balance < $minAmount || ($paymentMethods?->count() ?? 0) === 0)>
              Request&nbsp;Payout
          </button>
        @endif

        
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
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-pause-circle fa-2x text-warning me-3"></i>
                                <div>
                                    <div class="text-muted small">On Hold</div>
                                    <div class="fs-4 fw-bold">
                                        USD {{ number_format($onHold, 2) }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('wallet.index', array_merge(request()->query(), ['status' => 'on_hold'])) }}" class="btn btn-sm btn-outline-secondary" title="View on-hold transactions">
                                View
                            </a>
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
    <input type="hidden" name="require_otp" value="1">


    <div class="modal-header">
        <h5 class="modal-title">Request Payout</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        {{-- amount --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Amount</label>
            <div class="input-group">
              <input type="number"
                     name="amount"
                     class="form-control @error('amount') is-invalid @enderror"
                     step="0.01"
                     min="{{ number_format($minAmount, 2, '.', '') }}"
                     max="{{ number_format($maxPayout, 2, '.', '') }}"
                     value="{{ old('amount') }}"
                     required>
              <button class="btn btn-outline-secondary"
                      type="button"
                      id="payoutMaxBtn"
                      tabindex="-1"
                      aria-label="Use available balance"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="Use available balance" @disabled($maxPayout <= 0)>Max</button>
            </div>
            <div class="invalid-feedback">@error('amount') {{ $message }} @else Required @enderror</div>
            <small class="text-muted d-block">Available: {{ get_currency() }} {{ number_format($balance,2) }}</small>
            <small class="text-muted d-block">Max request (before fee deducted): {{ get_currency() }} {{ number_format($maxPayout,2) }}</small>
            <small class="text-muted d-block">
              Fee rate: {{ number_format($feeRate * 100, 2) }}% &middot; Minimum: {{ get_currency() }} {{ number_format($minAmount,2) }}
            </small>
            <small class="text-muted d-block">
              Estimated fee: <span id="payoutFee">0.00</span> &middot; You receive: <span id="payoutNet">0.00</span>
            </small>
            @if($maxPayout <= 0)
              <small class="text-danger d-block">Insufficient balance to request a payout. Increase your available balance or wait for on-hold funds to be released.</small>
            @endif
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
                No payout methods yet. <a href="{{ route('seller.payment-methods.index') }}" target="_blank">Add one</a> to continue or use the button below.
              </div>
            @endif
        </div>

        {{-- Add new payout method (separate modal trigger) --}}
        <div class="mb-3">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addPayoutMethodModal">
            <i class="bi bi-plus-lg"></i> Add Payout Method
          </button>
          <small class="text-muted ms-2">After saving, this page refreshes and you can submit the payout.</small>
        </div>

        
    </div>

    <div class="modal-footer d-flex align-items-center justify-content-between">
        <span class="badge bg-secondary" id="payoutNetBadge">You receive: {{ get_currency() }} 0.00</span>
        <button id="payoutSubmitBtn" class="btn btn-primary" type="submit">
            Submit&nbsp;Request
        </button>
    </div>
</form>

</div>

@if(!empty($otpPendingPayout))
  <div id="payout-verify-inline" class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white fw-semibold">Verify Payout</div>
    <div class="card-body">
      <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.submit') ? route('seller.payouts.otp.submit', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/verify')) }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Verification Code</label>
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="6-digit code" required>
          @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Verify &amp; Submit</button>
          <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/verify')) }}" class="btn btn-outline-secondary">Open full verify page</a>
        </div>
      </form>
      <div class="mt-2 d-flex gap-3">
        <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.resend') ? route('seller.payouts.otp.resend', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/resend-otp')) }}" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-link p-0">Resend code</button>
        </form>
        <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.cancel') ? route('seller.payouts.otp.cancel', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/cancel')) }}" class="d-inline" onsubmit="return confirm('Cancel this payout request?');">
          @csrf
          <button class="btn btn-link text-danger p-0">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endif
</div>

{{-- Add Payout Method Modal (separate to avoid nested forms) --}}
<div class="modal fade" id="addPayoutMethodModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('seller.payment-methods.store') }}" method="POST" class="modal-content needs-validation" novalidate>
      @csrf
      <input type="hidden" name="redirect_to" value="{{ route('wallet.index') }}">
      <input type="hidden" name="open_payout" value="1">
      <div class="modal-header">
        <h5 class="modal-title">Add Payout Method</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
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
          <div class="col-12">
            <label class="form-label">Account Number</label>
            <input type="text" class="form-control" name="account_number" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Save Method</button>
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
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
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
                            <th>Hold Info</th>
                            <th class="text-end">Credit (USD)</th>
                            <th class="text-end">Debit (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    {{ $transaction->description ?? 'Transaction' }}
                                    @php
                                        $meta = is_array($transaction->meta) ? $transaction->meta : (json_decode($transaction->meta ?? '', true) ?: []);
                                        $orderId = $meta['order_id'] ?? ($transaction->order_id ?? null);
                                    @endphp
                                    @if($orderId && (auth()->user()->isSeller() ?? false))
                                        <div class="small text-muted">
                                            Order #{{ $orderId }}
                                            @if(\Illuminate\Support\Facades\Route::has('seller.orders.show'))
                                                <a href="{{ route('seller.orders.show', $orderId) }}">view</a>
                                            @else
                                                <a href="{{ url('/seller/orders/'.$orderId) }}">view</a>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>{{ ucfirst(str_replace('_',' ', $transaction->status ?? 'completed')) }}</td>
                                <td>
                                    @if(($transaction->status ?? null) === 'on_hold')
                                        @php
                                            $oid = $orderId ?? null;
                                            $o = $oid ? ($ordersById[$oid] ?? null) : null;
                                        @endphp
                                        @if($o)
                                            @php
                                                $status = strtolower((string)($o->status ?? ''));
                                                $eta = null;
                                                if ($status === 'shipped') {
                                                    $base = $o->shipped_at ?? $o->updated_at;
                                                    if ($base) {
                                                        $eta = $base->copy()->addDays($autoReleaseDays);
                                                    }
                                                }
                                            @endphp
                                            <div class="small">
                                                <span class="badge bg-light text-dark">Order {{ $status }}</span>
                                                @if($eta)
                                                    <div class="text-muted">ETA: {{ $eta->format('d M Y') }} (auto-release {{ $autoReleaseDays }} days after shipped)</div>
                                                @else
                                                    @if($status === 'processing' || $status === 'pending')
                                                        <div class="text-muted">Waiting for shipment</div>
                                                    @elseif($status === 'delivered' || $status === 'completed')
                                                        <div class="text-muted">Releasing shortly</div>
                                                    @else
                                                        <div class="text-muted">Release after shipment/delivery</div>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <div class="small text-muted">Awaiting payment confirmation</div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-success">
                                    {{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No transactions found.</td>
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
    // Support inline payout form (preferred) or fallback to old modal selectors
    let input = document.querySelector('#payout-inline input[name="amount"]');
    let methodSel = document.querySelector('#payout-inline select[name="method"]');
    if(!input){
      input = document.querySelector('#payoutModal input[name="amount"]');
      methodSel = document.querySelector('#payoutModal select[name="method"]');
    }
    if(!input) return;
    const feeRate = {{ json_encode($feeRate) }};
    const feeEl = document.getElementById('payoutFee');
    const netEl = document.getElementById('payoutNet');
    // methodSel defined above
    const submitBtn = document.getElementById('payoutSubmitBtn');
    const maxBtn = document.getElementById('payoutMaxBtn');
    const fmt = (n) => (isFinite(n) ? Number(n).toFixed(2) : '0.00');
    const currency = {{ json_encode(get_currency()) }};
    const netBadge = document.getElementById('payoutNetBadge');
    function recalc(){
      const amt = parseFloat(input.value || '0');
      const fee = Math.round((amt * feeRate) * 100) / 100;
      const net = Math.max(0, amt - fee);
      if (feeEl) feeEl.textContent = fmt(fee);
      if (netEl) netEl.textContent = fmt(net);
      if (netBadge) netBadge.textContent = 'You receive: ' + currency + ' ' + fmt(net);
    }
    function updateSubmitDisabled(){
      let disabled = false;
      const v = parseFloat(input.value || '');
      const min = parseFloat(input.min || '0');
      const max = parseFloat(input.max || '0');
      if (!isFinite(v) || v < min || v > max) disabled = true;
      if (methodSel && (!methodSel.value || methodSel.value === '')) disabled = true;
      if (submitBtn) submitBtn.disabled = disabled;
    }
    input.addEventListener('input', recalc);
    input.addEventListener('input', updateSubmitDisabled);
    if (methodSel) methodSel.addEventListener('change', updateSubmitDisabled);
    if (maxBtn) maxBtn.addEventListener('click', function(){
      if (!input) return;
      const maxStr = input.max || '0';
      const maxVal = parseFloat(maxStr);
      const minVal = parseFloat(input.min || '0');
      if (!isFinite(maxVal)) return;
      // Confirm if max is effectively zero or below minimum
      if (maxVal <= 0.01 || maxVal < minVal) {
        const msg = 'Your maximum payout is very low or below the minimum threshold. You likely need more available balance. Continue to fill Max?';
        if (!window.confirm(msg)) return;
      }
      input.value = maxStr;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    // Disable Max button dynamically based on max value
    (function syncMaxBtnDisabled(){
      const maxVal = parseFloat(input.max || '0');
      if (maxBtn) maxBtn.disabled = !(isFinite(maxVal) && maxVal > 0);
    })();
    recalc();
    updateSubmitDisabled();
  })();
  // Bootstrap tooltip init (best-effort)
  (function(){
    try {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){
        new bootstrap.Tooltip(el);
      });
    } catch (e) {}
  })();
  // Auto-open payout modal if requested (after adding method)
  @if(session('open_payout_modal') || request()->boolean('open_payout') || $errors->has('amount') || $errors->has('method'))
    document.addEventListener('DOMContentLoaded', function(){
      var el = document.getElementById('payoutModal');
      if (el) { try { new bootstrap.Modal(el).show(); } catch(e) {} }
    });
  @endif
</script>
@endpush
