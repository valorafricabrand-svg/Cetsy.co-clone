@extends('layouts.app')

@section('title', 'My Buyers')

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
        .fa-users, .fa-eye {
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
                <h2 class="h5 fw-semibold mb-0">My Buyers</h2>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">
                        <i class="fas fa-users me-2 text-info" style="color: #027333;"></i>
                        {{ $buyers->count() }} Total Buyers
                    </span>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row gy-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-success">{{ $buyers->count() }}</div>
                            <div class="text-muted small">Total Buyers</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-shopping-cart fa-xl text-success"></i>
                            </div>
                            <div class="text-success">{{ $buyers->sum('total_orders') }}</div>
                            <div class="text-muted small">Total Orders</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-dollar-sign fa-xl text-warning"></i>
                            </div>
                            <div class="text-warning">${{ number_format($buyers->sum('total_spent'), 2) }}</div>
                            <div class="text-muted small">Total Revenue</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-chart-line fa-xl text-info"></i>
                            </div>
                            <div class="text-info">${{ $buyers->count() > 0 ? number_format($buyers->sum('total_spent') / $buyers->count(), 2) : '0.00' }}</div>
                            <div class="text-muted small">Avg. Customer Value</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Buyers Table --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h3 class="h6 fw-semibold mb-0">Buyer List</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Buyer</th>
                                    <th scope="col" class="text-center">Total Orders</th>
                                    <th scope="col" class="text-center">Total Spent</th>
                                    <th scope="col" class="text-center">First Order</th>
                                    <th scope="col" class="text-center">Last Order</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($buyers as $buyerData)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <img src="{{ $buyerData['customer']->get_gravatar(40) }}" 
                                                         alt="{{ $buyerData['customer']->name }}" 
                                                         class="rounded-circle" 
                                                         width="40" 
                                                         height="40">
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $buyerData['customer']->name }}</div>
                                                    <div class="text-muted small">{{ $buyerData['customer']->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $buyerData['total_orders'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold text-success">${{ number_format($buyerData['total_spent'], 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $buyerData['first_order_date']->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $buyerData['last_order_date']->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('seller.buyers.show', $buyerData['customer']->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-users fa-3x text-muted"></i>
                                            </div>
                                            <h5>No Buyers Yet</h5>
                                            <p class="mb-0">You haven't received any orders from buyers yet.</p>
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