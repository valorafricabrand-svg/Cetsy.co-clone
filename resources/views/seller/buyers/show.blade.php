@extends('layouts.app')

@section('title', 'Buyer Details - ' . $buyer->name)

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
        .fa-user, .fa-arrow-left {
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
                <div class="d-flex align-items-center">
                    <a href="{{ route('seller.buyers.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Buyers
                    </a>
                    <h2 class="h5 fw-semibold mb-0">Buyer Details</h2>
                </div>
            </div>

            {{-- Buyer Information Card --}}
            <div class="row gy-4 mb-4">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0">
                            <h3 class="h6 fw-semibold mb-0">Customer Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    <img src="{{ $buyer->get_gravatar(100) }}" 
                                         alt="{{ $buyer->name }}" 
                                         class="rounded-circle mb-3" 
                                         width="100" 
                                         height="100">
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-sm-6 mb-3">
                                            <label class="form-label text-muted small">Name</label>
                                            <div class="fw-semibold">{{ $buyer->name }}</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="form-label text-muted small">Email</label>
                                            <div class="fw-semibold">{{ $buyer->email }}</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="form-label text-muted small">Phone</label>
                                            <div class="fw-semibold">{{ $buyer->phone ?? 'Not provided' }}</div>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <label class="form-label text-muted small">Member Since</label>
                                            <div class="fw-semibold">{{ $buyer->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0">
                            <h3 class="h6 fw-semibold mb-0">Purchase Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="text-primary">{{ $orders->count() }}</div>
                                    <div class="text-muted small">Total Orders</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-success">${{ number_format($totalSpent, 2) }}</div>
                                    <div class="text-muted small">Total Spent</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-warning">${{ $orders->count() > 0 ? number_format($totalSpent / $orders->count(), 2) : '0.00' }}</div>
                                    <div class="text-muted small">Avg. Order Value</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-info">{{ $orders->where('created_at', '>=', now()->subDays(30))->count() }}</div>
                                    <div class="text-muted small">Orders (30 days)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orders Table --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h3 class="h6 fw-semibold mb-0">Order History</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Order ID</th>
                                    <th scope="col">Items</th>
                                    <th scope="col" class="text-center">Total</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-center">Date</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-semibold">#{{ $order->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @foreach($order->items as $item)
                                                    <div class="d-flex align-items-center mb-1">
                                                        <img src="{{ asset('images/default-thumb.jpg') }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             class="rounded me-2" 
                                                             width="30" 
                                                             height="30">
                                                        <div>
                                                            <div class="fw-semibold small">{{ $item->product->name }}</div>
                                                            <div class="text-muted small">Qty: {{ $item->quantity }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold text-success">${{ number_format($order->items->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $order->getStatusBadgeClass() }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $order->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('seller.orders.show', $order->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                                            </div>
                                            <h5>No Orders Found</h5>
                                            <p class="mb-0">This buyer hasn't placed any orders with your shop.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 