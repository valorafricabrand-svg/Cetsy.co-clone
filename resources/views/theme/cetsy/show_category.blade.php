{{-- resources/views/categories/show.blade.php --}}
@extends('layouts.frontapp')

{{-- Optional SEO title --}}
@section('title', $category->name . ' – Marketplace Category')

@section('main')
    {{-- ────────── Category Banner ────────── --}}
    <div class="position-relative bg-cover bg-center"
         style="background-image:url('{{ $category->image ? asset('storage/' . $category->image) : asset('assets/img/default-category.jpg') }}'); height:300px;">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-75 d-flex align-items-center justify-content-center">
            <div class="text-center text-white px-3">
                <h1 class="display-5 fw-bold text-white">{{ $category->name }}</h1>
                <p class="lead mb-0">
                    {{ $category->description ?? 'Explore a wide range of physical products, professional services and digital goods in this category.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ────────── Products Grid ────────── --}}
    <section class="py-5 bg-light">
        <div class="container">

            {{-- Header with “Browse all” link --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 fw-bold mb-0">Listings in {{ $category->name }}</h2>
                <a href="{{ route('products.index') }}" class="text-success text-decoration-none">
                    Browse All Listings
                </a>
            </div>

            @if ($products->count())
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">

                                {{-- Cover image (4 × 3) --}}
                                <a href="{{ route('listing.show', $product) }}" class="ratio ratio-4x3">
                                    @if ($img = $product->media->first())
                                        <img src="{{ asset('storage/' . $img->url) }}"
                                             alt="{{ $product->name }}"
                                             class="object-fit-cover rounded-top">
                                    @else
                                        <img src="{{ asset('assets/img/placeholder.svg') }}"
                                             alt="No image available"
                                             class="object-fit-cover rounded-top">
                                    @endif
                                </a>

                                {{-- Card body --}}
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h6 mb-1 text-truncate">
                                        <a href="{{ route('listing.show', $product) }}"
                                           class="text-dark text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </h3>

                                    <p class="fw-bold text-success mb-3">
                                        KES {{ number_format($product->price, 2) }}
                                    </p>

                                    {{-- Add-to-Cart --}}
                                    <div class="mt-auto">
                                        <form method="POST" action="{{ route('cart.add') }}">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">

                                            <button type="submit"
                                                    class="btn btn-outline-success w-100 d-flex justify-content-center align-items-center gap-2"
                                                    aria-label="Add {{ $product->name }} to cart">
                                                <span>Add to Cart</span>
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($products->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="alert alert-info">
                    No listings found in this category.
                </div>
            @endif
        </div>
    </section>
@endsection
