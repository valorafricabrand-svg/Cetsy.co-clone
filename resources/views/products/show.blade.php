@extends('layouts.app')

@section('content')
<div class="content">
    <div class="row g-4">
        <div class="col-lg-6">
            <!-- Image Carousel -->
            @if($product->media->count())
                <div id="productCarousel" class="carousel slide border rounded-4 shadow-sm" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->media as $key => $media)
                            <div class="carousel-item @if($key === 0) active @endif">
                                <img src="{{ asset('storage/' . $media->url) }}" class="d-block w-100 rounded-4" style="max-height: 400px; object-fit: cover;" alt="Product Image">
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            @else
                <div class="border rounded-4 d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
                    No product image available.
                </div>
            @endif
        </div>

        <div class="col-lg-6">
            <h2 class="mb-2">{{ $product->name }}</h2>
            <p class="text-muted">Type: <span class="badge bg-primary">{{ ucfirst($product->type) }}</span></p>

            <div class="mb-3">
                @if($product->discount_price)
                    <span class="h4 text-danger me-2">KES {{ number_format($product->discount_price) }}</span>
                    <span class="text-muted text-decoration-line-through">KES {{ number_format($product->price) }}</span>
                @else
                    <span class="h4">KES {{ number_format($product->price) }}</span>
                @endif
            </div>

            @if($product->stock !== null)
                <p><strong>Stock:</strong> {{ $product->stock }}</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($product->description)
        <div class="mt-5">
            <h5>Description</h5>
            <div class="border rounded p-3 bg-light">
                {!! $product->description !!}
            </div>
        </div>
    @endif
</div>
@endsection
