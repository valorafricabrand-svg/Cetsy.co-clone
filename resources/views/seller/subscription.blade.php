@extends('theme.'.theme().'.layouts.app')

@section('title', 'Seller Subscription')

@push('styles')
<style>
 .sub-page{
 --sub-brand: #198754;
 --sub-brand-2: #20c997;
 --sub-text: #0f172a;
 --sub-muted: #64748b;
 --sub-border: rgba(15, 23, 42, .10);
 --sub-shadow: 0 16px 40px rgba(15, 23, 42, .08);
 }
 .sub-hero{
 border: 1px solid var(--sub-border);
 border-radius: 1rem;
 background: linear-gradient(135deg, rgba(25,135,84,.10), rgba(32,201,151,.08));
 box-shadow: var(--sub-shadow);
 overflow: hidden;
 }
 .sub-hero__title{ color: var(--sub-text); letter-spacing: -.02em; }
 .sub-hero__subtitle{ color: var(--sub-muted); }
 .sub-status{
 border-radius: 1rem;
 border: 1px solid var(--sub-border);
 background: #fff;
 }
 .sub-status__icon{
 width: 44px; height: 44px; border-radius: 12px;
 display: inline-flex; align-items: center; justify-content: center;
 background: rgba(25,135,84,.10);
 color: var(--sub-brand);
 flex: 0 0 auto;
 }
 .sub-pill{
 border-radius: 999px;
 border: 1px solid rgba(0,0,0,.08);
 background: rgba(255,255,255,.85);
 color: var(--sub-text);
 }
 .sub-callout{
 border-radius: 1rem;
 border: 1px solid rgba(13,110,253,.18);
 background: rgba(13,110,253,.06);
 }
 .pricing-card{
 border-radius: 1rem;
 border: 1px solid var(--sub-border);
 background: #fff;
 transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
 box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
 overflow: hidden;
 }
 .pricing-card:hover{
 transform: translateY(-2px);
 box-shadow: 0 18px 44px rgba(15, 23, 42, .10);
 border-color: rgba(25,135,84,.25);
 }
 .pricing-card--featured{
 border-color: rgba(25,135,84,.35);
 box-shadow: 0 20px 60px rgba(25,135,84,.14);
 }
 .pricing-badge{
 display: inline-flex;
 align-items: center;
 gap: .35rem;
 border-radius: 999px;
 padding: .35rem .65rem;
 font-weight: 700;
 background: rgba(25,135,84,.12);
 color: var(--sub-brand);
 border: 1px solid rgba(25,135,84,.25);
 }
 .pricing-price{
 font-size: 2.1rem;
 letter-spacing: -.03em;
 color: var(--sub-text);
 line-height: 1.1;
 }
 .pricing-muted{ color: var(--sub-muted); }
 .feature-item{
 display: flex;
 align-items: flex-start;
 gap: .6rem;
 margin-bottom: .6rem;
 color: var(--sub-text);
 }
 .feature-item i{ color: var(--sub-brand); margin-top: .15rem; }
 .table thead th{ white-space: nowrap; }
 .sub-card-header{
 border-bottom: 1px solid rgba(15, 23, 42, .08);
 }
 .sub-method-badge{
 border-radius: 999px;
 border: 1px solid rgba(2, 115, 51, .18);
 background: rgba(2, 115, 51, .08);
 color: #0f172a;
 padding: .35rem .6rem;
 font-weight: 600;
 }
</style>
@endpush

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content sub-page">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 justify-center">
 <div class="col-span-12 lg:col-span-9 xl:col-span-10">

 <div class="sub-hero p-4 p-md-5 mb-4">
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
 <div>
 <h1 class="text-2xl font-semibold mb-1 sub-hero__title">Seller Subscription</h1>
 <div class="sub-hero__subtitle">Renew early, upgrade your plan, and view payment history.</div>
 </div>
 <div class="flex flex-wrap gap-2">
 <a href="{{ route('seller.billing.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">
 <i class="fa-solid fa-wallet mr-1"></i> Billing
 </a>
 <a href="{{ route('seller.dashboard') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-solid fa-gauge mr-1"></i> Dashboard
 </a>
 </div>
 </div>
 </div>

 @if(session('success'))
 <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 mb-4" role="alert">
 <strong>Success:</strong> {{ session('success') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
 </div>
 @endif

 @if(session('error'))
 <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700 mb-4" role="alert">
 <strong>Error:</strong> {{ session('error') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
 </div>
 @endif

 @php
 $isActive = $subscription && $subscription->isActive();
 $daysLeftSigned = $subscription?->end_date ? now()->diffInDays($subscription->end_date, false) : null;
 $daysLeft = !is_null($daysLeftSigned) ? max(0, (int) $daysLeftSigned) : null;
 @endphp

 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="p-4 p-md-5">

 {{-- Status --}}
 @if($isActive)
 <div class="sub-status p-4 mb-3">
 <div class="flex gap-3">
 <div class="sub-status__icon">
 <i class="fa-solid fa-certificate text-xl"></i>
 </div>
 <div class="flex-1">
 <div class="flex flex-wrap items-start justify-between gap-2">
 <div>
 <div class="text-xs text-uppercase font-semibold pricing-muted">Status</div>
 <div class="text-lg font-semibold mb-1">Active subscription</div>
 <div class="pricing-muted">
 Active until <strong>{{ $subscription->end_date->format('F j, Y') }}</strong>
 @if(!is_null($daysLeft))
 <span class="ml-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold sub-pill">
 @php $cls = $daysLeft <= 7 ? 'text-rose-600' : ($daysLeft <= 30 ? 'text-amber-600' : ''); @endphp
 <span class="font-semibold {{ $cls }}">Expires in {{ number_format($daysLeft, 0) }} {{ Str::plural('day', $daysLeft) }}</span>
 </span>
 @endif
 </div>
 @if($subscription->notes)
 <div class="text-xs pricing-muted mt-1">Plan: {{ $subscription->notes }}</div>
 @endif
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="sub-callout p-3 p-md-4 mb-4">
 <div class="flex items-start gap-2">
 <i class="fa-solid fa-circle-info text-emerald-600 mt-1"></i>
 <div>
 <div class="font-semibold">Renew early anytime</div>
 <div class="pricing-muted">
 Renewing or upgrading will extend from your current end date, so you don’t lose remaining days.
 </div>
 </div>
 </div>
 </div>
 @else
 @php
 $expiredInfo = null;
 if ($subscription && $subscription->end_date) {
 $signed = (int) now()->diffInDays($subscription->end_date, false);
 if ($signed <= 0) {
 $expiredDays = abs($signed);
 $expiredInfo = ['date' => $subscription->end_date->format('F j, Y'), 'days' => $expiredDays];
 }
 }
 @endphp

 @if($expiredInfo)
 <div class="sub-status p-4 mb-3">
 <div class="flex gap-3">
 <div class="sub-status__icon" style="background: rgba(220,53,69,.10); color:#dc3545;">
 <i class="fa-solid fa-triangle-exclamation text-xl"></i>
 </div>
 <div class="flex-1">
 <div class="text-xs text-uppercase font-semibold pricing-muted">Status</div>
 <div class="text-lg font-semibold mb-1">Subscription expired</div>
 <div class="pricing-muted">
 Expired on <strong>{{ $expiredInfo['date'] }}</strong>
 <span class="ml-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold sub-pill"><span class="font-semibold text-rose-600">Expired {{ number_format($expiredInfo['days'], 0) }} {{ Str::plural('day', $expiredInfo['days']) }} ago</span></span>
 </div>
 </div>
 </div>
 </div>
 @else
 <div class="sub-status p-4 mb-4">
 <div class="flex gap-3">
 <div class="sub-status__icon" style="background: rgba(255,193,7,.14); color:#b58100;">
 <i class="fa-solid fa-lock text-xl"></i>
 </div>
 <div class="flex-1">
 <div class="text-xs text-uppercase font-semibold pricing-muted">Status</div>
 <div class="text-lg font-semibold mb-1">Subscription required</div>
 <div class="pricing-muted">To access seller features, you need an active subscription.</div>
 </div>
 </div>
 </div>
 @endif

 @if($canStartTrial ?? false)
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4" style="border-radius: 1rem;">
 <div class="p-4 p-md-5">
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
 <div>
 <div class="pricing-badge mb-2"><i class="fa-solid fa-star"></i> Free trial</div>
 <div class="text-lg font-semibold mb-1">New seller? Try it free for {{ number_format($trialDays ?? 30, 0) }} {{ Str::plural('day', $trialDays ?? 30) }}</div>
 <div class="pricing-muted">Unlock seller features while you set up your shop. Upgrade anytime to keep selling.</div>
 </div>
 <form action="{{ route('seller.subscription.trial') }}" method="POST" class="m-0">
 @csrf
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 px-4">
 <i class="fa-solid fa-rocket mr-1"></i> Start Trial
 </button>
 </form>
 </div>
 </div>
 </div>
 @endif
 @endif

 {{-- Plans --}}
 @php
 $monthlyCta = $isActive ? 'Renew Monthly' : 'Choose Monthly';
 $yearlyCta = $isActive ? 'Upgrade / Renew Yearly' : 'Choose Yearly';
 @endphp

 <div class="flex items-end justify-between flex-wrap gap-2 mb-3">
 <div>
 <div class="text-lg font-semibold mb-1">{{ $isActive ? 'Renew / Upgrade' : 'Choose a plan' }}</div>
 <div class="pricing-muted">
 {{ $isActive ? 'Renewing extends your current end date.' : 'Select a plan to activate your seller subscription.' }}
 </div>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3 g-lg-4">
 <div class="col-span-12 md:col-span-6">
 <div class="pricing-card h-full">
 <div class="p-4">
 <div class="flex flex-wrap items-start justify-between gap-2">
 <div>
 <div class="font-semibold">Monthly</div>
 <div class="pricing-muted text-xs">Flexible month‑to‑month</div>
 </div>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold sub-pill"><i class="fa-regular fa-calendar mr-1"></i> 1 month</span>
 </div>

 <div class="mt-3">
 <div class="pricing-price">USD {{ number_format(config('subscription.monthly_fee', 5), 2) }}</div>
 <div class="pricing-muted">per month</div>
 </div>

 <hr class="my-4">

 <div class="mb-4">
 <div class="feature-item"><i class="fa-solid fa-circle-check"></i><span>Seller dashboard & analytics</span></div>
 <div class="feature-item"><i class="fa-solid fa-circle-check"></i><span>Unlimited listings</span></div>
 <div class="feature-item"><i class="fa-solid fa-circle-check"></i><span>Orders, payouts & messaging</span></div>
 <div class="feature-item mb-0"><i class="fa-solid fa-circle-check"></i><span>Account & KYC support</span></div>
 </div>

 <form action="{{ route('seller.subscription.subscribe') }}" method="POST" class="m-0">
 @csrf
 <input type="hidden" name="plan" value="monthly">
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 w-full">
 {{ $monthlyCta }}
 </button>
 </form>
 </div>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="pricing-card pricing-card--featured h-full">
 <div class="p-4">
 <div class="flex flex-wrap items-start justify-between gap-2">
 <div>
 <div class="font-semibold">Yearly</div>
 <div class="pricing-muted text-xs">Best value for serious sellers</div>
 </div>
 <span class="pricing-badge">
 <i class="fa-solid fa-award"></i> Save {{ config('subscription.yearly_discount_percent', 17) }}%
 </span>
 </div>

 <div class="mt-3">
 <div class="pricing-price">USD {{ number_format(config('subscription.yearly_fee', 50), 2) }}</div>
 <div class="pricing-muted">per year</div>
 <div class="text-xs pricing-muted mt-1">
 <span class="text-decoration-line-through">USD {{ number_format(config('subscription.monthly_fee', 5) * 12, 2) }}</span>
 <span class="ml-2">billed annually</span>
 </div>
 </div>

 <hr class="my-4">

 <div class="mb-4">
 <div class="feature-item"><i class="fa-solid fa-circle-check"></i><span>Everything in Monthly</span></div>
 <div class="feature-item"><i class="fa-solid fa-circle-check"></i><span>Priority support</span></div>
 <div class="feature-item"><i class="fa-solid fa-circle-check"></i><span>Advanced analytics</span></div>
 <div class="feature-item mb-0"><i class="fa-solid fa-circle-check"></i><span>Early access to new features</span></div>
 </div>

 <form action="{{ route('seller.subscription.subscribe') }}" method="POST" class="m-0">
 @csrf
 <input type="hidden" name="plan" value="yearly">
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 w-full">
 {{ $yearlyCta }}
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>

 </div>
 </div>

 {{-- Payment history --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-white sub-card-header">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
 <h3 class="text-lg font-semibold mb-0"><i class="fa-solid fa-receipt mr-2"></i>Subscription Payment History</h3>
 </div>
 </div>
 <div class="p-4">
 @if($subscriptionPayments->count() > 0)
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
 <thead class="bg-slate-50 text-slate-600">
 <tr>
 <th>Date</th>
 <th>Transaction</th>
 <th>Amount</th>
 <th>Method</th>
 <th>Status</th>
 </tr>
 </thead>
 <tbody>
 @foreach($subscriptionPayments as $payment)
 <tr>
 <td class="whitespace-nowrap">{{ $payment->created_at->format('M d, Y') }}<div class="text-xs text-slate-500">{{ $payment->created_at->format('h:i A') }}</div></td>
 <td><code class="text-xs">{{ $payment->local_transaction_id ?? 'N/A' }}</code></td>
 <td>
 <div class="font-semibold">${{ number_format($payment->total_amount, 2) }}</div>
 <div class="text-xs text-slate-500">{{ $payment->currency }}</div>
 </td>
 <td><span class="sub-method-badge">{{ payment_method_label($payment->payment_method) }}</span></td>
 <td>
 @if($payment->payment_status == 'successful')
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">Successful</span>
 @elseif($payment->payment_status == 'pending')
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 border-amber-200 text-slate-900">Pending</span>
 @else
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-rose-100 text-rose-800 border-rose-200">Failed</span>
 @endif
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @else
 <div class="text-center text-slate-500 py-5">
 <i class="fa-regular fa-credit-card text-4xl mb-2 block"></i>
 <div class="font-semibold">No subscription payments yet</div>
 <div class="text-xs">Once you subscribe or renew, your transactions will show here.</div>
 </div>
 @endif
 </div>
 </div>

 </div>
 </div>
</div>
 </div>
 </div>
 </div>
</section>
@endsection



