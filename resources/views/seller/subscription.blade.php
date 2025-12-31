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

          @php
            $isActive = $subscription && $subscription->isActive();
          @endphp

          @if($isActive)
            @php
              // Compute whole number of days left (signed then clamped to >= 0)
              $daysLeftSigned = $subscription->end_date? now()->diffInDays($subscription->end_date, false) : null;
              $daysLeft = !is_null($daysLeftSigned) ? max(0, (int) $daysLeftSigned) : null;
            @endphp
            <div class="alert alert-success mb-4">
              <h3 class="h5">Active Subscription</h3>
              <p class="mb-0">
                Your subscription is active until <strong>{{ $subscription->end_date->format('F j, Y') }}</strong>
                @if(!is_null($daysLeft) && $daysLeft <= 30)
                  @php $cls = $daysLeft <= 7 ? 'text-danger' : 'text-warning'; @endphp
                  <span class="fw-bold {{ $cls }} ms-1">
                    (Expires in {{ number_format($daysLeft, 0) }} {{ Str::plural('day', $daysLeft) }})
                  </span>
                @endif
                @if($subscription->notes)
                  <br><small class="text-muted">Plan: {{ $subscription->notes }}</small>
                @endif
              </p>
            </div>

            <div class="alert alert-info mb-4">
              <strong>Renew early:</strong> you can renew or upgrade at any time. Renewals extend from your current end
              date so you don’t lose remaining days.
            </div>
            {{-- Cancel Subscription button removed as requested --}}
          @else
            @php
              $expiredInfo = null;
              if ($subscription && $subscription->end_date) {
                $signed = (int) now()->diffInDays($subscription->end_date, false);
                if ($signed <= 0) {
                  $expiredDays = abs($signed);
                  $expiredInfo = [
                    'date' => $subscription->end_date->format('F j, Y'),
                    'days' => $expiredDays,
                  ];
                }
              }
            @endphp
            @if($expiredInfo)
              <div class="alert alert-danger mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <strong>Subscription Expired</strong>
                <div class="mt-1">
                  Expired on <strong>{{ $expiredInfo['date'] }}</strong>
                  <span class="fw-bold">(Expired {{ number_format($expiredInfo['days'], 0) }} {{ Str::plural('day', $expiredInfo['days']) }} ago)</span>
                </div>
                <a href="{{ route('seller.subscription') }}" class="btn btn-sm btn-light mt-2 mt-md-0">
                  <i class="fas fa-undo me-1"></i> Renew Now
                </a>
              </div>
            @endif

            <div class="alert alert-warning mb-4">
              <h3 class="h5">Subscription Required</h3>
              <p class="mb-0">
                To access seller features, you need an active subscription.
              </p>
            </div>

            @if($canStartTrial ?? false)
              <div class="card border-success mb-4">
                <div class="card-body text-center">
                  <h4 class="h5 text-success mb-2">New seller? Try it free for a month</h4>
                  <p class="text-muted mb-3">
                    Activate a complimentary 30-day trial to unlock every seller feature while you set up shop.
                  </p>
                  <form action="{{ route('seller.subscription.trial') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success px-4">
                      <i class="fas fa-rocket me-2"></i>Try for a Month
                    </button>
                  </form>
                  <p class="small text-muted mt-2 mb-0">
                    Trial expires automatically after 30 days. Upgrade anytime to keep selling.
                  </p>
                </div>
              </div>
            @endif

          @endif

          {{-- Plans (available for both new subscriptions and renewals) --}}
          @php
            $monthlyCta = $isActive ? 'Renew Monthly Plan' : 'Choose Monthly Plan';
            $yearlyCta = $isActive ? 'Upgrade / Renew Yearly Plan' : 'Choose Yearly Plan';
          @endphp
          <div class="mb-3">
            <h3 class="h5 mb-1">{{ $isActive ? 'Renew / Upgrade' : 'Choose a Plan' }}</h3>
            <p class="text-muted mb-0">
              {{ $isActive ? 'Renewing extends your current end date.' : 'Select a plan to activate your seller subscription.' }}
            </p>
          </div>

          <div class="row">
            <!-- Monthly Plan -->
            <div class="col-md-6 mb-4">
              <div class="card border h-100">
                <div class="card-body d-flex flex-column">
                  <div class="text-center mb-3">
                    <h3 class="h5 mb-2">Monthly Plan</h3>
                    <p class="display-6 fw-bold mb-2">
                      USD {{ number_format(config('subscription.monthly_fee', 5), 2) }}
                    </p>
                    <small class="text-muted">per month</small>
                  </div>

                  <ul class="list-unstyled mb-4 flex-grow-1">
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Access to seller dashboard</span>
                    </li>
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>List unlimited products</span>
                    </li>
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Process orders and payments</span>
                    </li>
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Access analytics and reports</span>
                    </li>
                    <li class="d-flex align-items-start">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>KYC verification support</span>
                    </li>
                  </ul>

                  <form action="{{ route('seller.subscription.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="btn btn-outline-primary w-100">
                      {{ $monthlyCta }}
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Yearly Plan -->
            <div class="col-md-6 mb-4">
              <div class="card border h-100 position-relative">
                <div class="position-absolute top-0 start-50 translate-middle-x">
                  <span class="badge bg-success px-3 py-2 rounded-pill">
                    Save {{ config('subscription.yearly_discount_percent', 17) }}%
                  </span>
                </div>
                <div class="card-body d-flex flex-column">
                  <div class="text-center mb-3">
                    <h3 class="h5 mb-2">Yearly Plan</h3>
                    <p class="display-6 fw-bold mb-2">
                      USD {{ number_format(config('subscription.yearly_fee', 50), 2) }}
                    </p>
                    <small class="text-muted">per year</small>
                    <div class="mt-2">
                      <small class="text-decoration-line-through text-muted">
                        USD {{ number_format(config('subscription.monthly_fee', 5) * 12, 2) }}
                      </small>
                    </div>
                  </div>

                  <ul class="list-unstyled mb-4 flex-grow-1">
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>All monthly features included</span>
                    </li>
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Priority customer support</span>
                    </li>
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Advanced analytics dashboard</span>
                    </li>
                    <li class="d-flex align-items-start mb-2">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Early access to new features</span>
                    </li>
                    <li class="d-flex align-items-start">
                      <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                      <span>Dedicated account manager</span>
                    </li>
                  </ul>

                  <form action="{{ route('seller.subscription.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="btn btn-success w-100">
                      {{ $yearlyCta }}
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- Subscription Payment History --}}
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h3 class="h5 mb-0">Subscription Payment History</h3>
        </div>
        <div class="card-body">
          @if($subscriptionPayments->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($subscriptionPayments as $payment)
                    <tr>
                      <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                      <td>
                        <code class="small">{{ $payment->local_transaction_id ?? 'N/A' }}</code>
                      </td>
                      <td>
                        <strong>${{ number_format($payment->total_amount, 2) }}</strong>
                        <small class="text-muted d-block">{{ $payment->currency }}</small>
                      </td>
                      <td>
                        <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                      </td>
                      <td>
                        @if($payment->payment_status == 'successful')
                          <span class="badge bg-success">
                            {{ ucfirst($payment->payment_status) }}
                          </span>
                        @elseif($payment->payment_status == 'pending')
                          <span class="badge bg-warning">
                            {{ ucfirst($payment->payment_status) }}
                          </span>
                        @else
                          <span class="badge bg-danger">
                            {{ ucfirst($payment->payment_status) }}
                          </span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center text-muted py-4">
              <i class="fas fa-credit-card fa-2x mb-3"></i>
              <p class="mb-0">No subscription payments found</p>
            </div>
          @endif
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
