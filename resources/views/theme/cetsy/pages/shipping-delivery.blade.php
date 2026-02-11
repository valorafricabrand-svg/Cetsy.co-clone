@extends('theme.'.theme().'.layouts.app')

@section('title', 'Shipping & Delivery Policy')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Shipping &amp; Delivery Policy</h1>
        <p class="text-muted mb-4">Effective: {{ policy_effective_label() }}</p>

        <p class="mb-4">
          Cetsy is a marketplace. Each seller is responsible for packing, shipping, and delivering their own items.
          Shipping prices, courier options, processing times, and delivery estimates may vary by seller and destination.
        </p>

        <h2 class="h5 fw-semibold mt-4">1) Processing Times</h2>
        <p class="mb-4">
          Sellers set their own processing times (time to prepare an order before shipping). You can view the seller’s
          stated processing time and shipping options at checkout and/or in the listing details.
        </p>

        <h2 class="h5 fw-semibold mt-4">2) Shipping Options &amp; Tracking</h2>
        <ul class="mb-4">
          <li>When available, sellers may provide tracking numbers after dispatch.</li>
          <li>Some low‑cost services may not include tracking. Delivery confirmation may vary by courier.</li>
          <li>If tracking shows an issue, contact the seller first for carrier follow‑up.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">3) Customs, Duties &amp; Taxes</h2>
        <p class="mb-4">
          International shipments may be subject to customs fees, import duties, and local taxes. Unless other
          arrangements have been made between the Buyer &amp; Seller, The Buyer / Consignee is always responsible to pay
          any import duties, taxes, or fees where applicable. Buyers should contact their local Customs Office to see
          if any fees apply.
        </p>

        <h2 class="h5 fw-semibold mt-4">4) Delays, Lost Packages &amp; Incorrect Addresses</h2>
        <ul class="mb-4">
          <li>Delays can occur due to weather, customs, peak seasons, or courier disruptions.</li>
          <li>Buyers are responsible for providing an accurate delivery address.</li>
          <li>If an order is confirmed delivered but you can’t locate it, contact the seller promptly to start a carrier inquiry.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">5) Help</h2>
        <p class="mb-0">
          If you can’t resolve a shipping issue with the seller, open a dispute from your order page or contact support
          at <a href="mailto:{{ support_email() }}">{{ support_email() }}</a>.
        </p>
      </div>
    </div>
  </section>
@endsection
