{{-- resources/views/products/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' – Lucare Product Details')

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
             .catch(() => toast('Unable to copy link', 'danger'));
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
      {{-- ─────────── Gallery ─────────── --}}
      <div class="col-lg-7" data-aos="fade-right">
        <div id="productCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden mb-3"
             data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($product->media as $i => $media)
              <div class="carousel-item @if($i === 0) active @endif">
                <img src="{{ asset('storage/'.$media->url) }}"
                     class="d-block w-100"
                     style="aspect-ratio:4/3;object-fit:cover"
                     alt="{{ $product->name }}">
              </div>
            @endforeach
          </div>

          @if($product->media->count() > 1)
            <button class="carousel-control-prev" type="button"
                    data-bs-target="#productCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button"
                    data-bs-target="#productCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          @endif
        </div>

        @if($product->media->count() > 1)
          <div class="d-flex gap-2 flex-wrap justify-content-center">
            @foreach($product->media as $i => $media)
              <img src="{{ asset('storage/'.$media->url) }}"
                   class="img-thumbnail p-0 thumb @if($i===0) border-primary @endif"
                   style="width:70px;height:70px;object-fit:cover;cursor:pointer"
                   data-bs-target="#productCarousel"
                   data-bs-slide-to="{{ $i }}"
                   title="View image {{ $i+1 }}">
            @endforeach
          </div>
        @endif
      </div>

      {{-- ─────────── Details & Purchase ─────────── --}}
      <div class="col-lg-5" data-aos="fade-left">
        <div class="position-lg-sticky top-lg-6">
          <h1 class="h2 fw-bold">{{ $product->name }}</h1>
          <p class="h4 text-primary fw-semibold mb-3">
            KES {{ number_format($product->price, 2) }}
          </p>

          {{-- Quick share --}}
          <div class="mb-4">
            <button class="btn btn-outline-secondary me-2" @click="share">
              <i class="fa-solid fa-share-nodes me-1"></i> Share
            </button>
          </div>

          {{-- Description --}}
          <div class="mb-4 text-muted small">
            {!! $product->description !!}
          </div>

          {{-- Category --}}
          <p class="mb-4">
            <strong class="me-1">Category:</strong>
            @if($product->category)
              <a href="{{ route('categories.show', $product->category->slug) }}"
                 class="badge bg-primary bg-opacity-10 text-primary text-decoration-none">
                {{ $product->category->name }}
              </a>
            @else
              <span class="badge bg-secondary">Uncategorized</span>
            @endif
          </p>

          {{-- Purchase Block --}}
          @if($product->type !== 'digital')
            <div class="border rounded-4 p-4 bg-light-subtle mb-4">
              {{-- Qty & Shipping --}}
              <div class="mb-3 d-flex align-items-center gap-2">
                <span class="fw-semibold">Qty</span>
                <button class="btn btn-outline-secondary btn-sm"
                        @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1">−</button>
                <input type="text" class="form-control text-center" style="width:60px" :value="qty" readonly>
                <button class="btn btn-outline-secondary btn-sm" @click="qty++">+</button>
              </div>

              @if($product->shippingProfiles->count())
                <div class="mb-3">
                  <label class="form-label fw-semibold">Shipping</label>
                  <select class="form-select" x-model="shippingProfileId">
                    @foreach($product->shippingProfiles as $profile)
                      <option value="{{ $profile->id }}">
                        {{ $profile->name }} – KES {{ number_format($profile->base_rate,2) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              @endif

              <div class="d-grid gap-2">
                <form method="POST" action="{{ route('cart.add') }}" @submit.prevent="busy=true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" :value="{{ $product->id }}">
                  <input type="hidden" name="quantity" :value="qty">
                  <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                  <button class="btn btn-primary btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-cart-plus me-1"></i> Add to Cart
                  </button>
                </form>
                <form method="POST" action="{{ route('cart.buy') }}" @submit.prevent="busy=true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" :value="{{ $product->id }}">
                  <input type="hidden" name="quantity" :value="qty">
                  <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                  <button class="btn btn-success btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-bolt me-1"></i> Buy Now
                  </button>
                </form>
              </div>
            </div>
          @else
            {{-- Digital Download --}}
            <div class="card border-primary border-start-4 shadow-sm mb-4">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px">
                  <i class="fa-solid fa-download fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1 fw-semibold text-primary">Instant Download</h6>
                  <p class="mb-0 small text-muted">
                    This is a digital product. You’ll receive download links immediately after checkout.
                  </p>
                </div>
              </div>
            </div>
            <div class="border rounded-4 p-4 bg-light-subtle mb-4">
              <div class="d-grid gap-2">
                <form method="POST" action="{{ route('cart.add') }}" @submit.prevent="busy=true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" value="1">
                  <button class="btn btn-primary btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-cart-plus me-1"></i> Add to Cart
                  </button>
                </form>
                <form method="POST" action="{{ route('cart.buy') }}" @submit.prevent="busy=true; $el.submit()">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" value="1">
                  <button class="btn btn-success btn-lg w-100" :disabled="busy">
                    <i class="fa-solid fa-bolt me-1"></i> Buy Now
                  </button>
                </form>
              </div>
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
      integrity="sha384-PU0QFv1kXlz9BM/UX5EwyV/ivxVMolZTUsjoeetfYxNdUswzqnMHipjInu6bcVCc"
      crossorigin="anonymous">
<style>
  .thumb.active,
  .thumb:hover { border: 2px solid #0d6efd !important; }
  .carousel-inner img { transition: .4s; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha384-xKDJcyOgCjL2mK9ZcYnmQgSJvMREh4baN4GckSbnREV7mY4T0kT2LSpJxErL8xP8"
        crossorigin="anonymous"></script>
<script>
  const toast = (msg, type = 'success') => {
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');
    el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(el);
    new bootstrap.Toast(el, { delay: 3000 }).show();
  };
  document.addEventListener('DOMContentLoaded', () => AOS.init({ duration: 800, once: true }));
</script>
@endpush
