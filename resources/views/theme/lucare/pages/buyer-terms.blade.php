@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare – Terms & Conditions')

@section('main')
  <!-- Spacer Section (optional) -->
  <section class="py-3">
    <div class="container">
      <!-- Top spacing -->
    </div>
  </section>

  <!-- ====== Terms & Conditions ====== -->
  <section class="py-5 bg-white">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Terms &amp; Conditions</h1>

          <p class="mb-4">
            <strong>Lucare</strong> is Kenya’s premier online beauty store, offering a fair and trusted platform for vendors to list skincare, cosmetics, and wellness products—provided all items comply with Kenyan law.
          </p>

          <p class="mb-4">
            <strong>Our promise: “Beauty for All, Delivered Nationwide.”</strong> We empower vendors to showcase quality products, and we expect prompt, honest service to our customers. Any misrepresentation, counterfeit goods, or failure to fulfill orders promptly will be grounds for removal from our platform.
          </p>

          <p class="mb-4">
            All vendors must act in good faith and maintain transparent communication with buyers. Disputes should be resolved amicably; Lucare reserves the right to mediate and enforce these Terms.
          </p>

          <h2 class="h5 fw-semibold mt-5">Order Cancellations &amp; Refunds</h2>
          <p class="mb-4">
            Buyers may cancel an order within 1 hour of purchase. After dispatch, cancellations incur a restocking fee. Refunds are processed back to the original payment method within 5–7 business days.
          </p>

          <h2 class="h5 fw-semibold mt-4">Shipping &amp; Delivery</h2>
          <p class="mb-3">
            Lucare offers free nationwide delivery on orders over {{ get_currency() }} 2,500. Standard shipping fees apply for smaller orders as shown at checkout.
          </p>
          <p class="mb-4">
            Vendors must ship orders within 2 business days. Delays beyond 5 business days must be communicated to Lucare support and the buyer.
          </p>

          <h2 class="h5 fw-semibold mt-4">Payments &amp; Chargebacks</h2>
          <p class="mb-4">
            We support M-Pesa, debit/credit cards, and mobile wallets. In case of a chargeback, Lucare will notify the vendor, and any disputed funds must be returned immediately upon resolution.
          </p>

          <h2 class="h5 fw-semibold mt-4">Taxes</h2>
          <p class="mb-3">
            Vendors are responsible for any applicable taxes (VAT, GST, etc.) on sales made through Lucare. Lucare does not withhold taxes on vendors’ behalf.
          </p>

          <p class="mb-4">
            By listing on Lucare, you agree to abide by these Terms &amp; Conditions and our <a href="{{ url('/vendor-agreement') }}" class="text-primary text-decoration-underline">Vendor Agreement</a>.
          </p>

          <a href="{{ url('/register') }}" class="btn btn-primary btn-lg">
            Get Started as a Vendor
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection

