@extends('layouts.frontapp')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="display-5 fw-bold mb-4">Restricted / Prohibited Items</h1>
          <p>
            To help keep buyers and sellers safe and compliant with applicable laws, certain items are restricted or
            prohibited on Cetsy. Listings that violate these rules may be removed and accounts may be suspended.
          </p>

          <h2 class="h5 fw-semibold mt-4">Strictly Prohibited</h2>
          <ul>
            <li>Illegal drugs, controlled substances, and drug paraphernalia.</li>
            <li>Weapons, explosives, ammunition, and related components.</li>
            <li>Counterfeit goods and unauthorized copies (copyright/trademark violations).</li>
            <li>Stolen property or items with tampered serial numbers.</li>
            <li>Human remains, organs, or body parts; hazardous biological materials.</li>
            <li>Adult or pornographic content involving minors, exploitation, or non‑consensual acts.</li>
          </ul>

          <h2 class="h5 fw-semibold mt-4">Restricted (Require Compliance)</h2>
          <ul>
            <li>Cosmetics, supplements, and medical devices: must comply with local regulations and labeling laws.</li>
            <li>Food and beverages: must be sealed, shelf‑stable where required, and labeled per regulations.</li>
            <li>Alcohol and tobacco: local laws and marketplace rules apply; may be disallowed in some regions.</li>
            <li>Live plants, seeds, and animals: subject to agricultural and import controls.</li>
            <li>Battery‑powered devices and hazardous materials: follow shipping carrier and safety rules.</li>
          </ul>

          <h2 class="h5 fw-semibold mt-4">Intellectual Property</h2>
          <p>
            Do not list items that infringe on another party’s IP. If you believe your rights are being violated, please
            review our <a href="{{ url('/cetsyip_policy') }}" class="text-success">IP Infringement Policy</a> for how to file a report.
          </p>

          <h2 class="h5 fw-semibold mt-4">Customs & Import</h2>
          <p>
            Buyers are responsible for understanding local import laws and taxes/VAT. Sellers must comply with export and
            carrier requirements. Some items may be legal in one country but prohibited in another.
          </p>

          <div class="alert alert-info mt-4" role="alert">
            Unsure whether your item is allowed? Contact support before listing, and consult relevant local laws and carrier
            policies.
          </div>

          <p class="mt-4">
            For community standards, see our <a href="{{ url('/house-policy') }}" class="text-success">House Rules &amp; Policy</a>.
          </p>
        </div>
      </div>
    </div>
  </section>
@endsection

