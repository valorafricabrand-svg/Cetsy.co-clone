{{-- resources/views/products/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

{{-- SEO-friendly page title --}}
@section('title', 'Lucare – Shop Skincare, Cosmetics & Wellness')

@section('main')
    {{-- ────────── Header / Hero ────────── --}}
    <section class="bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-2 text-white">All Products</h1>
            <p class="lead mb-0">
                Discover Lucare’s curated selection of skincare, cosmetics, and wellness essentials—delivered nationwide.
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
                            <a href="{{ route('listing.show', $product) }}" class="ratio ratio-4x3 rounded-top overflow-hidden">
                                @if ($img = $product->media->first())
                                    <img
                                        src="{{ asset('storage/' . $img->url) }}"
                                        alt="{{ $product->name }}"
                                        class="w-100 h-100 object-fit-cover">
                                @else
                                    <img
                                        src="{{ asset('assets/images/placeholder-beauty.svg') }}"
                                        alt="No image available"
                                        class="w-100 h-100 object-fit-cover">
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

                                <p class="fw-bold text-primary mb-3">
                                    {{ money($product->price) }}
                                </p>

                                <div class="mt-auto">
                                    <a href="{{ route('listing.show', $product) }}"
                                       class="btn btn-outline-primary w-100 d-flex justify-content-center align-items-center gap-2"
                                       aria-label="View {{ $product->name }}">
                                        <span>View Product</span>
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
                        <p class="mb-0">No products available right now. Please check back soon!</p>
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

