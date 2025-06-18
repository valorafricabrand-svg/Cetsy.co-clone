@extends('layouts.app')

@section('title', 'KYC Management')

@section('content')
<div class="content">
    {{-- Return to Admin Button (when impersonating) --}}
    @if(session('impersonating'))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-user-secret me-2"></i>
                    <strong>Admin Impersonation Active</strong>
                    <br>
                    <small>You are currently logged in as {{ auth()->user()->name }} (Seller)</small>
                </div>
                <a href="{{ route('admin.return-from-impersonation') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Return to Admin
                </a>
            </div>
        </div>
    @endif

    <style>
        .text-primary, .btn-outline-primary, .bg-success, .btn-primary, .badge.bg-success {
            color: #fff !important;
            background-color: #27b105 !important;
            border-color: #27b105 !important;
        }
        .btn-outline-primary {
            color: #27b105 !important;
            background-color: #fff !important;
            border-color: #27b105 !important;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: #27b105 !important;
            color: #fff !important;
        }
        .fa-sync-alt, .fa-box-open {
            color: #27b105 !important;
        }
        .badge.bg-success {
            background-color: #27b105 !important;
        }
    </style>
    <div class="row gx-4 gy-4">
        <div class="col-12">

            {{-- Page Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="h5 fw-semibold mb-0">General Report</h2>
                <a href="#" class="text-primary d-flex align-items-center">
                    <i class="fas fa-sync-alt me-2"></i> Reload Data
                </a>
            </div>

    {{-- Summary Cards --}}
<div class="row gy-4">
    {{-- Total Orders --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('seller.orders.index') }}" class="text-decoration-none text-dark">
            <div class="card shadow-sm border-0 hover-shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-credit-card fa-xl text-warning"></i>
                    </div>
                    <div class="fs-4 fw-semibold">{{ $total_orders }}</div>
                    <div class="text-muted small">Total Orders</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Total Products --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('products.index', ['layout' => 'side-menu']) }}" class="text-decoration-none text-dark">
            <div class="card shadow-sm border-0 hover-shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-box-open fa-xl text-info"></i>
                    </div>
                    <div class="fs-4 fw-semibold">{{ $total_products }}</div>
                    <div class="text-muted small">Total Products</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Wallet Balance --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('wallet.index') }}" class="text-decoration-none text-dark">
            <div class="card shadow-sm border-0 hover-shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-wallet fa-xl text-success"></i>
                    </div>
                    <div class="fs-4 fw-semibold">${{ number_format(wallet(), 2) }}</div>
                    <div class="text-muted small">Wallet Balance</div>
                </div>
            </div>
        </a>
    </div>
</div>


            {{-- Top Products Table --}}
            <div class="mt-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 fw-semibold mb-0">Top Products</h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Image</th>
                                <th scope="col">Product Name</th>
                                <th scope="col" class="text-center">Stock</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Created On</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                            @php
                        
                            $original =  asset('images/default.jpg');
                            $thumb = asset('images/default-thumb.jpg');
                            @endphp
                                <tr>
                                    <td style="width: 60px;">
                                        <img src="{{ $thumb }}" alt="{{ $product->name }}" class="rounded-circle" width="40" height="40" title="Uploaded at {{ $product->created_at }}">
                                    </td>
                                    <td>
                                        <a href="{{ url('seller.editProduct', $product->id) }}" target="_blank" class="fw-semibold text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $product->stock_quantity }}</td>
                                    <td class="text-center">
                                        @if ($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="text-center">{{ $product->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ url('seller.editProduct', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ url('seller.deleteProduct', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No top products available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
