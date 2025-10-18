@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ====== Important Tips on Selling with Cetsy ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Important Tips on Selling with Cetsy</h1>
          <p class="h5 text-secondary mb-4">
            Thank you for your interest in becoming a Seller on Cetsy.co. We want you to be as successful as possible, so here are our top tips for crafting winning listings.
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Use high-quality visuals.</strong> Provide 13 MP+ photos from multiple angles and, when relevant, short videos to showcase items like cars or instruments.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Shoot in natural light.</strong> The best photos come from indirect sunlight between 10 AM and 2 PM.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Keep backgrounds simple.</strong> Remove distractions so the focus remains on your product.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Write clear descriptions.</strong> Be concise but thorough—include size, material, variations, and any special features.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Detail your shipping.</strong> Decide whether you ship locally or globally, research courier rates, and factor in all costs (including Cetsy fees) before listing.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Prioritize customer service.</strong> Be honest, polite, and prompt. Buyers appreciate clear communication and reliable fulfillment.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Engage with favorites.</strong> When someone favorites your item, send a friendly message—offer related products or a small discount to encourage purchase.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Consider paid advertising.</strong> Boost visibility with Cetsy ads starting at \$1/day to drive more traffic to key listings.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              <strong>Promote your shop.</strong> Use Cetsy’s Services section to highlight additional offerings (e.g., custom painting services) and link back to your shop.
            </li>
            <li class="list-group-item border-0 ps-0">
              <strong>Respond quickly to messages.</strong> Even tough questions can be turned positive—treat every inquiry as an opportunity.
            </li>
          </ol>

          <p class="lead mb-4">
            We hope these tips give you confidence as you start selling. If you need further assistance, our 24/7 Live Chat, phone, and email support are always available.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started as a Seller
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
