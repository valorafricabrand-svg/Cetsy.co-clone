@extends('layouts.frontapp')

@section('main')
  <!-- ====== Prohibited Items Policy ====== -->
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 800px;">
        <h3 class="text-warning fw-bold mb-3">Prohibited Items Policy</h3>
        <h4 class="fw-semibold mb-4">Here, we explain what is prohibited / restricted / not permitted on Cetsy.</h4>
        <p class="mb-3">
          Cetsy has a zero-tolerance policy for items that promote hatred or violence, or are unlawful in your region.
          Continued violations may result in immediate suspension or termination.
        </p>
        <p class="mb-3">
          By opening a shop or placing an ad you confirm you’re 18+, agree to our policies, and understand we may
          deactivate or suspend an account (with notice) if Terms are violated.
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
            ['title'=>'Restricted for Sale',       'text'=>'See which items are prohibited or restricted on Cetsy.','url'=>url('/restricted_for_sale'),'color'=>'primary'],
            ['title'=>'Fees & Payments Policy',    'text'=>'Details on seller fees and payment processes.','url'=>url('/payment_policy'),'color'=>'success'],
            ['title'=>'Behavioral Policy',         'text'=>'Our community guidelines for respectful engagement.','url'=>url('/community_policy'),'color'=>'warning'],
            ['title'=>'Advertising & Marketing',   'text'=>'Promote your listings with our ad channels.','url'=>url('/marketing_policy'),'color'=>'info'],
            ['title'=>'Governing Law & Disputes',  'text'=>'This agreement is governed by Ohio, U.S.A. law.','url'=>url('/governing_law'),'color'=>'danger'],
            ['title'=>'Force Majeure',             'text'=>'Events beyond control that excuse performance.','url'=>url('/force_majeure'),'color'=>'secondary'],
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
