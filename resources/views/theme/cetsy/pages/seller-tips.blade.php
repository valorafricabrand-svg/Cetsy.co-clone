@extends('theme.'.theme().'.layouts.app')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Important Tips on Selling with Cetsy</h1>
        <p class="mt-4 text-base text-slate-600 sm:text-lg">
          Thank you for your interest in becoming a Seller on Cetsy.co. We want you to be as successful as possible, so here are our top tips for crafting winning listings.
        </p>

        <ol class="mt-6 space-y-3">
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>1. Use high-quality visuals.</strong> Provide 13 MP+ photos from multiple angles and, when relevant, short videos to showcase items like cars or instruments.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>2. Shoot in natural light.</strong> The best photos come from indirect sunlight between 10 AM and 2 PM.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>3. Keep backgrounds simple.</strong> Remove distractions so the focus remains on your product.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>4. Write clear descriptions.</strong> Be concise but thorough-include size, material, variations, and any special features.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>5. Detail your shipping.</strong> Decide whether you ship locally or globally, research courier rates, and factor in all costs (including Cetsy fees) before listing.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>6. Prioritize customer service.</strong> Be honest, polite, and prompt. Buyers appreciate clear communication and reliable fulfillment.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>7. Engage with favorites.</strong> When someone favorites your item, send a friendly message-offer related products or a small discount to encourage purchase.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>8. Consider paid advertising.</strong> Boost visibility with Cetsy ads starting at $1/day to drive more traffic to key listings.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>9. Promote your shop.</strong> Use Cetsy Services section to highlight additional offerings (e.g., custom painting services) and link back to your shop.</li>
          <li class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-700"><strong>10. Respond quickly to messages.</strong> Even tough questions can be turned positive-treat every inquiry as an opportunity.</li>
        </ol>

        <p class="mt-6 leading-7 text-slate-700">
          We hope these tips give you confidence as you start selling. If you need further assistance, our 24/7 Live Chat, phone, and email support are always available.
        </p>

        <a href="{{ url('/login') }}" class="mt-6 inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
          Get Started as a Seller
        </a>
      </article>
    </div>
  </section>
@endsection
