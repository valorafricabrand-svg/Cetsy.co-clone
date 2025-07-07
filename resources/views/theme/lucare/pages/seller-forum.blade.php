@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare Vendor Forum')

@section('main')
  <!-- ====== Lucare Vendor Forum ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Vendor Forum</h1>
          <p class="h5 text-secondary mb-4">
            Welcome! Thank you for partnering with <strong>Lucare</strong>. In the Lucare Vendor Forum, please follow these guidelines to keep our community helpful and respectful:
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item">Be courteous and patient—support fellow vendors with kindness.</li>
            <li class="list-group-item">Use professional, respectful language; stay on topics related to Lucare and beauty products.</li>
            <li class="list-group-item">Share your expertise—help others grow, and don’t undermine anyone’s efforts.</li>
            <li class="list-group-item">There are no silly questions. If you need clarification, ask away.</li>
            <li class="list-group-item">Do not post personal contact details (addresses, phone numbers, bank info) in public threads.</li>
            <li class="list-group-item">Keep private support conversations confidential—do not share internal chat transcripts here.</li>
            <li class="list-group-item">Engage positively and celebrate each other’s successes in Lucare community spirit.</li>
          </ol>

          <p class="lead mb-4">
            We hope this forum helps you succeed. For direct assistance, our support team is available via live chat and email 24/7.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Join the Forum
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
