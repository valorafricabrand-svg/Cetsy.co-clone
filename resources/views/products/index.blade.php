{{-- resources/views/products/index.blade.php (or similar) --}}
@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">My Listings</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add New Listing
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($products->count())
        <div class="row g-4">
            @foreach($products as $product)
                <div class="col-md-6 col-lg-4">
                    {{-- position-relative lets us place the badge --}}
                    <div class="card position-relative h-100 shadow-sm border-0 rounded-4">

                        {{-- Inactive badge --}}
                        @if(! $product->is_active)
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">
                                Inactive
                            </span>
                        @endif

                        {{-- Image --}}
                        @if($img = $product->media->first())
                            <img src="{{ asset('storage/' . $img->url) }}"
                                 class="card-img-top rounded-top-4"
                                 style="height:220px;object-fit:cover;"
                                 alt="{{ $product->name }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="height:220px;">
                                <span class="text-muted">No Image</span>
                            </div>
                        @endif

                        {{-- Body --}}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ Str::limit($product->name, 40) }}</h5>
                            <p class="mb-2 text-muted small">
                                {{ ucfirst($product->type) }}
                                @if(!is_null($product->stock))
                                    | Stock: {{ $product->stock }}
                                @endif
                            </p>

                            {{-- Price --}}
                            <p class="fw-bold mb-3">
                                @if($product->discount_price)
                                    <span class="text-danger me-2">
                                        {{ get_currency() }} {{ number_format($product->discount_price) }}
                                    </span>
                                    <span class="text-muted text-decoration-line-through">
                                        {{ get_currency() }} {{ number_format($product->price) }}
                                    </span>
                                @else
                                    <span>{{ get_currency() }} {{ number_format($product->price) }}</span>
                                @endif
                            </p>

                            {{-- Actions --}}
                            <div class="mt-auto d-flex justify-content-between">
                                <a href="{{ route('products.show', $product) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                                <a href="{{ route('products.edit', $product) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info rounded-3 text-center py-4">
            You haven’t listed any products yet.
            <div class="mt-2">
                <a href="{{ route('products.create') }}" class="btn btn-sm btn-success rounded-pill">
                    <i class="fas fa-plus-circle me-1"></i> Create Your First Product
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
