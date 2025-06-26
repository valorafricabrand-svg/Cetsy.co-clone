@extends('theme.'.theme().'.layouts.app')

@section('main')
  <!-- ====== Jaat.co.ke Buyer Tips ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">

          <h1 class="display-5 fw-bold mb-4">Jaat.co.ke Buyer Tips</h1>
          <p class="h5 text-secondary mb-4">
            Karibu! Thank you for choosing to shop on <strong>Jaat.co.ke</strong>. Below are top tips to help you enjoy a smooth, secure, and rewarding buying experience on Kenya’s favourite marketplace.
          </p>

          <ol class="list-group list-group-numbered mb-4">
            <li class="list-group-item border-0 ps-0 mb-2">
              Always be polite and respectful toward sellers and fellow buyers. Encouraging feedback keeps our community thriving—remember, without sellers there would be no marketplace.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Keep all communication inside Jaat’s chat system. It’s the only channel we can verify and moderate in case of disputes.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Use the “favourite” ❤ button on items you love. Sellers may reach out via chat with greetings, special offers, or related products—feel free to engage.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Conduct every transaction on Jaat.co.ke. Deals taken off-platform (WhatsApp, cash on the side, etc.) are outside our protection and support.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Review your cart carefully before checkout. Consider the sale final once you pay, unless you have agreed alternative arrangements with the seller in chat.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              See a Jaat charge you don’t recognise? Contact us via chat or email <em>before</em> lodging a bank dispute. We’ll quickly verify whether it’s a forgotten purchase, a family member’s order, or potential fraud.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              When your parcel arrives, rate the seller and product—not the courier. If the item is damaged, message the seller first so they can resolve it before leaving a negative review.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              Left feedback below 5 stars? Sellers may follow up to make things right. Please respond within 48 hours. If you agree to edit your review after a refund or replacement, kindly keep your promise.
            </li>
            <li class="list-group-item border-0 ps-0 mb-2">
              For international orders, know your country’s import rules and any customs taxes or VAT. Jaat and sellers can’t refund items seized by customs.
            </li>
            <li class="list-group-item border-0 ps-0">
              Your card or M-Pesa details are never stored on Jaat. All payments go through PCI-DSS compliant processors with 3-D Secure encryption for your protection.
            </li>
          </ol>

          <p class="lead mb-4">
            Welcome to the Jaat family—where you can find almost anything, from anyone, anywhere in Kenya and beyond. Need help? Reach our support team via live chat, phone, or email during business hours.
          </p>

          <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            Get Started
          </a>

        </div>
      </div>
    </div>
  </section>
@endsection
