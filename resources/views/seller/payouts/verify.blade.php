@extends('theme.'.theme().'.layouts.app')
@section('title','Verify Payout #'.$payout->id)

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content py-4">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6" style="max-width: 720px">
    {{-- Stepper: 1) Request  2) Verify  3) Submitted --}}
    <div class="flex items-center justify-center gap-3 mb-3">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">1</span>
        <span class="text-xs">Requested</span>
      </div>
      <div class="text-slate-500">&rarr;</div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600">2</span>
        <span class="text-xs">Verify</span>
      </div>
      <div class="text-slate-500">&rarr;</div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200">3</span>
        <span class="text-xs">Submitted</span>
      </div>
    </div>
    <h3 class="mb-3">Verify Payout Request</h3>
    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
      We sent a verification code to your email. Enter it below to confirm your payout of {{ get_currency() }} {{ number_format($payout->amount,2) }}.
    </div>
    @if(session('success'))
      <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.submit') ? route('seller.payouts.otp.submit', $payout) : url('/seller/payouts/'.$payout->id.'/verify')) }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 p-3 mb-3">
      @csrf
      <div class="mb-3">
        <label class="form-label">Verification Code</label>
        <input type="text" name="code" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('code') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" placeholder="6-digit code" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
      <div class="flex items-center gap-2">
        <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700" type="submit">Verify &amp; Submit</button>
        <a href="{{ route('seller.payouts.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">Back</a>
      </div>
    </form>
    @php
      $canResend = true;
      if(isset($canResendAt) && $canResendAt instanceof \Carbon\Carbon) {
        $canResend = now()->gte($canResendAt);
      }
    @endphp
    <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.resend') ? route('seller.payouts.otp.resend', $payout) : url('/seller/payouts/'.$payout->id.'/resend-otp')) }}" class="inline">
      @csrf
      <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition text-emerald-700 hover:underline p-0" {{ $canResend ? '' : 'disabled' }} title="{{ isset($canResendAt) && !$canResend ? 'You can resend at '.$canResendAt->format('H:i:s') : '' }}">Resend code</button>
    </form>
    <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.cancel') ? route('seller.payouts.otp.cancel', $payout) : url('/seller/payouts/'.$payout->id.'/cancel')) }}" class="inline ml-3"
          onsubmit="return confirm('Cancel this payout request?');">
      @csrf
      <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition text-emerald-700 hover:underline text-rose-600 p-0">Cancel request</button>
    </form>
  </div>
  </div>
      </div>
    </div>
  </div>
</section>
@endsection





