@extends('theme.'.theme().'.layouts.app')

@section('title', 'Privacy Policy')
@section('meta_description', 'Read the Cetsy privacy policy covering data collection, cookies, account information, sharing, security, and user rights.')
@section('canonical_url', route('privacy'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Privacy Policy</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }}</p>

        <p class="mt-6 leading-7 text-slate-700">
          This Privacy Policy explains how {{ config('app.name','Cetsy') }} ("we", "us", "our") collects, uses, and
          shares personal information when you use our website, create an account, open a shop, make a purchase, or
          otherwise interact with our services.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">1) Information We Collect</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li><strong>Account data:</strong> name, email, phone number, login and profile details.</li>
          <li><strong>Order data:</strong> items purchased, shipping address, messages between buyers and sellers, order status.</li>
          <li><strong>Payment data:</strong> payment confirmations and transaction identifiers. Card details are processed by our payment processors and are not stored on our servers.</li>
          <li><strong>Seller/shop data:</strong> shop profile, listings, payout details, and verification information where required.</li>
          <li><strong>Device/usage data:</strong> IP address, browser/device information, logs, cookies and similar technologies.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">2) How We Use Information</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Provide the Platform (accounts, listings, orders, and customer support).</li>
          <li>Process payments, refunds, and dispute resolution.</li>
          <li>Prevent fraud, detect abuse, and secure the Platform.</li>
          <li>Comply with legal and regulatory obligations.</li>
          <li>Improve features and user experience.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">3) Sharing &amp; Disclosure</h2>
        <p class="mt-3 leading-7 text-slate-700">We may share information with:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li><strong>Payment processors (including Stripe):</strong> to process card payments, refunds, and fraud prevention.</li>
          <li><strong>Other payment providers (where available):</strong> such as PayPal and M-Pesa.</li>
          <li><strong>Service providers:</strong> hosting, email delivery, analytics, and support tools.</li>
          <li><strong>Logistics/carriers:</strong> where needed for shipping and delivery support.</li>
          <li><strong>Legal:</strong> if required by law, court order, or to protect users, the Platform, or our rights.</li>
        </ul>
        <p class="mt-3 leading-7 text-slate-700">We do not sell your personal data.</p>

        <h2 id="cookies" class="mt-8 text-lg font-bold text-slate-900">4) Cookies &amp; Analytics</h2>
        <p class="mt-3 leading-7 text-slate-700">
          We use cookies and similar technologies to keep you signed in, remember preferences, understand site usage,
          and improve the Platform. You can control cookies through your browser settings.
        </p>

        <h2 id="rights" class="mt-8 text-lg font-bold text-slate-900">5) Your Rights</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Depending on your location, you may request access, correction, deletion, or portability of your data, and
          object to or restrict certain processing.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">6) Security &amp; Retention</h2>
        <p class="mt-3 leading-7 text-slate-700">
          We use reasonable safeguards to protect your information. No method of transmission or storage is 100%
          secure. We retain information as needed to provide the service, comply with legal obligations, resolve
          disputes, and enforce agreements.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">7) Contact</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Questions or requests? Email <a href="mailto:{{ support_email() }}" class="font-semibold text-emerald-700 hover:text-emerald-600">{{ support_email() }}</a> or visit our
          <a href="{{ url('/contact') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Contact</a> page.
        </p>
      </article>
    </div>
  </section>
@endsection
