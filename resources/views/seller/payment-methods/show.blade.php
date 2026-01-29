@extends('layouts.app')

@section('title', 'Payment Method Details')

@section('content')
<div class="content">
    <div class="container-xxl">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Payment Method Details</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('seller.payment-methods.edit', $paymentMethod->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
                <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Payment Methods
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Payment Method Information -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Method Information</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">ID</dt>
                            <dd class="col-sm-9">{{ $paymentMethod->id }}</dd>

                            <dt class="col-sm-3">Payment Type</dt>
                            <dd class="col-sm-9">
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
                            </dd>

                            <dt class="col-sm-3">Account Name</dt>
                            <dd class="col-sm-9">
                                <strong>{{ $paymentMethod->account_name }}</strong>
                            </dd>

                            <dt class="col-sm-3">Account Number</dt>
                            <dd class="col-sm-9">
                                <code class="fs-6">{{ $paymentMethod->account_number }}</code>
                            </dd>

                            @if($paymentMethod->bank_name || $paymentMethod->bank_country || $paymentMethod->bank_currency || $paymentMethod->bank_routing_number || $paymentMethod->swift_bic || $paymentMethod->iban || $paymentMethod->bank_address)
                                <dt class="col-sm-3">Bank Details</dt>
                                <dd class="col-sm-9">
                                    <div class="small text-muted">
                                        @if($paymentMethod->bank_name)<div><strong>Bank:</strong> {{ $paymentMethod->bank_name }}</div>@endif
                                        @if($paymentMethod->bank_country)<div><strong>Country:</strong> {{ $paymentMethod->bank_country }}</div>@endif
                                        @if($paymentMethod->bank_currency)<div><strong>Currency:</strong> {{ $paymentMethod->bank_currency }}</div>@endif
                                        @if($paymentMethod->bank_routing_number)<div><strong>Routing/Sort:</strong> {{ $paymentMethod->bank_routing_number }}</div>@endif
                                        @if($paymentMethod->swift_bic)<div><strong>SWIFT/BIC:</strong> {{ $paymentMethod->swift_bic }}</div>@endif
                                        @if($paymentMethod->iban)<div><strong>IBAN:</strong> {{ $paymentMethod->iban }}</div>@endif
                                        @if($paymentMethod->bank_address)<div><strong>Bank Address:</strong> {{ $paymentMethod->bank_address }}</div>@endif
                                    </div>
                                </dd>
                            @endif

                            @if($paymentMethod->wise_email || $paymentMethod->wise_recipient_id || $paymentMethod->wise_profile_id)
                                <dt class="col-sm-3">Wise Details</dt>
                                <dd class="col-sm-9">
                                    <div class="small text-muted">
                                        @if($paymentMethod->wise_email)<div><strong>Email:</strong> {{ $paymentMethod->wise_email }}</div>@endif
                                        @if($paymentMethod->wise_recipient_id)<div><strong>Recipient ID:</strong> {{ $paymentMethod->wise_recipient_id }}</div>@endif
                                        @if($paymentMethod->wise_profile_id)<div><strong>Profile ID:</strong> {{ $paymentMethod->wise_profile_id }}</div>@endif
                                    </div>
                                </dd>
                            @endif

                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9">
                                <span class="badge bg-success">Active</span>
                            </dd>

                            <dt class="col-sm-3">Created</dt>
                            <dd class="col-sm-9">{{ $paymentMethod->created_at->format('d M Y, h:i A') }}</dd>

                            <dt class="col-sm-3">Last Updated</dt>
                            <dd class="col-sm-9">{{ $paymentMethod->updated_at->format('d M Y, h:i A') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Payment Type Details -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Type Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Type Name:</strong><br>
                                <span class="text-muted">{{ $paymentMethod->paymentType->name }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong><br>
                                <span class="badge {{ $paymentMethod->paymentType->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($paymentMethod->paymentType->status) }}
                                </span>
                            </div>
                        </div>
                        @if($paymentMethod->paymentType->description)
                            <div class="mt-3">
                                <strong>Description:</strong><br>
                                <span class="text-muted">{{ $paymentMethod->paymentType->description }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Payment Type Image -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Type Image</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($paymentMethod->paymentType->image)
                            <img src="{{ asset('storage/' . $paymentMethod->paymentType->image) }}" 
                                 alt="{{ $paymentMethod->paymentType->name }}" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 200px;">
                            <div class="mt-2">
                                <small class="text-muted">{{ $paymentMethod->paymentType->name }}</small>
                            </div>
                        @else
                            <div class="py-4">
                                <i class="fas fa-credit-card text-muted mb-2" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0">No image available</p>
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
                            <a href="{{ route('seller.payment-methods.edit', $paymentMethod->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Payment Method
                            </a>
                            <form action="{{ route('seller.payment-methods.destroy', $paymentMethod->id) }}" 
                                  method="POST" 
                                  class="d-grid"
                                  onsubmit="return confirm('Are you sure you want to delete this payment method? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>Delete Payment Method
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-success">Payment Processing</h6>
                            <p class="small text-muted mb-0">
                                This payment method will be used to receive payments from your customers.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-primary">Security</h6>
                            <p class="small text-muted mb-0">
                                Your payment information is securely stored and encrypted.
                            </p>
                        </div>
                        <div>
                            <h6 class="text-warning">Important</h6>
                            <p class="small text-muted mb-0">
                                Keep your account information up to date for smooth transactions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection 
