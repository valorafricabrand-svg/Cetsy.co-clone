{{-- resources/views/listings/index.blade.php --}}
@extends('theme.layouts.main')

@section('main')
  <!-- Listings Header -->
  <div class="bg-success py-5">
    <div class="container text-center text-white">
      <h1 class="display-5 fw-bold text-white">All Handmade Listings</h1>
      <p class="lead">Browse through all available products from our talented sellers.</p>
    </div>
  </div>

  <!-- Product Listings -->
  <section class="py-5 bg-light">
    <div class="container">
      <div class="row g-4">
        @forelse($products as $product)
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0">
              <a href="{{ route('listing.show', $product) }}" class="text-decoration-none">
                @if($img = $product->media->first())
                  <img 
                    src="{{ asset('storage/'.$img->url) }}" 
                    class="card-img-top" 
                    alt="{{ $product->name }}" 
                    style="height:200px; object-fit:cover;"
                  >
                @else
                  <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:200px;">
                    No Image
                  </div>
                @endif
              </a>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate">{{ $product->name }}</h5>
                <p class="card-text text-success fw-bold mb-3">
                  KES {{ number_format($product->price, 2) }}
                </p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                  <a 
                    href="{{ route('listing.show', $product) }}" 
                    class="small text-muted text-decoration-none"
                  >
                    View Details
                  </a>
                  <form 
                    method="POST" 
                    action="{{ route('cart.add') }}" 
                    x-data="{ busy: false }" 
                    @submit="busy = true"
                    class="m-0"
                  >
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button 
                      type="submit" 
                      class="btn btn-success btn-sm rounded-circle p-2" 
                      :disabled="busy"
                      aria-label="Add {{ $product->name }} to cart"
                    >
                      <i class="fas fa-cart-plus"></i>
                      <span x-show="busy" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center text-muted">
            No products found at this time.
          </div>
        @endforelse
      </div>

      <!-- Pagination -->
      @if($products->hasPages())
        <div class="mt-4 d-flex justify-content-center">
          {{ $products->links('pagination::bootstrap-5') }}
        </div>
      @endif
    </div>
  </section>
@endsection
