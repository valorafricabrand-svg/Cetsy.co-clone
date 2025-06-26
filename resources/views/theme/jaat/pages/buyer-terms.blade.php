@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- Spacer Section (optional) -->
  <section class="py-3">
    <div class="container">
      <!-- Intentionally left blank for top spacing -->
    </div>
  </section>

  <!-- ====== House Terms & Conditions ====== -->
  <section class="py-5 bg-white">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">House Terms &amp; Conditions</h1>

          <p class="mb-4">
            <strong>Jaat.co.ke</strong> is a Kenyan e-commerce marketplace that offers an equal and fair venue for all to trade goods and services—regardless of race, religion, colour, or political views—provided the items and services are legal within the seller’s jurisdiction.
          </p>

          <p class="mb-4">
            <strong>Our motto is “Everything Kenyan. Everyone. Everywhere.”</strong> We encourage sellers to be creative, grow with the Jaat community, and, most importantly, deliver accurately and promptly to buyers country-wide (and beyond). Please honour our House Rules: do not create an environment of mistrust, misinformation, or deceit.
          </p>

          <p class="mb-4">
            The spirit of utmost good faith and genuine trade intentions must anchor your shop and listings. Buyers and sellers alike should embody good thoughts, good words, and good actions. If any of these virtues is missing, kindly address it before proceeding.
          </p>

          <h2 class="h5 fw-semibold mt-5">Chargebacks &amp; Cancellations</h2>
          <p class="mb-4">
            If a buyer disputes a card payment, the issuing bank may raise a chargeback. Should a chargeback occur after funds have been disbursed, the seller must, without exception, return the remitted amount to the payment processor or to Jaat as instructed.
          </p>

          <h2 class="h5 fw-semibold mt-4">Taxes</h2>
          <p class="mb-3">
            Unless Kenyan law stipulates otherwise, the seller is responsible for collecting and remitting all taxes associated with sales made through Jaat’s services.
          </p>
          <p class="mb-4">
            In some countries VAT is referred to as GST, HST, or another term. Throughout these Terms, all such sales taxes are referred to collectively as “VAT.”
          </p>

          <p class="mb-4">
            Ready to trade on Jaat? <a href="{{ url('/seller-agreement') }}" class="text-decoration-underline">Read the Seller Agreement</a> to understand your responsibilities and benefits, then create your seller account in just a few steps.
          </p>

          <a href="{{ url('/register') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
