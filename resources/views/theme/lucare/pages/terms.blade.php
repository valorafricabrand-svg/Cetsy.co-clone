@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare – Terms & Conditions')

@section('main')
  <!-- ====== Lucare Terms & Conditions ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="display-6 fw-bold mb-4">Terms &amp; Conditions</h1>

          <p class="lead mb-4">
            <strong>Lucare</strong> is Kenya’s premier online beauty store, providing a fair and trusted platform for vendors to list skincare, cosmetics, and wellness products—provided all items comply with Kenyan law.
          </p>

          <p class="mb-3">
            <strong>Our promise:</strong> <em>“Beauty for All, Delivered Nationwide.”</em> We empower vendors to showcase high-quality products, and we expect prompt, honest service to customers. Please uphold these rules to maintain trust and transparency.
          </p>

          <p class="mb-4">
            Good intentions, good communication, and good service should underpin every listing. If any of these is lacking, address it before proceeding.
          </p>

          <!-- Payments -->
          <h2 class="h5 fw-semibold mt-5">Payments &amp; Payouts</h2>
          <p class="mb-4">
            Vendors can accept payments via M-Pesa, credit/debit cards, and mobile wallets through Lucare’s secure payment gateway. Funds are disbursed to your account within 2 business days of order confirmation.
          </p>

          <!-- Taxes -->
          <h2 class="h5 fw-semibold">Taxes</h2>
          <p class="mb-3">
            Vendors are responsible for collecting and remitting all applicable taxes (e.g., VAT) on sales. Lucare will collect marketplace-facilitated VAT at checkout when required by law and remit it to the Kenya Revenue Authority.
          </p>
          <p class="mb-4">
            Ensure your listings are correctly categorized so tax rules apply accurately.
          </p>

          <!-- Cancellations & Chargebacks -->
          <h2 class="h5 fw-semibold">Cancellations &amp; Chargebacks</h2>
          <p class="mb-4">
            Buyers may cancel an order within 1 hour of purchase. After dispatch, cancellations incur a restocking fee. In case of a chargeback after payout, vendors must return the disputed funds immediately upon notification.
          </p>

          <!-- Listing Compliance -->
          <h2 class="h5 fw-semibold">Listing &amp; Compliance</h2>
          <p class="mb-4">
            All products must be legal in Kenya and accurately described. Misleading or prohibited items will be removed, and repeat offenses may lead to account suspension.
          </p>

          <!-- Legal Requests -->
          <h2 class="h5 fw-semibold">Legal &amp; Regulatory Requests</h2>
          <p class="mb-3">
            Government or regulatory bodies may send takedown or information requests to <a href="mailto:legal@lucare.co.ke">legal@lucare.co.ke</a>. Please include:
          </p>
          <ul class="list-unstyled ps-3 mb-4">
            <li>• Full name &amp; agency</li>
            <li>• Official contact details</li>
            <li>• Detailed request &amp; relevant product links</li>
          </ul>

          <p class="mb-4">
            For general inquiries, please use our <a href="{{ url('/contact') }}" class="link-primary">Contact</a> page or Live Chat.
          </p>

          <a href="{{ url('/register') }}" class="btn btn-primary btn-lg">
            Get Started as a Vendor
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== Quick Links ====== -->
  <section class="py-4 bg-light">
    <div class="container">
      <div class="row justify-content-center g-3">
        <div class="col-auto">
          <a href="{{ url('/buyer-tips') }}" class="btn btn-outline-primary px-4">
            Buyer Tips
          </a>
        </div>
        <div class="col-auto">
          <a href="{{ url('/vendor-guidelines') }}" class="btn btn-outline-secondary px-4">
            Vendor Guidelines
          </a>
        </div>
      </div>
    </div>
  </section>
@endsection
