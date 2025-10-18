@extends('theme.'.theme().'.layouts.app')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="display-5 fw-bold mb-4">Fees &amp; Payments Policy</h1>

          <p>
            This page outlines how fees and payments work on Cetsy, including listing fees, transaction fees, and payment
            processing. Amounts may vary by currency and payment method.
          </p>

          <h2 class="h5 fw-semibold mt-4">Listing fees</h2>
          <p>
            Some categories may include a listing or subscription fee. Review your category details for current rates and
            billing periods.
          </p>

          <h2 class="h5 fw-semibold mt-4">Transaction &amp; payout</h2>
          <p>
            When you sell an item, transaction and processing fees may apply based on your chosen payment provider. Payouts
            are released according to your account’s payout schedule and country availability.
          </p>

          <h2 class="h5 fw-semibold mt-4">Payment processors</h2>
          <p>
            Third‑party processors (e.g., PayPal, card processors, or mobile money) may charge additional fees or require
            verification steps per their policies. Refer to your provider’s documentation for details.
          </p>

          <p class="mt-4">
            See also our <a href="{{ url('/privacy') }}" class="text-success">Privacy Policy</a> and
            <a href="{{ url('/house-policy') }}" class="text-success">House Rules &amp; Policy</a>.
          </p>
        </div>
      </div>
    </div>
  </section>
@endsection

