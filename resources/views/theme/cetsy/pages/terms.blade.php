@extends('theme.'.theme().'.layouts.app')

@section('title', 'Terms & Conditions')
@section('meta_description', 'Read Cetsy terms and conditions covering accounts, listings, payments, shipping, disputes, enforcement, liability, and contact.')
@section('canonical_url', localized_route('terms'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Terms &amp; Conditions</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }}</p>

        <p class="mt-6 leading-7 text-slate-700">
          These Terms &amp; Conditions govern your use of {{ config('app.name','Cetsy') }} (the "Platform"). By accessing
          the Platform, creating an account, opening a shop, listing items, or making a purchase, you agree to these
          Terms.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">1) Marketplace Role (Important)</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>{{ config('app.name','Cetsy') }} is an online marketplace platform.</li>
          <li>Items are sold by independent third-party sellers. Sellers are not employees, agents, or representatives of {{ config('app.name','Cetsy') }}.</li>
          <li>When you buy an item, the sales contract is between the buyer and the seller. {{ config('app.name','Cetsy') }} is not the seller of items listed by third parties.</li>
          <li>{{ config('app.name','Cetsy') }} facilitates checkout, payment processing, and certain support/dispute tools.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">2) Accounts &amp; Eligibility</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>You must provide accurate information and keep your account secure.</li>
          <li>You are responsible for activity under your account.</li>
          <li>We may suspend or terminate accounts that violate policies or create risk to users or payment partners.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">3) Listings, Products &amp; Prohibited Items</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Sellers must only list items that are permitted on the Platform and legal to sell and ship.</li>
          <li>Listings must be accurate (description, price, photos, condition, shipping terms).</li>
          <li>Prohibited and restricted items are not allowed. See <a href="{{ url('/prohibited-items') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Prohibited / Restricted Items</a>.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">4) Payments</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Payments are processed through third-party payment processors (for example, Stripe and other supported
          providers). By making or receiving payments, you also agree to the applicable processor terms.
        </p>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Buyers authorize charges for purchases, shipping, and applicable taxes/fees shown at checkout.</li>
          <li>Sellers authorize {{ config('app.name','Cetsy') }} and its payment partners to collect payments on their behalf and to distribute payouts according to platform rules.</li>
          <li>Refunds (when approved) are returned to the buyer's original payment method.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">5) Shipping, Delivery &amp; Returns</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Sellers are responsible for packaging, shipping, delivery, and providing customer support for their orders.</li>
          <li>See our <a href="{{ url('/shipping-delivery') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Shipping &amp; Delivery Policy</a> and <a href="{{ url('/refunds-returns') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Refund &amp; Returns Policy</a>.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">6) Disputes, Chargebacks &amp; Enforcement</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>If there's an issue with an order, buyers should contact the seller first and then open a dispute if unresolved.</li>
          <li>We may request evidence from buyers/sellers to help resolve disputes (photos, tracking, messages, etc.).</li>
          <li>Chargebacks may result in reversals and additional fees and may require cooperation from the parties.</li>
          <li>We may remove listings, withhold payouts where permitted, or suspend accounts for policy violations or risk reasons.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">7) Limitation of Liability</h2>
        <p class="mt-3 leading-7 text-slate-700">
          To the maximum extent permitted by law, {{ config('app.name','Cetsy') }} is not liable for indirect, incidental,
          special, consequential, or punitive damages, or for losses arising from transactions between buyers and
          sellers. Our total liability for any claim related to the Platform is limited to the amount of fees paid to
          {{ config('app.name','Cetsy') }} for the transaction giving rise to the claim (if any), unless required
          otherwise by law.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">8) Governing Law / Jurisdiction</h2>
        <p class="mt-3 leading-7 text-slate-700">
          These Terms are governed by the laws of <strong>{{ legal_jurisdiction() }}</strong>, and disputes will be
          handled in the courts/tribunals with jurisdiction in <strong>{{ legal_jurisdiction() }}</strong>, unless
          consumer protection laws provide otherwise.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">9) Changes</h2>
        <p class="mt-3 leading-7 text-slate-700">
          We may update these Terms from time to time. We will post the updated version on this page with a new
          effective date.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">10) Contact</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Questions about these Terms? Email <a href="mailto:{{ support_email() }}" class="font-semibold text-emerald-700 hover:text-emerald-600">{{ support_email() }}</a> or visit
          our <a href="{{ url('/contact') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Contact</a> page.
        </p>
      </article>
    </div>
  </section>
@endsection
