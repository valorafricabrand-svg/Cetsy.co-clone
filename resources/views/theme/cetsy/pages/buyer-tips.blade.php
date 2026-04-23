@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy Buyer Tips')
@section('meta_description', 'Helpful Cetsy buyer tips for choosing sellers, reviewing listings, communicating clearly, and shopping with confidence.')
@section('canonical_url', localized_route('buyer-tips'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Cetsy.co Buyer Tips</h1>
        <p class="mt-4 text-base text-slate-600 sm:text-lg">
          Thank you for your interest in becoming a Buyer on Cetsy.co. We would like you to be as successful as you can be with your purchases, and as such, share some top tips regarding your shopping experience.
        </p>

        <ol class="mt-6 space-y-3">
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">1.</strong>Always be polite and respectful towards our sellers and others on the Cetsy.co platform. When a buyer misuses or abuses a seller, they may become frustrated and shift focus-even if they excel at their craft. Rather than discourage, be encouraging. Remember, without sellers, there is no marketplace to shop.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">2.</strong>All communications should be done through the Cetsy chat system. If you need Cetsy to review communications with a seller, only chat messages are legally verifiable. External messages (email, WhatsApp, etc.) must be verifiable.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">3.</strong>When browsing, "favorite" items that speak to you. Do not be surprised if the seller reaches out via chat with greetings, offers, or similar listings-be open to communication.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">4.</strong>Conduct all business on the Cetsy.co platform. Transactions outside Cetsy are not protected or moderated by us. We cannot help if issues arise off-platform.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">5.</strong>Before checkout, review your order thoroughly. Do not purchase and then immediately cancel-unless the purchase was unauthorized. Consider the transaction complete at checkout unless you arrange otherwise with the seller.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">6.</strong>If you see a Cetsy.co charge you do not recognize, contact us before your bank. It may be a forgotten purchase or a household member's order. If it's fraudulent, we'll provide the documentation you need.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">7.</strong>When you receive your item, rate only the seller and the item condition-not the shipping carrier. If it arrives damaged, report via chat so the seller can resolve it. Do not leave a negative review for transit damage.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">8.</strong>If you leave less-than-perfect feedback (below 5 stars), the seller may follow up. Please respond kindly and honestly within 48 hours. If you agree to change your review after a refund or replacement, keep your promise.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">9.</strong>You are responsible for knowing import rules and any taxes/VAT in your country. If uncertain, contact your customs authority before purchasing. Cetsy.co and sellers do not issue refunds for prohibited imports.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong class="mr-1">10.</strong>We do not retain your payment information, nor do sellers. All transactions use a 3rd-party processor with 3D-secure encryption.</li>
        </ol>

        <p class="mt-6 leading-7 text-slate-700">
          And finally, welcome to our Cetsy.co family. We hope these tips give you confidence to do business here-where you can find everything, from everyone, everywhere. For more help, reach us via Live Chat, phone, or email during our customer service hours.
        </p>

        <a href="{{ url('/login') }}" class="mt-6 inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
          Get Started
        </a>
      </article>
    </div>
  </section>
@endsection
