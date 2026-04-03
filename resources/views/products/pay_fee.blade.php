{{-- resources/views/products/pay_fee.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title', 'Process Payment')

@php
  // ------- Inputs -------
  $currency      = $order->currency ?? 'USD';
  $zeroDecimal   = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
  $isZeroDecimal = in_array($currency, $zeroDecimal, true);

  // Normalize the plan configuration coming from the controller
  $planConfig = [];
  foreach (($plans ?? []) as $planKey => $config) {
    $rawAmount = (float) ($config['amount'] ?? 0);
    $amount    = $isZeroDecimal ? (float) round($rawAmount) : (float) round($rawAmount, 2);

    $planConfig[$planKey] = [
      'label' => $config['label'] ?? ucfirst(str_replace('_', ' ', $planKey)),
      'amount'=> $amount,
    ];
  }

  $planKeys    = array_keys($planConfig);
  $initialPlan = $plan ?? ($planKeys[0] ?? null);
  if (! in_array($initialPlan, $planKeys, true)) {
    $initialPlan = $planKeys[0] ?? null;
  }

  $initialFee = ($initialPlan && isset($planConfig[$initialPlan]))
    ? (float) $planConfig[$initialPlan]['amount']
    : 0.0;

  // Wallet balance (from helper/controller) - clamp negatives to 0 for safety/visibility
  $walletBalanceRaw = $walletBalance ?? (function_exists('wallet') ? wallet() : 0);
  $walletBalance    = max(0, (float) ($walletBalanceRaw ?? 0));

  // Server-side gate: show the activation button either for free plans or for wallet-covered plans.
  $showWalletFormInitial = ($initialFee <= 0) || (($walletBalance > 0) && ($initialFee > 0) && ($walletBalance >= $initialFee));

  // Build a robust deposit URL (supports multiple route names)
  $depositUrl = \Illuminate\Support\Facades\Route::has('wallet.deposit.form')
      ? route('wallet.deposit.form')
      : (\Illuminate\Support\Facades\Route::has('wallet.deposit')
          ? route('wallet.deposit')
          : url('/wallet/deposit'));
@endphp

@section('main')
<div class="content">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 py-5">
    <div class="grid grid-cols-12 gap-4 justify-center">
      <div class="col-span-12 lg:col-span-7">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 p-5 md:p-5">

          <div class="text-center mb-3">
            <div class="badge-dot success subtle mb-2">Secure Checkout</div>
            <h1 class="text-xl font-semibold headline mb-1">{{ $initialFee <= 0 ? 'Activate Your Listing' : 'Complete Your Payment' }}</h1>
            <p class="subtle mb-0">{{ $initialFee <= 0 ? 'This listing plan is free and can be activated right away.' : "We'll use your wallet to pay for the listing." }}</p>
          </div>

          <div
            x-data="checkoutPlans({
              currency: @js($currency),
              plans: @js($planConfig),
              wallet: @js($walletBalance),
              initial: @js($initialPlan),
              zeroDec: @js($isZeroDecimal),
              orderId: @js($order->id),
              depositUrl: @js($depositUrl),
            })"
            x-init="init()"
            x-cloak
          >
            {{-- PLAN SELECTOR --}}
            <div class="plan-toggle flex justify-center gap-3 mb-4" x-show="hasPlans">
              <template x-for="([key, details], index) in planEntries" :key="key">
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50"
                  :class="{ 'active bg-slate-600 text-white hover:bg-slate-500 border-0': plan === key }"
                  @click="setPlan(key)"
                >
                  <div class="font-semibold" x-text="details.label || key"></div>
                  <small class="subtle">
                    <span x-text="fmt(details.amount)"></span> {{ $currency }}
                  </small>
                </button>
              </template>
            </div>
            <div x-show="!hasPlans" class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mt-3">
              No listing plans are currently available for this category. Please contact support.
            </div>

            {{-- BREAKDOWN --}}
            <div class="mb-3">
              <ul class="list-unstyled mb-0">
                <li class="px-4 py-3">
                  <span>Selected Plan <span class="subtle">(<span x-text="planLabel"></span>)</span></span>
                  <span class="amount"><span x-text="fmt(currentFee)"></span> {{ $currency }}</span>
                </li>
                <li class="px-4 py-3">
                  <span>Wallet Balance</span>
                  <span class="amount"><span x-text="fmt(wallet)"></span> {{ $currency }}</span>
                </li>
                <li class="px-4 py-3 font-semibold">
                  <span>Amount Due</span>
                  <span class="amount">
                    <template x-if="noPaymentDue">
                      <span class="text-emerald-600">0.00 {{ $currency }}</span>
                    </template>
                    <template x-if="!noPaymentDue">
                      <span :class="canPayFromWallet ? 'text-emerald-600' : 'text-rose-600'">
                        <span x-text="fmt(currentFee)"></span> {{ $currency }}
                      </span>
                    </template>
                  </span>
                </li>
                <template x-if="!noPaymentDue && !canPayFromWallet">
                  <li class="px-4 py-3">
                    <span>Top Up Needed</span>
                    <span class="amount text-rose-600"><span x-text="fmt(topUpNeeded)"></span> {{ $currency }}</span>
                  </li>
                </template>
              </ul>
            </div>

            @php
              $initialActionLabel = $initialFee <= 0 ? 'Activate Listing' : 'Pay via Wallet';
              $initialActionHint = $initialFee <= 0
                ? 'No wallet charge will be made for this plan.'
                : 'Your wallet will be debited immediately.';
            @endphp
            {{-- ACTIONS --}}
            <div class="mt-4">
              {{-- SERVER-SAFE VISIBILITY: also add hidden when initial wallet is <=0 or insufficient --}}
              <form
                id="wallet-pay-form"
                class="grid gap-2 {{ $showWalletFormInitial ? '' : 'hidden' }}"
                x-show="canSubmitListing"
                method="POST"
                action="{{ route('listing.wallet.pay', $order->id) }}"
                @submit="$el.querySelector('[name=plan]').value = plan"
              >
                @csrf
                <input type="hidden" name="plan" value="">
                <input type="hidden" name="via"  value="wallet">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base" x-text="noPaymentDue ? 'Activate Listing' : 'Pay via Wallet'">
                  {{ $initialActionLabel }}
                </button>
                <small class="text-center subtle" x-text="noPaymentDue ? 'No wallet charge will be made for this plan.' : 'Your wallet will be debited immediately.'">{{ $initialActionHint }}</small>
              </form>

              {{-- If nothing to pay at all (free/comp) --}}
              <div x-show="noPaymentDue" class="rounded-xl border px-4 py-3 text-sm subtle mt-3" style="background:rgba(148,163,184,.12);">
                <strong class="block mb-1" style="color:var(--ink)">No payment due</strong>
                <span>Your selected plan does not require a payment right now.</span>
              </div>

              {{-- If wallet is 0 or insufficient: show Top Up Wallet CTA only --}}
              <div x-show="!canPayFromWallet && !noPaymentDue" class="text-center">
                <a :href="buildDepositUrl()" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base w-full">
                  Top Up Wallet
                </a>
                <small class="block subtle mt-2">
                  You will be redirected to the wallet deposit page to add
                  <strong><span x-text="fmt(topUpNeeded)"></span> {{ $currency }}</strong>.
                </small>
              </div>
            </div>

            <div class="mt-4">
              <div class="rounded-xl border px-4 py-3 text-sm subtle" style="background:rgba(148,163,184,.12);">
                <strong class="block mb-1" style="color:var(--ink)">Heads up</strong>
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
function checkoutPlans(cfg) {
  return {
    currency: cfg.currency,
    plans: cfg.plans || {},
    wallet: Number(cfg.wallet || 0),
    zeroDec: !!cfg.zeroDec,
    plan: cfg.initial && cfg.plans && cfg.plans[cfg.initial] ? cfg.initial : null,
    depositUrl: cfg.depositUrl,
    orderId: cfg.orderId,

    get planEntries() {
      return Object.entries(this.plans || {});
    },

    get hasPlans() {
      return this.planEntries.length > 0;
    },

    get currentDetails() {
      if (!this.plan || !this.plans[this.plan]) {
        return { amount: 0, label: '' };
      }
      return this.plans[this.plan];
    },

    get currentFee() {
      return Number(this.currentDetails.amount || 0);
    },

    get planLabel() {
      return this.currentDetails.label || (this.plan ?? '');
    },

    get canPayFromWallet() {
      return this.hasPlans && this.wallet > 0 && this.currentFee > 0 && this.wallet >= this.currentFee;
    },

    get canSubmitListing() {
      return this.hasPlans && (this.noPaymentDue || this.canPayFromWallet);
    },

    get noPaymentDue() {
      return !this.hasPlans || this.currentFee <= 0.000001;
    },

    get topUpNeeded() {
      return Math.max(0, this.currentFee - this.wallet);
    },

    fmt(value) {
      const v = Number(value || 0);
      return this.zeroDec ? String(Math.round(v)) : v.toFixed(2);
    },

    setPlan(key) {
      if (!this.plans[key]) {
        return;
      }
      this.plan = key;
      this.syncForm();
    },

    buildDepositUrl() {
      const amt = this.zeroDec ? Math.round(this.topUpNeeded) : this.topUpNeeded.toFixed(2);
      const u = new URL(this.depositUrl, window.location.origin);
      u.searchParams.set('amount', amt);
      u.searchParams.set('return', window.location.href);
      return u.toString();
    },

    syncForm() {
      const form = document.getElementById('wallet-pay-form');
      if (form) {
        if (this.canSubmitListing) {
          form.classList.remove('hidden');
        } else {
          form.classList.add('hidden');
        }
      }
    },

    init() {
      if (!this.plan && this.hasPlans) {
        this.plan = this.planEntries[0][0];
      }
      this.syncForm();
    },
  };
}
</script>
@endsection
