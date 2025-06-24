@extends('layouts.frontapp')

@section('main')
  <!-- ====== Cetsy.co Buyer Tips ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Cetsy.co Buyer Tips</h1>
          <p class="h5 text-secondary mb-4">
            Thank you for your interest in becoming a Buyer on Cetsy.co. We would like you to be as successful as you can be with your purchases, and as such, share some top tips regarding your shopping experience.
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item border-0 ps-0 mb-2">
              Always be polite and respectful towards our sellers and others on the Cetsy.co platform. When a buyer misuses or abuses a seller, they may become frustrated and shift focus—even if they excel at their craft. Rather than discourage, be encouraging. Remember, without sellers, there is no marketplace to shop.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              All communications should be done through the Cetsy chat system. If you need Cetsy to review communications with a seller, only chat messages are legally verifiable. External messages (email, WhatsApp, etc.) must be verifiable.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              When browsing, “favorite” items that speak to you. Don’t be surprised if the seller reaches out via chat with greetings, offers, or similar listings—be open to communication.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Conduct all business on the Cetsy.co platform. Transactions outside Cetsy are not protected or moderated by us. We cannot help if issues arise off-platform.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Before checkout, review your order thoroughly. Do not purchase and then immediately cancel—unless the purchase was unauthorized. Consider the transaction complete at checkout unless you arrange otherwise with the seller.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              If you see a Cetsy.co charge you don’t recognize, contact us before your bank. It may be a forgotten purchase or a household member’s order. If it’s fraudulent, we’ll provide the documentation you need.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              When you receive your item, rate only the seller and the item condition—not the shipping carrier. If it arrives damaged, report via chat so the seller can resolve it. Don’t leave a negative review for transit damage.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              If you leave less-than-perfect feedback (below 5 stars), the seller may follow up. Please respond kindly and honestly within 48 hours. If you agree to change your review after a refund or replacement, keep your promise.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              You are responsible for knowing import rules and any taxes/VAT in your country. If uncertain, contact your customs authority before purchasing. Cetsy.co and sellers do not issue refunds for prohibited imports.
            </li>
            <li class="list-group-item border-0 ps-0">
              We do not retain your payment information, nor do sellers. All transactions use a 3rd-party processor with 3D-secure encryption.
            </li>
          </ol>

          <p class="lead mb-4">
            And finally, welcome to our Cetsy.co family. We hope these tips give you confidence to do business here—where you can find everything, from everyone, everywhere. For more help, reach us via Live Chat, phone, or email during our customer service hours.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
