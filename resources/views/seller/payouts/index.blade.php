@extends('layouts.app')
@section('title','Payout Requests')

@section('content')
<div class="content py-4">
  <div class="container-xxl">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="mb-0">Payout Requests</h3>
        <div>
          <a href="{{ route('wallet.index', ['open_payout' => 1]) }}" class="btn btn-sm btn-primary">
            Request Payout
          </a>
        </div>
      </div>

      @if(!empty($otpPending))
        <div class="alert alert-warning d-flex align-items-center justify-content-between">
          <div>
            <i class="bi bi-shield-lock me-2"></i>
            You have a payout request (ID #{{ $otpPending->id }}) awaiting verification.
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/verify')) }}" class="btn btn-sm btn-primary">Continue Verification</a>
            <form action="{{ route('seller.payouts.otp.cancel', $otpPending) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this payout request?');">
              @csrf
              <button class="btn btn-sm btn-outline-danger">Cancel</button>
            </form>
          </div>
        </div>
      @endif

      <div class="alert alert-info mb-4">
          Available balance: {{ get_currency() }} {{ number_format($balance,2) }}
      </div>

      @if(!empty($otpPending))
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-white fw-semibold">
            Verify Payout Request #{{ $otpPending->id }}
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.submit') ? route('seller.payouts.otp.submit', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/verify')) }}" class="row g-3">
              @csrf
              <div class="col-12 col-md-6">
                <label class="form-label">Verification Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="6-digit code" required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Verify &amp; Submit</button>
                <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/verify')) }}" class="btn btn-outline-secondary">Open full verify page</a>
              </div>
            </form>
            <div class="mt-2 d-flex gap-3">
              <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.resend') ? route('seller.payouts.otp.resend', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/resend-otp')) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link p-0">Resend code</button>
              </form>
              <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.cancel') ? route('seller.payouts.otp.cancel', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/cancel')) }}" class="d-inline" onsubmit="return confirm('Cancel this payout request?');">
                @csrf
                <button class="btn btn-link text-danger p-0">Cancel</button>
              </form>
            </div>
          </div>
        </div>
      @endif

      <div class="card shadow-sm border-0">
          <div class="table-responsive">
              <table class="table table-striped table-hover mb-0 align-middle">
                  <thead class="table-light">
                      <tr>
                          <th>#</th>
                          <th>Amount</th>
                          <th>Status</th>
                          <th>Requested&nbsp;On</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($requests as $req)
                          <tr>
                              <td>{{ $req->id }}</td>
                              <td>{{ get_currency() }} {{ number_format($req->amount,2) }}</td>
                              <td>
                                @php
                                  $st = strtolower((string) $req->status);
                                  $map = [
                                    'otp_pending' => ['warning','Awaiting verification'],
                                    'pending'     => ['secondary','Pending'],
                                    'approved'    => ['primary','Approved'],
                                    'sent'        => ['info','Sent'],
                                    'paid'        => ['success','Paid'],
                                    'rejected'    => ['danger','Rejected'],
                                    'cancelled'   => ['secondary','Cancelled'],
                                  ];
                                  [$cls, $label] = $map[$st] ?? ['dark', ucfirst($st)];
                                @endphp
                                <span class="badge bg-{{ $cls }}">{{ $label }}</span>
                              </td>
                              <td>{{ $req->created_at->format('d M Y') }}</td>
                              <td class="text-nowrap">
                                @if($req->status === 'otp_pending')
                                  <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $req) : url('/seller/payouts/'.$req->id.'/verify')) }}" class="btn btn-sm btn-primary">Verify</a>
                                  <form action="{{ route('seller.payouts.otp.cancel', $req) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this payout request?');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                  </form>
                                @else
                                  <span class="text-muted">-</span>
                                @endif
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="4" class="text-center py-4">
                                  No payout requests yet.
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
          <div class="card-footer">
              {{ $requests->links() }}
          </div>
      </div>

  </div>
</div>

@endsection

 

 
