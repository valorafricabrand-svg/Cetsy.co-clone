@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ====== Seller Forum ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Seller Forum</h1>
          <p class="h5 text-secondary mb-4">
            Thank you for your interest in improving your business and for reaching out to your Cetsy.co community.
            When using the Cetsy.co “Forum,” please remember to follow these basic guidelines:
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item">Be kind and patient with others.</li>
            <li class="list-group-item">No vulgar or offensive language; stay on topic relevant to Cetsy.co.</li>
            <li class="list-group-item">Use the forum to learn and educate others—do not belittle anyone.</li>
            <li class="list-group-item">Never be afraid to ask for help. The only bad question is the one not asked.</li>
            <li class="list-group-item">Do not share personal details (addresses, phone numbers, bank info) publicly.</li>
            <li class="list-group-item">Keep private conversations private—do not discuss staff or private chats here.</li>
            <li class="list-group-item">Enjoy and engage positively with our Cetsy.co community.</li>
          </ol>

          <p class="lead mb-4">
            We hope the Cetsy.co forum helps you find the answers you need to grow your business. If you require
            further assistance, our Live Chat is available 24/7.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
