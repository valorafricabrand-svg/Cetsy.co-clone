{{-- resources/views/items/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name .' – Item Details')

@section('main')
<section class="py-6" style="background:#f8faf9">
  <div class="container"
       x-data="{
         qty: 1,
         busy: false,
         shippingProfileId: {{ $product->shippingProfiles->firstWhere('is_default', true)->id ?? 'null' }},
         share() {
           navigator.clipboard.writeText('{{ url()->current() }}')
             .then(() => toast('Link copied to clipboard!'))
             .catch(() => toast('Unable to copy link','danger'));
         }
       }">

    {{-- Flash --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        {!! session('success') !!}
        <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row g-lg-5">
      {{-- Gallery --}}
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

      {{-- Details --}}
      <div class="col-lg-5" data-aos="fade-left">
        <div class="position-lg-sticky" style="top: 1rem;">
          <h1 class="h2 fw-bold">{{ $product->name }}</h1>
       @if(!empty($product->discount_price) && $product->discount_price < $product->price)
  <div class="d-flex align-items-baseline gap-3">
    <span class="h4 text-success fw-semibold mb-0">
      {{ get_currency() }} {{ number_format($product->discount_price, 2) }}
    </span>
    <span class="h6 text-muted text-decoration-line-through mb-0">
      {{ get_currency() }} {{ number_format($product->price, 2) }}
    </span>
  </div>
@else
  <p class="h4 text-success fw-semibold mb-2">
    {{ get_currency() }} {{ number_format($product->price, 2) }}
  </p>
@endif

          {{-- Status & Shop --}}
          <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="badge bg-success bg-opacity-10 text-success">
              <i class="fa-solid fa-store me-1"></i>
              <a href="{{ route('shop.show', $product->shop) }}" class="text-success text-decoration-none">
                {{ $product->shop->name }}
              </a>
            </span>
            @if($product->stock > 0)
              <span class="badge bg-primary bg-opacity-10 text-primary">In Stock</span>
            @else
              <span class="badge bg-danger bg-opacity-10 text-danger">Out of Stock</span>
            @endif
          </div>

          {{-- Quick actions --}}
          <div class="d-flex flex-wrap gap-2 mb-4">
            <form method="POST" action="{{ route('favorites.toggle') }}">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <button class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Add to favourites">
                <i class="fa-regular fa-heart{{ $isFavorited ? ' text-danger fa-solid' : '' }}"></i>
              </button>
            </form>
            <button class="btn btn-outline-secondary" @click="share" data-bs-toggle="tooltip" title="Copy link">
              <i class="fa-solid fa-share-nodes"></i>
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#offerModal">
              <i class="fa-solid fa-hand-holding-dollar me-1"></i>Offer
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#messageModal">
              <i class="fa-regular fa-comments me-1"></i>Message
            </button>
          </div>

          {{-- Description --}}
          <div class="mb-4 text-muted small">{!! $product->description !!}</div>

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

          @if($product->type !== 'service')
            {{-- Physical or Digital Product --}}
            <div class="border rounded-4 p-4 bg-light-subtle">
              {{-- Quantity --}}
              <div class="mb-3 d-flex align-items-center gap-2">
                <span class="fw-semibold">Qty</span>
                <button class="btn btn-outline-secondary btn-sm"
                        @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1">−</button>
                <input type="text" class="form-control text-center" style="width:60px" :value="qty" readonly>
                <button class="btn btn-outline-secondary btn-sm" @click="qty++">+</button>
              </div>

              {{-- Shipping --}}
              @if($product->shippingProfiles->count())
                <div class="mb-3">
                  <label class="form-label fw-semibold">Shipping</label>
                  <select class="form-select" x-model="shippingProfileId">
                    @foreach($product->shippingProfiles as $profile)
                      <option value="{{ $profile->id }}">
                        {{ $profile->name }} – {{ get_currency() }} {{ number_format($profile->base_rate, 2) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              @endif

              {{-- Add to Cart / Buy Now --}}
              <div class="d-grid gap-2">
                <form method="POST" action="{{ route('cart.add') }}" @submit.prevent="busy = true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" :value="qty">
                  <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                  <button type="submit" class="btn btn-success btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-cart-plus me-1"></i>Add to Cart
                  </button>
                </form>

                <form method="POST" action="{{ route('cart.buy') }}" @submit.prevent="busy = true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" :value="qty">
                  <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                  <button type="submit" class="btn btn-primary btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-bolt me-1"></i>Buy Now
                  </button>
                </form>
              </div>
            </div>
          @else
            {{-- Service Listing --}}
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
        </div>
      </div>
    </div>
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
@endsection

@push('styles')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
      integrity="sha384-PU0QFv1kXlz9BM/UX5EwyV/ivxVMolZTUsjoeetfYxNdUswzqnMHipjInu6bcVCc"
      crossorigin="anonymous">
<style>
  .thumb.active,
  .thumb:hover { border:2px solid #198754!important }
  .carousel-inner img { transition:.4s }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha384-xKDJcyOgCjL2mK9ZcYnmQgSJvMREh4baN4GckSbnREV7mY4T0kT2LSpJxErL8xP8"
        crossorigin="anonymous">
</script>
<script>
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
  document.addEventListener('DOMContentLoaded', () => AOS.init({ duration:800, once:true }));
</script>
@endpush
