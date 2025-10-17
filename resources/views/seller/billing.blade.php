@extends('layouts.frontapp')

@section('title', 'Seller Billing')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <h1 class="fw-bold mb-3">Billing &amp; Payments</h1>
          <p class="text-muted mb-4">Quick access to your wallet, deposits, subscriptions, and payouts.</p>

          <div class="row g-4">
            <div class="col-12 col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Wallet</h5>
                  <p class="mb-2">Current balance: <strong>${{ number_format(wallet(), 2) }}</strong></p>
                  <div class="d-flex gap-2">
                    <a href="{{ route('wallet.index') }}" class="btn btn-outline-primary">View Wallet</a>
                    @if(\Illuminate\Support\Facades\Route::has('wallet.deposit.form'))
                      <a href="{{ route('wallet.deposit.form') }}" class="btn btn-success">Deposit Funds</a>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Subscription</h5>
                  <p class="mb-2">Manage your seller plan and renewals.</p>
                  <a href="{{ route('seller.subscription') }}" class="btn btn-outline-primary">Manage Subscription</a>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Payouts</h5>
                  <p class="mb-2">Request payouts and track status.</p>
                  <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-primary">Payout Requests</a>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Order Payments</h5>
                  <p class="mb-2">Review payments received for orders.</p>
                  @if(\Illuminate\Support\Facades\Route::has('seller.orders.payments'))
                    <a href="{{ route('seller.orders.payments') }}" class="btn btn-outline-primary">View Payments</a>
                  @else
                    <a href="{{ url('/seller/order/payments') }}" class="btn btn-outline-primary">View Payments</a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

