@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ===== House Terms & Conditions ===== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="display-6 fw-bold mb-4">House Terms &amp; Conditions</h1>

          <p class="lead mb-4">
            <strong>Jaat.co.ke</strong> is a Kenyan e-commerce marketplace that offers a fair space for everyone to trade
            goods and services—regardless of race, religion, or political beliefs—provided the items or services are
            legal where the seller operates.
          </p>

          <p class="mb-3">
            <strong>Our motto:</strong> <em>Everything Kenyan. Everyone. Everywhere.</em> We encourage sellers to be
            creative, grow with the Jaat community, and deliver accurately and promptly to buyers country-wide (and
            beyond). Please uphold these House Rules and help maintain an environment of trust and honesty.
          </p>

          <p class="mb-4">
            Good thoughts, good words, and good actions should anchor every shop and listing. If any of these
            virtues is lacking, kindly address that before proceeding.
          </p>

          <!-- Payments -->
          <h2 class="h5 fw-semibold mt-5">Jaat Payments (Funds Received by Sellers)</h2>
          <p class="mb-4">
            Sellers can accept payments via Visa, Mastercard, American Express, M-Pesa, Airtel Money,
            and PayPal (USD or {{ get_currency() }}) through Jaat’s payment gateway.
          </p>

          <!-- Taxes -->
          <h2 class="h5 fw-semibold">Taxes</h2>
          <p class="mb-3">
            Unless Kenyan law requires otherwise, sellers are responsible for collecting and remitting any taxes
            linked to sales on Jaat, including VAT. Where legislation obliges Jaat to collect VAT (or other levies)
            at checkout, we will do so and remit the funds to the Kenya Revenue Authority (KRA) or relevant agency.
          </p>
          <p class="mb-4">
            Ensure your listings are correctly categorised so the proper tax rules apply.
          </p>

          <!-- Chargebacks -->
          <h2 class="h5 fw-semibold">Chargebacks &amp; Cancellations</h2>
          <p class="mb-4">
            If a buyer disputes a payment and a chargeback is issued after you have been paid out, you must return
            the remitted funds to the payment processor or to Jaat immediately and unconditionally.
          </p>

          <!-- Listing & Tax Compliance -->
          <h2 class="h5 fw-semibold">Listing Your Items &amp; Tax Compliance</h2>
          <p class="mb-3">
            Kenyan sellers must include VAT in listing prices unless exempt. Sellers outside Kenya should comply with
            their local tax laws and incorporate any required taxes in the item price, unless a marketplace-facilitated
            tax programme covers them.
          </p>
          <p class="mb-4">
            Misclassified listings may lead to under- or over-collection of tax, so please double-check category and
            compliance details before publishing.
          </p>

          <!-- Marketplace Reporting -->
          <h2 class="h5 fw-semibold">Marketplace Reporting</h2>
          <p class="mb-4">
            Where required by law, Jaat will report seller transaction data to the Kenya Revenue Authority or other
            regulators. Your personal information will only be shared when legally compelled.
          </p>

          <!-- Legal Requests -->
          <h2 class="h5 fw-semibold">Legal &amp; Regulatory Requests</h2>
          <p class="mb-3">
            Government or regulatory bodies may send takedown or information requests to
            <a href="mailto:legal@jaat.co.ke">legal@jaat.co.ke</a>. Please include:
          </p>
          <ul class="list-unstyled ps-3 mb-4">
            <li>• Your full name &amp; agency</li>
            <li>• Official email address</li>
            <li>• Position &amp; jurisdiction</li>
            <li>• Detailed regulatory concern &amp; links to relevant listings</li>
          </ul>

          <p class="mb-4">
            For non-legal questions, please use our
            <a href="{{ url('/contact') }}" class="link-primary">Contact</a> page or Live Chat.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-danger btn-lg">
            Get Started as a Seller
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Quick Links ===== -->
  <section class="py-4 bg-light">
    <div class="container">
      <div class="row justify-content-center g-3">
        <div class="col-auto">
          <a href="{{ url('/buyer_tips') }}" class="btn btn-outline-primary px-4">
            Buyer Tips
          </a>
        </div>
        <div class="col-auto">
          <a href="{{ url('/privacy') }}" class="btn btn-outline-secondary px-4">
            Seller Privacy Policy
          </a>
        </div>
      </div>
    </div>
  </section>
@endsection

