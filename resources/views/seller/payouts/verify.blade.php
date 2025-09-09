@extends('layouts.app')
@section('title','Verify Payout #'.$payout->id)

@section('content')
<div class="content py-4">
  <div class="container-xxl" style="max-width: 720px">
    <h3 class="mb-3">Verify Payout Request</h3>
    <div class="alert alert-info">
      We sent a verification code to your email. Enter it below to confirm your payout of {{ get_currency() }} {{ number_format($payout->amount,2) }}.
    </div>
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.submit') ? route('seller.payouts.otp.submit', $payout) : url('/seller/payouts/'.$payout->id.'/verify')) }}" class="card shadow-sm border-0 p-3 mb-3">
      @csrf
      <div class="mb-3">
        <label class="form-label">Verification Code</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="6-digit code" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary">Verify &amp; Submit</button>
        <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-secondary">Back</a>
      </div>
    </form>
    @php
      $canResend = true;
      if(isset($canResendAt) && $canResendAt instanceof \Carbon\Carbon) {
        $canResend = now()->gte($canResendAt);
      }
    @endphp
    <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.resend') ? route('seller.payouts.otp.resend', $payout) : url('/seller/payouts/'.$payout->id.'/resend-otp')) }}" class="d-inline">
      @csrf
      <button class="btn btn-link p-0" {{ $canResend ? '' : 'disabled' }} title="{{ isset($canResendAt) && !$canResend ? 'You can resend at '.$canResendAt->format('H:i:s') : '' }}">Resend code</button>
    </form>
    <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.cancel') ? route('seller.payouts.otp.cancel', $payout) : url('/seller/payouts/'.$payout->id.'/cancel')) }}" class="d-inline ms-3"
          onsubmit="return confirm('Cancel this payout request?');">
      @csrf
      <button class="btn btn-link text-danger p-0">Cancel request</button>
    </form>
  </div>
  </div>
@endsection
