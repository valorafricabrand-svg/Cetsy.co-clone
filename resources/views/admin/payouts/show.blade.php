@extends('layouts.app')
@section('title','Payout #'.$payout->id)

@section('content')
<div class="content py-4">
    <div class="container-xxl" style="max-width: 700px">

        <h2 class="mb-2">Payout&nbsp;#{{ $payout->id }}</h2>
        @php
          $st = $payout->status;
          $stepApproved = in_array($st, ['approved','sent','paid','failed','rejected']);
          $stepSent     = in_array($st, ['sent','paid','failed']);
          $stepPaid     = ($st === 'paid');
          $stepFailed   = in_array($st, ['failed','rejected']);
        @endphp
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
          <span class="badge {{ 'bg-success' }}"><i class="bi bi-check2 me-1"></i> Requested</span>
          <span class="badge {{ $stepApproved ? 'bg-success' : 'bg-secondary' }}">
            <i class="bi {{ $stepApproved ? 'bi-check2' : 'bi-dot' }} me-1"></i> Approved
          </span>
          <span class="badge {{ $stepSent ? 'bg-success' : 'bg-secondary' }}">
            <i class="bi {{ $stepSent ? 'bi-check2' : 'bi-dot' }} me-1"></i> Sent
          </span>
          @if($stepFailed)
            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Failed</span>
          @else
            <span class="badge {{ $stepPaid ? 'bg-success' : 'bg-secondary' }}">
              <i class="bi {{ $stepPaid ? 'bi-check2' : 'bi-dot' }} me-1"></i> Paid
            </span>
          @endif
        </div>

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

        {{-- DISBURSEMENT DETAILS --}}
        @php
          $methodName = optional(optional($payout->paymentMethod)->paymentType)->name;
          $supportsAuto = false; $autoLabel = 'Automatic via Method';
          if ($methodName) {
            $n = strtolower($methodName);
            if (str_contains($n,'paypal')) { $supportsAuto = true; $autoLabel = 'Automatic via PayPal'; }
            if (str_contains($n,'mpesa') || str_contains($n,'m-pesa')) { $supportsAuto = true; $autoLabel = 'Automatic via M-Pesa'; }
          }
        @endphp
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-white fw-semibold">Disbursement Details</div>
          <div class="card-body small">
            <div class="row g-3">
              <div class="col-md-4"><span class="fw-semibold">Supports Auto:</span> {{ $supportsAuto ? 'Yes' : 'No' }}</div>
              <div class="col-md-4"><span class="fw-semibold">Method:</span> {{ $methodName ?? 'N/A' }}</div>
              <div class="col-md-4"><span class="fw-semibold">Current Status:</span> <span class="text-capitalize">{{ $payout->status }}</span></div>
            </div>
            @if($payout->status === 'sent')
              <div class="alert alert-info mt-3 mb-0">Awaiting provider confirmation. You can resend automatic payout or override with manual mark paid.</div>
            @endif
          </div>
        </div>

        {{-- AUDIT TIMELINE --}}
        @php
          $approvedBy = $payout->approved_by ? optional(\App\Models\User::find($payout->approved_by))->name : null;
          $paidBy     = $payout->paid_by ? optional(\App\Models\User::find($payout->paid_by))->name : null;
          $rejectedBy = $payout->rejected_by ? optional(\App\Models\User::find($payout->rejected_by))->name : null;
          $sentAtIso  = data_get($payout->meta,'sent_at');
          try { $sentAtFmt = $sentAtIso ? \Carbon\Carbon::parse($sentAtIso)->format('d M Y, H:i') : null; } catch (\Throwable $e) { $sentAtFmt = $sentAtIso; }
          $failedReason = data_get($payout->meta,'failed_reason');
        @endphp
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-white fw-semibold">Audit Timeline</div>
          <div class="card-body small">
            <div class="row gy-2">
              <div class="col-12"><span class="fw-semibold">Requested:</span> {{ $payout->created_at?->format('d M Y, H:i') }}</div>
              @if($payout->approved_at)
                <div class="col-12"><span class="fw-semibold">Approved:</span> {{ $payout->approved_at->format('d M Y, H:i') }} @if($approvedBy) <span class="text-muted">by {{ $approvedBy }}</span> @endif</div>
              @endif
              @if(!empty($sentAtFmt))
                <div class="col-12"><span class="fw-semibold">Sent:</span> {{ $sentAtFmt }}</div>
              @endif
              @if($payout->paid_at)
                <div class="col-12"><span class="fw-semibold">Paid:</span> {{ $payout->paid_at->format('d M Y, H:i') }} @if($paidBy) <span class="text-muted">(manual by {{ $paidBy }})</span> @endif</div>
              @endif
              @if($payout->rejected_at)
                <div class="col-12"><span class="fw-semibold">Rejected:</span> {{ $payout->rejected_at->format('d M Y, H:i') }} @if($rejectedBy) <span class="text-muted">by {{ $rejectedBy }}</span> @endif</div>
              @endif
              @if($payout->status === 'failed')
                <div class="col-12"><span class="fw-semibold">Failed:</span> {{ data_get($payout->meta,'failed_at') ?? '' }} @if($failedReason) <span class="text-muted">&mdash; {{ $failedReason }}</span> @endif</div>
              @endif
            </div>
          </div>
        </div>

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
        @elseif(in_array($payout->status, ['approved','sent']))
            <!-- Mark as paid -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paidModal">
                {{ $payout->status === 'sent' ? 'Resend/Mark Paid' : 'Mark Paid' }}
            </button>
            @if($supportsAuto && $payout->status === 'sent')
            <form method="POST" action="{{ route('admin.payouts.resend',$payout) }}" class="d-inline">
                @csrf
                <button class="btn btn-outline-primary ms-2" title="Retry provider payout without opening the modal">Resend Automatic</button>
            </form>
            @endif
            <!-- Mark failed -->
            <button class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#failModal">
                Mark Failed & Refund
            </button>
        @endif

        {{-- FLASH / ERROR MESSAGES --}}
        @if(session('success'))
          <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if($errors->has('paid') || $errors->has('resend'))
          <div class="alert alert-danger mt-3">
            <div class="fw-semibold mb-1">Payment Provider Error</div>
            @if($errors->has('paid'))
              <div>{{ $errors->first('paid') }}</div>
            @endif
            @if($errors->has('resend'))
              <div>{{ $errors->first('resend') }}</div>
            @endif
          </div>
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

{{-- Fail modal --}}
<div class="modal fade" id="failModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.payouts.fail',$payout) }}" class="modal-content needs-validation" novalidate>
        @csrf
        <div class="modal-header"><h5 class="modal-title">Mark as Failed & Refund</h5></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Reason (shown to seller)</label>
                <textarea name="reason" class="form-control" required></textarea>
                <div class="invalid-feedback">Please provide a reason.</div>
            </div>
            <div class="alert alert-warning small">This will refund the payout amount{{ data_get($payout->meta,'fee',0)>0 ? ' and fee' : '' }} to the seller's wallet and mark the request as failed.</div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline-danger">Confirm Fail & Refund</button>
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

  // Toast notifications (less intrusive)
  (function(){
    const successMsg = @json(session('success'));
    const paidErr    = @json($errors->first('paid'));
    const resendErr  = @json($errors->first('resend'));
    const container  = document.getElementById('payoutToasts');
    if (!container) return;

    function showToast(opts){
      const { title, body, color } = opts;
      const el = document.createElement('div');
      el.className = 'toast align-items-center text-bg-' + (color||'primary') + ' border-0 mb-2';
      el.setAttribute('role','alert');
      el.setAttribute('aria-live','assertive');
      el.setAttribute('aria-atomic','true');
      el.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            <div class="fw-semibold">${title||'Notice'}</div>
            <div>${body||''}</div>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>`;
      container.appendChild(el);
      try { new bootstrap.Toast(el, { delay: 5000 }).show(); } catch(e) {}
    }

    if (successMsg) {
      showToast({ title: 'Success', body: successMsg, color: 'success' });
    }
    if (paidErr) {
      showToast({ title: 'Payment Error', body: paidErr, color: 'danger' });
    }
    if (resendErr) {
      showToast({ title: 'Resend Error', body: resendErr, color: 'danger' });
    }
  })();
</script>
@endpush
<!-- Toast container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
  <div id="payoutToasts"></div>
  </div>
@endsection
