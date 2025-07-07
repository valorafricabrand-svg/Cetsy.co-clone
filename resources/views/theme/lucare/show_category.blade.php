{{-- resources/views/categories/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $category->name . ' – Lucare Category')

@section('main')
    {{-- ────────── Category Banner ────────── --}}
    <div class="position-relative bg-cover bg-center"
         style="background-image:url('{{ $category->image
            ? asset('storage/' . $category->image)
            : asset('assets/images/default-category-beauty.jpg') }}'); height:300px;">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-75 d-flex align-items-center justify-content-center">
            <div class="text-center text-white px-3">
                <h1 class="display-5 fw-bold">{{ $category->name }}</h1>
                <p class="lead mb-0">
                    {{ $category->description
                        ?? 'Explore our curated selection of skincare, cosmetics & wellness essentials.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ────────── Products Grid ────────── --}}
    <section class="py-5 bg-light">
        <div class="container">
            {{-- Header with “Browse all” link --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 fw-bold mb-0">Products in {{ $category->name }}</h2>
                <a href="{{ route('products.index') }}" class="text-primary text-decoration-none">
                    Browse All Products
                </a>
            </div>

            @if ($products->count())
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">
                                {{-- Cover image (4×3) --}}
                                <a href="{{ route('products.show', $product) }}" class="ratio ratio-4x3 rounded-top overflow-hidden">
                                    @if ($img = $product->media->first())
                                        <img src="{{ asset('storage/' . $img->url) }}"
                                             alt="{{ $product->name }}"
                                             class="w-100 h-100 object-fit-cover">
                                    @else
                                        <img src="{{ asset('assets/images/placeholder-beauty.svg') }}"
                                             alt="No image available"
                                             class="w-100 h-100 object-fit-cover">
                                    @endif
                                </a>

                                {{-- Card body --}}
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h6 mb-1 text-truncate">
                                        <a href="{{ route('products.show', $product) }}"
                                           class="text-dark text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </h3>

                                    <p class="fw-bold text-primary mb-3">
                                        KES {{ number_format($product->price, 2) }}
                                    </p>

                                    {{-- View Product --}}
                                    <div class="mt-auto">
                                        <a href="{{ route('products.show', $product) }}"
                                           class="btn btn-outline-primary w-100 d-flex justify-content-center align-items-center gap-2"
                                           aria-label="View {{ $product->name }}">
                                            <span>View Product</span>
                                            <i class="fas fa-eye"></i>
                                        </a>
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
                <div class="alert alert-info shadow-sm text-center">
                    No products found in this category. Please check back soon!
                </div>
            @endif
        </div>
    </section>
@endsection
