@extends('theme.'.theme().'.layouts.app')

@section('title', 'Shipping & Delivery Policy')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Shipping &amp; Delivery Policy</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }}</p>

        <p class="mt-6 leading-7 text-slate-700">
          Cetsy is a marketplace. Each seller is responsible for packing, shipping, and delivering their own items.
          Shipping prices, courier options, processing times, and delivery estimates may vary by seller and destination.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">1) Processing Times</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Sellers set their own processing times (time to prepare an order before shipping). You can view the seller's
          stated processing time and shipping options at checkout and/or in the listing details.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">2) Shipping Options &amp; Tracking</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>When available, sellers may provide tracking numbers after dispatch.</li>
          <li>Some low-cost services may not include tracking. Delivery confirmation may vary by courier.</li>
          <li>If tracking shows an issue, contact the seller first for carrier follow-up.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">3) Customs, Duties &amp; Taxes</h2>
        <p class="mt-3 leading-7 text-slate-700">
          International shipments may be subject to customs fees, import duties, and local taxes. Unless other
          arrangements have been made between the Buyer &amp; Seller, The Buyer / Consignee is always responsible to pay
          any import duties, taxes, or fees where applicable. Buyers should contact their local Customs Office to see
          if any fees apply.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">4) Delays, Lost Packages &amp; Incorrect Addresses</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Delays can occur due to weather, customs, peak seasons, or courier disruptions.</li>
          <li>Buyers are responsible for providing an accurate delivery address.</li>
          <li>If an order is confirmed delivered but you can't locate it, contact the seller promptly to start a carrier inquiry.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">5) Help</h2>
        <p class="mt-3 leading-7 text-slate-700">
          If you can't resolve a shipping issue with the seller, open a dispute from your order page or contact support
          at <a href="mailto:{{ support_email() }}" class="font-semibold text-emerald-700 hover:text-emerald-600">{{ support_email() }}</a>.
        </p>
      </article>
    </div>
  </section>
@endsection
