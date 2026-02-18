@extends('theme.'.theme().'.layouts.app')

@section('title', 'Prohibited / Restricted Items')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Prohibited / Restricted Items</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }}</p>

        <p class="mt-6 leading-7 text-slate-700">
          Sellers may only list items that are legal to sell and ship and that comply with Cetsy policies. Certain
          items are prohibited or restricted to protect buyers, sellers, and payment partners.
          This list is not exhaustive-sellers must follow all applicable laws and regulations.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Not Allowed on Cetsy</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Illegal drugs, drug paraphernalia, and controlled substances.</li>
          <li>Weapons, ammunition, explosives, and weapon parts (including realistic replicas where restricted).</li>
          <li>Adult sexual content and services; pornography; prostitution/escort services.</li>
          <li>Counterfeit goods, stolen items, or items that infringe intellectual property rights.</li>
          <li>Hate content, harassment, or items promoting violence or discrimination.</li>
          <li>Exotic animals and animal trafficking products.</li>
          <li>Financial products/services, money laundering schemes, and "get rich quick" products.</li>
          <li>Gambling products/services and lotteries where restricted.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Restricted Items (May Require Extra Compliance)</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Alcohol, tobacco, vaping products, and nicotine products.</li>
          <li>CBD/THC and other regulated supplements or pharmaceuticals.</li>
          <li>Medical devices and health claims.</li>
          <li>Gift cards, prepaid cards, and stored-value products.</li>
        </ul>

        <p class="mt-6 leading-7 text-slate-700">
          Sellers are responsible for ensuring compliance. If you're unsure whether an item is allowed, contact
          us via the <a href="{{ url('/contact') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">Contact Us form</a> before listing.
        </p>
      </article>
    </div>
  </section>
@endsection
