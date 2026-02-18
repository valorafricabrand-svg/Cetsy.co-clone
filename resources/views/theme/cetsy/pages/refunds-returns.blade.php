@extends('theme.'.theme().'.layouts.app')

@section('title', 'Refund & Returns Policy')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Refund &amp; Returns Policy</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }}</p>

        <p class="mt-6 leading-7 text-slate-700">
          Cetsy is an online marketplace. Most items are sold and shipped by independent third-party sellers. Cetsy
          facilitates checkout and payments, but the seller is responsible for fulfilling the order and handling
          returns, refunds, and exchanges, subject to this policy and applicable law.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">1) How to Request a Return or Refund</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-6 text-slate-700">
          <li>Contact the seller from your order page as soon as you notice an issue.</li>
          <li>If the seller doesn't respond or you can't reach agreement, open a dispute and contact support.</li>
          <li>Provide photos and details (e.g., damage, wrong item, missing parts) to speed up resolution.</li>
        </ol>

        <h2 class="mt-8 text-lg font-bold text-slate-900">2) Timeframes</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li><strong>Physical items:</strong> request a return/refund within <strong>14 days</strong> of delivery.</li>
          <li><strong>Damaged / incorrect items:</strong> report within <strong>7 days</strong> of delivery.</li>
          <li><strong>Digital items (if offered):</strong> refunds may be limited once a download is delivered, except where required by law or if the file is defective/not as described.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">3) Eligible Conditions</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Item arrived damaged.</li>
          <li>Wrong item received.</li>
          <li>Item not as described (material mismatch, missing parts, etc.).</li>
          <li>Item not received (after reasonable carrier investigation / tracking review).</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">4) Return Shipping</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Unless otherwise agreed, buyers are responsible for return shipping for "changed mind" returns. For damaged,
          incorrect, or significantly not-as-described items, the seller may be responsible for return shipping or may
          offer a replacement or refund without requiring a return.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">5) Refund Method</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Approved refunds are processed back to the <strong>original payment method</strong> used at checkout (for
          example, via Stripe card refund). Processing time depends on your bank/payment provider.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">6) Disputes &amp; Chargebacks</h2>
        <p class="mt-3 leading-7 text-slate-700">
          If you can't resolve an issue with the seller, you can open a dispute on Cetsy. Please contact us before
          filing a chargeback with your bank-chargebacks can delay resolution and may require additional evidence.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">7) Contact</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Questions about this policy? Contact us at <a href="mailto:{{ support_email() }}" class="font-semibold text-emerald-700 hover:text-emerald-600">{{ support_email() }}</a> or
          visit our <a href="{{ url('/contact') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Contact</a> page.
        </p>
      </article>
    </div>
  </section>
@endsection
