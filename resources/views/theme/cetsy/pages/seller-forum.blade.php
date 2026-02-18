@extends('theme.'.theme().'.layouts.app')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Seller Forum</h1>
        <p class="mt-4 text-base text-slate-600 sm:text-lg">
          Thank you for your interest in improving your business and for reaching out to your Cetsy.co community.
          When using the Cetsy.co "Forum," please remember to follow these basic guidelines:
        </p>

        <ol class="mt-6 space-y-3">
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">1.</strong>Be kind and patient with others.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">2.</strong>No vulgar or offensive language; stay on topic relevant to Cetsy.co.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">3.</strong>Use the forum to learn and educate others-do not belittle anyone.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">4.</strong>Never be afraid to ask for help. The only bad question is the one not asked.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">5.</strong>Do not share personal details (addresses, phone numbers, bank info) publicly.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">6.</strong>Keep private conversations private-do not discuss staff or private chats here.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">7.</strong>Enjoy and engage positively with our Cetsy.co community.</li>
        </ol>

        <p class="mt-6 leading-7 text-slate-700">
          We hope the Cetsy.co forum helps you find the answers you need to grow your business. If you require
          further assistance, our Live Chat is available 24/7.
        </p>

        <a href="{{ url('/login') }}" class="mt-6 inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
          Get Started
        </a>
      </article>
    </div>
  </section>
@endsection
