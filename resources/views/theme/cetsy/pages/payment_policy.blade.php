@extends('theme.'.theme().'.layouts.app')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Fees &amp; Payments Policy</h1>

        <p class="mt-6 leading-7 text-slate-700">
          This page outlines how fees and payments work on Cetsy, including listing fees, transaction fees, and payment
          processing. Amounts may vary by currency and payment method.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Listing fees</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Some categories may include a listing or subscription fee. Review your category details for current rates and
          billing periods.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Transaction &amp; payout</h2>
        <p class="mt-3 leading-7 text-slate-700">
          When you sell an item, transaction and processing fees may apply based on your chosen payment provider. Payouts
          are released according to your account's payout schedule and country availability.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Payment processors</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Third-party processors (e.g., PayPal, card processors, or mobile money) may charge additional fees or require
          verification steps per their policies. Refer to your provider's documentation for details.
        </p>

        <p class="mt-6 leading-7 text-slate-700">
          See also our <a href="{{ url('/privacy') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Privacy Policy</a> and
          <a href="{{ url('/house-policy') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">House Rules &amp; Policy</a>.
        </p>
      </article>
    </div>
  </section>
@endsection
