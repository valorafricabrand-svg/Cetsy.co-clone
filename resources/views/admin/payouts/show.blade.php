@extends('layouts.app')
@section('title','Payout #'.$payout->id)

@section('content')
<div class="content py-4">
    <div class="container-xxl" style="max-width: 700px">

        <h2 class="mb-4">Payout&nbsp;#{{ $payout->id }}</h2>

        <ul class="list-group mb-4">
            <li class="list-group-item d-flex justify-content-between">
                <span>Seller</span>
                <span>{{ $payout->wallet->user->name ?? $payout->user_id }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Amount</span>
                <span>{{ get_currency() }} {{ number_format($payout->amount,2) }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Status</span>
                <span class="text-capitalize">{{ $payout->status }}</span>
            </li>
            @if($payout->paymentMethod)
            <li class="list-group-item d-flex justify-content-between">
                <span>Payment Method</span>
                <span>{{ optional($payout->paymentMethod->paymentType)->name }} — {{ $payout->paymentMethod->account_name }}</span>
            </li>
            @endif
            <li class="list-group-item"><span class="fw-semibold">Meta / Bank details</span><br>
                <pre class="mb-0 small bg-light p-2">{{ json_encode($payout->meta, JSON_PRETTY_PRINT) }}</pre>
            </li>
        </ul>

        {{-- ACTION BUTTONS --}}
        @if($payout->status === 'pending')
            <form method="POST" action="{{ route('admin.payouts.approve',$payout) }}" class="d-inline">
                @csrf
                <button class="btn btn-success">Approve</button>
            </form>

            <!-- Reject with reason -->
            <button class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                Reject
            </button>
        @elseif($payout->status === 'approved')
            <!-- Mark as paid -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paidModal">
                Mark&nbsp;Paid
            </button>
        @endif

        <a href="{{ route('admin.payouts.index') }}" class="btn btn-link ms-2">Back</a>
    </div>
</div>

{{-- Reject modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.payouts.reject',$payout) }}" class="modal-content">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject Payout</h5></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Reason (shown to seller)</label>
                <textarea name="reason" class="form-control" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger">Reject &amp; Refund</button>
        </div>
    </form>
  </div>
</div>

{{-- Mark paid modal --}}
<div class="modal fade" id="paidModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.payouts.paid',$payout) }}" class="modal-content needs-validation" novalidate>
        @csrf
        <div class="modal-header"><h5 class="modal-title">Mark as Paid</h5></div>
        <div class="modal-body">
            <div class="alert alert-info small">
                <div><span class="fw-semibold">Seller:</span> {{ $payout->wallet->user->name ?? $payout->user_id }}</div>
                <div><span class="fw-semibold">Amount:</span> {{ get_currency() }} {{ number_format($payout->amount,2) }}</div>
                @if($payout->paymentMethod)
                  <div><span class="fw-semibold">Method:</span> {{ optional($payout->paymentMethod->paymentType)->name }} — {{ $payout->paymentMethod->account_name }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">Transaction Reference (optional)</label>
                <input type="text" name="txn_reference" class="form-control">
            </div>
            @php
              $methodName = optional(optional($payout->paymentMethod)->paymentType)->name;
              $methodAccount = optional($payout->paymentMethod)->account_number;
              $supportsAuto = false;
              $autoLabel = 'Automatic via Method';
              if ($methodName) {
                $n = strtolower($methodName);
                if (str_contains($n,'paypal')) { $supportsAuto = true; $autoLabel = 'Automatic via PayPal'; }
                if (str_contains($n,'mpesa') || str_contains($n,'m-pesa')) { $supportsAuto = true; $autoLabel = 'Automatic via M-Pesa'; }
              }
            @endphp
            <div class="mb-3">
              <label class="form-label">Disbursement</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="disburse" id="disb_manual" value="manual" checked>
                <label class="form-check-label" for="disb_manual">Manual (I sent funds outside the system)</label>
              </div>
              @if($supportsAuto)
              <div class="form-check mt-1">
                <input class="form-check-input" type="radio" name="disburse" id="disb_auto" value="auto">
                <label class="form-check-label" for="disb_auto">{{ $autoLabel }} <span class="text-muted">({{ $methodAccount }})</span></label>
              </div>
              @else
              <div class="form-text">This payout method doesn’t support automated disbursement.</div>
              @endif
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="confirmPaidCheck" required>
                <label class="form-check-label" for="confirmPaidCheck">
                    I confirm the funds were sent to the seller.
                </label>
                <div class="invalid-feedback">Please confirm funds were sent.</div>
            </div>
        </div>
        <div class="modal-footer d-flex align-items-center justify-content-between">
            <span class="badge bg-secondary">Paying: {{ get_currency() }} {{ number_format($payout->amount,2) }}</span>
            <button id="confirmPaidBtn" class="btn btn-primary" disabled>Confirm Paid</button>
        </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
  (function(){
    const modal = document.getElementById('paidModal');
    if(!modal) return;
    modal.addEventListener('shown.bs.modal', function(){
      const chk = document.getElementById('confirmPaidCheck');
      const btn = document.getElementById('confirmPaidBtn');
      if(!chk || !btn) return;
      function sync(){ btn.disabled = !chk.checked; }
      chk.addEventListener('change', sync);
      sync();
    });
  })();
  // Basic client-side Bootstrap validation
  (function(){
    document.querySelectorAll('.needs-validation').forEach(function(form){
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>
@endpush
@endsection
