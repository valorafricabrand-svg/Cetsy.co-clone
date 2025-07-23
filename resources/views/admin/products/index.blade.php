{{-- resources/views/admin/products/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">{{ __('Product Listings') }}</h2>
@endsection

@section('content')
<div class="content">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Search and Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Products</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="Search by name or description...">
                </div>
                <div class="col-md-3">
                    <label for="shop_id" class="form-label">Filter by Shop</label>
                    <select id="shop_id" name="shop_id" class="form-select">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Filter by Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="physical" {{ request('type') == 'physical' ? 'selected' : '' }}>Physical</option>
                        <option value="digital" {{ request('type') == 'digital' ? 'selected' : '' }}>Digital</option>
                        <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Service</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table --}}
    @if($products->count())
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">All Products ({{ $products->total() }})</h5>
                    <div class="text-muted small">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Product</th>
                            <th scope="col">Shop</th>
                            <th scope="col">Type</th>
                            <th scope="col">Price</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <th scope="row">
                                    {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
                                </th>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->media->count() > 0)
                                            <img src="{{ Storage::url($product->media->first()->url) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded me-3" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ Str::limit($product->name, 40) }}</h6>
                                            <small class="text-muted">
                                                @if($product->category)
                                                    {{ $product->category->name }}
                                                @else
                                                    No Category
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($product->shop)
                                        <span class="fw-medium">{{ $product->shop->name }}</span>
                                    @else
                                        <span class="text-muted">No Shop</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($product->type) }}</span>
                                </td>
                                <td>
                                    @if($product->discount_price)
                                        <div>
                                            <span class="text-danger fw-bold">KES {{ number_format($product->discount_price) }}</span>
                                            <br>
                                            <small class="text-muted text-decoration-line-through">
                                                KES {{ number_format($product->price) }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="fw-bold">KES {{ number_format($product->price) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->type === 'physical')
                                        @if($product->stock > 0)
                                            <span class="badge bg-success">{{ $product->stock }}</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active == 1)
                                        <span class="badge bg-success">Active</span>
                                    @elseif($product->is_active == 2)
                                        <span class="badge bg-warning">Suspended</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $product->created_at->format('M d, Y') }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline-secondary me-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- Status Management Button --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            title="Manage Status"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#statusModal{{ $product->id }}">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
          

              {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted mb-0">
                    @if(request('search') || request('shop_id') || request('type'))
                        Try adjusting your search criteria or filters.
                    @else
                        There are no products in the system yet.
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>

{{-- Status Management Modals --}}
@foreach($products as $product)
    <div class="modal fade" id="statusModal{{ $product->id }}" tabindex="-1" aria-labelledby="statusModalLabel{{ $product->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel{{ $product->id }}">
                        Manage Product Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">{{ $product->name }}</h6>
                            <p class="text-muted mb-3">Select the new status for this product:</p>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="inactive{{ $product->id }}" value="0" {{ $product->is_active == 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="inactive{{ $product->id }}">
                                    <span class="badge bg-secondary me-2">Inactive</span>
                                    Product will not be visible to customers
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="active{{ $product->id }}" value="1" {{ $product->is_active == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="active{{ $product->id }}">
                                    <span class="badge bg-success me-2">Active</span>
                                    Product will be visible and purchasable
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="suspended{{ $product->id }}" value="2" {{ $product->is_active == 2 ? 'checked' : '' }}>
                                <label class="form-check-label" for="suspended{{ $product->id }}">
                                    <span class="badge bg-warning me-2">Suspended</span>
                                    Product is temporarily unavailable (admin action required)
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Current Status:</strong> 
                            @if($product->is_active == 1)
                                <span class="badge bg-success">Active</span>
                            @elseif($product->is_active == 2)
                                <span class="badge bg-warning">Suspended</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
