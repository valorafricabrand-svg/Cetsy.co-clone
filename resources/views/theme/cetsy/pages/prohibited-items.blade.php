@extends('theme.'.theme().'.layouts.app')

@section('title', 'Prohibited / Restricted Items')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-2">Prohibited / Restricted Items</h1>
        <p class="text-muted mb-4">Effective: {{ policy_effective_label() }}</p>

        <p class="mb-4">
          Sellers may only list items that are legal to sell and ship and that comply with Cetsy policies. Certain
          items are prohibited or restricted to protect buyers, sellers, and payment partners.
          This list is not exhaustive—sellers must follow all applicable laws and regulations.
        </p>

        <h2 class="h5 fw-semibold mt-4">Not Allowed on Cetsy</h2>
        <ul class="mb-4">
          <li>Illegal drugs, drug paraphernalia, and controlled substances.</li>
          <li>Weapons, ammunition, explosives, and weapon parts (including realistic replicas where restricted).</li>
          <li>Adult sexual content and services; pornography; prostitution/escort services.</li>
          <li>Counterfeit goods, stolen items, or items that infringe intellectual property rights.</li>
          <li>Hate content, harassment, or items promoting violence or discrimination.</li>
          <li>Exotic animals and animal trafficking products.</li>
          <li>Financial products/services, money laundering schemes, and “get rich quick” products.</li>
          <li>Gambling products/services and lotteries where restricted.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">Restricted Items (May Require Extra Compliance)</h2>
        <ul class="mb-4">
          <li>Alcohol, tobacco, vaping products, and nicotine products.</li>
          <li>CBD/THC and other regulated supplements or pharmaceuticals.</li>
          <li>Medical devices and health claims.</li>
          <li>Gift cards, prepaid cards, and stored‑value products.</li>
        </ul>

        <p class="mb-0">
          Sellers are responsible for ensuring compliance. If you’re unsure whether an item is allowed, contact
          us via the <a href="{{ url('/contact') }}">Contact Us form</a> before listing.
        </p>
      </div>
    </div>
  </section>
@endsection
