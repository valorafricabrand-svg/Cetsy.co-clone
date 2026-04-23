@extends('theme.'.theme().'.layouts.app')

@section('title', 'Restricted and Prohibited Items')
@section('meta_description', 'See which items are restricted or prohibited for sale on Cetsy and how compliance, IP, and import rules apply.')
@section('canonical_url', route('restricted_for_sale'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Restricted / Prohibited Items</h1>
        <p class="mt-6 leading-7 text-slate-700">
          To help keep buyers and sellers safe and compliant with applicable laws, certain items are restricted or
          prohibited on Cetsy. Listings that violate these rules may be removed and accounts may be suspended.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Strictly Prohibited</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Illegal drugs, controlled substances, and drug paraphernalia.</li>
          <li>Weapons, explosives, ammunition, and related components.</li>
          <li>Counterfeit goods and unauthorized copies (copyright/trademark violations).</li>
          <li>Stolen property or items with tampered serial numbers.</li>
          <li>Human remains, organs, or body parts; hazardous biological materials.</li>
          <li>Adult or pornographic content involving minors, exploitation, or non-consensual acts.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Restricted (Require Compliance)</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
          <li>Cosmetics, supplements, and medical devices: must comply with local regulations and labeling laws.</li>
          <li>Food and beverages: must be sealed, shelf-stable where required, and labeled per regulations.</li>
          <li>Alcohol and tobacco: local laws and marketplace rules apply; may be disallowed in some regions.</li>
          <li>Live plants, seeds, and animals: subject to agricultural and import controls.</li>
          <li>Battery-powered devices and hazardous materials: follow shipping carrier and safety rules.</li>
        </ul>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Intellectual Property</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Do not list items that infringe on another party's IP. If you believe your rights are being violated, please
          review our <a href="{{ url('/cetsyip_policy') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">IP Infringement Policy</a> for how to file a report.
        </p>

        <h2 class="mt-8 text-lg font-bold text-slate-900">Customs &amp; Import</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Buyers are responsible for understanding local import laws and taxes/VAT. Sellers must comply with export and
          carrier requirements. Some items may be legal in one country but prohibited in another.
        </p>

        <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
          Unsure whether your item is allowed? Contact support before listing, and consult relevant local laws and carrier policies.
        </div>

        <p class="mt-6 leading-7 text-slate-700">
          For community standards, see our <a href="{{ url('/house-policy') }}" class="font-semibold text-emerald-700 hover:text-emerald-600">House Rules &amp; Policy</a>.
        </p>
      </article>
    </div>
  </section>
@endsection
