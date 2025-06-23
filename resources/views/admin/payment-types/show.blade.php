@extends('layouts.app')

@section('title', 'Payment Type Details')

@section('content')
<div class="content">
    <div class="container-xxl">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Payment Type Details</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.payment-types.edit', $paymentType->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
                <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Payment Types
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Type Information</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">ID</dt>
                            <dd class="col-sm-9">{{ $paymentType->id }}</dd>

                            <dt class="col-sm-3">Name</dt>
                            <dd class="col-sm-9">
                                <strong>{{ $paymentType->name }}</strong>
                            </dd>

                            <dt class="col-sm-3">Description</dt>
                            <dd class="col-sm-9">
                                {{ $paymentType->description ?: 'No description provided' }}
                            </dd>

                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9">
                                <span class="badge {{ $paymentType->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($paymentType->status) }}
                                </span>
                            </dd>

                            <dt class="col-sm-3">Created</dt>
                            <dd class="col-sm-9">{{ $paymentType->created_at->format('d M Y, h:i A') }}</dd>

                            <dt class="col-sm-3">Last Updated</dt>
                            <dd class="col-sm-9">{{ $paymentType->updated_at->format('d M Y, h:i A') }}</dd>
                        </dl>
                    </div>
                </div>

                
            </div>

            <div class="col-md-4">
                <!-- Image Section -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Type Image</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($paymentType->image)
                            <img src="{{ asset('storage/' . $paymentType->image) }}" 
                                 alt="{{ $paymentType->name }}" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 200px;">
                            <div class="mt-2">
                                <small class="text-muted">Image uploaded on {{ \Carbon\Carbon::parse($paymentType->updated_at)->format('d M Y') }}</small>
                            </div>
                        @else
                            <div class="py-4">
                                <i class="fas fa-image text-muted mb-2" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0">No image uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.payment-types.edit', $paymentType->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Payment Type
                            </a>
                            <form action="{{ route('admin.payment-types.destroy', $paymentType->id) }}" method="POST" class="d-grid">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this payment type? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i>Delete Payment Type
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection 