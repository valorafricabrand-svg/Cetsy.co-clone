@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ====== About Section Start ====== -->
  <section class="py-5 bg-white">
    <div class="container">
      <div class="row gx-5">

        <!-- Left Column: Images (hidden on sm) -->
        <div class="col-lg-6 d-none d-lg-block">
          <div class="row g-3">
            <div class="col-6">
              <img src="{{ asset('build/assets/images/jaatabout1.jpg') }}"
                   alt="Jaat Boutique"
                   class="img-fluid rounded-3 shadow-sm">
            </div>
            <div class="col-6">
              <img src="{{ asset('build/assets/images/jaatabout3.jpg') }}"
                   alt="Jaat Marketplace"
                   class="img-fluid rounded-3 shadow-sm">
            </div>
            <div class="col-12 position-relative mt-3">
              <img src="{{ asset('build/assets/images/jaatabout2.jpg') }}"
                   alt="Jaat Community"
                   class="img-fluid rounded-3 shadow-sm">
              <div class="position-absolute" style="bottom: -10px; right: -10px; opacity: .15;">
                <!-- Decorative SVG dots -->
                <svg width="100" height="80" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="10" cy="70" r="3" fill="#027333"/>
                  <circle cx="30" cy="70" r="3" fill="#027333"/>
                  <circle cx="50" cy="70" r="3" fill="#027333"/>
                  <!-- add more if you like -->
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Text -->
        <div class="col-12 col-lg-6">
          <span class="text-primary text-uppercase fw-semibold mb-2 d-block">Welcome to Jaat</span>
          <h2 class="fw-bold mb-4">
            Kenya’s Marketplace Where You’ll Find Almost Everything From Everyone, Everywhere
          </h2>

          <p class="mb-4">
            Jaat is a home-grown e-commerce marketplace, founded in Nairobi in 2024 to empower local makers
            and enterprises. From Kisumu to Mombasa, we connect Kenyans with authentic products and services—
            all in one trusted platform.
          </p>

          <h5 class="fw-semibold">What We Do</h5>
          <p class="mb-4">
            We link buyers and sellers across Kenya, offering secure payment options such as M-Pesa,
            debit/credit cards, and mobile wallets. Our mission is to nourish Kenyan creativity while giving
            shoppers real choice and convenience.
          </p>

          <h6 class="fst-italic text-secondary">Sellers</h6>
          <p>
            If an item or service is legal in Kenya, you can list it on Jaat.
          </p>
          <p class="mb-4">
            Examples of <strong>tangible items</strong> include—but aren’t limited to—handcrafted décor,
            fresh farm produce, jewelry, artwork, livestock, vehicles, furniture, and outdoor gear.
            Every listing supports up to eight high-quality images; short videos with audio are coming soon.
          </p>
          <p class="mb-4">
            <strong>Digital downloads</strong>—such as original music, e-books, design templates,
            or recipe collections—are also welcome.
          </p>
          <p class="mb-4">
            Not sure if your product complies with Kenyan law? Kindly consult the relevant authorities or
            Kenya Bureau of Standards (KEBS) before posting.
          </p>
          <p class="mb-5">
            Becoming a Jaat seller takes minutes. Review our <a href="{{ url('/seller-agreement') }}"
            class="text-primary fw-semibold">Seller Agreement</a> to see what we expect from you—and what you
            can expect from us. For help, email our support team or chat live with us 24/7.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started as a Seller
          </a>
        </div>

      </div>
    </div>
  </section>
  <!-- ====== About Section End ====== -->
@endsection
