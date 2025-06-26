@extends('layouts.frontapp')

@section('main')
  <!-- ====== About Section Start ====== -->
  <section class="py-5 bg-white">
    <div class="container">
      <div class="row gx-5">

        <!-- Left Column: Images (hidden on sm) -->
        <div class="col-lg-6 d-none d-lg-block">
          <div class="row g-3">
            <div class="col-6">
              <img src="{{ asset('build/assets/images/cetsybout.jpg') }}"
                   alt="Cetsy Boutique"
                   class="img-fluid rounded-3 shadow-sm">
            </div>
            <div class="col-6">
              <img src="{{ asset('build/assets/images/cetsyabout3.jpg') }}"
                   alt="Cetsy About"
                   class="img-fluid rounded-3 shadow-sm">
            </div>
            <div class="col-12 position-relative mt-3">
              <img src="{{ asset('build/assets/images/cetsyabout2.jpg') }}"
                   alt="Cetsy Community"
                   class="img-fluid rounded-3 shadow-sm">
              <div class="position-absolute" style="bottom: -10px; right: -10px; opacity: .15;">
                <!-- Decorative SVG dots -->
                <svg width="100" height="80" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="10" cy="70" r="3" fill="#3056D3"/>
                  <circle cx="30" cy="70" r="3" fill="#3056D3"/>
                  <circle cx="50" cy="70" r="3" fill="#3056D3"/>
                  <!-- etc. -->
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Text -->
        <div class="col-12 col-lg-6">
          <span class="text-primary text-uppercase fw-semibold mb-2 d-block">Welcome to Cetsy</span>
          <h2 class="fw-bold mb-4">
            Your Global Marketplace Where One Can Find Almost Everything From Everyone, Everywhere
          </h2>

          <p class="mb-4">
            Cetsy is a global e-commerce marketplace, founded in 2021 with the intent to better connect all global markets.
            It is a privately held company based in Ohio, USA, which allows the sale of nearly any item that a seller
            can legally sell in his/her geographical region or state.
          </p>

          <h5 class="fw-semibold">What we do</h5>
          <p class="mb-4">
            We connect buyers and sellers across all global marketplaces, offering multiple secure payment solutions
            to meet the diverse needs of our buyers, while rarely limiting the creativity of our sellers.
          </p>

          <h6 class="fst-italic text-secondary">Sellers</h6>
          <p>
            If you can legally sell the item or service in your country, you can list it on Cetsy.
          </p>
          <p class="mb-4">
            Examples of <strong>tangible items</strong> include—but are not limited to—household goods, collectibles,
            jewelry, artwork, livestock, vehicles, handmade crafts, real estate/property, and outdoor equipment.
            All listings can include photos; video with audio is coming soon.
          </p>
          <p class="mb-4">
            <strong>Digital downloads</strong> can also be listed, such as original music, e-books, recipes, and more.
          </p>
          <p class="mb-4">
            Unsure if your item qualifies? Please check with your local authorities to confirm it’s legal in your jurisdiction
            before posting any listing.
          </p>
          <p class="mb-5">
            To become a Cetsy seller in just a few simple steps, please review the Cetsy Seller Agreement,
            which outlines what we expect from our sellers and what sellers can expect from Cetsy.
            For further questions or assistance, email us or reach us via 24/7 Live Chat.
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
