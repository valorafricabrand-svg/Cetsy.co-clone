{{-- resources/views/listings/checkout.blade.php --}}
@extends('layouts.app')
@section('title','Process Payment')



@php
  // ------- Inputs -------
  $currency        = $order->currency ?? 'USD';
  $fourMonthFeeRaw = (float) ($order->category?->listing_fee ?? 0);   // base for 4 months

  // Currencies without minor units (for formatting)
  $zeroDecimal     = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
  $isZeroDecimal   = in_array($currency, $zeroDecimal, true);

  // Round values consistently for the UI
  $fourMonthFee    = $isZeroDecimal ? (float) round($fourMonthFeeRaw) : (float) round($fourMonthFeeRaw, 2);
  $monthlyFee      = $isZeroDecimal ? (float) round($fourMonthFee / 4) : (float) round($fourMonthFee / 4, 2);

  // Wallet balance (from helper) — clamp negatives to 0 for safety/visibility
  $walletBalanceRaw = wallet();
  $walletBalance    = max(0, (float) ($walletBalanceRaw ?? 0));

  // Initial plan & fee (so we can safely server-hide wallet button before JS)
  $initialPlan = $plan ?? 'monthly';
  $initialFee  = $initialPlan === 'monthly' ? $monthlyFee : $fourMonthFee;

  // Server-side gate: should the wallet button be visible on first render?
  $showWalletFormInitial = ($walletBalance > 0) && ($initialFee > 0) && ($walletBalance >= $initialFee);

  // Build a robust deposit URL (supports multiple route names)
  $depositUrl = \Illuminate\Support\Facades\Route::has('wallet.deposit.form')
      ? route('wallet.deposit.form')
      : (\Illuminate\Support\Facades\Route::has('wallet.deposit')
          ? route('wallet.deposit')
          : url('/wallet/deposit'));
@endphp

@section('content')
<div class="content">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-7">
        <div class="card p-4 p-md-5">

          <div class="text-center mb-3">
            <div class="badge-dot success subtle mb-2">Secure Checkout</div>
            <h1 class="h3 headline mb-1">Complete Your Payment</h1>
            <p class="subtle mb-0">We’ll use your wallet to pay for the listing.</p>
          </div>

          <div
            x-data="checkoutPlans({
              currency: @js($currency),
              monthly : @js($monthlyFee),
              four    : @js($fourMonthFee),
              wallet  : @js($walletBalance),    // already clamped to 0
              initial : @js($initialPlan),
              zeroDec : @js($isZeroDecimal),
              orderId : @js($order->id),
              depositUrl: @js($depositUrl),
            })"
            x-init="init()"
            x-cloak
          >
            {{-- PLAN SELECTOR --}}
            <div class="plan-toggle d-flex justify-content-center gap-3 mb-4">
              <button type="button" class="btn" :class="{ 'active': plan==='monthly' }" @click="setPlan('monthly')">
                <div class="fw-semibold">Monthly</div>
                <small class="subtle">
                  <span x-text="fmt(monthly)"></span> {{ $currency }}
                </small>
              </button>
              <button type="button" class="btn" :class="{ 'active': plan==='4months' }" @click="setPlan('4months')">
                <div class="fw-semibold">4-Month</div>
                <small class="subtle">
                  <span x-text="fmt(four)"></span> {{ $currency }}
                </small>
              </button>
            </div>

            {{-- BREAKDOWN --}}
            <div class="mb-3">
              <ul class="list-unstyled mb-0">
                <li class="list-group-item">
                  <span>Selected Plan <span class="subtle">(<span x-text="planLabel"></span>)</span></span>
                  <span class="amount"><span x-text="fmt(currentFee)"></span> {{ $currency }}</span>
                </li>
                <li class="list-group-item">
                  <span>Wallet Balance</span>
                  <span class="amount"><span x-text="fmt(wallet)"></span> {{ $currency }}</span>
                </li>
                <li class="list-group-item fw-semibold">
                  <span>Amount Due</span>
                  <span class="amount">
                    <template x-if="noPaymentDue">
                      <span class="text-success">0.00 {{ $currency }}</span>
                    </template>
                    <template x-if="!noPaymentDue">
                      <span :class="canPayFromWallet ? 'text-success' : 'text-danger'">
                        <span x-text="fmt(currentFee)"></span> {{ $currency }}
                      </span>
                    </template>
                  </span>
                </li>
                <template x-if="!noPaymentDue && !canPayFromWallet">
                  <li class="list-group-item">
                    <span>Top Up Needed</span>
                    <span class="amount text-danger"><span x-text="fmt(topUpNeeded)"></span> {{ $currency }}</span>
                  </li>
                </template>
              </ul>
            </div>

            {{-- ACTIONS --}}
            <div class="mt-4">
              {{-- SERVER-SAFE VISIBILITY: also add d-none when initial wallet is <=0 or insufficient --}}
              <form
                id="wallet-pay-form"
                class="d-grid gap-2 {{ $showWalletFormInitial ? '' : 'd-none' }}"
                x-show="canPayFromWallet"
                method="POST"
                action="{{ route('listing.wallet.pay', $order->id) }}"
                @submit="$el.querySelector('[name=plan]').value = plan"
              >
                @csrf
                <input type="hidden" name="plan" value="">
                <input type="hidden" name="via"  value="wallet">
                <button type="submit" class="btn btn-primary btn-lg">
                  Pay via Wallet
                </button>
                <small class="text-center subtle">Your wallet will be debited immediately.</small>
              </form>

              {{-- If nothing to pay at all (free/comp) --}}
              <div x-show="noPaymentDue" class="alert subtle mt-3" style="background:rgba(148,163,184,.12);">
                <strong class="d-block mb-1" style="color:var(--ink)">No payment due</strong>
                <span>Your selected plan doesn’t require a payment right now.</span>
              </div>

              {{-- If wallet is 0 or insufficient: show Top Up Wallet CTA only --}}
              <div x-show="!canPayFromWallet && !noPaymentDue" class="text-center">
                <a :href="buildDepositUrl()" class="btn btn-success btn-lg w-100">
                  Top Up Wallet
                </a>
                <small class="d-block subtle mt-2">
                  You’ll be redirected to the wallet deposit page to add
                  <strong><span x-text="fmt(topUpNeeded)"></span> {{ $currency }}</strong>.
                </small>
              </div>
            </div>

            <div class="mt-4">
              <div class="alert subtle" style="background:rgba(148,163,184,.12);">
                <strong class="d-block mb-1" style="color:var(--ink)">Heads up</strong>
                <span>If you just topped up but still see an insufficient balance, refresh this page to re-check your wallet.</span>
              </div>
            </div>
          </div> {{-- /x-data --}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

<script>
function checkoutPlans(cfg){
  return {
    currency: cfg.currency,
    monthly : Number(cfg.monthly || 0),
    four    : Number(cfg.four || 0),
    wallet  : Number(cfg.wallet || 0), // server already clamps negatives to 0
    zeroDec : !!cfg.zeroDec,
    plan    : cfg.initial ?? 'monthly',
    orderId : cfg.orderId,
    depositUrl: cfg.depositUrl,

    get currentFee(){ return this.plan === 'monthly' ? this.monthly : this.four; },
    get planLabel(){ return this.plan === 'monthly' ? 'Monthly' : '4-Month'; },

    // Wallet button must be hidden when wallet <= 0 OR wallet < fee
    get canPayFromWallet(){
      return (this.wallet > 0) && (this.currentFee > 0) && (this.wallet >= this.currentFee);
    },

    // Nothing to pay (e.g., fee is 0)
    get noPaymentDue(){ return this.currentFee <= 0.000001; },

    get topUpNeeded(){ return Math.max(0, this.currentFee - this.wallet); },

    fmt(n){
      const v = Number(n || 0);
      return this.zeroDec ? String(Math.round(v)) : v.toFixed(2);
    },

    setPlan(p){
      this.plan = p;
      // Also toggle server-hidden form in case Alpine wasn't initialized yet
      const form = document.getElementById('wallet-pay-form');
      if(form){
        if(this.canPayFromWallet){ form.classList.remove('d-none'); }
        else{ form.classList.add('d-none'); }
      }
    },

    buildDepositUrl(){
      const amt = this.zeroDec ? Math.round(this.topUpNeeded) : this.topUpNeeded.toFixed(2);
      const u = new URL(this.depositUrl, window.location.origin);
      u.searchParams.set('amount', amt);
      u.searchParams.set('return', window.location.href);
      return u.toString();
    },

    init(){
      // Ensure correct initial visibility even if Alpine loads after DOM paint
      const form = document.getElementById('wallet-pay-form');
      if(form){
        if(this.canPayFromWallet){ form.classList.remove('d-none'); }
        else{ form.classList.add('d-none'); }
      }
    }
  }
}
</script>
@endsection
