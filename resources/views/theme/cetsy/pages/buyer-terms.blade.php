@extends('theme.'.theme().'.layouts.app')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">House Terms &amp; Conditions</h1>

        <p class="mt-6 leading-7 text-slate-700">
          Cetsy.co is a global e-commerce platform that provides an equal and fair place for all to trade their goods or services regardless of an individual's race, religion, color, political, or religious beliefs-so long as those goods or services are legally permitted for sale in that seller's region or jurisdiction.
        </p>

        <p class="mt-4 leading-7 text-slate-700">
          <strong>Our motto is Everything, Everyone, Everywhere.</strong> We encourage all sellers to be creative, to grow with the Cetsy community, and most importantly, to deliver accurately and promptly to buyers worldwide. Please uphold our House Rules and do not create an environment filled with mistrust, disbelief, or lies.
        </p>

        <p class="mt-4 leading-7 text-slate-700">
          The spirit of utmost good faith and genuine trade intentions should be the anchor of your shop or listings. Above all, both buyers and sellers should embody good thoughts, good words, and good actions. If any of these three traits is missing, please remedy it before proceeding.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Chargebacks &amp; Cancellations</h2>
        <p class="mt-3 leading-7 text-slate-700">
          If a Buyer disputes a credit/debit card payment, the issuer submits a claim. Should a chargeback occur after payout, the Merchant must unconditionally return the remitted funds to the Transaction Processor.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Taxes</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Except where local law requires otherwise, the Seller is responsible for collecting and paying any taxes associated with sales through Cetsy services.
        </p>
        <p class="mt-3 leading-7 text-slate-700">
          Note that some regions call VAT by other names (e.g., GST or HST). Here, we collectively refer to all such sales taxes as "VAT."
        </p>

        <p class="mt-6 leading-7 text-slate-700">
          To become a Cetsy Seller in just a few simple steps,
          <a href="{{ url('/login') }}" class="font-semibold text-emerald-700 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-600">CLICK HERE</a>.
        </p>

        <a href="{{ url('/login') }}" class="mt-6 inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
          Get Started
        </a>
      </article>
    </div>
  </section>
@endsection
