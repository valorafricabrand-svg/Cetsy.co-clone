@extends('theme.layouts.main')

@section('main')
<div class="container py-5" x-data="{ qty: 1, busy: false, shippingProfileId: {{ $product->shippingProfiles->firstWhere('is_default', true)->id ?? 'null' }} }">

  {{-- Flash Message --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {!! session('success') !!}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row g-5">
    {{-- Image Carousel --}}
    <div class="col-lg-6">
      <div id="productCarousel" class="carousel slide mb-4 shadow-sm rounded" data-bs-ride="carousel">
        <div class="carousel-inner rounded">
          @foreach($product->media as $i => $media)
            <div class="carousel-item @if($i===0) active @endif">
              <img
                src="{{ asset('storage/'.$media->url) }}"
                class="d-block w-100 rounded"
                alt="{{ $product->name }}"
                style="object-fit:cover; max-height:500px;"
              >
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
        <div class="d-flex justify-content-center gap-2 flex-wrap">
          @foreach($product->media as $i => $media)
            <img
              src="{{ asset('storage/'.$media->url) }}"
              class="img-thumbnail @if($i===0) border-success @endif"
              style="width:75px; height:75px; object-fit:cover; cursor:pointer;"
              data-bs-target="#productCarousel"
              data-bs-slide-to="{{ $i }}"
            >
          @endforeach
        </div>
      @endif
    </div>

    {{-- Product Details --}}
    <div class="col-lg-6">
      <h1 class="display-5 fw-bold mb-3">{{ $product->name }}</h1>

      <p class="h4 text-success fw-semibold mb-4">
        KES {{ number_format($product->price, 2) }}
      </p>

      <div class="mb-4 text-secondary fs-6">
        {!! $product->description !!}
      </div>

      <p class="mb-2">
        <strong>Category:</strong>
        @if($product->category)
          <a href="{{ route('category.show', $product->category->slug) }}" class="badge bg-success text-decoration-none">
            {{ $product->category->name }}
          </a>
        @else
          <span class="badge bg-secondary">Uncategorized</span>
        @endif
      </p>

      <p class="mb-4">
        <strong>Shop:</strong>
        <a href="{{ route('shop.show', $product->shop) }}" class="text-decoration-none text-success">
          {{ $product->shop->name }}
        </a>
      </p>

      <form action="{{ route('cart.add') }}" method="POST" class="mb-3" @submit.prevent="busy = true; $el.submit()">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="quantity" :value="qty">
        <input type="hidden" name="size_id" value="0">

        {{-- Quantity Selector --}}
        <div class="mb-3 d-flex align-items-center gap-2">
          <label for="quantity" class="form-label mb-0 fw-semibold">Quantity:</label>
          <button type="button" class="btn btn-outline-secondary" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1">-</button>
          <input type="text" id="quantity" class="form-control text-center" style="width: 70px;" :value="qty" readonly>
          <button type="button" class="btn btn-outline-secondary" @click="qty++">+</button>
        </div>

        {{-- Shipping Profile Selector --}}
        @if($product->shippingProfiles->count())
          <div class="mb-4">
            <label for="shipping_profile" class="form-label fw-semibold">Shipping Profile:</label>
            <select id="shipping_profile" name="shipping_profile_id" class="form-select" x-model="shippingProfileId" required>
              @foreach($product->shippingProfiles as $profile)
                <option value="{{ $profile->id }}">
                  {{ $profile->name }} - KES {{ number_format($profile->base_rate, 2) }}
                </option>
              @endforeach
            </select>
          </div>
        @endif

        <button type="submit" class="btn btn-success btn-lg shadow-sm px-4" :disabled="busy">
          <i class="fas fa-cart-plus me-2"></i> Add to Cart
        </button>
      </form>

      <form action="{{ route('cart.buy') }}" method="POST" @submit.prevent="busy = true; $el.submit()">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="quantity" :value="qty">
        <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
        <button type="submit" class="btn btn-outline-primary btn-lg shadow-sm px-4" :disabled="busy">
          <i class="fas fa-bolt me-2"></i> Buy Now
        </button>
      </form>
    </div>
  </div>
</div>

{{-- Bootstrap JS Bundle --}}
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-qQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
  crossorigin="anonymous"
></script>
@endsection
