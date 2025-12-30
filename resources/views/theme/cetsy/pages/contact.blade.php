@extends('theme.'.theme().'.layouts.app')

@section('title', 'Contact Us')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Contact Us</h1>
        <p class="text-muted mb-4">We’re here to help with orders, payments, refunds, disputes, and account issues.</p>

        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h2 class="h5 fw-semibold mb-3">Support</h2>
            <ul class="list-unstyled mb-0">
              <li class="mb-2">
                <strong>Email:</strong>
                <a href="mailto:{{ support_email() }}">{{ support_email() }}</a>
              </li>

              <li class="mb-2">
                <strong>Phone:</strong>
                @php($phone = support_phone())
                @if($phone !== '')
                  <a href="tel:{{ $phone }}">{{ $phone }}</a>
                @else
                  <span class="text-muted">Please set a public support phone number in Settings or `SUPPORT_PHONE`.</span>
                @endif
              </li>

              @php($address = support_address())
              @if($address !== '')
                <li class="mb-2">
                  <strong>Address:</strong> {{ $address }}
                </li>
              @endif
            </ul>
          </div>
        </div>

        <div class="mt-4">
          <h2 class="h5 fw-semibold mb-2">Order Help</h2>
          <ul class="mb-0">
            <li>For delivery questions, contact the seller first (each seller ships their own items).</li>
            <li>If you can’t resolve an issue with a seller, open a dispute from your order page and we’ll assist.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>
@endsection

