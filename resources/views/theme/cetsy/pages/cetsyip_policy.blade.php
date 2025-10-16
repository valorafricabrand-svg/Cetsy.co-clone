@extends('layouts.frontapp')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="display-5 fw-bold mb-4">Cetsy IP Infringement Policy</h1>

          <p>
            We respect the intellectual property rights of others and expect our community to do the same. This page explains
            how to report alleged IP infringements and how we process such reports.
          </p>

          <h2 class="h5 fw-semibold mt-4">What qualifies as infringement</h2>
          <p>
            Infringements may include unauthorized use of copyrighted material, trademarks, or patents. If you believe your
            IP has been used without permission on Cetsy, you can submit a report using the process below.
          </p>

          <h2 class="h5 fw-semibold mt-4">How to report</h2>
          <ol>
            <li>Provide a description of the work and proof of ownership.</li>
            <li>Identify the listing or content in question (URL or screenshots).</li>
            <li>Include your contact details for follow-up.</li>
          </ol>

          <p>
            After receiving a complete report, we typically review within 48–72 hours and may contact the seller for a response.
            Content may be removed if we determine it violates our policies or applicable law.
          </p>

          <p class="mt-4">
            For broader community standards, please also review our
            <a href="{{ url('/house-policy') }}" class="text-success">House Rules &amp; Policy</a>.
          </p>
        </div>
      </div>
    </div>
  </section>
@endsection

