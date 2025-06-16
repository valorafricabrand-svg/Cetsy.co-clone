@extends('layouts.app')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          <h2 class="h4 mb-0">Seller Subscription</h2>
        </div>
        <div class="card-body">

          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if($subscription && $subscription->isActive())
            <div class="alert alert-success mb-4">
              <h3 class="h5">Active Subscription</h3>
              <p class="mb-0">
                Your subscription is active until <strong>{{ $subscription->end_date->format('F j, Y') }}</strong>
              </p>
            </div>

            <form action="{{ route('seller.subscription.cancel') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger">
                Cancel Subscription
              </button>
            </form>

          @else
            <div class="alert alert-warning mb-4">
              <h3 class="h5">Subscription Required</h3>
              <p class="mb-0">
                To access seller features, you need an active subscription.
              </p>
            </div>

            <div class="card border mb-4">
              <div class="card-body">
                <h3 class="h5 mb-3">Monthly Subscription</h3>
                <p class="display-6 fw-bold mb-4">
                  KES {{ number_format(config('subscription.monthly_fee', 1000), 2) }}
                </p>

                <ul class="list-unstyled mb-4">
                  <li class="d-flex align-items-start mb-2">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                    <span>Access to seller dashboard</span>
                  </li>
                  <li class="d-flex align-items-start mb-2">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                    <span>List unlimited products</span>
                  </li>
                  <li class="d-flex align-items-start">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                    <span>Process orders and payments</span>
                  </li>
                </ul>

                <form action="{{ route('seller.subscription.subscribe') }}" method="POST">
                  @csrf
                  <input type="hidden" name="payment_method" value="mpesa">
                  <input type="hidden" name="transaction_id" value="{{ uniqid() }}">
                  <button type="submit" class="btn btn-success w-100">
                    Subscribe Now
                  </button>
                </form>
              </div>
            </div>

          @endif

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
