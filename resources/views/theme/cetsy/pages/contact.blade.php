@extends('theme.'.theme().'.layouts.app')

@section('title', 'Contact Us')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Contact Us</h1>
        <p class="text-muted mb-4">We are here to help with orders, payments, refunds, disputes, and account issues.</p>

        @if(session('success'))
          <div class="alert alert-success shadow-sm border-0">
            {{ session('success') }}
          </div>
        @endif

        @if(session('danger'))
          <div class="alert alert-danger shadow-sm border-0">
            {{ session('danger') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger shadow-sm border-0">
            <ul class="mb-0 ps-3">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h2 class="h5 fw-semibold mb-3">Send Us a Message</h2>
            <form method="POST" action="{{ route('contact.submit') }}" novalidate>
              @csrf

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="contact_name" class="form-label">Full Name</label>
                  <input
                    id="contact_name"
                    type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    maxlength="120"
                    required
                    value="{{ old('name', auth()->check() ? auth()->user()->name : '') }}"
                  >
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="contact_email" class="form-label">Email Address</label>
                  <input
                    id="contact_email"
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    maxlength="255"
                    required
                    value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                  >
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="contact_subject" class="form-label">Subject</label>
                  <input
                    id="contact_subject"
                    type="text"
                    name="subject"
                    class="form-control @error('subject') is-invalid @enderror"
                    maxlength="160"
                    required
                    value="{{ old('subject') }}"
                  >
                  @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="contact_order_number" class="form-label">Order Number (Optional)</label>
                  <input
                    id="contact_order_number"
                    type="text"
                    name="order_number"
                    class="form-control @error('order_number') is-invalid @enderror"
                    maxlength="120"
                    value="{{ old('order_number') }}"
                  >
                  @error('order_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 d-none" aria-hidden="true">
                  <label for="contact_website" class="form-label">Website</label>
                  <input
                    id="contact_website"
                    type="text"
                    name="website"
                    tabindex="-1"
                    autocomplete="off"
                    class="form-control"
                    value=""
                  >
                </div>

                <div class="col-12">
                  <label for="contact_message" class="form-label">Message</label>
                  <textarea
                    id="contact_message"
                    name="message"
                    rows="6"
                    class="form-control @error('message') is-invalid @enderror"
                    maxlength="5000"
                    required
                  >{{ old('message') }}</textarea>
                  @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <p class="small text-muted mb-0">Please do not include card numbers or passwords.</p>
                  <button type="submit" class="btn btn-success px-4">
                    Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="mt-4">
          <h2 class="h5 fw-semibold mb-2">Order Help</h2>
          <ul class="mb-0">
            <li>For delivery questions, contact the seller first (each seller ships their own items).</li>
            <li>If you cannot resolve an issue with a seller, open a dispute from your order page and we will assist.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>
@endsection
