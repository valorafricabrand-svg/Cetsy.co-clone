@extends('theme.'.theme().'.layouts.app')
@section('title','Subscription Payment')

@section('styles')
<style>
 .subpay{
 --sub-brand: #198754;
 --sub-text: #0f172a;
 --sub-muted: #64748b;
 --sub-border: rgba(15, 23, 42, .10);
 --sub-shadow: 0 16px 40px rgba(15, 23, 42, .08);
 }
 .subpay-hero{
 border: 1px solid var(--sub-border);
 border-radius: 1rem;
 background: linear-gradient(135deg, rgba(25,135,84,.10), rgba(32,201,151,.08));
 box-shadow: var(--sub-shadow);
 }
 .subpay-card{
 border-radius: 1rem;
 border: 1px solid var(--sub-border);
 box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
 }
 .subpay-badge{
 border-radius: 999px;
 padding: .35rem .65rem;
 font-weight: 700;
 border: 1px solid rgba(25,135,84,.22);
 background: rgba(25,135,84,.10);
 color: var(--sub-text);
 }
 .subpay-muted{ color: var(--sub-muted); }
 .subpay-kpi{
 border-radius: .9rem;
 border: 1px solid rgba(0,0,0,.08);
 background: rgba(255,255,255,.85);
 }
 .subpay-kpi .label{ color: var(--sub-muted); font-size: .85rem; }
 .subpay-kpi .value{ font-weight: 800; color: var(--sub-text); }
 .subpay-divider{ height: 1px; background: rgba(15, 23, 42, .08); }
 .subpay-methods button, .subpay-methods a{ border-radius: .85rem; }
 .subpay-result{ min-height: 1.25rem; }
 .subpay-paypal{ border-radius: 1rem; border: 1px solid rgba(0,0,0,.08); padding: 1rem; background: rgba(248,250,252,.9); }
 .alert{ border-radius:12px; border:1px solid transparent; padding:.6rem .8rem; font-size:.82rem; font-weight:500; }
 .tw-alert-success{background:#dcfce7;border-color:#86efac;color:#166534;}
 .tw-alert-warning{background:#fef3c7;border-color:#fcd34d;color:#92400e;}
 .tw-alert-danger{background:#fee2e2;border-color:#fca5a5;color:#991b1b;}
 .disabled{opacity:.6;pointer-events:none;}
 .tw-spinner{display:inline-block;width:1rem;height:1rem;border:.15rem solid rgba(255,255,255,.45);border-right-color:transparent;border-radius:999px;animation:subSpin .6s linear infinite;}
 @keyframes subSpin{to{transform:rotate(360deg)}}
</style>
@endsection

@php
 $currency = 'USD';
 $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA',
 'PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];

 $plan = session('selected_subscription_plan', request('plan', 'monthly'));

 if ($plan === 'yearly') {
 $rawAmount = (float) config('subscription.yearly_fee', 50);
 $planName = 'Yearly';
 $duration = '1 year';
 } else {
 $rawAmount = (float) config('subscription.monthly_fee', 5);
 $planName = 'Monthly';
 $duration = '1 month';
 }

 $paypalAmount = in_array($currency, $zeroDecimal)
 ? (string) intval(round($rawAmount))
 : number_format($rawAmount, 2, '.', '');

 $walletBalance = wallet();
 $canPayWithWallet = $walletBalance >= $rawAmount;
 $topUpNeeded = max(0, (float) $rawAmount - (float) $walletBalance);

 $paypalAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('paypal') : true;
 $stripeAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('stripe') : true;
 $paystackAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('paystack') : true;
 $mpesaAvailable = function_exists('payment_gateway_available') ? payment_gateway_available('mpesa') : true;

 $redirectToAfterTopup = route('seller.subscription.pay', ['plan' => $plan, 'autopay' => 1], false);
@endphp

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content subpay">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 justify-center">
 <div class="md:col-span-9 xl:col-span-10">

 <div class="subpay-hero p-4 md:p-5 mb-4">
 <div class="flex flex-col md:flex-row md:items-start justify-between gap-3">
 <div>
 <div class="subpay-badge inline-flex items-center gap-2 mb-2">
 <i class="fa-solid fa-shield"></i> Secure checkout
 </div>
 <h1 class="text-2xl font-semibold mb-1" style="color: var(--sub-text)">Pay for {{ $planName }} plan</h1>
 <div class="subpay-muted">Complete payment using wallet, Stripe, Paystack, M-Pesa, or PayPal (when enabled).</div>
 </div>
 <div class="flex gap-2">
 <a href="{{ route('seller.subscription') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">
 <i class="fa-solid fa-arrow-left mr-1"></i> Back
 </a>
 </div>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
 <div class="md:col-span-5">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm subpay-card">
 <div class="p-4">
 <div class="flex items-start justify-between">
 <div>
 <div class="text-xs text-uppercase font-semibold subpay-muted">Plan</div>
 <div class="text-lg font-semibold mb-1">{{ $planName }} subscription</div>
 <div class="subpay-muted text-xs">Duration: {{ $duration }}</div>
 </div>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">{{ $currency }}</span>
 </div>

 <div class="subpay-divider my-3"></div>

 <div class="subpay-kpi p-3 mb-2">
 <div class="label">Plan amount</div>
 <div class="value">{{ $currency }} {{ number_format($rawAmount, 2) }}</div>
 </div>

 <div class="subpay-kpi p-3 mb-2">
 <div class="label">Wallet balance</div>
 <div class="value">{{ $currency }} {{ number_format((float) $walletBalance, 2) }}</div>
 </div>

 <div class="subpay-kpi p-3">
 <div class="label">Amount due</div>
 <div class="value {{ $canPayWithWallet ? 'text-emerald-700' : 'text-rose-700' }}">
 {{ $currency }} {{ number_format($rawAmount, 2) }}
 </div>
 @unless($canPayWithWallet)
 <div class="text-xs text-rose-600 mt-1">Top up needed: {{ $currency }} {{ number_format($topUpNeeded, 2) }}</div>
 @endunless
 </div>
 </div>
 </div>
 </div>

 <div class="md:col-span-7">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm subpay-card">
 <div class="p-4 md:p-5">
 <div class="flex items-start justify-between gap-2 mb-3">
 <div>
 <div class="text-xs text-uppercase font-semibold subpay-muted">Payment</div>
 <div class="text-lg font-semibold mb-0">Choose a method</div>
 </div>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $canPayWithWallet ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
 {{ $canPayWithWallet ? 'Wallet ready' : 'Top up required' }}
 </span>
 </div>

 <form id="wallet-pay-form" action="{{ route('seller.subscription.wallet.pay') }}" method="POST" class="mb-3">
 @csrf
 <input type="hidden" name="plan" value="{{ $plan }}">
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition w-full bg-emerald-600 text-white hover:bg-emerald-700 {{ $canPayWithWallet ? '' : 'disabled' }}" @disabled(!$canPayWithWallet)>
 <i class="fa-solid fa-wallet mr-1"></i> Pay with Wallet
 </button>
 @unless($canPayWithWallet)
 <div class="text-xs subpay-muted mt-2">
 Wallet balance is not enough. Top up then this button will auto-enable.
 </div>
 @endunless
 </form>

 @unless($canPayWithWallet)
 <div class="subpay-methods grid gap-2 mb-3">
 @if($stripeAvailable)
 <button type="button" id="btn-sub-stripe" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-900 bg-slate-900 text-white hover:bg-slate-800">
 <i class="fa-regular fa-credit-card mr-1"></i> Pay with Stripe
 </button>
 @endif
 @if($paystackAvailable)
 <button type="button" id="btn-sub-paystack" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-regular fa-credit-card mr-1"></i> Pay with Paystack
 </button>
 @endif
 @if($mpesaAvailable)
 <button type="button" id="btn-sub-mpesa-toggle" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
 <i class="fa-solid fa-mobile-screen-button mr-1"></i> Pay with M-Pesa (STK)
 </button>
 @endif
 <a href="{{ route('wallet.deposit.form', ['redirect_to' => $redirectToAfterTopup, 'amount' => $topUpNeeded], false) }}"
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">
 <i class="fa-solid fa-circle-plus mr-1"></i> Top up wallet (all methods)
 </a>
 </div>

 @if($mpesaAvailable)
 <div id="mpesa-sub-section" class="subpay-kpi p-3 hidden mb-3">
 <div class="font-semibold mb-1">M-Pesa STK Push</div>
 <div class="text-xs subpay-muted mb-2">Enter Safaricom number (07XXXXXXXX / 2547XXXXXXXX).</div>
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-2 items-end">
 <div class="col-span-12 md:col-span-7">
 <label class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
 <input type="text" id="mpesa_sub_phone" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="07XXXXXXXX">
 </div>
 <div class="col-span-12 md:col-span-5">
 <label class="mb-1 block text-sm font-medium text-slate-700">KES (estimate)</label>
 <input type="text" id="mpesa_sub_kes_preview" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" readonly>
 </div>
 </div>
 <div class="grid mt-3">
 <button type="button" id="btn-sub-start-stk" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <span id="sub-stk-spinner" class="tw-spinner mr-2 hidden" role="status" aria-hidden="true"></span>
 Send STK Push
 </button>
 </div>
 <div id="sub-stk-live-status" class="rounded-xl border px-4 py-3 text-sm mt-3 hidden mb-0"></div>
 </div>
 @endif
 @endunless

 @if($paypalAvailable)
 <div class="subpay-divider my-4"></div>
 <div class="font-semibold mb-2">PayPal / Card</div>
 <div class="subpay-paypal">
 <div id="paypal-button-container" class="text-center"></div>
 </div>
 @else
 <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mt-3 mb-0">
 PayPal is currently disabled. Please use wallet, Stripe, Paystack, or M-Pesa.
 </div>
 @endif

 <div id="generic-result" class="subpay-result text-center mt-3 font-semibold text-rose-600"></div>
 </div>
 </div>
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

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

@if($paypalAvailable)
 @php $ppClient = config('services.paypal.client_id') ?: (function_exists('setting') ? (setting('paypal_client_id') ?: 'sb') : 'sb'); @endphp
 <script src="https://www.paypal.com/sdk/js?client-id={{ $ppClient }}&currency={{ $currency }}"></script>
@endif

<script>
$(function () {
 const canPayWithWallet = @json($canPayWithWallet);
 const shouldAutoPay = @json((bool) request()->query('autopay'));
 if (shouldAutoPay && canPayWithWallet) {
 const form = document.getElementById('wallet-pay-form');
 if (form) form.submit();
 return;
 }

 const topUpNeeded = Number(@json($topUpNeeded));
 const redirectToAfterTopup = @json($redirectToAfterTopup);

 @if($stripeAvailable)
 $('#btn-sub-stripe').on('click', function(){
 if (!topUpNeeded || topUpNeeded <= 0) return;
 const $btn = $(this);
 $btn.prop('disabled', true).addClass('disabled').text('Redirecting to Stripe...');
 $('#generic-result').removeClass('text-rose-700 text-emerald-700').text('');

 $.post(@json(route('wallet.deposit.stripe.session')), {
 _token: @json(csrf_token()),
 amount: topUpNeeded,
 currency: 'USD',
 redirect_to: redirectToAfterTopup
 }, function(resp){
 if (resp?.success && resp?.url) {
 window.location = resp.url;
 } else {
 $btn.prop('disabled', false).removeClass('disabled').text('Pay with Stripe');
 $('#generic-result').addClass('text-rose-700').text(resp?.message || 'Unable to start Stripe checkout.');
 }
 }).fail(function(xhr){
 $btn.prop('disabled', false).removeClass('disabled').text('Pay with Stripe');
 $('#generic-result').addClass('text-rose-700').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
 });
 });
 @endif

 @if($paystackAvailable)
 $('#btn-sub-paystack').on('click', function(){
 if (!topUpNeeded || topUpNeeded <= 0) return;
 const $btn = $(this);
 $btn.prop('disabled', true).addClass('disabled').text('Redirecting to Paystack...');
 $('#generic-result').removeClass('text-rose-700 text-emerald-700').text('');

 $.post(@json(route('wallet.deposit.paystack.session')), {
 _token: @json(csrf_token()),
 amount: topUpNeeded,
 currency: 'USD',
 redirect_to: redirectToAfterTopup
 }, function(resp){
 if (resp?.success && resp?.url) {
 window.location = resp.url;
 } else {
 $btn.prop('disabled', false).removeClass('disabled').text('Pay with Paystack');
 $('#generic-result').addClass('text-rose-700').text(resp?.message || 'Unable to start Paystack checkout.');
 }
 }).fail(function(xhr){
 $btn.prop('disabled', false).removeClass('disabled').text('Pay with Paystack');
 $('#generic-result').addClass('text-rose-700').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
 });
 });
 @endif

 @if($mpesaAvailable)
 $('#btn-sub-mpesa-toggle').on('click', function(){
 $('#mpesa-sub-section').toggleClass('hidden');
 updateKesPreview();
 });

 const USD_TO_KES = parseFloat(@json((string) (float) env('USD_TO_KES', 130)));
 function normalizeMsisdn(raw) {
 if (!raw) return null;
 let p = String(raw).replace(/\D/g, '');
 if (p.startsWith('0') && p.length === 10) p = '254' + p.substring(1);
 else if (p.startsWith('7') && p.length === 9) p = '254' + p;
 if (/^2547\d{8}$/.test(p)) return p;
 return null;
 }
 function updateKesPreview(){
 const kes = Math.ceil((topUpNeeded || 0) * USD_TO_KES);
 $('#mpesa_sub_kes_preview').val('KES ' + kes.toFixed(2));
 }

 let subPollTimer = null;
 const POLL_INTERVAL_MS = 3000;
 const MAX_POLLS = 40;
 function startPolling(ref) {
 let attempts = 0;
 clearInterval(subPollTimer);
 const $live = $('#sub-stk-live-status');
 $live.removeClass('hidden tw-alert-danger tw-alert-success tw-alert-warning')
 .addClass('rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900')
 .html('<i class="fa fa-sync-alt fa-spin mr-2"></i>Waiting for M-Pesa confirmation... (this can take up to 2 minutes)');

 subPollTimer = setInterval(function(){
 attempts++;
 $.get(@json(route('wallet.deposit.mpesa.status', '__REF__')).replace('__REF__', encodeURIComponent(ref)), function(resp){
 const msg = resp?.message || '';
 if (resp?.status === 'success') {
 clearInterval(subPollTimer);
 $live.removeClass('tw-alert-warning tw-alert-danger').addClass('tw-alert-success')
 .html('<i class="fa fa-check-circle mr-2"></i>Top up successful. Activating your subscription...');
 setTimeout(() => document.getElementById('wallet-pay-form')?.submit(), 1200);
 return;
 }
 if (resp?.status === 'failed') {
 clearInterval(subPollTimer);
 $live.removeClass('tw-alert-warning tw-alert-success').addClass('tw-alert-danger')
 .html('<i class="fa fa-exclamation-triangle mr-2"></i>Payment failed: ' + (msg || 'Unknown error') + '.');
 }
 });

 if (attempts >= MAX_POLLS) {
 clearInterval(subPollTimer);
 $live.removeClass('tw-alert-success tw-alert-danger').addClass('tw-alert-warning')
 .html('<i class="fa fa-hourglass-half mr-2"></i>It\'s taking longer than expected to confirm. If you\'ve approved the prompt, please try again shortly.');
 }
 }, POLL_INTERVAL_MS);
 }

 $('#btn-sub-start-stk').on('click', function(){
 if (!topUpNeeded || topUpNeeded <= 0) return;
 const phone = normalizeMsisdn($('#mpesa_sub_phone').val());
 if (!phone) {
 $('#generic-result').addClass('text-rose-700').text('Enter a valid Safaricom number (07XXXXXXXX, 7XXXXXXXX, or 2547XXXXXXXX).');
 return;
 }
 const $btn = $(this);
 $('#sub-stk-spinner').removeClass('hidden');
 $btn.prop('disabled', true);
 $('#generic-result').removeClass('text-rose-700 text-emerald-700').text('');

 $.post(@json(route('wallet.deposit.mpesa.stk')), {
 _token: @json(csrf_token()),
 phone: phone,
 usd_amount: topUpNeeded
 }, function(resp){
 if (resp?.success && resp?.ref) {
 $('#generic-result').addClass('text-emerald-700').text('STK Push sent. Check your phone and approve.');
 startPolling(resp.ref);
 } else {
 $('#generic-result').addClass('text-rose-700').text(resp?.message || 'Failed to start STK Push.');
 }
 }).fail(function(xhr){
 $('#generic-result').addClass('text-rose-700').text('Server error: ' + (xhr.responseJSON?.message ?? 'Unknown error'));
 }).always(function(){
 $('#sub-stk-spinner').addClass('hidden');
 $btn.prop('disabled', false);
 });
 });
 @endif

@if($paypalAvailable)
 paypal.Buttons({
 style:{ layout :'vertical', color :'blue', shape :'rect', label :'paypal' },
 createOrder: (_, actions) => {
 return actions.order.create({ purchase_units:[{ amount:{ value:'{{ $paypalAmount }}' } }] });
 },
 onApprove: (_, actions) => {
 $('#generic-result').empty();
 return actions.order.capture().then(() => {
 window.location = @json(route('seller.subscription.success', auth()->id())) + '?plan={{ $plan }}';
 });
 },
 onError: err => {
 console.error(err);
 $('#generic-result').text('PayPal error: ' + err.message);
 }
 }).render('#paypal-button-container');
@endif
});
</script>
@endsection









