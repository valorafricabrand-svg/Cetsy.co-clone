@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ====== Important Tips on Selling with Jaat ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Important Tips on Selling with Jaat</h1>
          <p class="h5 text-secondary mb-4">
            Asante for choosing to sell on <strong>Jaat.co.ke</strong>. To help you succeed, here are our top tips for crafting listings that convert.
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Use high-quality visuals.</strong> Upload clear, well-lit photos (13 MP or higher) from multiple angles—plus short videos for items like vehicles or musical instruments.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Shoot in natural light.</strong> Indirect sunlight between 10 a.m. and 2 p.m. usually yields the best results.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Keep backgrounds simple.</strong> A clean backdrop keeps the buyer’s attention on your product.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Write clear descriptions.</strong> Be concise yet thorough—include dimensions, materials, variations, and standout features.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Detail your delivery plan.</strong> Specify whether you ship locally or nationwide, research courier or rider fees, and price your item to cover all costs (including Jaat fees).
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Prioritise customer service.</strong> Honesty, polite communication, and prompt fulfilment build repeat business.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Engage with favourites.</strong> When a shopper favourites your item, send a warm message—maybe offer a small discount or suggest related products.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Consider paid ads.</strong> Boost visibility with Jaat Ads from as little as <span class="text-nowrap">KES 150/day</span> to drive traffic to key listings.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Promote your shop services.</strong> Highlight extras such as custom engraving or gift-wrapping in your Services tab and cross-link to relevant listings.
            </li>
            <li class="list-group-item border-0 ps-0">
              <strong>Respond quickly to messages.</strong> Every enquiry is a chance to build trust—aim to reply within an hour whenever possible.
            </li>
          </ol>

          <p class="lead mb-4">
            We hope these tips give you confidence as you start selling on Jaat. Need more help? Our live-chat, phone, and email support teams are available 24/7.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started as a Seller
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
