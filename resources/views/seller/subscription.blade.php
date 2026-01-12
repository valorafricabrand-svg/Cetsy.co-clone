@extends('layouts.app')

@section('title', 'Seller Subscription')

@section('styles')
<style>
  .sub-page{
    --sub-brand: #198754;
    --sub-brand-2: #20c997;
    --sub-text: #0f172a;
    --sub-muted: #64748b;
    --sub-border: rgba(15, 23, 42, .10);
    --sub-shadow: 0 16px 40px rgba(15, 23, 42, .08);
  }
  .sub-hero{
    border: 1px solid var(--sub-border);
    border-radius: 1rem;
    background: linear-gradient(135deg, rgba(25,135,84,.10), rgba(32,201,151,.08));
    box-shadow: var(--sub-shadow);
    overflow: hidden;
  }
  .sub-hero__title{ color: var(--sub-text); letter-spacing: -.02em; }
  .sub-hero__subtitle{ color: var(--sub-muted); }
  .sub-status{
    border-radius: 1rem;
    border: 1px solid var(--sub-border);
    background: #fff;
  }
  .sub-status__icon{
    width: 44px; height: 44px; border-radius: 12px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(25,135,84,.10);
    color: var(--sub-brand);
    flex: 0 0 auto;
  }
  .sub-pill{
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.08);
    background: rgba(255,255,255,.85);
    color: var(--sub-text);
  }
  .sub-callout{
    border-radius: 1rem;
    border: 1px solid rgba(13,110,253,.18);
    background: rgba(13,110,253,.06);
  }
  .pricing-card{
    border-radius: 1rem;
    border: 1px solid var(--sub-border);
    background: #fff;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    overflow: hidden;
  }
  .pricing-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 18px 44px rgba(15, 23, 42, .10);
    border-color: rgba(25,135,84,.25);
  }
  .pricing-card--featured{
    border-color: rgba(25,135,84,.35);
    box-shadow: 0 20px 60px rgba(25,135,84,.14);
  }
  .pricing-badge{
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .35rem .65rem;
    font-weight: 700;
    background: rgba(25,135,84,.12);
    color: var(--sub-brand);
    border: 1px solid rgba(25,135,84,.25);
  }
  .pricing-price{
    font-size: 2.1rem;
    letter-spacing: -.03em;
    color: var(--sub-text);
    line-height: 1.1;
  }
  .pricing-muted{ color: var(--sub-muted); }
  .feature-item{
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    margin-bottom: .6rem;
    color: var(--sub-text);
  }
  .feature-item i{ color: var(--sub-brand); margin-top: .15rem; }
  .table thead th{ white-space: nowrap; }
  .sub-card-header{
    border-bottom: 1px solid rgba(15, 23, 42, .08);
  }
  .sub-method-badge{
    border-radius: 999px;
    border: 1px solid rgba(2, 115, 51, .18);
    background: rgba(2, 115, 51, .08);
    color: #0f172a;
    padding: .35rem .6rem;
    font-weight: 600;
  }
</style>
@endsection

@section('content')
<div class="content sub-page">
  <div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">

      <div class="sub-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h1 class="h3 mb-1 sub-hero__title">Seller Subscription</h1>
            <div class="sub-hero__subtitle">Renew early, upgrade your plan, and view payment history.</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('seller.billing.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-wallet2 me-1"></i> Billing
            </a>
            <a href="{{ route('seller.dashboard') }}" class="btn btn-success">
              <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
          </div>
        </div>
      </div>

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
          <strong>Success:</strong> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
          <strong>Error:</strong> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @php
        $isActive = $subscription && $subscription->isActive();
        $daysLeftSigned = $subscription?->end_date ? now()->diffInDays($subscription->end_date, false) : null;
        $daysLeft = !is_null($daysLeftSigned) ? max(0, (int) $daysLeftSigned) : null;
      @endphp

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-md-5">

          {{-- Status --}}
          @if($isActive)
            <div class="sub-status p-4 mb-3">
              <div class="d-flex gap-3">
                <div class="sub-status__icon">
                  <i class="bi bi-patch-check-fill fs-4"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                    <div>
                      <div class="small text-uppercase fw-semibold pricing-muted">Status</div>
                      <div class="h5 mb-1">Active subscription</div>
                      <div class="pricing-muted">
                        Active until <strong>{{ $subscription->end_date->format('F j, Y') }}</strong>
                        @if(!is_null($daysLeft))
                          <span class="ms-2 badge sub-pill">
                            @php $cls = $daysLeft <= 7 ? 'text-danger' : ($daysLeft <= 30 ? 'text-warning' : ''); @endphp
                            <span class="fw-semibold {{ $cls }}">Expires in {{ number_format($daysLeft, 0) }} {{ Str::plural('day', $daysLeft) }}</span>
                          </span>
                        @endif
                      </div>
                      @if($subscription->notes)
                        <div class="small pricing-muted mt-1">Plan: {{ $subscription->notes }}</div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="sub-callout p-3 p-md-4 mb-4">
              <div class="d-flex align-items-start gap-2">
                <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                <div>
                  <div class="fw-semibold">Renew early anytime</div>
                  <div class="pricing-muted">
                    Renewing or upgrading will extend from your current end date, so you don’t lose remaining days.
                  </div>
                </div>
              </div>
            </div>
          @else
            @php
              $expiredInfo = null;
              if ($subscription && $subscription->end_date) {
                $signed = (int) now()->diffInDays($subscription->end_date, false);
                if ($signed <= 0) {
                  $expiredDays = abs($signed);
                  $expiredInfo = ['date' => $subscription->end_date->format('F j, Y'), 'days' => $expiredDays];
                }
              }
            @endphp

            @if($expiredInfo)
              <div class="sub-status p-4 mb-3">
                <div class="d-flex gap-3">
                  <div class="sub-status__icon" style="background: rgba(220,53,69,.10); color:#dc3545;">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="small text-uppercase fw-semibold pricing-muted">Status</div>
                    <div class="h5 mb-1">Subscription expired</div>
                    <div class="pricing-muted">
                      Expired on <strong>{{ $expiredInfo['date'] }}</strong>
                      <span class="ms-2 badge sub-pill"><span class="fw-semibold text-danger">Expired {{ number_format($expiredInfo['days'], 0) }} {{ Str::plural('day', $expiredInfo['days']) }} ago</span></span>
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="sub-status p-4 mb-4">
                <div class="d-flex gap-3">
                  <div class="sub-status__icon" style="background: rgba(255,193,7,.14); color:#b58100;">
                    <i class="bi bi-lock-fill fs-4"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="small text-uppercase fw-semibold pricing-muted">Status</div>
                    <div class="h5 mb-1">Subscription required</div>
                    <div class="pricing-muted">To access seller features, you need an active subscription.</div>
                  </div>
                </div>
              </div>
            @endif

            @if($canStartTrial ?? false)
              <div class="card border-0 shadow-sm mb-4" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">
                  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                      <div class="pricing-badge mb-2"><i class="bi bi-stars"></i> Free trial</div>
                      <div class="h5 mb-1">New seller? Try it free for {{ number_format($trialDays ?? 30, 0) }} {{ Str::plural('day', $trialDays ?? 30) }}</div>
                      <div class="pricing-muted">Unlock seller features while you set up your shop. Upgrade anytime to keep selling.</div>
                    </div>
                    <form action="{{ route('seller.subscription.trial') }}" method="POST" class="m-0">
                      @csrf
                      <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-rocket-takeoff me-1"></i> Start Trial
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            @endif
          @endif

          {{-- Plans --}}
          @php
            $monthlyCta = $isActive ? 'Renew Monthly' : 'Choose Monthly';
            $yearlyCta  = $isActive ? 'Upgrade / Renew Yearly' : 'Choose Yearly';
          @endphp

          <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
            <div>
              <div class="h5 mb-1">{{ $isActive ? 'Renew / Upgrade' : 'Choose a plan' }}</div>
              <div class="pricing-muted">
                {{ $isActive ? 'Renewing extends your current end date.' : 'Select a plan to activate your seller subscription.' }}
              </div>
            </div>
          </div>

          <div class="row g-3 g-lg-4">
            <div class="col-md-6">
              <div class="pricing-card h-100">
                <div class="p-4">
                  <div class="d-flex align-items-start justify-content-between">
                    <div>
                      <div class="fw-semibold">Monthly</div>
                      <div class="pricing-muted small">Flexible month‑to‑month</div>
                    </div>
                    <span class="badge sub-pill"><i class="bi bi-calendar3 me-1"></i> 1 month</span>
                  </div>

                  <div class="mt-3">
                    <div class="pricing-price">USD {{ number_format(config('subscription.monthly_fee', 5), 2) }}</div>
                    <div class="pricing-muted">per month</div>
                  </div>

                  <hr class="my-4">

                  <div class="mb-4">
                    <div class="feature-item"><i class="bi bi-check-circle-fill"></i><span>Seller dashboard & analytics</span></div>
                    <div class="feature-item"><i class="bi bi-check-circle-fill"></i><span>Unlimited listings</span></div>
                    <div class="feature-item"><i class="bi bi-check-circle-fill"></i><span>Orders, payouts & messaging</span></div>
                    <div class="feature-item mb-0"><i class="bi bi-check-circle-fill"></i><span>Account & KYC support</span></div>
                  </div>

                  <form action="{{ route('seller.subscription.subscribe') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="btn btn-outline-success w-100">
                      {{ $monthlyCta }}
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="pricing-card pricing-card--featured h-100">
                <div class="p-4">
                  <div class="d-flex align-items-start justify-content-between">
                    <div>
                      <div class="fw-semibold">Yearly</div>
                      <div class="pricing-muted small">Best value for serious sellers</div>
                    </div>
                    <span class="pricing-badge">
                      <i class="bi bi-award-fill"></i> Save {{ config('subscription.yearly_discount_percent', 17) }}%
                    </span>
                  </div>

                  <div class="mt-3">
                    <div class="pricing-price">USD {{ number_format(config('subscription.yearly_fee', 50), 2) }}</div>
                    <div class="pricing-muted">per year</div>
                    <div class="small pricing-muted mt-1">
                      <span class="text-decoration-line-through">USD {{ number_format(config('subscription.monthly_fee', 5) * 12, 2) }}</span>
                      <span class="ms-2">billed annually</span>
                    </div>
                  </div>

                  <hr class="my-4">

                  <div class="mb-4">
                    <div class="feature-item"><i class="bi bi-check-circle-fill"></i><span>Everything in Monthly</span></div>
                    <div class="feature-item"><i class="bi bi-check-circle-fill"></i><span>Priority support</span></div>
                    <div class="feature-item"><i class="bi bi-check-circle-fill"></i><span>Advanced analytics</span></div>
                    <div class="feature-item mb-0"><i class="bi bi-check-circle-fill"></i><span>Early access to new features</span></div>
                  </div>

                  <form action="{{ route('seller.subscription.subscribe') }}" method="POST" class="m-0">
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

      {{-- Payment history --}}
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white sub-card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="h5 mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Subscription Payment History</h3>
          </div>
        </div>
        <div class="card-body">
          @if($subscriptionPayments->count() > 0)
            <div class="table-responsive">
              <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Transaction</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($subscriptionPayments as $payment)
                    <tr>
                      <td class="text-nowrap">{{ $payment->created_at->format('M d, Y') }}<div class="small text-muted">{{ $payment->created_at->format('h:i A') }}</div></td>
                      <td><code class="small">{{ $payment->local_transaction_id ?? 'N/A' }}</code></td>
                      <td>
                        <div class="fw-semibold">${{ number_format($payment->total_amount, 2) }}</div>
                        <div class="small text-muted">{{ $payment->currency }}</div>
                      </td>
                      <td><span class="sub-method-badge">{{ ucfirst($payment->payment_method) }}</span></td>
                      <td>
                        @if($payment->payment_status == 'successful')
                          <span class="badge bg-success">Successful</span>
                        @elseif($payment->payment_status == 'pending')
                          <span class="badge bg-warning text-dark">Pending</span>
                        @else
                          <span class="badge bg-danger">Failed</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center text-muted py-5">
              <i class="bi bi-credit-card-2-front fs-1 mb-2 d-block"></i>
              <div class="fw-semibold">No subscription payments yet</div>
              <div class="small">Once you subscribe or renew, your transactions will show here.</div>
            </div>
          @endif
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
