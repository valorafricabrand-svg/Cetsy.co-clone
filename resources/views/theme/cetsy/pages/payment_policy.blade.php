@extends('theme.'.theme().'.layouts.app')
@section('title', 'Cetsy Payments, Settlement & Global Payouts')

@section('main')
<section class="relative overflow-x-clip bg-slate-50 py-10 sm:py-12">
  <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-200/35 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 top-[22rem] h-72 w-72 rounded-full bg-cyan-200/25 blur-3xl"></div>

  <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white sm:p-8">
        <span class="inline-flex items-center rounded-full border border-white/30 bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em]">
          Payments & Settlement
        </span>
        <h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">How Buyers Pay and How Sellers Withdraw on Cetsy</h1>
        <p class="mt-3 max-w-3xl text-sm text-emerald-50 sm:text-base">
          Cetsy operates a centralized escrow and settlement model. Buyer payments are processed through Paystack checkout,
          seller earnings are reflected in Cetsy Wallet, and withdrawals are processed through approved payout channels by country.
        </p>
      </div>

      <div class="p-5 sm:p-7 lg:p-8">
        <div class="grid gap-4 md:grid-cols-4">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Step 1</p>
            <h2 class="mt-2 text-base font-bold text-slate-900">Buyer Checkout</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Buyers pay through Paystack-supported checkout methods for their region.</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Step 2</p>
            <h2 class="mt-2 text-base font-bold text-slate-900">Central Settlement</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Funds settle into Cetsy's settlement account under our platform controls.</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Step 3</p>
            <h2 class="mt-2 text-base font-bold text-slate-900">Wallet Credit</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Seller net earnings appear in Cetsy Wallet based on order and hold rules.</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Step 4</p>
            <h2 class="mt-2 text-base font-bold text-slate-900">Withdrawal</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Withdrawals are sent through approved payout channels by country eligibility.</p>
          </div>
        </div>

        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
          <h3 class="text-base font-bold text-slate-900">Collection & Payout Channels We Display on Cetsy</h3>
          <p class="mt-2 text-sm leading-6 text-slate-700">
            <strong>Collection:</strong> Paystack checkout for buyer payments.<br>
            <strong>Payout rails:</strong> SWIFT, Wise, PayPal, and local bank transfer channels where available.
          </p>
          <div class="mt-3 flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-credit-card mr-1.5 text-emerald-700"></i>Paystack (Collection)</span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-building-columns mr-1.5 text-emerald-700"></i>SWIFT</span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-globe mr-1.5 text-emerald-700"></i>Wise</span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fab fa-paypal mr-1.5 text-emerald-700"></i>PayPal</span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"><i class="fas fa-university mr-1.5 text-emerald-700"></i>Local Bank Transfer</span>
          </div>
        </div>

        <div class="mt-6">
          <h3 class="text-lg font-extrabold text-slate-900">Country Availability Guide (Buyers & Sellers)</h3>
          <p class="mt-2 text-sm text-slate-600">Final channel availability depends on country, verification tier, currency corridor, and compliance checks.</p>

          <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50 text-slate-600">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">Region</th>
                  <th class="px-4 py-3 text-left font-semibold">Buyer Payment (via Paystack)</th>
                  <th class="px-4 py-3 text-left font-semibold">Seller Withdrawal Channels</th>
                  <th class="px-4 py-3 text-left font-semibold">Notes</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">West Africa</td>
                  <td class="px-4 py-3 text-slate-700">Cards, bank transfer, local methods</td>
                  <td class="px-4 py-3 text-slate-700">Local bank transfer, SWIFT (where applicable)</td>
                  <td class="px-4 py-3 text-slate-600">Fast local settlement corridors supported.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">East Africa</td>
                  <td class="px-4 py-3 text-slate-700">Cards, bank transfer, wallet-supported methods</td>
                  <td class="px-4 py-3 text-slate-700">Local bank transfer, Wise, SWIFT</td>
                  <td class="px-4 py-3 text-slate-600">Method availability differs by country and bank.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">South Africa</td>
                  <td class="px-4 py-3 text-slate-700">International card rails</td>
                  <td class="px-4 py-3 text-slate-700">Local bank transfer, SWIFT, Wise</td>
                  <td class="px-4 py-3 text-slate-600">Bank corridor support depends on provider coverage and verification tier.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">Europe / UK</td>
                  <td class="px-4 py-3 text-slate-700">International card rails</td>
                  <td class="px-4 py-3 text-slate-700">Wise, SWIFT, PayPal (eligible countries)</td>
                  <td class="px-4 py-3 text-slate-600">Identity verification required before withdrawal.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">North America</td>
                  <td class="px-4 py-3 text-slate-700">International card rails</td>
                  <td class="px-4 py-3 text-slate-700">SWIFT, Wise, PayPal (where enabled)</td>
                  <td class="px-4 py-3 text-slate-600">Provider corridor and sanctions checks apply.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">South &amp; Central America</td>
                  <td class="px-4 py-3 text-slate-700">International card rails</td>
                  <td class="px-4 py-3 text-slate-700">SWIFT, Wise, PayPal (eligible countries)</td>
                  <td class="px-4 py-3 text-slate-600">Availability varies by country, currency corridor, and payout partner rules.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">Australia</td>
                  <td class="px-4 py-3 text-slate-700">International card rails</td>
                  <td class="px-4 py-3 text-slate-700">SWIFT, Wise, PayPal (where enabled)</td>
                  <td class="px-4 py-3 text-slate-600">Provider limits and compliance checks apply before payout.</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-900">Asia-Pacific</td>
                  <td class="px-4 py-3 text-slate-700">International card rails</td>
                  <td class="px-4 py-3 text-slate-700">SWIFT, Wise, selected digital payout channels</td>
                  <td class="px-4 py-3 text-slate-600">Country-specific availability and limits apply.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h4 class="text-base font-bold text-slate-900">For Buyers</h4>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-6 text-slate-700">
              <li>Pay securely through Paystack checkout on supported methods.</li>
              <li>Order issues and eligible refunds follow Cetsy policy and payment rail rules.</li>
              <li>Refund destination depends on payment method and local regulations.</li>
            </ul>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h4 class="text-base font-bold text-slate-900">For Sellers</h4>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-6 text-slate-700">
              <li>Earnings appear in Cetsy Wallet after order processing rules are met.</li>
              <li>Request withdrawals to approved channels available for your country.</li>
              <li>Withdrawal timing depends on verification, review, and provider processing times.</li>
            </ul>
          </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
          <p><strong class="text-slate-900">Compliance note:</strong> Cetsy is a marketplace platform, not a bank. Payment processing and cross-border disbursement are handled via regulated third-party providers. We may request KYC/KYB documents and may adjust available channels per legal or operational requirements.</p>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
          <a href="{{ route('contact') }}" class="inline-flex items-center rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
            <i class="fas fa-headset mr-2"></i> Contact Payments Support
          </a>
          <a href="{{ route('seller-policy') }}" class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-5 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
            <i class="fas fa-file-contract mr-2"></i> Seller Policy
          </a>
        </div>
      </div>
    </article>
  </div>
</section>
@endsection
