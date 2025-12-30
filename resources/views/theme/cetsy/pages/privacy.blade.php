@extends('theme.'.theme().'.layouts.app')

@section('title', 'Privacy Policy')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Privacy Policy</h1>
        <p class="text-muted mb-4">Effective: {{ now()->format('F j, Y') }}</p>

        <p class="mb-4">
          This Privacy Policy explains how {{ config('app.name','Cetsy') }} (“we”, “us”, “our”) collects, uses, and
          shares personal information when you use our website, create an account, open a shop, make a purchase, or
          otherwise interact with our services.
        </p>

        <h2 class="h5 fw-semibold mt-4">1) Information We Collect</h2>
        <ul class="mb-4">
          <li><strong>Account data:</strong> name, email, phone number, login and profile details.</li>
          <li><strong>Order data:</strong> items purchased, shipping address, messages between buyers and sellers, order status.</li>
          <li><strong>Payment data:</strong> payment confirmations and transaction identifiers. Card details are processed by our payment processors and are not stored on our servers.</li>
          <li><strong>Seller/shop data:</strong> shop profile, listings, payout details, and verification information where required.</li>
          <li><strong>Device/usage data:</strong> IP address, browser/device information, logs, cookies and similar technologies.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">2) How We Use Information</h2>
        <ul class="mb-4">
          <li>Provide the Platform (accounts, listings, orders, and customer support).</li>
          <li>Process payments, refunds, and dispute resolution.</li>
          <li>Prevent fraud, detect abuse, and secure the Platform.</li>
          <li>Comply with legal and regulatory obligations.</li>
          <li>Improve features and user experience.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">3) Sharing &amp; Disclosure</h2>
        <p class="mb-3">
          We may share information with:
        </p>
        <ul class="mb-4">
          <li><strong>Payment processors (including Stripe):</strong> to process card payments, refunds, and fraud prevention.</li>
          <li><strong>Other payment providers (where available):</strong> such as PayPal and M‑Pesa.</li>
          <li><strong>Service providers:</strong> hosting, email delivery, analytics, and support tools.</li>
          <li><strong>Logistics/carriers:</strong> where needed for shipping and delivery support.</li>
          <li><strong>Legal:</strong> if required by law, court order, or to protect users, the Platform, or our rights.</li>
        </ul>
        <p class="mb-4">We do not sell your personal data.</p>

        <h2 id="cookies" class="h5 fw-semibold mt-4">4) Cookies &amp; Analytics</h2>
        <p class="mb-4">
          We use cookies and similar technologies to keep you signed in, remember preferences, understand site usage,
          and improve the Platform. You can control cookies through your browser settings.
        </p>

        <h2 id="rights" class="h5 fw-semibold mt-4">5) Your Rights</h2>
        <p class="mb-4">
          Depending on your location, you may request access, correction, deletion, or portability of your data, and
          object to or restrict certain processing.
        </p>

        <h2 class="h5 fw-semibold mt-4">6) Security &amp; Retention</h2>
        <p class="mb-4">
          We use reasonable safeguards to protect your information. No method of transmission or storage is 100%
          secure. We retain information as needed to provide the service, comply with legal obligations, resolve
          disputes, and enforce agreements.
        </p>

        <h2 class="h5 fw-semibold mt-4">7) Contact</h2>
        <p class="mb-0">
          Questions or requests? Email <a href="mailto:{{ support_email() }}">{{ support_email() }}</a> or visit our
          <a href="{{ url('/contact') }}">Contact</a> page.
        </p>
      </div>
    </div>
  </section>
@endsection

