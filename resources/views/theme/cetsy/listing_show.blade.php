{{-- resources/views/items/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name .' – Item Details')

@section('main')
@php
    /* Build a flat list from ProductVariations so we can force ONE choice only */
    $flatVariations = collect($product->productVariations ?? $product->variations ?? [])
        ->map(function ($v) {
            return [
                'id'    => $v->id,
                'type'  => $v->type ?: 'Option',
                'opt'   => $v->variation_option ?? $v->name ?? 'Value',
                'price' => (float) ($v->price ?? $v->price_override ?? 0),
                'stock' => (int) ($v->stock ?? 0),
                'sku'   => $v->sku ?? null,
                'img'   => $v->image ? asset('storage/'.$v->image) : null,
            ];
        });

    // Group ONLY for display headers; radios still share ONE name="product_variation_id"
    $groupedVariations = $flatVariations->groupBy('type');
@endphp

<section class="py-6" style="background:#f8faf9">
  <div class="container"
       x-data="{
         qty: 1,
         busy: false,
         shippingProfileId: {{ $product->shippingProfiles->firstWhere('is_default', true)->id ?? 'null' }},
         dec(){ this.qty = Math.max(1, this.qty-1) },
         inc(){ this.qty++ }
       }">

    {{-- Flash --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        {!! session('success') !!}
        <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row g-lg-5">
      {{-- GALLERY ------------------------------------------------------- --}}
      <div class="col-lg-7" data-aos="fade-right">
        <div id="productCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden mb-3" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($product->media as $i => $media)
              <div class="carousel-item @if($i === 0) active @endif">
                <img src="{{ asset('storage/' . $media->url) }}"
                     class="d-block w-100"
                     style="aspect-ratio:4/3;object-fit:cover"
                     alt="{{ $product->name }}">
              </div>
            @endforeach
          </div>
          @if($product->media->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          @endif
        </div>

        @if($product->media->count() > 1)
          <div class="d-flex gap-2 flex-wrap justify-content-center">
            @foreach($product->media as $i => $media)
              <img src="{{ asset('storage/' . $media->url) }}"
                   class="img-thumbnail p-0 thumb @if($i === 0) border-success @endif"
                   style="width:70px;height:70px;object-fit:cover;cursor:pointer"
                   data-bs-target="#productCarousel"
                   data-bs-slide-to="{{ $i }}"
                   title="View image {{ $i + 1 }}">
            @endforeach
          </div>
        @endif
      </div>

      {{-- DETAILS (sticky) --------------------------------------------- --}}
      <div class="col-lg-5" data-aos="fade-left">
        <div class="position-lg-sticky" style="top: 1rem;">
          <h1 class="h2 fw-bold">{{ $product->name }}</h1>

          {{-- Ratings --}}
          <div class="mb-2">
            @for($i=1; $i<=5; $i++)
              <i class="fa-star{{ $i <= round($product->avg_rating ?? 0) ? ' fa-solid text-warning' : ' fa-regular text-muted' }}"></i>
            @endfor
            <small class="ms-1 text-muted">
              ({{ $product->reviews_count ?? 0 }} reviews)
            </small>
          </div>

          @php
            $basePrice  = $product->price;
            $finalPrice = $product->discounted_price;
          @endphp

          @if($finalPrice < $basePrice)
            <div class="d-flex align-items-baseline gap-3 mb-3">
              <span class="fw-bold text-success">
                {{ get_currency() }} {{ number_format($finalPrice, 2) }}
              </span>
              <span class="text-muted text-decoration-line-through">
                {{ get_currency() }} {{ number_format($basePrice, 2) }}
              </span>
            </div>
          @else
            <p class="fw-bold text-success mb-3">
              {{ get_currency() }} {{ number_format($basePrice, 2) }}
            </p>
          @endif

          {{-- Shop & Stock badges --}}
          <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="badge bg-success bg-opacity-10 text-success">
              <i class="fa-solid fa-store me-1"></i>
              <a href="{{ route('shop.show', $product->shop->slug) }}" class="text-success text-decoration-none">
                {{ $product->shop->name }}
              </a>
            </span>

            @if($product->type == 'physical')
              <span class="badge {{ $product->stock > 0 ? 'bg-primary bg-opacity-10 text-primary' : 'bg-danger bg-opacity-10 text-danger' }}">
                {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
              </span>
            @endif
          </div>

          {{-- Quick-action buttons --}}
          <div class="d-flex flex-wrap gap-2 mb-4">
            <form method="POST" action="{{ route('favorites.toggle') }}">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Add to favourites">
                <i class="fa-regular fa-heart{{ $isFavorited ? ' text-danger fa-solid' : '' }}"> </i> Favourites
              </button>
            </form>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#offerModal">
              <i class="fa-solid fa-hand-holding-dollar me-1"></i>Make an offer
            </button>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#messageModal">
              <i class="fa-regular fa-comments me-1"></i>Message seller
            </button>
          </div>

          {{-- Highlights --}}
          @if($product->highlights)
            <ul class="list-unstyled mb-4 small">
              @foreach($product->highlights as $highlight)
                <li class="d-flex mb-1">
                  <i class="fa-solid fa-check text-success me-2"></i>
                  <span>{{ $highlight }}</span>
                </li>
              @endforeach
            </ul>
          @endif

          {{-- Category --}}
          <p class="mb-2">
            <strong class="me-1">Category:</strong>
            @if($product->category)
              <a href="{{ route('category.show', $product->category->slug) }}"
                 class="badge bg-success bg-opacity-25 text-success text-decoration-none">
                {{ $product->category->name }}
              </a>
            @else
              <span class="badge bg-secondary">Uncategorised</span>
            @endif
          </p>

          {{-- Country --}}
          @if($product->country)
            <p class="mb-4 small text-muted">
              <i class="fa-solid fa-globe-africa me-1"></i>Ship from {{ $product->country->name }}
            </p>
          @endif

          {{-- ADD-TO-CART BLOCK ----------------------------------------- --}}
          @if($product->type !== 'service')
            <div class="border rounded-4 p-4 bg-light-subtle">

              {{-- Variations: ONE radio required --}}
              @if($groupedVariations->isNotEmpty())
                <div class="mb-3">
                  <label class="form-label fw-semibold d-block">Select Variation</label>

                  @foreach($groupedVariations as $type => $options)
                    <div class="mb-2">
                      <span class="small text-muted d-block mb-1">{{ $type }}</span>
                      <div class="d-flex flex-column gap-2">
                        @foreach($options as $opt)
                          <label class="border rounded-3 p-2 d-flex align-items-start gap-2 {{ $opt['stock'] <= 0 ? 'opacity-50' : 'cursor-pointer' }}">
                            <input type="radio"
                                   name="product_variation_id"
                                   value="{{ $opt['id'] }}"
                                   class="form-check-input mt-1"
                                   form="addCartForm"
                                   {{ $opt['stock'] <= 0 ? 'disabled' : '' }}
                                   required>
                            <div class="flex-grow-1">
                              <span class="fw-semibold">{{ $opt['opt'] }}</span>
                              @if($opt['sku'])
                                <span class="badge bg-secondary bg-opacity-25 text-secondary ms-2">SKU: {{ $opt['sku'] }}</span>
                              @endif
                              @if($opt['price'] != 0)
                                <span class="d-block small text-primary fw-semibold">
                                  {{ get_currency() }} {{ number_format($opt['price'], 2) }}
                                </span>
                              @endif
                              @if($opt['stock'] <= 0)
                                <span class="badge bg-danger bg-opacity-25 text-danger">Out of stock</span>
                              @endif
                            </div>
                            @if($opt['img'])
                              <img src="{{ $opt['img'] }}" alt="{{ $opt['opt'] }}" style="width:52px;height:52px;object-fit:cover" class="rounded">
                            @endif
                          </label>
                        @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif

              {{-- Quantity --}}
              <div class="mb-3 d-flex align-items-center gap-2">
                <span class="fw-semibold">Qty</span>
                <button class="btn btn-outline-secondary btn-sm" @click="dec()" :disabled="qty <= 1">−</button>
                <input type="text" class="form-control text-center" style="width:60px" :value="qty" readonly>
                <button class="btn btn-outline-secondary btn-sm" @click="inc()">+</button>
              </div>

              {{-- Shipping --}}
              @if($product->shippingProfiles->count())
                <div class="mb-3">
                  <label class="form-label fw-semibold">Shipping</label>
                  <select class="form-select" x-model="shippingProfileId" form="addCartForm">
                    @foreach($product->shippingProfiles as $profile)
                      <option value="{{ $profile->id }}">
                        {{ $profile->name }} – {{ get_currency() }} {{ number_format($profile->base_rate, 2) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              @endif

              {{-- Forms --}}
              <div class="d-grid gap-2">
                {{-- Add to Cart --}}
                <form id="addCartForm" method="POST" action="{{ route('cart.add') }}"
                      @submit.prevent="busy = true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" :value="qty">
                  <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                  {{-- product_variation_id comes from the radio above --}}
                  <button type="submit" class="btn btn-success btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-cart-plus me-1"></i>Add to Cart
                  </button>
                </form>

                {{-- Buy Now --}}
                <form id="buyNowForm" method="POST" action="{{ route('cart.buy') }}"
                      @submit.prevent="busy = true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" :value="qty">
                  <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                  <input type="hidden" name="product_variation_id" value=""
                         x-init="$watch(() => document.querySelector('input[name=product_variation_id]:checked')?.value, v => $el.value = v);">
                  <button type="submit" class="btn btn-primary btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-bolt me-1"></i>Buy Now
                  </button>
                </form>
              </div>
            </div>
          @else
            {{-- Service notice --}}
            <div class="card border-info border-start-4 shadow-sm mb-4">
              <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px">
                  <i class="fa-solid fa-concierge-bell fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1 fw-semibold text-info">Service Listing</h6>
                  <p class="mb-0 small text-muted">
                    This is a <strong>service</strong>. Contact the seller below for quotes.
                  </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal">
                    <i class="fa-regular fa-comments me-1"></i>Message Seller
                  </button>
                  <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#offerModal">
                    <i class="fa-solid fa-handshake-simple me-1"></i>Make an Offer
                  </button>
                </div>
              </div>
            </div>
          @endif

          {{-- Share --}}
          <div class="mt-3 small">
            <span class="me-1">Share:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank">
              <i class="fa-brands fa-facebook fa-lg text-primary"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" class="mx-2" target="_blank">
              <i class="fa-brands fa-x-twitter fa-lg"></i>
            </a>
            <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&media={{ asset('storage/'.$product->featured_image) }}&description={{ urlencode($product->name) }}" target="_blank">
              <i class="fa-brands fa-pinterest fa-lg text-danger"></i>
            </a>
            <button class="btn btn-link text-decoration-none p-0 ms-2" data-bs-toggle="modal" data-bs-target="#reportModal" title="Report this listing">
              <i class="fa-solid fa-flag fa-lg text-muted"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- DESCRIPTION TABS ------------------------------------------------ --}}
    <ul class="nav nav-tabs mt-5" id="itemTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane" type="button">
          Description
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping-pane" type="button">
          Shipping & Returns
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button">
          Reviews ({{ $product->reviews_count ?? 0 }})
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq-pane" type="button">
          FAQs
        </button>
      </li>
    </ul>

    <div class="tab-content bg-white p-4 border-bottom border-start border-end rounded-bottom-4 shadow-sm" id="itemTabContent">
      <div class="tab-pane fade show active" id="desc-pane" role="tabpanel">
        {!! $product->description !!}
      </div>

      <div class="tab-pane fade" id="shipping-pane" role="tabpanel">
        <h5 class="fw-semibold mb-3">Shipping policies</h5>
        <p class="small text-muted">{{ $shopPolicies->shipping ?? 'Shipping details coming soon.' }}</p>
        <h5 class="fw-semibold mt-4 mb-3">Returns & exchanges</h5>
        <p class="small text-muted">{{ $shopPolicies->returns ?? 'Returns policy coming soon.' }}</p>
      </div>

      <div class="tab-pane fade" id="reviews-pane" role="tabpanel">
        @forelse($reviews as $review)
          <div class="border-bottom py-3">
            <div class="d-flex align-items-center mb-1">
              @for($i=1; $i<=5; $i++)
                <i class="fa-star{{ $i <= $review->rating ? ' fa-solid text-warning' : ' fa-regular text-muted' }} me-1"></i>
              @endfor
              <small class="text-muted ms-auto">{{ $review->created_at->diffForHumans() }}</small>
            </div>
            <p class="small mb-0">{{ $review->comment }}</p>
            <div class="small text-muted mt-1">{{ $review->user->name }}</div>
          </div>
        @empty
          <p class="text-muted small mb-0">No reviews yet.</p>
        @endforelse
      </div>

      <div class="tab-pane fade" id="faq-pane" role="tabpanel">
        <div class="accordion" id="faqAccordion">
          @forelse($faqs as $i => $faq)
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqHeading{{ $i }}">
                <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $i }}">
                  {{ $faq->question }}
                </button>
              </h2>
              <div id="faqCollapse{{ $i }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}"
                   data-bs-parent="#faqAccordion">
                <div class="accordion-body small">{{ $faq->answer }}</div>
              </div>
            </div>
          @empty
            <p class="text-muted small mb-0">Seller hasn’t added any FAQs yet.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- MORE FROM SHOP -------------------------------------------------- --}}
    @if($moreFromShop->count())
      <h3 class="h5 fw-bold mt-5 mb-3">More from {{ $product->shop->name }}</h3>
      <div class="row g-3">
        @foreach($moreFromShop as $item)
          <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
        @endforeach
      </div>
    @endif

    {{-- RELATED --------------------------------------------------------- --}}
    @if($relatedProducts->count())
      <h3 class="h5 fw-bold mt-5 mb-3">Related items</h3>
      <div class="row g-3">
        @foreach($relatedProducts as $item)
          <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
        @endforeach
      </div>
    @endif

  </div>
</section>

{{-- Message Modal --}}
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('messages.store') }}">
      @csrf
      <input type="hidden" name="receiver_id" value="{{ $product->shop->user_id }}">
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Message Seller – {{ $product->shop->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="messageBody" class="form-label">Your message</label>
        <textarea id="messageBody" name="message" rows="4" class="form-control" required></textarea>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Send Message</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- Offer Modal --}}
<div class="modal fade" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('offers.store') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="offerModalLabel">Make an Offer for {{ $product->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="offerPrice" class="form-label">Your offer price ({{ get_currency() }})</label>
        <input type="number" name="offer_price" id="offerPrice" min="1" step="1" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Submit Offer</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- Report Modal --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('product-reports.store') }}">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="reportModalLabel">Report Listing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Help us keep our community safe by reporting listings that violate our policies.</p>
        
        <div class="mb-3">
          <label for="reportReason" class="form-label">Reason for report</label>
          <select name="reason" id="reportReason" class="form-select" required>
            <option value="">Select a reason...</option>
            <option value="inappropriate">Inappropriate content</option>
            <option value="counterfeit">Counterfeit or fake item</option>
            <option value="spam">Spam or misleading</option>
            <option value="misleading">False advertising</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <div class="mb-3">
          <label for="reportDescription" class="form-label">Please provide details</label>
          <textarea name="description" id="reportDescription" rows="4" class="form-control" placeholder="Please describe the issue in detail..." required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Submit Report</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
      integrity="sha384-PU0QFv1kXlz9BM/UX5EwyV/ivxVMolZTUsjoeetfYxNdUswzqnMHipjInu6bcVCc"
      crossorigin="anonymous">
<style>
  .thumb.active, .thumb:hover { border:2px solid #198754!important }
  .carousel-inner img { transition:.4s }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha384-xKDJcyOgCjL2mK9ZcYnmQgSJvMREh4baN4GckSbnREV7mY4T0kT2LSpJxErL8xP8"
        crossorigin="anonymous"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => AOS.init({ duration:800, once:true }));

  const toast = (msg, type = 'success') => {
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
    el.setAttribute('role','alert');
    el.setAttribute('aria-live','assertive');
    el.setAttribute('aria-atomic','true');
    el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(el);
    new bootstrap.Toast(el,{delay:3000}).show();
  };
</script>
@endpush
