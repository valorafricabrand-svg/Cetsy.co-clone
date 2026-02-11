@extends('theme.'.theme().'.layouts.app')

@section('title', 'Seller Policy / Seller Agreement')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Seller Policy / Seller Agreement</h1>
        <p class="text-muted mb-4">Effective: {{ policy_effective_label() }}</p>

        <p class="mb-4">
          This Seller Policy explains what’s required to sell on Cetsy. By opening a shop, creating listings, or
          accepting orders through Cetsy, you agree to follow this policy in addition to our
          <a href="{{ url('/terms') }}">Terms &amp; Conditions</a> and <a href="{{ url('/prohibited-items') }}">Prohibited Items</a> rules.
        </p>

        <h2 class="h5 fw-semibold mt-4">1) Marketplace Role</h2>
        <p class="mb-4">
          Cetsy is a marketplace platform. You are an independent seller responsible for your listings, products,
          pricing, customer service, fulfillment, and compliance with law. Cetsy facilitates listing tools and payment
          processing, and may assist with dispute resolution.
        </p>

        <h2 class="h5 fw-semibold mt-4">2) Seller Onboarding &amp; Verification</h2>
        <ul class="mb-4">
          <li>You must provide accurate account and payout information.</li>
          <li>We may require identity verification (KYC) and additional documents to comply with payment and fraud rules.</li>
          <li>We may suspend shops that fail verification or present elevated risk.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">3) Listing Rules</h2>
        <ul class="mb-4">
          <li>Listings must be accurate (title, photos, condition, materials, size, origin, shipping details).</li>
          <li>You must only sell items that are permitted on Cetsy and legal to sell and ship to the buyer’s destination.</li>
          <li>Counterfeit goods, regulated items, and other restricted products are not allowed.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">4) Fulfillment &amp; Shipping</h2>
        <ul class="mb-4">
          <li>You are responsible for packing, shipping, and delivery (including providing tracking when available).</li>
          <li>You must ship within your stated processing time and communicate delays proactively.</li>
          <li>You are responsible for resolving delivery issues with carriers when disputes arise.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">5) Fees, Payouts &amp; Refunds</h2>
        <ul class="mb-4">
          <li>Platform fees may apply. See <a href="{{ url('/payment_policy') }}">Fees &amp; Payments</a> for details.</li>
          <li>Refunds must be handled promptly and are returned to the buyer’s original payment method where applicable.</li>
          <li>Chargebacks and payment disputes may result in reversals or additional fees, and you may be asked to provide evidence.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">6) Prohibited Conduct</h2>
        <ul class="mb-4">
          <li>Fraud, misleading listings, and manipulation of reviews are not allowed.</li>
          <li>Off‑platform payment requests are not allowed.</li>
          <li>Harassment or discriminatory behavior is not allowed.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">7) Enforcement</h2>
        <p class="mb-0">
          We may remove listings, restrict shop features, or suspend accounts for policy violations, elevated risk, or
          legal compliance reasons.
        </p>
      </div>
    </div>
  </section>
@endsection
