@extends('theme.'.theme().'.layouts.app')

@section('title', 'About Lucare – Kenya’s Top Online Beauty Store')

@section('main')
  <!-- ====== About Section Start ====== -->
  <section class="py-5 bg-white">
    <div class="container">
      <div class="row gx-5">

        <!-- Left Column: Images (hidden on sm) -->
        <div class="col-lg-6 d-none d-lg-block">
          <div class="row g-3">
            <div class="col-6">
              <img src="{{ asset('assets/images/lucare_about1.jpg') }}"
                   alt="Lucare Skincare Collection"
                   class="img-fluid rounded-3 shadow-sm">
            </div>
            <div class="col-6">
              <img src="{{ asset('assets/images/lucare_about2.jpg') }}"
                   alt="Lucare Cosmetics Range"
                   class="img-fluid rounded-3 shadow-sm">
            </div>
            <div class="col-12 position-relative mt-3">
              <img src="{{ asset('assets/images/lucare_about3.jpg') }}"
                   alt="Wellness Essentials"
                   class="img-fluid rounded-3 shadow-sm">
              <div class="position-absolute" style="bottom: -10px; right: -10px; opacity: .15;">
                <!-- Decorative SVG dots -->
                <svg width="100" height="80" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="10" cy="70" r="3" fill="#0d6efd"/>
                  <circle cx="30" cy="70" r="3" fill="#0d6efd"/>
                  <circle cx="50" cy="70" r="3" fill="#0d6efd"/>
                  <circle cx="70" cy="70" r="3" fill="#0d6efd"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Text -->
        <div class="col-12 col-lg-6">
          <span class="text-primary text-uppercase fw-semibold mb-2 d-block">About Lucare</span>
          <h2 class="fw-bold mb-4">
            Kenya’s Premier Online Beauty Store
          </h2>

          <p class="mb-4">
            Lucare brings the best of skincare, cosmetics, and wellness directly to your doorstep—curated from trusted local and international brands.
          </p>

          <h5 class="fw-semibold">Our Mission</h5>
          <p class="mb-4">
            To empower every Kenyan to look and feel their best by providing a seamless online shopping experience, unmatched product quality, and reliable nationwide delivery.
          </p>

          <h6 class="fst-italic text-secondary">What We Offer</h6>
          <p>
            Discover everything from daily skincare essentials and makeup must-haves to holistic wellness products—all in one trusted platform.
          </p>
          <p class="mb-4">
            We partner with over 100 brands, ensuring authenticity and high standards. Enjoy secure payment options including M-Pesa, credit/debit cards, and mobile wallets.
          </p>

          <p class="mb-4">
            Need assistance? Our dedicated customer support team is available via live chat and email 24/7 to help you with product recommendations, order tracking, and more.
          </p>

          <p class="mb-5">
            Join thousands of satisfied customers who trust Lucare for their beauty and wellness needs. Experience convenience, quality, and style—all at your fingertips.
          </p>

          <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">
            Contact Our Support
          </a>
        </div>

      </div>
    </div>
  </section>
  <!-- ====== About Section End ====== -->
@endsection
