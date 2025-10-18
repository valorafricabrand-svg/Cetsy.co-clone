@extends('layouts.frontapp')

@section('title', 'Cetsy User Agreement')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Cetsy User Agreement</h1>
        <p class="text-muted mb-4">Effective: {{ now()->format('F Y') }}</p>

        <p class="mb-3">
          This User Agreement summarizes the core terms that govern your use of Cetsy’s
          website, apps, and services. It works together with our detailed policies below.
          By using Cetsy, you agree to these terms and policies.
        </p>

        <div class="alert alert-info mb-4">
          Looking for the full details? Use the quick links below to view each policy section.
        </div>

        <div class="row g-3 mb-5">
          <div class="col-12 col-md-6">
            <a href="{{ url('/terms') }}" class="text-decoration-none">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <h5 class="card-title mb-2">Terms & Conditions</h5>
                  <p class="card-text text-muted mb-0">Rules for using Cetsy, including accounts, marketplace rules, fees, and dispute terms.</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-6">
            <a href="{{ url('/privacy') }}" class="text-decoration-none">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <h5 class="card-title mb-2">Privacy Policy</h5>
                  <p class="card-text text-muted mb-0">How we collect, use, and protect your personal information and cookies.</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-6">
            <a href="{{ url('/house-policy') }}" class="text-decoration-none">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <h5 class="card-title mb-2">House Rules & Behaviour</h5>
                  <p class="card-text text-muted mb-0">Community standards, conduct expectations, and enforcement guidelines.</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-6">
            <a href="{{ url('/restricted_for_sale') }}" class="text-decoration-none">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <h5 class="card-title mb-2">Prohibited & Restricted Items</h5>
                  <p class="card-text text-muted mb-0">What cannot be listed or is restricted on Cetsy.</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-6">
            <a href="{{ url('/payment_policy') }}" class="text-decoration-none">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <h5 class="card-title mb-2">Fees & Marketplace Commissions</h5>
                  <p class="card-text text-muted mb-0">Listing fees, commissions, and currency conversion overview.</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-6">
            <a href="{{ url('/buyer-terms') }}" class="text-decoration-none">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <h5 class="card-title mb-2">Buyer Terms</h5>
                  <p class="card-text text-muted mb-0">Guidelines and expectations specific to Buyers on Cetsy.</p>
                </div>
              </div>
            </a>
          </div>
        </div>

        <h2 class="h4 mt-4">Summary of Key Points</h2>
        <ul class="mb-4">
          <li><strong>Accounts & Eligibility:</strong> You must be of legal age in your jurisdiction and keep your account information accurate.</li>
          <li><strong>Marketplace Role:</strong> Cetsy connects buyers and sellers; we are not a party to the sale between buyer and seller.</li>
          <li><strong>Payments & Fees:</strong> Payments are handled by third-party processors; listing and commission fees may apply.</li>
          <li><strong>Prohibited Items:</strong> Certain items and content are not allowed (see Prohibited & Restricted Items).</li>
          <li><strong>Privacy & Cookies:</strong> We collect and process data to provide and improve the service; see Privacy Policy.</li>
          <li><strong>Disputes:</strong> Buyer–seller disputes should be handled directly; arbitration may apply for disputes with Cetsy.</li>
        </ul>

        <p class="mb-0 text-muted">Questions? Contact us at <a href="mailto:hello@cetsy.co">hello@cetsy.co</a>.</p>
      </div>
    </div>
  </section>
@endsection

@push('styles')
<style>
  .card:hover { transform: translateY(-3px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.1); transition: .2s; }
  .card-title { font-weight: 600; }
  .card-text { font-size: .95rem; }
</style>
@endpush

