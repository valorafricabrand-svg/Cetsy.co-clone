@extends('layouts.app')
@section('title', 'Product Favorites')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Product Favorites</h1>
            <p class="text-muted mb-0">See which customers have added your products to their favorites</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-6 px-3 py-2">
                <i class="bi bi-heart-fill me-1"></i>{{ $favorites->count() }} total favorites
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if($favorites->isEmpty())
        <div class="card shadow border-0">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <i class="bi bi-heart text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No favorites yet</h4>
                    <p class="text-muted mb-0">When customers add your products to their favorites, they'll appear here.</p>
                </div>
            </div>
        </div>
    @else
        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow border-0 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-box-seam fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $favoritesByProduct->count() }}</h5>
                                <p class="mb-0 opacity-75">Products Favorited</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow border-0 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-people fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $favorites->unique('user_id')->count() }}</h5>
                                <p class="mb-0 opacity-75">Unique Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow border-0 bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-heart fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $favorites->where('created_at', '>=', now()->subDays(7))->count() }}</h5>
                                <p class="mb-0 opacity-75">This Week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Favorites by Product --}}
        <div class="card shadow border-0">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-heart-fill me-2 text-danger"></i>
                    <h5 class="mb-0">Favorites by Product</h5>
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($favoritesByProduct as $productId => $productFavorites)
                    @php
                        $product = $productFavorites->first()->product;
                        $favoriteCount = $productFavorites->count();
                        $uniqueBuyers = $productFavorites->unique('user_id')->count();
                    @endphp
                    <div class="product-favorites-section border-bottom">
                        <div class="p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="product-info d-flex align-items-center flex-grow-1">
                                    @if($product->media->first())
                                        <img src="{{ asset('storage/' . $product->media->first()->url) }}" 
                                             alt="{{ $product->name }}" 
                                             class="rounded me-3" 
                                             style="width:60px;height:60px;object-fit:cover;">
                                    @else
                                        <div class="bg-light border rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width:60px;height:60px;">
                                            <i class="bi bi-box text-secondary"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ $product->name }}</h6>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-primary">{{ shop_currency() }} {{ number_format($product->price, 2) }}</span>
                                            <span class="badge bg-success">{{ $favoriteCount }} favorites</span>
                                            <span class="badge bg-info">{{ $uniqueBuyers }} unique customers</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="product-actions">
                                    <a href="{{ route('products.show', $product->slug ?? $product->id) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Product
                                    </a>
                                </div>
                            </div>

                            {{-- Buyers who favorited this product --}}
                            <div class="buyers-section">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-people me-1"></i>Customers who favorited this product
                                </h6>
                                <div class="row g-3">
                                    @foreach($productFavorites->take(6) as $favorite)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="buyer-card border rounded p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width:40px;height:40px;">
                                                        {{ strtoupper(substr($favorite->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-semibold">{{ $favorite->user->name ?? 'Unknown Customer' }}</h6>
                                                        <div class="text-muted small">
                                                            <i class="bi bi-envelope me-1"></i>
                                                            {{ \Illuminate\Support\Str::limit($favorite->user->email ?? '', 25) }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            {{ $favorite->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($productFavorites->count() > 6)
                                        <div class="col-12">
                                            <div class="text-center">
                                                <span class="text-muted">
                                                    +{{ $productFavorites->count() - 6 }} more customers
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Favorites Timeline --}}
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    <h5 class="mb-0">Recent Favorites</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="timeline-container">
                    @foreach($favorites->take(10) as $favorite)
                        <div class="timeline-item border-bottom p-3">
                            <div class="d-flex align-items-center">
                                <div class="timeline-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width:40px;height:40px;">
                                    <i class="bi bi-heart-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong>{{ $favorite->user->name ?? 'Unknown Customer' }}</strong>
                                            <span class="text-muted">favorited</span>
                                            <strong>{{ $favorite->product->name }}</strong>
                                        </div>
                                        <div class="text-muted small">
                                            {{ $favorite->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-envelope me-1"></i>{{ $favorite->user->email ?? 'No email' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .empty-state {
        padding: 2rem;
    }
    .product-favorites-section:last-child {
        border-bottom: none !important;
    }
    .buyer-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .buyer-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .timeline-item {
        transition: background-color 0.2s;
    }
    .timeline-item:hover {
        background-color: #f8f9fa;
    }
    .timeline-icon {
        flex-shrink: 0;
    }
    .avatar-sm {
        font-weight: 600;
        font-size: 0.9rem;
    }
    @media (max-width: 768px) {
        .product-info {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .product-actions {
            margin-top: 1rem;
        }
        .buyers-section .row {
            margin: 0;
        }
        .buyers-section .col-md-6 {
            padding: 0.5rem;
        }
    }
</style>
@endpush
@endsection
