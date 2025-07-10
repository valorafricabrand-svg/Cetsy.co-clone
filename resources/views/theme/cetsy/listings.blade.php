{{-- resources/views/listings/index.blade.php --}}
@extends('layouts.frontapp')

{{-- SEO-friendly page title --}}
@section('title', 'Marketplace – Products, Services & Digital Goods')

@section('main')
    {{-- ────────── Header / Hero ────────── --}}
    <section class="bg-success text-white py-5">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-2 text-white">All Listings</h1>
            <p class="lead mb-0">
                Browse our global marketplace for physical products, professional services, and instant digital downloads.
            </p>
        </div>
    </section>

    {{-- ────────── Product Grid ────────── --}}
    <section class="pb-5 bg-light">
        <div class="container">
            <div class="row g-4">
                @forelse ($products as $product)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm">

                            {{-- Cover 4 × 3 image --}}
                            <a href="{{ route('listing.show', $product) }}" class="ratio ratio-4x3">
                                @if ($img = $product->media->first())
                                    <img
                                        src="{{ asset( $product->featured_image ?? 'storage/' . $img->url ) }}"
                                        alt="{{ $product->name }}"
                                        class="object-fit-cover rounded-top">
                                @else
                                    <img
                                        src="{{ asset('assets/img/placeholder.svg') }}"
                                        alt="No image available"
                                        class="object-fit-cover rounded-top">
                                @endif
                            </a>

                            {{-- Card body --}}
                            <div class="card-body d-flex flex-column">
                                <h2 class="h6 mb-1 text-truncate">
                                    <a href="{{ route('listing.show', $product) }}"
                                       class="text-dark text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                </h2>

                                @if(!empty($product->discount_price) && $product->discount_price < $product->price)
  <div class="d-flex align-items-baseline gap-3 mb-3">
    <span class="fw-bold text-success">
      {{ get_currency() }} {{ number_format($product->discount_price, 2) }}
    </span>
    <span class="text-muted text-decoration-line-through">
      {{ get_currency() }} {{ number_format($product->price, 2) }}
    </span>
  </div>
@else
  <p class="fw-bold text-success mb-3">
    {{ get_currency() }} {{ number_format($product->price, 2) }}
  </p>
@endif


                                <div class="mt-auto">
                                    <a href="{{ route('listing.show', $product) }}"
                                       class="btn btn-outline-success w-100 d-flex justify-content-center align-items-center gap-2"
                                       aria-label="View {{ $product->name }}">
                                        <span>View Listing</span>
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Empty state --}}
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fas fa-box-open fa-2x mb-3"></i>
                        <p class="mb-0">No listings available right now. Please check back soon!</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($products->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>
@endsection
