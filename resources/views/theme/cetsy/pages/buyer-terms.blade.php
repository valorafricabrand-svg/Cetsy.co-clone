@extends('layouts.frontapp')

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
            Cetsy.co is a global e-commerce platform that provides an equal and fair place for all to trade their goods or services regardless of an individual’s race, religion, color, political, or religious beliefs—so long as those goods or services are legally permitted for sale in that seller’s region or jurisdiction.
          </p>

          <p class="mb-4">
            <strong>Our motto is Everything, Everyone, Everywhere.</strong> We encourage all sellers to be creative, to grow with the Cetsy community, and most importantly, to deliver accurately and promptly to buyers worldwide. Please uphold our House Rules and do not create an environment filled with mistrust, disbelief, or lies.
          </p>

          <p class="mb-4">
            The spirit of utmost good faith and genuine trade intentions should be the anchor of your shop or listings. Above all, both buyers and sellers should embody good thoughts, good words, and good actions. If any of these three traits is missing, please remedy it before proceeding.
          </p>

          <h2 class="h5 fw-semibold mt-5">Chargebacks &amp; Cancellations</h2>
          <p class="mb-4">
            If a Buyer disputes a credit/debit card payment, the issuer submits a claim. Should a chargeback occur after payout, the Merchant must unconditionally return the remitted funds to the Transaction Processor.
          </p>

          <h2 class="h5 fw-semibold mt-4">Taxes</h2>
          <p class="mb-3">
            Except where local law requires otherwise, the Seller is responsible for collecting and paying any taxes associated with sales through Cetsy’s services.
          </p>
          <p class="mb-4">
            Note that some regions call VAT by other names (e.g., GST or HST). Here, we collectively refer to all such sales taxes as “VAT.”
          </p>

          <p class="mb-4">
            To become a Cetsy Seller in just a few simple steps, <a href="{{ url('/login') }}" class="text-decoration-underline">CLICK HERE</a>.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
