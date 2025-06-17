{{-- resources/views/categories/show.blade.php --}}
@extends('theme.layouts.main')

@section('main')
  <!-- Category Banner -->
  <div 
    class="position-relative bg-cover bg-center" 
    style="background-image: url('{{ $category->image ? asset('storage/' . $category->image) : asset('images/default-category.jpg') }}'); height: 300px;"
  >
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-75 d-flex align-items-center justify-content-center">
      <div class="text-center text-white px-3">
        <h1 class="display-5 fw-bold">{{ $category->name }}</h1>
        <p class="mt-2 lead">
          {{ $category->description ?? 'Explore unique handmade treasures in this category.' }}
        </p>
      </div>
    </div>
  </div>

  <!-- Products Grid -->
  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0">Products in {{ $category->name }}</h2>
        <a href="{{ route('products.index') }}" class="text-success text-decoration-none">
          Browse All Products
        </a>
      </div>

      @if($products->count())
        <div class="row g-4">
          @foreach($products as $product)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
              <div class="card h-100 shadow-sm border-0">
                <a href="{{ route('listing.show', $product) }}" class="text-decoration-none">
                  @if($img = $product->media->first())
                    <img
                      src="{{ asset('storage/' . $img->url) }}"
                      alt="{{ $product->name }}"
                      class="card-img-top"
                      style="height:200px; object-fit:cover;"
                    >
                  @else
                    <div 
                      class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                      style="height:200px;"
                    >
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
          @endforeach
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
          <div class="mt-4 d-flex justify-content-center">
            {{ $products->links('pagination::bootstrap-5') }}
          </div>
        @endif

      @else
        <div class="alert alert-info">
          No products found in this category.
        </div>
      @endif
    </div>
  </section>
@endsection
