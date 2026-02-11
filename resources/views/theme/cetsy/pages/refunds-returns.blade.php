@extends('theme.'.theme().'.layouts.app')

@section('title', 'Refund & Returns Policy')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Refund &amp; Returns Policy</h1>
        <p class="text-muted mb-4">Effective: {{ policy_effective_label() }}</p>

        <p class="mb-4">
          Cetsy is an online marketplace. Most items are sold and shipped by independent third‑party sellers. Cetsy
          facilitates checkout and payments, but the seller is responsible for fulfilling the order and handling
          returns, refunds, and exchanges, subject to this policy and applicable law.
        </p>

        <h2 class="h5 fw-semibold mt-4">1) How to Request a Return or Refund</h2>
        <ol class="mb-4">
          <li>Contact the seller from your order page as soon as you notice an issue.</li>
          <li>If the seller doesn’t respond or you can’t reach agreement, open a dispute and contact support.</li>
          <li>Provide photos and details (e.g., damage, wrong item, missing parts) to speed up resolution.</li>
        </ol>

        <h2 class="h5 fw-semibold mt-4">2) Timeframes</h2>
        <ul class="mb-4">
          <li><strong>Physical items:</strong> request a return/refund within <strong>14 days</strong> of delivery.</li>
          <li><strong>Damaged / incorrect items:</strong> report within <strong>7 days</strong> of delivery.</li>
          <li><strong>Digital items (if offered):</strong> refunds may be limited once a download is delivered, except where required by law or if the file is defective/not as described.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">3) Eligible Conditions</h2>
        <ul class="mb-4">
          <li>Item arrived damaged.</li>
          <li>Wrong item received.</li>
          <li>Item not as described (material mismatch, missing parts, etc.).</li>
          <li>Item not received (after reasonable carrier investigation / tracking review).</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">4) Return Shipping</h2>
        <p class="mb-4">
          Unless otherwise agreed, buyers are responsible for return shipping for “changed mind” returns. For damaged,
          incorrect, or significantly not‑as‑described items, the seller may be responsible for return shipping or may
          offer a replacement or refund without requiring a return.
        </p>

        <h2 class="h5 fw-semibold mt-4">5) Refund Method</h2>
        <p class="mb-4">
          Approved refunds are processed back to the <strong>original payment method</strong> used at checkout (for
          example, via Stripe card refund). Processing time depends on your bank/payment provider.
        </p>

        <h2 class="h5 fw-semibold mt-4">6) Disputes &amp; Chargebacks</h2>
        <p class="mb-4">
          If you can’t resolve an issue with the seller, you can open a dispute on Cetsy. Please contact us before
          filing a chargeback with your bank—chargebacks can delay resolution and may require additional evidence.
        </p>

        <h2 class="h5 fw-semibold mt-4">7) Contact</h2>
        <p class="mb-0">
          Questions about this policy? Contact us at <a href="mailto:{{ support_email() }}">{{ support_email() }}</a> or
          visit our <a href="{{ url('/contact') }}">Contact</a> page.
        </p>
      </div>
    </div>
  </section>
@endsection
