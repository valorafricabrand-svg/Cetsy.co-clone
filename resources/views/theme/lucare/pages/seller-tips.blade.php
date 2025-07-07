@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare – Vendor Tips')

@section('main')
  <!-- ====== Important Tips for Selling on Lucare ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Top Tips for Selling on Lucare</h1>
          <p class="h5 text-secondary mb-4">
            Asante for joining <strong>Lucare</strong>! Follow these best practices to create listings that attract beauty buyers and boost your sales.
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Showcase products clearly.</strong> Upload high-resolution images (at least 13 MP) with multiple angles—include close-ups of textures or packaging for skincare and cosmetics.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Use natural, soft lighting.</strong> Photograph in indirect daylight (10 am–2 pm) to capture true colors and finishes without harsh shadows.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Keep backgrounds minimal.</strong> A clean, neutral backdrop highlights your product. Avoid busy patterns or clutter.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Write detailed descriptions.</strong> List ingredients, skin-type suitability, SPF ratings, volume/weight, and usage instructions to inform and build trust.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Highlight key benefits.</strong> Mention cruelty-free, vegan, organic, or dermatologically tested features prominently in your title or bullet points.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Price competitively.</strong> Factor in product cost, Lucare’s seller fees, and delivery charges. Use tiered pricing for bundles or multi-pack offers.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Engage with saved items.</strong> When a customer “favorites” your product, send a friendly message or limited-time discount to encourage purchase.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Consider Lucare Ads.</strong> Boost visibility for new launches or bestsellers with targeted ads starting at <span class="text-nowrap">KES 150/day</span>.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Offer bundle deals.</strong> Combine complementary items—like cleanser + toner or lipstick + liner—to increase average order value.
            </li>
            <li class="list-group-item border-0 ps-0">
              <strong>Respond promptly.</strong> Aim to reply to customer inquiries within one hour. Quick, helpful communication leads to higher conversion and ratings.
            </li>
          </ol>

          <p class="lead mb-4">
            These tips will help your Lucare shop shine. For further assistance, our support team is available via live chat or email 24/7.
          </p>

          <a href="{{ url('/register') }}" class="btn btn-primary btn-lg">
            Get Started as a Vendor
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
