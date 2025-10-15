@extends('layouts.frontapp')

@section('main')
<!-- ====== Become a Seller Starts Here ====== -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center gy-4">
      
      <!-- Text Content -->
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold">How to Become a Seller at Cetsy.co</h1>
        <p class="h5 text-secondary mb-4">
          Thank you for your interest in becoming a <strong>Seller</strong> on Cetsy â“ a global marketplace where you can offer nearly anything, to anyone, anywhere.
        </p>

        <p class="lead">
          If you can legally sell the item in your country, you can probably list it on Cetsy. Tangible goods include (but arenâ™t limited to): household items, collectibles, jewelry, artwork, vehicles, pharmaceuticals, handmade crafts, real estate, outdoor equipment, and more. Each listing can include photos, videos, and audio.
        </p>
        <p class="lead">
          Digital products (âœintangible itemsâ) are also welcome: original music, e-books, recipes, and other downloads.
        </p>
        <p class="lead">
          Not sure if your item qualifies? <a href="{{ url('/contact') }}" class="text-decoration-underline">Contact us via live chat</a> and weâ™ll help.
        </p>
        <p class="lead">
          Ready to get started? <a href="{{ url('/login') }}" class="fw-bold text-danger text-decoration-none">Review our Seller Agreement</a> to see both our expectations and your benefits as a Seller.
        </p>

        <a href="{{ url('/register') }}" class="btn btn-primary btn-lg mt-3">
          Get Started
        </a>
      </div>

      <!-- Illustration (optional) -->
      <div class="col-lg-4 text-center">
        @php
          $imgCandidate = public_path('images/become-seller.svg');
          $imgUrl = file_exists($imgCandidate)
            ? asset('images/become-seller.svg')
            : asset('assets/img/blog/blog-1.png');
        @endphp
        <img src="{{ $imgUrl }}" alt="Become a Seller" class="img-fluid" style="max-height: 300px;"
             onerror="this.onerror=null;this.src=@json(asset('assets/images/default-og-image-cetsy.jpg'));">
      </div>

    </div>
  </div>
</section>
<!-- ====== Become a Seller Ends Here ====== -->
@endsection

