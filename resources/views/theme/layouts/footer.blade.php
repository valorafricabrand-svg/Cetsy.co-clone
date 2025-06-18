

<!-- Footer Section -->
<footer class="bg-dark text-white py-5">
  <div class="container px-3 px-sm-5">
    <div class="row gx-4 gy-5">

      <!-- Sellers -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">Sellers</h5>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2">
            <a href="{{ url('/become_seller') }}" class="text-white text-decoration-none footer-link">
              Become a Seller
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/privacy') }}" class="text-white text-decoration-none footer-link">
              Privacy Policy
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/terms') }}" class="text-white text-decoration-none footer-link">
              Terms &amp; Conditions
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/seller_forum') }}" class="text-white text-decoration-none footer-link">
              Seller Forum
            </a>
          </li>
          <li>
            <a href="{{ url('/seller_tips') }}" class="text-white text-decoration-none footer-link">
              Seller Tips
            </a>
          </li>
        </ul>
      </div>

      <!-- Buyers -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">Buyers</h5>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2">
            <a href="{{ url('/buyer_tips') }}" class="text-white text-decoration-none footer-link">
              Buyer Tips
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ url('/privacy') }}" class="text-white text-decoration-none footer-link">
              Privacy Policy
            </a>
          </li>
          <li>
            <a href="{{ url('/buyer_terms') }}" class="text-white text-decoration-none footer-link">
              Terms &amp; Conditions
            </a>
          </li>
        </ul>
      </div>

      <!-- About -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">About</h5>
        <ul class="list-unstyled small mb-0">
          <li class="mb-2">
            <a href="{{ url('/about') }}" class="text-white text-decoration-none footer-link">
              About Cetsy
            </a>
          </li>
          <li>
            <a href="{{ url('/house_policy') }}" class="text-white text-decoration-none footer-link">
              House Rules &amp; Policy
            </a>
          </li>
        </ul>
      </div>

      <!-- Support -->
      <div class="col-md-3 col-6">
        <h5 class="text-uppercase text-success mb-3 border-bottom border-success pb-2">Support</h5>
        <ul class="list-unstyled small mb-4">
          <li class="mb-2">
            <a href="{{ url('/contact') }}" class="text-white text-decoration-none footer-link">
              Reach Us
            </a>
          </li>
          <li class="text-secondary small text-white">
            Email: support@cetsy.co
          </li>
        </ul>

        <!-- Social Icons -->
        <div class="d-flex gap-3">
          <a href="#!" aria-label="Facebook" class="text-white footer-link">
            <i class="fab fa-facebook-f fs-5"></i>
          </a>
          <a href="#!" aria-label="Instagram" class="text-white footer-link">
            <i class="fab fa-instagram fs-5"></i>
          </a>
          <a href="#!" aria-label="Twitter" class="text-white footer-link">
            <i class="fab fa-twitter fs-5"></i>
          </a>
          <a href="#!" aria-label="LinkedIn" class="text-white footer-link">
            <i class="fab fa-linkedin-in fs-5"></i>
          </a>
        </div>
      </div>

    </div>

    <div class="mt-5 pt-4 border-top border-secondary text-center text-secondary small text-white">
      &copy; {{ date('Y') }} cetsy.co — All rights reserved.
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
