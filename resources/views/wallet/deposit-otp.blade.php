@extends('theme.'.theme().'.layouts.app')

@section('title', 'Verify Deposit')

@section('main')
@php
  $showBuyerSidebar = auth()->check() && auth()->user()->isBuyer();
  $showSellerSidebar = auth()->check() && auth()->user()->isSeller();
  $hasSidebar = $showBuyerSidebar || $showSellerSidebar;
@endphp
<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      @if($hasSidebar)
        <div class="col-span-12 lg:col-span-3">
          @if($showBuyerSidebar)
            @include('buyer.partials.sidebar')
          @elseif($showSellerSidebar)
            @include('seller.partials.sidebar')
          @endif
        </div>
      @endif

      <div class="{{ $hasSidebar ? 'col-span-12 lg:col-span-9' : 'col-span-12' }}">
        <div class="mx-auto w-full max-w-xl">
      <div class="mt-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4 sm:p-5">
          <h2 class="mb-3 text-center text-base font-semibold">Two-Step Verification</h2>
          <p class="text-center text-slate-500">We sent a code to <strong>{{ auth()->user()->email }}</strong>. Enter it to continue {{ $purpose ?? 'with your deposit' }}.</p>

          @if(session('status'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
          @endif
          @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ $verifyRoute ?? route('wallet.deposit.otp.verify') }}" class="mt-4 grid grid-cols-12 gap-3">
            @csrf
            <div class="col-span-12">
              <label class="mb-1 block text-sm font-medium text-slate-700">Verification Code</label>
              <input type="text" name="code" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" placeholder="6-digit code" autofocus required>
              @error('code') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div class="col-span-12 flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
              <a class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto" href="{{ route('wallet.index') }}">Cancel</a>
              <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 sm:w-auto">Verify</button>
            </div>
          </form>

          <form method="POST" action="{{ $resendRoute ?? route('wallet.deposit.otp.resend') }}" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
            @csrf
            <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition text-emerald-700 hover:text-emerald-600 underline-offset-2 hover:underline sm:w-auto" type="submit">Resend code</button>
            @if(!empty($cooldown) && $cooldown > 0)
              <small class="text-slate-500">Please wait {{ $cooldown }}s to resend.</small>
            @endif
          </form>
        </div>
      </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection



