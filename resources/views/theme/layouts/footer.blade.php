

<!-- Footer Section -->
<footer class="bg-dark text-white pt-5 pb-4">
  <div class="container">
    <div class="row gy-4">
      <!-- About Cetsy -->
      <div class="col-lg-3 col-md-6">
        <h5 class="text-primary fw-bold mb-3">About Cetsy</h5>
        <p>All‑in‑one platform to showcase your handmade products to a global audience.</p>
        <a href="{{ url('/about') }}" class="btn btn-outline-light btn-sm mt-2">Learn More</a>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-3 col-md-6">
        <h5 class="text-primary fw-bold mb-3">Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="{{ url('/') }}" class="text-light text-decoration-none">Home</a></li>
          <li><a href="{{ url('about') }}" class="text-light text-decoration-none">About</a></li>
          <li><a href="{{ url('/features') }}" class="text-light text-decoration-none">Features</a></li>
          <li><a href="{{ url('/pricing') }}" class="text-light text-decoration-none">Pricing</a></li>
          <li><a href="{{ url('/contact') }}" class="text-light text-decoration-none">Contact</a></li>
        </ul>
      </div>

      <!-- Payments & Support -->
      <div class="col-lg-3 col-md-6">
        <h5 class="text-primary fw-bold mb-3">Payments & Support</h5>
        <ul class="list-unstyled">
          <li><a href="{{ url('bank_deposits') }}" class="text-light text-decoration-none">Bank Deposits</a></li>
          <li><a href="{{ url('mpesa_deposits') }}" class="text-light text-decoration-none">M‑Pesa Deposits</a></li>
          <li><a href="{{ url('payment_methods') }}" class="text-light text-decoration-none">Payment Methods</a></li>
        </ul>
      </div>

      <!-- Stay Updated & Social Media -->
      <div class="col-lg-3 col-md-6">
        <h5 class="text-primary fw-bold mb-3">Stay Updated</h5>
        <p>Subscribe to our newsletter for the latest updates and trends.</p>
        <h5 class="text-primary fw-bold mb-3">Contact Us</h5>
        <p class="mb-1">
          <i class="fas fa-phone-alt me-2"></i>
          <a href="tel:+254725537399" class="text-light text-decoration-none">+254 725 345 345</a>
        </p>
        <p class="mb-3">
          <i class="fas fa-envelope me-2"></i>
          <a href="mailto:support@cetsy.com" class="text-light text-decoration-none">support@cetsy.com</a>
        </p>
        <div class="d-flex gap-3">
          <a href="#" class="text-light fs-5"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-light fs-5"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-light fs-5"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" class="text-light fs-5"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <hr class="border-light my-4">

    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
        <p class="mb-0 small">&copy; {{ date('Y') }} Cetsy. All rights reserved. Powered by <a target="_blank" href="https://zama.co.ke">Zama Web Experts</a></p>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <a href="{{ url('/privacy-policy') }}" class="text-light small me-3">Privacy Policy</a>
        <a href="{{ url('/terms-of-service') }}" class="text-light small">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>


</main>

<!-- ===============================================-->
<!--    End of Main Content-->
<!-- ===============================================-->

<!-- ===============================================-->
<!--    JavaScripts-->
<!-- ===============================================-->
<script src="{{ asset('') }}vendors/popper/popper.min.js"></script>
<script src="{{ asset('') }}vendors/bootstrap/bootstrap.min.js"></script>
<script src="{{ asset('') }}vendors/anchorjs/anchor.min.js"></script>
<script src="{{ asset('') }}vendors/is/is.min.js"></script>
<script src="{{ asset('') }}vendors/fontawesome/all.min.js"></script>
<script src="{{ asset('') }}vendors/lodash/lodash.min.js"></script>
<script src="{{ asset('') }}vendors/list.js/list.min.js"></script>
<script src="{{ asset('') }}vendors/feather-icons/feather.min.js"></script>
<script src="{{ asset('') }}vendors/dayjs/dayjs.min.js"></script>
<script src="{{ asset('') }}vendors/mapbox-gl/mapbox-gl.js"></script>
<script src="{{ asset('') }}assets/js/phoenix.js"></script>
<script src="{{ asset('') }}vendors/isotope-layout/isotope.pkgd.min.js"></script>
<script src="{{ asset('') }}vendors/imagesloaded/imagesloaded.pkgd.min.js"></script>
<script src="{{ asset('') }}vendors/isotope-packery/packery-mode.pkgd.min.js"></script>
<script src="{{ asset('') }}vendors/bigpicture/BigPicture.js"></script>
<script src="{{ asset('') }}vendors/countup/countUp.umd.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDbaQGvhe7Af-uOMJz68NWHnO34UjjE7Lo&amp;callback=initMap" async></script>
<script src="{{ asset('') }}/{{ asset('') }}/../smtpjs.com/v3/smtp.js"></script>

@include('chat_widget')
@yield('scripts')
 @stack('scripts')
</body>
</html>
