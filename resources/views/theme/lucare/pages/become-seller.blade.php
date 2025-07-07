@extends('theme.'.theme().'.layouts.app')

@section('title', 'Become a Vendor – Lucare')

@section('main')
<!-- ====== Become a Vendor Starts Here ====== -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center gy-4">
      
      <!-- Text Content -->
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold">How to Become a Vendor on Lucare</h1>
        <p class="h5 text-secondary mb-4">
          Thank you for your interest in joining <strong>Lucare</strong>—Kenya’s premier online beauty store. Become a Lucare vendor to showcase your skincare, cosmetics, or wellness products to customers nationwide.
        </p>

        <p class="lead">
          Lucare partners with trusted local and international brands. We accept physical products such as skincare, makeup, haircare, wellness supplements, and beauty tools. Each product listing supports multiple high-quality images.
        </p>
        <p class="lead">
          <strong>Digital products</strong> are welcome too—think beauty tutorials, e-books, or downloadable wellness guides.
        </p>
        <p class="lead">
          Unsure whether your product fits our guidelines? <a href="{{ url('/contact') }}" class="text-decoration-underline">Chat with our support team</a>, and we’ll help clarify.
        </p>
        <p class="lead">
          Ready to get started? First, <a href="{{ url('/vendor-agreement') }}" class="fw-bold text-danger text-decoration-none">review our Vendor Agreement</a> to understand our partnership terms and benefits, including prompt payouts and marketing support.
        </p>

        <a href="{{ url('/register') }}" class="btn btn-primary btn-lg mt-3">
          Get Started as a Vendor
        </a>
      </div>

      <!-- Illustration -->
      <div class="col-lg-4 text-center">
        <img src="{{ asset('assets/images/lucare-become-vendor.svg') }}"
             alt="Become a Vendor on Lucare"
             class="img-fluid"
             style="max-height: 300px;">
      </div>

    </div>
  </div>
</section>
<!-- ====== Become a Vendor Ends Here ====== -->
@endsection
