{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
    <div class="row g-4">
        {{-- ───────── Image Carousel ───────── --}}
        <div class="col-lg-6">
            @if ($product->media->count())
                <div id="productCarousel" class="carousel slide border rounded-4 shadow-sm" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($product->media as $i => $media)
                            <div class="carousel-item @if($i===0) active @endif">
                                <img src="{{ asset('storage/'.$media->url) }}"
                                     class="d-block w-100 rounded-4"
                                     style="max-height:400px;object-fit:cover;"
                                     alt="{{ $product->name }}">
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            @else
                <div class="border rounded-4 d-flex align-items-center justify-content-center text-muted"
                     style="height:300px;">
                    No product image available.
                </div>
            @endif
        </div>

        {{-- ───────── Details ───────── --}}
        <div class="col-lg-6">
            <h2 class="mb-2 d-flex align-items-center gap-2">
                {{ $product->name }}
                @unless($product->is_active)
                    <span class="badge bg-warning text-dark">Inactive</span>
                @endunless
            </h2>

            <p class="text-muted">
                Type: <span class="badge bg-primary">{{ ucfirst($product->type) }}</span>
            </p>

            {{-- Price --}}
            <div class="mb-2">
                @if ($product->discount_price)
                    <span class="h4 text-danger me-2">KES {{ number_format($product->discount_price) }}</span>
                    <span class="text-muted text-decoration-line-through">KES {{ number_format($product->price) }}</span>
                @else
                    <span class="h4">KES {{ number_format($product->price) }}</span>
                @endif
            </div>

            {{-- Listing dates if active --}}
            @if ($product->is_active)
                <p class="text-muted small mb-3">
                    Listed on: {{$product->listing_paid_at }}<br>
                    Next due:  {{ $product->next_due_date }}
                </p>
            @endif

            {{-- Stock --}}
            @if (!is_null($product->stock))
                <p><strong>Stock:</strong> {{ $product->stock }}</p>
            @endif

            {{-- Pay listing fee if inactive --}}
            @unless ($product->is_active)
                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    This listing is inactive. Pay the listing fee to publish it.
                </div>

                @php
                    $fee = $product->category?->listing_fee ?? 0;
                @endphp

                <form method="POST" action="{{ route('products.pay-fee', $product) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-success">
                        Pay Listing Fee (KES {{ number_format($fee, 2) }})
                    </button>
                </form>
            @endunless

            {{-- Action buttons --}}
            <div class="mt-4">
                <a href="{{ route('products.edit', $product) }}"
                   class="btn btn-outline-secondary me-2">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if ($product->description)
        <div class="mt-5">
            <h5>Description</h5>
            <div class="border rounded p-3 bg-light">
                {!! $product->description !!}
            </div>
        </div>
    @endif
</div>
@endsection
