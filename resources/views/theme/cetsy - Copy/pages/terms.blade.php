@extends('layouts.frontapp')

@section('main')
  <!-- ===== House Terms & Conditions ===== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="display-6 fw-bold mb-4">House Terms &amp; Conditions</h1>
          <p class="lead mb-4">
            Cetsy.co is a global e-commerce marketplace providing a fair place for all to trade goods or services
            regardless of race, religion, color or political beliefs—so long as those goods or services are legally
            permitted in the seller’s region or jurisdiction.
          </p>
          <p class="mb-3">
            <strong>Our motto:</strong> Everything, Everyone, Everywhere. We encourage sellers to be creative, grow with
            the Cetsy community, and deliver accurately and promptly to buyers worldwide. Please uphold our House Rules
            and maintain an environment of trust and honesty.
          </p>
          <p class="mb-4">
            Good thoughts, good words, and good actions are the anchor of every shop and listing. If any of these
            character traits are missing, please address that before proceeding.
          </p>

          <h2 class="h5 fw-semibold mt-5">Cetsy Payment (Funds Received by Sellers)</h2>
          <p class="mb-4">
            Sellers can accept payments via Visa, Mastercard, American Express, M-Pesa, and PayPal in their local
            currencies using Cetsy’s platform.
          </p>

          <h2 class="h5 fw-semibold">Taxes</h2>
          <p class="mb-3">
            The Seller is responsible for collecting and paying any taxes associated with sales on Cetsy, including
            VAT/GST/HST or any local sales tax, unless local law requires Cetsy to collect on your behalf.
          </p>
          <p class="mb-4">
            Cetsy will calculate and remit taxes where required by law. You must ensure your listings are correctly
            categorized so the appropriate taxes are applied.
          </p>

          <h2 class="h5 fw-semibold">Chargebacks &amp; Cancellations</h2>
          <p class="mb-4">
            If a payment is disputed by the buyer and a chargeback is issued after payout, the seller must return
            the remitted funds to the transaction processor unconditionally.
          </p>

          <h2 class="h5 fw-semibold">Listing Your Items &amp; Tax Compliance</h2>
          <p class="mb-3">
            In the U.S., Cetsy’s payment processor may calculate, collect, and remit sales tax on your behalf
            depending on your state’s laws. Canadian sellers may need to include GST/HST in listing prices or have
            Cetsy collect on their behalf per provincial requirements.
          </p>
          <p class="mb-4">
            Sellers outside the U.S. and Canada must include applicable local taxes in their prices unless Cetsy is
            legally required to collect them.
          </p>

          <h2 class="h5 fw-semibold">Marketplace Reporting</h2>
          <p class="mb-4">
            Where legally required, Cetsy will report seller transaction data and personal details to authorities.
            Personal information will not be shared with third parties unless lawfully compelled.
          </p>

          <h2 class="h5 fw-semibold">Legal &amp; Regulatory Requests</h2>
          <p class="mb-3">
            Government or regulatory agencies may submit takedown or information requests to  
            <a href="mailto:legal@cetsy.co">legal@cetsy.co</a>. Please include:
          </p>
          <ul class="list-unstyled ps-3 mb-4">
            <li>• Your full name &amp; agency</li>
            <li>• Official email address</li>
            <li>• Position &amp; jurisdiction</li>
            <li>• Detailed regulatory concern &amp; links to relevant listings</li>
          </ul>

          <p class="mb-4">
            For general non-legal concerns, please use our  
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
