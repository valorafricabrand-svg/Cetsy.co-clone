@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare Buyer Tips')

@section('main')
  <!-- ====== Lucare Buyer Tips ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Lucare Buyer Tips</h1>
          <p class="h5 text-secondary mb-4">
            Karibu! Thank you for shopping at <strong>Lucare</strong>. Follow these tips to make your beauty haul smooth, safe, and satisfying.
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item border-0 ps-0 mb-2">
              Always read product descriptions fully—check ingredients, SPF ratings, and expiry dates to ensure suitability for your skin type.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Use the “favorite” ❤ button to save must-haves. We’ll notify you of restocks, special offers, and new arrivals.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Communicate through Lucare’s chat. It’s the only channel we can track to help in case of any order issues.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Double-check your shade selections for foundations, lipsticks, and concealers. If in doubt, ask our support for swatch guidance.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Review your cart before checkout. Once payment is confirmed, orders enter processing—changes may incur delays.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              We offer free delivery on orders over {{ get_currency() }} 2,500—otherwise, shipping fees apply as shown at checkout. Track your parcel via the link in your email.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Upon receiving your order, inspect packaging seals and expiry labels. Report any damage or discrepancies via chat within 24 hours.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Leave honest reviews—ratings help other beauty lovers choose the right products and help us support reliable vendors.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              All payments are processed securely via PCI-DSS compliant gateways. Lucare never stores your card or M-Pesa details.
            </li>
            <li class="list-group-item border-0 ps-0">
              For digital products (e-guides, tutorials), check your email for download links immediately after checkout.
            </li>
          </ol>

          <p class="lead mb-4">
            Welcome to the Lucare community—where beauty meets convenience. Need assistance? Reach our support team via live chat or email anytime.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection

