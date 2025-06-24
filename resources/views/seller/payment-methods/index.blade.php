@extends('layouts.app')

@section('title', 'Payment Methods')

@section('content')
<div class="content">
    <div class="container-xxl">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Payment Methods</h2>
            <a href="{{ route('seller.shops.show', $shop) }}" class="btn btn-outline-success">
                <i class="fas fa-arrow-left me-2"></i>Back to Shop
            </a>
            <a href="{{ route('seller.payment-methods.create') }}" class="btn btn-outline-success">
                <i class="fas fa-plus me-2"></i>Add Payment Method
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h5 class="mb-0">My Payment Methods</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Payment Type</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentMethods as $paymentMethod)
                            <tr>
                                <td>{{ $paymentMethod->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($paymentMethod->paymentType->image)
                                            <img src="{{ asset('storage/' . $paymentMethod->paymentType->image) }}" 
                                                 alt="{{ $paymentMethod->paymentType->name }}" 
                                                 class="rounded me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-credit-card text-white" style="font-size: 14px;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $paymentMethod->paymentType->name }}</strong>
                                            @if($paymentMethod->paymentType->description)
                                                <br><small class="text-muted">{{ $paymentMethod->paymentType->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $paymentMethod->account_name }}</td>
                                <td>
                                    <code>{{ $paymentMethod->account_number }}</code>
                                </td>
                                <td>{{ $paymentMethod->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('seller.payment-methods.show', $paymentMethod->id) }}" 
                                           class="btn btn-outline-secondary btn-sm" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('seller.payment-methods.edit', $paymentMethod->id) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('seller.payment-methods.destroy', $paymentMethod->id) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-credit-card mb-3" style="font-size: 3rem;"></i>
                                        <h5>No Payment Methods Found</h5>
                                        <p class="mb-3">You haven't added any payment methods yet.</p>
                                        <a href="{{ route('seller.payment-methods.create') }}" class="btn btn-outline-success">
                                            <i class="fas fa-plus me-2"></i>Add Your First Payment Method
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentMethods->hasPages())
                <div class="card-footer">
                    {{ $paymentMethods->links() }}
                </div>
            @endif
        </div>

        @if($paymentMethods->count() > 0)
            <div class="mt-4">
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>Payment Method Information
                    </h6>
                    <p class="mb-0">
                        These are the payment methods you've configured to receive payments from your customers. 
                        Make sure to keep your account information up to date for smooth transactions.
                    </p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection 