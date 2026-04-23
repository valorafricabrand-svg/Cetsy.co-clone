@extends('theme.'.theme().'.layouts.app')

@section('title', 'Seller Policy / Seller Agreement')
@section('meta_description', 'Review Cetsy seller policy requirements for onboarding, listings, fulfillment, fees, payouts, refunds, and enforcement.')
@section('canonical_url', route('seller-policy'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Seller Policy / Seller Agreement</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }}</p>

        <p class="mt-6 leading-7 text-slate-700">
          This Seller Policy explains what's required to sell on Cetsy. By opening a shop, creating listings, or
          accepting orders through Cetsy, you agree to follow this policy in addition to our
          <a href="{{ url('/terms') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Terms &amp; Conditions</a> and <a href="{{ url('/prohibited-items') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Prohibited Items</a> rules.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">1) Marketplace Role</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Cetsy is a marketplace platform. You are an independent seller responsible for your listings, products,
          pricing, customer service, fulfillment, and compliance with law. Cetsy facilitates listing tools and payment
          processing, and may assist with dispute resolution.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">2) Seller Onboarding &amp; Verification</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>You must provide accurate account and payout information.</li>
          <li>We may require identity verification (KYC) and additional documents to comply with payment and fraud rules.</li>
          <li>We may suspend shops that fail verification or present elevated risk.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">3) Listing Rules</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Listings must be accurate (title, photos, condition, materials, size, origin, shipping details).</li>
          <li>You must only sell items that are permitted on Cetsy and legal to sell and ship to the buyer's destination.</li>
          <li>Counterfeit goods, regulated items, and other restricted products are not allowed.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">4) Fulfillment &amp; Shipping</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>You are responsible for packing, shipping, and delivery (including providing tracking when available).</li>
          <li>You must ship within your stated processing time and communicate delays proactively.</li>
          <li>You are responsible for resolving delivery issues with carriers when disputes arise.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">5) Fees, Payouts &amp; Refunds</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Platform fees may apply. See <a href="{{ route('payment_policy') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Fees &amp; Payments</a> for details.</li>
          <li>Refunds must be handled promptly and are returned to the buyer's original payment method where applicable.</li>
          <li>Chargebacks and payment disputes may result in reversals or additional fees, and you may be asked to provide evidence.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">6) Prohibited Conduct</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Fraud, misleading listings, and manipulation of reviews are not allowed.</li>
          <li>Off-platform payment requests are not allowed.</li>
          <li>Harassment or discriminatory behavior is not allowed.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">7) Enforcement</h2>
        <p class="mt-3 leading-7 text-slate-700">
          We may remove listings, restrict shop features, or suspend accounts for policy violations, elevated risk, or
          legal compliance reasons.
        </p>
      </article>
    </div>
  </section>
@endsection
