@extends('layouts.app')

@section('title', 'Verify Deposit')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm border-0 mt-5">
        <div class="card-body p-4">
          <h2 class="h5 fw-semibold mb-3 text-center">Two‑Step Verification</h2>
          <p class="text-muted text-center">We sent a code to <strong>{{ auth()->user()->email }}</strong>. Enter it to continue {{ $purpose ?? 'with your deposit' }}.</p>

          @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ $verifyRoute ?? route('wallet.deposit.otp.verify') }}" class="row g-3">
            @csrf
            <div class="col-12">
              <label class="form-label">Verification Code</label>
              <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="6-digit code" autofocus required>
              @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 d-flex align-items-center justify-content-between">
              <a class="btn btn-outline-secondary" href="{{ route('wallet.index') }}">Cancel</a>
              <button class="btn btn-primary">Verify</button>
            </div>
          </form>

          <form method="POST" action="{{ $resendRoute ?? route('wallet.deposit.otp.resend') }}" class="mt-3">
            @csrf
            <button class="btn btn-link" type="submit">Resend code</button>
            @if(!empty($cooldown) && $cooldown > 0)
              <small class="text-muted">Please wait {{ $cooldown }}s to resend.</small>
            @endif
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
