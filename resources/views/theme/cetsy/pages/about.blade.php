@extends('theme.'.theme().'.layouts.app')

@section('main')
@php($__about = App\Models\PolicySection::where('slug','about-cetsy')->first())
@if($__about && trim((string)$__about->content) !== '')
{!! $__about->content !!}
@else
<!-- ====== About Section (Enhanced) ====== -->
<section class="py-6 bg-white position-relative overflow-hidden">
    <style>
    .py-6 {
        padding-top: 4rem;
        padding-bottom: 4rem;
    }

    /* Subtle entrance animation */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .reveal {
        opacity: 0;
        animation: fadeUp .6s ease forwards;
    }

    .reveal.d1 {
        animation-delay: .08s
    }

    .reveal.d2 {
        animation-delay: .16s
    }

    .reveal.d3 {
        animation-delay: .24s
    }

    /* Eyebrow + chips */
    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        background: rgba(25, 135, 84, .06);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, .25);
        font-weight: 600;
        font-size: .85rem;
    }

    .feature-chip {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .75rem 1rem;
        border-radius: .85rem;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 2px 10px rgba(0, 0, 0, .04);
        font-weight: 600;
        color: #198754;
    }

    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .65rem;
        border-radius: 999px;
        font-weight: 700;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .06);
        color: #198754;
    }

    /* Image collage polish */
    .img-card {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .12);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .img-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(16, 24, 40, .16);
    }

    .badge-float {
        position: absolute;
        left: 1rem;
        bottom: 1rem;
        background: rgba(255, 255, 255, .9);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: .75rem;
        padding: .5rem .75rem;
        font-weight: 700;
        color: #198754;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .08);
    }

    /* Callouts & lists */
    .callout {
        background: linear-gradient(180deg, rgba(25, 135, 84, .06), rgba(25, 135, 84, .04));
        border: 1px solid rgba(25, 135, 84, .15);
        border-radius: 1rem;
    }

    .list-icon li {
        padding-left: 1.75rem;
        position: relative;
        margin-bottom: .5rem;
    }

    .list-icon li i {
        position: absolute;
        left: 0;
        top: .2rem;
        color: #198754;
    }

    /* Buttons */
    .btn-pill {
        border-radius: 999px;
    }

    .btn-soft-success {
        background: rgba(25, 135, 84, .08);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, .25);
    }

    .btn-soft-success:hover {
        background: rgba(25, 135, 84, .12);
        color: #157347;
        border-color: rgba(25, 135, 84, .35);
    }
    </style>

    <div class="container">
        <div class="row gx-5 align-items-center">
            <!-- Left Column: Images (hidden on small) -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="row g-3">
                    <div class="col-6 reveal">
                        <div class="img-card">
                            <img src="{{ asset('build/assets/images/cetsybout.jpg') }}" alt="Cetsy Boutique"
                                class="img-fluid">
                        </div>
                    </div>
                    <div class="col-6 reveal d1">
                        <div class="img-card">
                            <img src="{{ asset('build/assets/images/cetsyabout3.jpg') }}" alt="About Cetsy"
                                class="img-fluid">
                        </div>
                    </div>

                    <div class="col-12 position-relative mt-2 reveal d2">
                        <div class="img-card">
                            <img src="{{ asset('build/assets/images/cetsyabout2.jpg') }}" alt="Cetsy Community"
                                class="img-fluid w-100">
                        </div>

                        <!-- Decorative dots (kept, with improved positioning) -->
                        <div class="position-absolute" style="bottom:-8px; right:-8px; opacity:.14;">
                            <svg width="120" height="90" xmlns="http://www.w3.org/2000/svg">
                                @for($x=10; $x
                                <=110; $x+=20) <circle cx="{{ $x }}" cy="70" r="3" fill="#198754" />
                                @endfor
                            </svg>
                        </div>

                        <!-- Floating badge -->
                        <div class="badge-float">
                            <i class="fas fa-globe me-1"></i> Global since 2021
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Content -->
            <div class="col-12 col-lg-6 reveal">
                <span class="eyebrow mb-2">
                    <i class="fas fa-store"></i> Welcome to Cetsy
                </span>

                <h2 class="fw-bold mb-3">
                    About {{ config('app.name','Cetsy') }}: an online marketplace
                </h2>

                <p class="lead text-muted mb-4">
                    {{ config('app.name','Cetsy') }} is an online marketplace platform that connects independent sellers
                    with buyers. We provide the website, listing tools, and checkout to help facilitate transactions,
                    but sellers are responsible for their own products, fulfillment, and customer service.
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="feature-chip">
                            <i class="fas fa-shield-alt"></i> Buyer Protection
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="feature-chip">
                            <i class="fas fa-wallet"></i> Multiple Payment Options
                        </div>
                    </div>
                </div>

                <h5 class="fw-semibold mb-2">What we do</h5>
                <p class="mb-4">
                    We help buyers discover unique products from independent sellers, and we help sellers run their
                    shops with tools for listings, orders, and payments. {{ config('app.name','Cetsy') }} is operated
                    from <strong>{{ operating_region() }}</strong>.
                </p>

                <div class="callout p-3 p-md-4 mb-4">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="stat-chip"><i class="fas fa-users"></i> 50k+ Buyers</span>
                        <span class="stat-chip"><i class="fas fa-store"></i> 10k+ Sellers</span>
                        <span class="stat-chip"><i class="fas fa-box-open"></i> Physical & Digital</span>
                    </div>
                </div>

                <h6 class="fst-italic text-secondary mb-1">Sellers</h6>
                <p class="mb-3">
                    Sellers on {{ config('app.name','Cetsy') }} are independent businesses. They set prices, create
                    listings, ship orders, and handle returns/refunds in line with our policies.
                </p>

                <h6 class="fw-semibold mb-2">Allowed product types (clear &amp; limited)</h6>
                <p class="mb-2">
                  Sellers may list products that fit within these categories and comply with our rules:
                </p>
                <ul class="mb-3">
                  <li>Handmade goods and artisan products</li>
                  <li>Craft supplies and materials</li>
                  <li>Art, prints, and stationery</li>
                  <li>Home décor and accessories</li>
                  <li>Fashion accessories and jewelry</li>
                  <li>Vintage items (where permitted)</li>
                  <li>Digital downloads (where offered and permitted)</li>
                </ul>
                <p class="mb-3">
                  Prohibited and restricted items are not allowed. See our
                  <a href="{{ url('/prohibited-items') }}" class="link-primary">Prohibited / Restricted Items</a> list.
                </p>

                <ul class="list-unstyled list-icon mb-4">
                    <li><i class="fas fa-check-circle"></i> Verify local legality before posting a listing.</li>
                    <li><i class="fas fa-check-circle"></i> Follow our <a href="{{ url('/seller-policy') }}">Seller Policy</a> for a smooth experience.</li>
                    <li><i class="fas fa-check-circle"></i> Reach support via <a href="{{ url('/contact') }}">Contact</a>.</li>
                </ul>

                <p class="mb-4">
                    To become a seller, review our <a href="{{ url('/seller-policy') }}">Seller Policy</a> and our
                    <a href="{{ url('/terms') }}">Terms &amp; Conditions</a>. Questions? Visit our
                    <a href="{{ url('/contact') }}">Contact</a> page.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ url('/login') }}" class="btn btn-success btn-lg btn-pill">
                        <i class="fas fa-user-check me-2"></i> Get Started as a Seller
                    </a>
                    <a href="{{ url('/listings') }}" class="btn btn-soft-success btn-lg btn-pill">
                        <i class="fas fa-compass me-2"></i> Explore Marketplace
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ====== About Section End ====== -->
@endif
@endsection
