@extends('theme.'.theme().'.layouts.app')

@section('title', 'Lucare Community Guidelines')

@section('main')
  <!-- ====== Lucare Community Guidelines ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Lucare Community Guidelines</h1>

          <p class="mb-4">
            At <strong>Lucare</strong>, we foster a respectful, inclusive beauty community. Our Forums and in-platform Chat are spaces to share tips, reviews, and inspiration—while upholding our values of kindness and integrity.
          </p>

          <h2 class="h5 fw-semibold mt-4">General Principles</h2>
          <ul class="mb-4 ps-3">
            <li class="mb-2">Treat all members with respect—no harassment, hate speech, or discrimination.</li>
            <li class="mb-2">Keep communication on Lucare’s platform so we can support you if issues arise.</li>
            <li class="mb-2">No spamming, self-promotion, or unsolicited advertising in community spaces.</li>
            <li class="mb-2">Respect privacy: do not share personal or transaction details publicly.</li>
            <li class="mb-2">Follow Kenyan laws and Lucare’s <a href="{{ url('/terms') }}" class="text-primary text-decoration-underline">Terms & Conditions</a>.</li>
          </ul>

          <h2 class="h5 fw-semibold mt-4">Forums</h2>
          <p class="mb-4">
            Lucare Forums are public Q&A and discussion hubs. Be courteous: assume good intent, use supportive language, and only flag content that clearly violates guidelines.
          </p>

          <h2 class="h5 fw-semibold mt-4">Direct Messages</h2>
          <p class="mb-4">
            Use Lucare Chat for one-on-one conversations. Avoid abusive or obscene language. Do not send unsolicited promotional messages. If you receive inappropriate DMs, report them using the “Report” button.
          </p>

          <h2 class="h5 fw-semibold mt-4">Reviews & Feedback</h2>
          <p class="mb-4">
            Leave honest, constructive reviews. Do not post false or malicious feedback. Vendors may respond politely to questions or to offer solutions—always keep it professional.
          </p>

          <h2 class="h5 fw-semibold mt-4">Intellectual Property</h2>
          <p class="mb-4">
            Respect copyrights and trademarks. Do not share or promote counterfeit products or unauthorized content.
          </p>

          <h2 class="h5 fw-semibold mt-4">Enforcement</h2>
          <p class="mb-4 fst-italic">
            Lucare reviews reported violations within 48 hours. Accounts or content that breach guidelines may be suspended or removed.
          </p>

          <h2 class="h5 fw-semibold mt-4">Need Help?</h2>
          <p class="mb-4">
            Contact our support team via Chat or email at <a href="mailto:support@lucare.co.ke" class="text-decoration-underline">support@lucare.co.ke</a>.
          </p>

          <a href="{{ url('/terms') }}" class="btn btn-primary btn-lg">
            View Full Terms & Conditions
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
