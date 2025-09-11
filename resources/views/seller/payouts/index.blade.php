@extends('layouts.app')
@section('title','Payout Requests')

@section('content')
<div class="content py-4">
  <div class="container-xxl">

      <h3 class="mb-4">Payout Requests</h3>

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
          Available balance: {{ shop_currency() }} {{ number_format($balance,2) }}
      </div>

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
                              <td>{{ shop_currency() }} {{ number_format($req->amount,2) }}</td>
                              <td class="text-capitalize">{{ $req->status }}</td>
                              <td>{{ $req->created_at->format('d M Y') }}</td>
                              <td class="text-nowrap">
                                @if($req->status === 'otp_pending')
                                  <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $req) : url('/seller/payouts/'.$req->id.'/verify')) }}" class="btn btn-sm btn-primary">Verify</a>
                                  <form action="{{ route('seller.payouts.otp.cancel', $req) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this payout request?');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                  </form>
                                @else
                                  <span class="text-muted">—</span>
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
