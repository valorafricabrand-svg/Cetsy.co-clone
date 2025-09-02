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
    <form method="POST" action="{{ route('admin.payouts.paid',$payout) }}" class="modal-content">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Mark as Paid</h5></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Transaction Reference (optional)</label>
                <input type="text" name="txn_reference" class="form-control">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary">Confirm Paid</button>
        </div>
    </form>
  </div>
</div>
@endsection
