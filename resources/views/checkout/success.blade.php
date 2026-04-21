@extends('theme.'.theme().'.layouts.app')

@section('title','Order Confirmed')

@section('main')
<section class="bg-slate-50 py-12 md:py-16">
  <div class="mx-auto w-full max-w-3xl px-4 sm:px-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-10">
      <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
        <i class="fa-solid fa-check text-lg" aria-hidden="true"></i>
      </div>

      <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Thank You</h1>
      <p class="mt-2 text-sm text-slate-500">Your order was placed successfully.</p>

      <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 py-2 text-sm">
          <span class="text-slate-600">Order Number</span>
          <span class="font-semibold text-slate-900">#{{ $order->id }}</span>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
          <span class="text-slate-600">Total Paid</span>
          <span class="font-semibold text-slate-900">{{ money($order->total) }}</span>
        </div>
      </div>

      <a href="{{ route('home') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
        Continue Shopping
      </a>
    </div>
  </div>
</section>
@endsection
