@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h4 mb-0">{{ __('Product Details') }}</h2>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Products
            </a>
        </div>
    </div>
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

    <div class="row g-4">
        <!-- Product Images -->
        <div class="col-lg-6">
            @if($product->media->count())
                <div id="productCarousel" class="carousel slide border rounded-4 shadow-sm" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->media as $key => $media)
                            <div class="carousel-item @if($key === 0) active @endif">
                                <img src="{{ Storage::url($media->url) }}" 
                                     class="d-block w-100 rounded-4" 
                                     style="max-height: 400px; object-fit: cover;" 
                                     alt="Product Image">
                            </div>
                        @endforeach
                    </div>
                    @if($product->media->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="border rounded-4 d-flex align-items-center justify-content-center text-muted" style="height: 300px;">
                    <div class="text-center">
                        <i class="fas fa-image fa-3x mb-3"></i>
                        <p>No product images available</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Product Information -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 class="card-title mb-0">{{ $product->name }}</h2>
                        
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-3">
                        @if($product->is_active == 1)
                            <span class="badge bg-success fs-6">Active</span>
                        @elseif($product->is_active == 2)
                            <span class="badge bg-warning fs-6">Suspended</span>
                        @else
                            <span class="badge bg-secondary fs-6">Inactive</span>
                        @endif
                    </div>

                    <!-- Basic Info -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Product Type</small>
                            <p class="mb-0"><span class="badge bg-info">{{ ucfirst($product->type) }}</span></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Category</small>
                            <p class="mb-0">
                                @if($product->category)
                                    {{ $product->category->name }}
                                @else
                                    <span class="text-muted">No Category</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="mb-3">
                        <small class="text-muted">Pricing</small>
                        <div class="d-flex align-items-center">
                            @if($product->discount_price)
                                <span class="h4 text-danger me-2">{{ money($product->discount_price) }}</span>
                                <span class="text-muted text-decoration-line-through">{{ money($product->price) }}</span>
                            @else
                                <span class="h4">{{ money($product->price) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Information -->
                    @if($product->type === 'physical')
                        <div class="mb-3">
                            <small class="text-muted">Stock Level</small>
                            <p class="mb-0">
                                @if($product->stock > 0)
                                    <span class="badge bg-success">{{ $product->stock }} units available</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Shop Information -->
                    <div class="mb-3">
                        <small class="text-muted">Shop</small>
                        @if($product->shop)
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    @if($product->shop->logo)
                                        <img src="{{ Storage::url($product->shop->logo) }}" 
                                             alt="{{ $product->shop->name }}" 
                                             class="rounded" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 30px; height: 30px;">
                                            <i class="fas fa-store text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="mb-0 fw-medium">{{ $product->shop->name }}</p>
                                    <small class="text-muted">
                                        @if($product->shop->location)
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $product->shop->location }}
                                        @endif
                                        @if($product->shop->phone)
                                            <br><i class="fas fa-phone me-1"></i>{{ $product->shop->phone }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> View Shop Details
                                </a>
                            </div>
                        @else
                            <p class="mb-0">
                                <span class="text-muted">No Shop Assigned</span>
                            </p>
                        @endif
                    </div>

                    <!-- Status Management -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#statusModal">
                            <i class="fas fa-cog me-1"></i> Manage Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Product Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Description</h6>
                            @if($product->description)
                                <div class="border rounded p-3 bg-light">
                                    {!! $product->description !!}
                                </div>
                            @else
                                <p class="text-muted">No description available</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Product Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $product->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $product->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Product ID:</strong></td>
                                    <td>{{ $product->id }}</td>
                                </tr>
                                @if($product->digitalFiles->count() > 0)
                                    <tr>
                                        <td><strong>Digital Files:</strong></td>
                                        <td>{{ $product->digitalFiles->count() }} file(s)</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Information Section -->
    @if($product->shop)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Shop Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                @if($product->shop->logo)
                                    <img src="{{ Storage::url($product->shop->logo) }}" 
                                         alt="{{ $product->shop->name }}" 
                                         class="img-fluid rounded" 
                                         style="max-width: 150px;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 150px; height: 100px;">
                                        <i class="fas fa-store fa-2x text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">{{ $product->shop->name }}</h6>
                                        @if($product->shop->description)
                                            <p class="text-muted">{{ Str::limit($product->shop->description, 150) }}</p>
                                        @endif
                                        
                                        <div class="mb-2">
                                            @if($product->shop->is_verified)
                                                <span class="badge bg-success me-2">
                                                    <i class="fas fa-check-circle me-1"></i>Verified
                                                </span>
                                            @endif
                                            @if($product->shop->is_active)
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-store me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-store me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Contact Information</h6>
                                        <table class="table table-sm">
                                            @if($product->shop->email)
                                                <tr>
                                                    <td><i class="fas fa-envelope text-muted me-2"></i></td>
                                                    <td>{{ $product->shop->email }}</td>
                                                </tr>
                                            @endif
                                            @if($product->shop->phone)
                                                <tr>
                                                    <td><i class="fas fa-phone text-muted me-2"></i></td>
                                                    <td>{{ $product->shop->phone }}</td>
                                                </tr>
                                            @endif
                                            @if($product->shop->location)
                                                <tr>
                                                    <td><i class="fas fa-map-marker-alt text-muted me-2"></i></td>
                                                    <td>{{ $product->shop->location }}</td>
                                                </tr>
                                            @endif
                                            @if($product->shop->website)
                                                <tr>
                                                    <td><i class="fas fa-globe text-muted me-2"></i></td>
                                                    <td>
                                                        <a href="{{ $product->shop->website }}" target="_blank" class="text-decoration-none">
                                                            {{ $product->shop->website }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                        
                                        <div class="mt-3">
                                            <a href="{{ route('admin.sellers.login-as', $product->shop->user_id) }}" 
                                               class="btn btn-sm btn-outline-primary me-2"
                                               onclick="return confirm('Are you sure you want to login as this seller?')">
                                                <i class="fas fa-user-secret me-1"></i> Login as Seller
                                            </a>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Digital Files Section (if applicable) -->
    @if($product->type === 'digital' && $product->digitalFiles->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Digital Files</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Source</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->digitalFiles as $file)
                                        <tr>
                                            <td>{{ $file->filename }}</td>
                                            <td>{{ $file->isExternalUrl() ? 'External link' : 'Uploaded file' }}</td>
                                            <td>
                                                @if($file->isExternalUrl() && $file->external_url)
                                                    <a href="{{ $file->external_url }}" target="_blank" rel="noopener">{{ preg_replace('/^www\\./i', '', (string) parse_url($file->external_url, PHP_URL_HOST)) }}</a>
                                                @elseif($file->filesize)
                                                    {{ number_format($file->filesize / 1024 / 1024, 2) }} MB
                                                @else
                                                    {{ $file->filetype ?: '-' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Status Management Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">
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
                            <input class="form-check-input" type="radio" name="status" id="inactive" value="0" {{ $product->is_active == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="inactive">
                                <span class="badge bg-secondary me-2">Inactive</span>
                                Product will not be visible to customers
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="active" value="1" {{ $product->is_active == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                <span class="badge bg-success me-2">Active</span>
                                Product will be visible and purchasable
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="suspended" value="2" {{ $product->is_active == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="suspended">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteProduct() {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection
