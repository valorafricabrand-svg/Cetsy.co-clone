@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ====== Seller Forum ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Seller Forum</h1>
          <p class="h5 text-secondary mb-4">
            Karibu! Thank you for choosing to grow your business with the <strong>Jaat.co.ke</strong> community.
            When participating in the Jaat Seller Forum, please keep these simple guidelines in mind:
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item">Be kind and patient with others.</li>
            <li class="list-group-item">No vulgar or offensive language; stay on topics relevant to Jaat.</li>
            <li class="list-group-item">Use the forum to learn and help others—never belittle anyone.</li>
            <li class="list-group-item">There are no bad questions. If you need help, just ask.</li>
            <li class="list-group-item">Do not share personal details (addresses, phone numbers, bank info) publicly.</li>
            <li class="list-group-item">Keep private conversations private—do not post staff or chat transcripts here.</li>
            <li class="list-group-item">Engage positively and enjoy the Jaat community spirit.</li>
          </ol>

          <p class="lead mb-4">
            We hope the Jaat forum helps you get the answers you need to grow. For direct assistance,
            our live-chat team is available 24/7.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
