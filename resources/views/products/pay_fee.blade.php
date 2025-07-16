{{-- resources/views/listings/checkout.blade.php --}}
@extends('layouts.app')
@section('title','Process Payment')

@section('styles')
<style>
  :root { --accent:#0275d8; --accent-dark:#025aa5; }
  .checkout-page { background:#f8f9fa; min-height:100vh; display:flex; align-items:center; }
  .card.glass { backdrop-filter:blur(6px); background:rgba(255,255,255,.85); border-radius:1rem; }
  @media (prefers-color-scheme:dark){
    .checkout-page{background:#121212;}
    .card.glass{background:rgba(35,35,35,.55);}
  }
  .plan-btn { width:120px; }
  .plan-btn.active { background:var(--accent); border-color:var(--accent); color:white; }
  .plan-btn:not(.active) { background:transparent; border:1px solid #ccc; color:#333; }
</style>
@endsection

@php
    $currency      = $order->currency ?? 'USD';
    $zeroDecimal   = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
    $fourMonthFee  = (float)($order->category?->listing_fee ?? 0);
    $monthlyFee    = $fourMonthFee / 4;
    $walletBalance = auth()->check() ? (float)(wallet() ?? 0) : 0;
@endphp

@section('content')
<div class="checkout-page w-100" x-data="checkout({{ json_encode($plan) }})">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 glass">
          <div class="card-body p-5">
            <h1 class="h3 fw-bold text-center mb-3">Complete Your Payment</h1>
            <p class="text-center text-body-secondary mb-4">
              Secure checkout — choose your plan.
            </p>

            {{-- 1) Plan selector --}}
            <div class="d-flex justify-content-center gap-3 mb-4">
              <button 
                type="button"
                class="plan-btn btn"
                :class="{ 'active': plan==='monthly' }"
                @click="setPlan('monthly')"
              >
                Monthly<br>
                <small>{{ $currency }}<span x-text="format(monthlyFee)"></span></small>
              </button>
              <button 
                type="button"
                class="plan-btn btn"
                :class="{ 'active': plan==='4months' }"
                @click="setPlan('4months')"
              >
                4‑Month<br>
                <small>{{ $currency }}<span x-text="format(fourMonthFee)"></span></small>
              </button>
            </div>

            {{-- 2) Display amount --}}
            <div class="alert alert-info text-center fw-semibold">
              Plan: <strong x-text="planLabel"></strong><br>
              Amount: {{ $currency }}<span x-text="format(currentFee)"></span>
            </div>

            {{-- 3) Wallet payment --}}
            <template x-if="walletBalance > 0">
              <form method="POST"
                    action="{{ route('listing.wallet.pay', $order->id) }}"
                    class="d-grid gap-2 mb-3"
                    @submit="$el.querySelector('[name=plan]').value = plan"
              >
                @csrf
                <input type="hidden" name="plan" value="">
                <input type="hidden" name="via"  value="wallet">
                <button type="submit" class="btn btn-primary">
                  Pay via Wallet ({{ $currency }}<span x-text="format(walletBalance)"></span>)
                </button>
              </form>
            </template>

            {{-- 4) PayPal --}}
            <div id="paypal-button-container" class="d-grid gap-2 mb-3"></div>
            <div id="generic-result" class="text-center fw-semibold"></div>

            <p class="text-center mt-4 small">
              <a href="{{ url()->previous() }}" class="text-decoration-none text-muted">
                &larr; Cancel and return
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id','sb') }}&currency={{ $currency }}"></script>
<script>
function checkout(initialPlan) {
  return {
    plan: initialPlan,
    monthlyFee: {{ $monthlyFee }},
    fourMonthFee: {{ $fourMonthFee }},
    walletBalance: {{ $walletBalance }},
    get currentFee() {
      return this.plan === 'monthly' ? this.monthlyFee : this.fourMonthFee;
    },
    get planLabel() {
      return this.plan === 'monthly' ? 'Monthly' : '4‑Month';
    },
    format(n) {
      return {{ in_array($currency, $zeroDecimal) ? 'Math.round(n)' : 'n.toFixed(2)' }};
    },
    setPlan(p) {
      this.plan = p;
      this.renderPayPal();
    },
    renderPayPal() {
      document.getElementById('paypal-button-container').innerHTML = '';
      const resultBlock = document.getElementById('generic-result');
      paypal.Buttons({
        style: { layout:'vertical', color:'blue', shape:'rect', label:'paypal', tagline:false },
        createOrder: (_, actions) => actions.order.create({
          purchase_units: [{ amount: { value: this.format(this.currentFee) } }]
        }),
        onApprove: (_, actions) => {
          resultBlock.textContent = '';
          return actions.order.capture().then(() => {
            // dynamically submit POST with plan & via=paypal
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('success_deposit_fee', $order->id) }}';
            form.innerHTML = `
              @csrf
              <input type="hidden" name="plan" value="${this.plan}">
              <input type="hidden" name="via"  value="paypal">
            `;
            document.body.appendChild(form);
            form.submit();
          });
        },
        onCancel: () => {
          resultBlock.className = 'text-warning fw-semibold';
          resultBlock.textContent = 'Payment cancelled – you can try again.';
        },
        onError: err => {
          console.error(err);
          resultBlock.className = 'text-danger fw-semibold';
          resultBlock.textContent = 'Unable to process payment: ' + (err?.message || 'Unknown error');
        }
      }).render('#paypal-button-container');
    },
    init() {
      this.renderPayPal();
    }
  }
}
</script>
@endsection
