@extends('theme.'.theme().'.layouts.app')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Cetsy IP Infringement Policy</h1>

        <p class="mt-6 leading-7 text-slate-700">
          We respect the intellectual property rights of others and expect our community to do the same. This page explains
          how to report alleged IP infringements and how we process such reports.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">What qualifies as infringement</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Infringements may include unauthorized use of copyrighted material, trademarks, or patents. If you believe your
          IP has been used without permission on Cetsy, you can submit a report using the process below.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">How to report</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-6 text-slate-700">
          <li>Provide a description of the work and proof of ownership.</li>
          <li>Identify the listing or content in question (URL or screenshots).</li>
          <li>Include your contact details for follow-up.</li>
        </ol>

        <p class="mt-6 leading-7 text-slate-700">
          After receiving a complete report, we typically review within 48-72 hours and may contact the seller for a response.
          Content may be removed if we determine it violates our policies or applicable law.
        </p>

        <p class="mt-6 leading-7 text-slate-700">
          For broader community standards, please also review our
          <a href="{{ url('/house-policy') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">House Rules &amp; Policy</a>.
        </p>
      </article>
    </div>
  </section>
@endsection
