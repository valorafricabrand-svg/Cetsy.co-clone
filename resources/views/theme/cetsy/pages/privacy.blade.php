@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy Policies & Privacy')

@section('main')
  <!-- ====== Policies & Privacy (Overview) ====== -->
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 800px;">
        <h1 class="fw-bold mb-2">Cetsy Policies & Privacy</h1>
        <p class="text-muted mb-4">Effective: {{ now()->format('F j, Y') }}</p>
        <p class="mb-3">
          This hub brings together our key policies: marketplace rules, seller and buyer guidelines, and how we
          manage your data and privacy. Use the quick links below to jump to the section you need.
        </p>
        <div class="d-flex flex-wrap gap-2 mb-2">
          <a class="btn btn-sm btn-outline-primary" href="#privacy">Privacy Policy</a>
          <a class="btn btn-sm btn-outline-primary" href="#cookies">Cookie Notice</a>
          <a class="btn btn-sm btn-outline-primary" href="#rights">Your Data Rights</a>
          <a class="btn btn-sm btn-outline-primary" href="#security">Security</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== Related Policies (Cards) ====== -->
  <section class="pb-5 bg-light">
    <div class="container">
      <div class="row g-4 justify-content-center">
        @php
          $cards = [
            ['title'=>'Cookie Notice',             'text'=>'Learn how and why we use cookies and similar technologies.','url'=>url('/privacy#cookies'),'color'=>'primary'],
            ['title'=>'Data Requests',             'text'=>'Request access, correction, or deletion of your personal data.','url'=>url('/privacy#rights'),'color'=>'success'],
            ['title'=>'Security',                  'text'=>'Overview of how we protect your data.','url'=>url('/privacy#security'),'color'=>'warning'],
            ['title'=>'House Rules & Policy',      'text'=>'Community standards for using Cetsy services.','url'=>url('/house-policy'),'color'=>'info'],
            ['title'=>'Fees & Payments',           'text'=>'How fees and payments work on Cetsy.','url'=>url('/payment_policy'),'color'=>'danger'],
            ['title'=>'Restricted / Prohibited Items','text'=>'What cannot be listed or is restricted on Cetsy.','url'=>url('/restricted_for_sale'),'color'=>'secondary'],
          ];
        @endphp

        @foreach($cards as $card)
          <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ $card['url'] }}" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-header bg-{{ $card['color'] }} text-white">
                  <h4 class="card-title mb-0 text-white">{{ $card['title'] }}</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <p class="card-text flex-grow-1 text-dark">{{ $card['text'] }}</p>
                  <span class="mt-3 btn btn-outline-{{ $card['color'] }}">View More</span>
                </div>
              </div>
            </a>
          </div>
        @endforeach

      </div>
    </div>
  </section>

  <!-- ====== Privacy Policy (Detailed) ====== -->
  <section id="privacy" class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h2 class="fw-semibold mb-3">Privacy Policy</h2>
        <p class="mb-3">
          This Privacy Policy explains how Cetsy (“we”, “us”, “our”) collects, uses, and shares information when
          you use our website, create an account, open a shop, make a purchase, or otherwise interact with our
          services.
        </p>
        <ul class="mb-4">
          <li><strong>Information we collect:</strong> account details, contact information, shop and listing data, order and payment details, device and usage data (cookies and similar technologies).</li>
          <li><strong>How we use it:</strong> provide and improve the service, process orders and payments, personalize content, prevent fraud and abuse, and comply with legal obligations.</li>
          <li><strong>Sharing:</strong> with payment processors, logistics partners, service providers, and when required by law. We do not sell your personal data.</li>
        </ul>
        <h3 id="cookies" class="h5 mt-4">Cookie Notice</h3>
        <p class="mb-3">We use cookies to keep you signed in, remember preferences, understand site usage, and personalize content. You can control cookies in your browser settings.</p>
        <h3 id="rights" class="h5 mt-4">Your Data Rights</h3>
        <p class="mb-3">Where applicable, you may request access, correction, deletion, or portability of your data, and object to or restrict certain processing.</p>
        <h3 id="security" class="h5 mt-4">Security</h3>
        <p class="mb-0">We use industry-standard safeguards. No method is 100% secure. For questions, contact <a href="mailto:hello@cetsy.co">hello@cetsy.co</a>.</p>
      </div>
    </div>
  </section>
@endsection

@push('styles')
<style>
  .card-hover {
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,.1);
  }
  .card-header h4 {
    font-size: 1.25rem;
  }
  .card-body p {
    font-size: 0.95rem;
  }
  .card-body .btn {
    font-size: 0.9rem;
    padding: 0.4rem 0.75rem;
  }
</style>
@endpush
