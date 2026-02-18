{{-- resources/views/seller/orders/payments.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Order Payments')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">

 {{-- ───────────── PAGE TITLE & FILTERS ───────────── --}}
 <div class="flex flex-col md:flex-row justify-between md:items-center mb-4 gap-3">
 <h2 class="text-emerald-600 mb-0">
 <i class="fa-regular fa-credit-card mr-1"></i>
 Payments
 </h2>

 {{-- Filters --}}
 <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-2 items-end">
 <div class="w-auto">
 <label for="status" class="form-label mb-0 text-xs text-slate-500">Status</label>
 <select name="status" id="status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 <option value="">All</option>
 <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
 <option value="success" {{ request('status')=='success'?'selected':'' }}>Success</option>
 <option value="failed" {{ request('status')=='failed' ?'selected':'' }}>Failed</option>
 </select>
 </div>

 <div class="w-auto">
 <label for="method" class="form-label mb-0 text-xs text-slate-500">Method</label>
 <select name="method" id="method" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 <option value="">All</option>
 <option value="mpesa" {{ request('method')=='mpesa' ?'selected':'' }}>M-Pesa</option>
 <option value="paypal" {{ request('method')=='paypal'?'selected':'' }}>PayPal</option>
 <option value="stripe" {{ request('method')=='stripe'?'selected':'' }}>Stripe</option>
 <option value="paystack" {{ request('method')=='paystack'?'selected':'' }}>Paystack</option>
 <option value="cash" {{ request('method')=='cash' ?'selected':'' }}>Cash</option>
 <option value="card" {{ request('method')=='card' ?'selected':'' }}>Card</option>
 </select>
 </div>

 <div class="w-auto">
 <label for="per_page" class="form-label mb-0 text-xs text-slate-500">Per Page</label>
 <select name="per_page" id="per_page" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 @foreach ([20,50,100] as $size)
 <option value="{{ $size }}" {{ request('per_page',20)==$size?'selected':'' }}>{{ $size }}</option>
 @endforeach
 </select>
 </div>

 <div class="w-auto">
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-solid fa-filter mr-1"></i> Apply
 </button>
 @if(request()->hasAny(['status','method','per_page']))
 <a href="{{ route(Route::currentRouteName()) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">Reset</a>
 @endif
 </div>
 </form>
 </div>

 @if ($payments->isNotEmpty())
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex justify-between items-center">
 <span>Payment History ({{ $payments->total() }})</span>
 <span class="text-slate-500 text-xs">
 Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}
 </span>
 </div>

 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
 <thead class="bg-slate-50 text-slate-600">
 <tr>
 <th>#</th>
 <th>Reference</th>
 <th class="text-right">Amount</th>
 <th>Method</th>
 <th>Paid&nbsp;On</th>
 </tr>
 </thead>

 <tbody>
 @foreach ($payments as $payment)
 @php
 $badge = [
 'pending' => 'secondary',
 'success' => 'success',
 'failed' => 'danger',
 ][$payment->status] ?? 'dark';

 $symbol = currency_symbol();
 @endphp
 <tr>
 <td>{{ $payment->id }}</td>

 {{-- Copy-to-clipboard helper via tooltip --}}
 <td>
 <span class="whitespace-nowrap"
 data-ui-toggle="tooltip"
 data-placement="top"
 data-title="Click to copy"
 style="cursor:pointer"
 onclick="navigator.clipboard.writeText('{{ $payment->local_transaction_id }}')">
 {{ \Illuminate\Support\Str::limit($payment->local_transaction_id, 18, '…') }}
 </span>
 </td>

 <td class="text-right">{{ money($payment->total_amount) }}</td>

 <td>
 <span class="inline-flex items-center gap-1 capitalize">
 @switch($payment->payment_method)
 @case('mpesa') <i class="fa-solid fa-phone"></i> @break
 @case('paypal') <i class="fa-brands fa-paypal"></i> @break
 @case('stripe') <i class="fa-regular fa-credit-card"></i> @break
 @case('paystack') <i class="fa-regular fa-credit-card"></i> @break
 @case('card') <i class="fa-regular fa-credit-card"></i> @break
 @default <i class="fa-solid fa-money-bill-wave"></i>
 @endswitch
 {{ $payment->payment_method }}
 </span>
 </td>

 

 <td>{{ optional($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>

 {{-- Pagination --}}
 <div class="border-t border-slate-200 px-4 py-3">
 {{ $payments->links('pagination::tailwind') }}
 </div>
 </div>
 @else
 <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mt-4">
 <i class="fa-solid fa-circle-exclamation mr-1"></i>
 No payments recorded yet.
 </div>
 @endif
</div>
 </div>
 </div>
 </div>
</section>
@endsection








