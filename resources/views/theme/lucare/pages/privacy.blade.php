@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare – Prohibited Items Policy')

@section('main')
  <!-- ====== Lucare Prohibited Items Policy ====== -->
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 800px;">
        <h3 class="text-primary fw-bold mb-3">Prohibited Items Policy</h3>
        <h4 class="fw-semibold mb-4">
          Below is a list of items and services not permitted for sale on Lucare.
        </h4>
        <p class="mb-3">
          <strong>Lucare</strong> maintains a strict policy against any products that are illegal under Kenyan law or pose risks to consumer safety. Violations may result in immediate suspension or permanent account termination.
        </p>
        <p class="mb-3">
          By listing on Lucare, you confirm you are 18 years or older, accept our policies, and understand that Lucare may deactivate or suspend your account if these Terms or the law are breached.
        </p>
      </div>
    </div>
  </section>

  <!-- ====== Policy Cards ====== -->
  <section class="pb-5 bg-light">
    <div class="container">
      <div class="row g-4 justify-content-center">
        @php
          $cards = [
            [
              'title' => 'Restricted & Prohibited',
              'text'  => 'See which beauty and wellness items cannot be sold on Lucare.',
              'url'   => url('/policies/restricted-items'),
              'color' => 'primary',
            ],
            [
              'title' => 'Fees & Payments',
              'text'  => 'Learn about seller fees and payment processing (M-Pesa, cards).',
              'url'   => url('/policies/fees-payments'),
              'color' => 'success',
            ],
            [
              'title' => 'Community Guidelines',
              'text'  => 'Standards for respectful engagement on Lucare.',
              'url'   => url('/policies/community-guidelines'),
              'color' => 'warning',
            ],
            [
              'title' => 'Advertising Policy',
              'text'  => 'Rules for promoting your listings on Lucare.',
              'url'   => url('/policies/advertising'),
              'color' => 'info',
            ],
            [
              'title' => 'Governing Law',
              'text'  => 'This platform is governed by the laws of Kenya.',
              'url'   => url('/policies/governing-law'),
              'color' => 'danger',
            ],
            [
              'title' => 'Force Majeure',
              'text'  => 'Events beyond control that may excuse performance.',
              'url'   => url('/policies/force-majeure'),
              'color' => 'secondary',
            ],
          ];
        @endphp

        @foreach($cards as $card)
          <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ $card['url'] }}" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-header bg-{{ $card['color'] }} text-white">
                  <h4 class="card-title mb-0">{{ $card['title'] }}</h4>
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
