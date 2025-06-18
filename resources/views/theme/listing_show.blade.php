{{-- resources/views/products/show.blade.php --}}
@extends('theme.layouts.main')

@section('main')
  <div class="container py-5" x-data="{ qty: 1, busy: false }">

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
        <div id="productCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($product->media as $i => $media)
              <div class="carousel-item @if($i===0) active @endif">
                <img
                  src="{{ asset('storage/'.$media->url) }}"
                  class="d-block w-100 rounded shadow-sm"
                  alt="{{ $product->name }}"
                >
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
          <div class="d-flex justify-content-center gap-2">
            @foreach($product->media as $i => $media)
              <img
                src="{{ asset('storage/'.$media->url) }}"
                class="img-thumbnail @if($i===0) border-primary @endif"
                style="width:75px; height:75px; object-fit:cover; cursor:pointer;"
                data-bs-target="#productCarousel"
                data-bs-slide-to="{{ $i }}"
              >
            @endforeach
          </div>
        @endif
      </div>

      {{-- Details & Add to Cart --}}
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold">{{ $product->name }}</h1>
        <p class="h4 text-success fw-semibold mb-3">
          KES {{ number_format($product->price, 2) }}
        </p>
        <p class="mb-4">{!! $product->description !!}</p>

        <p>
          <strong>Category:</strong>
          @if($product->category)
            <a
              href="{{ route('category.show', $product->category->slug) }}"
              class="badge bg-success text-decoration-none"
            >{{ $product->category->name }}</a>
          @else
            <span class="badge bg-secondary">Uncategorized</span>
          @endif
        </p>

        <p>
          <strong>Shop:</strong>
          <a
            href="{{ route('shop.show', $product->shop) }}"
            class="text-success"
          >{{ $product->shop->name }}</a>
        </p>

        <div class="d-flex align-items-center mb-4">
        
         

             <form action="{{ route('cart.add') }}" method="POST" class="d-flex">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">Add to Cart</button>
                            </form>
                            <form action="{{ route('cart.buy') }}" method="POST" class="d-flex">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-outline-primary btn-lg shadow-sm">Buy Now</button>
                            </form>




        </div>
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
