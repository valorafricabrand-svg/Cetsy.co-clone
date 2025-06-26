@extends('theme.'.theme().'.layouts.app')

@section('main')
<!-- ====== Become a Seller Starts Here ====== -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center gy-4">
      
      <!-- Text Content -->
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold">How to Become a Seller on Jaat.co.ke</h1>
        <p class="h5 text-secondary mb-4">
          Thank you for your interest in becoming a <strong>Seller</strong> on Jaat – Kenya’s marketplace where you can offer almost anything to shoppers nationwide.
        </p>

        <p class="lead">
          If an item or service is legal in Kenya, you can list it on Jaat. Tangible goods include (but aren’t limited to): handcrafted décor, collectibles, jewelry, artwork, vehicles, livestock, handmade crafts, property, outdoor equipment, and more. Each listing supports photos and—very soon—short videos with audio.
        </p>
        <p class="lead">
          <strong>Digital products</strong> (“intangible items”) are also welcome: original music, e-books, design templates, recipes, and other downloads.
        </p>
        <p class="lead">
          Not sure if your item qualifies? <a href="{{ url('/contact') }}" class="text-decoration-underline">Chat with us live</a> and we’ll guide you.
        </p>
        <p class="lead">
          Ready to begin? First, <a href="{{ url('/seller-agreement') }}" class="fw-bold text-danger text-decoration-none">review our Seller Agreement</a> to understand both our expectations and the benefits you’ll enjoy as a Jaat seller – including fast M-Pesa payouts.
        </p>

        <a href="{{ url('/register') }}" class="btn btn-primary btn-lg mt-3">
          Get Started
        </a>
      </div>

      <!-- Illustration (optional) -->
      <div class="col-lg-4 text-center">
        <img src="{{ asset('images/jaat-become-seller.svg') }}"
             alt="Become a Seller on Jaat"
             class="img-fluid"
             style="max-height: 300px;">
      </div>

    </div>
  </div>
</section>
<!-- ====== Become a Seller Ends Here ====== -->
@endsection
