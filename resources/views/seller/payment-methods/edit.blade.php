@extends('layouts.app')

@section('title', 'Edit Payment Method')

@section('content')
<div class="content">
    <div class="container-xxl">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Payment Method</h2>
            <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Payment Methods
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Edit Payment Method Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('seller.payment-methods.update', $paymentMethod->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Payment Type Selection -->
                            <div class="mb-4">
                                <label for="payment_type_id" class="form-label">Payment Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_type_id') is-invalid @enderror" 
                                        id="payment_type_id" name="payment_type_id" required>
                                    <option value="">Select a payment type</option>
                                    @foreach($paymentTypes as $paymentType)
                                        <option value="{{ $paymentType->id }}" 
                                                {{ old('payment_type_id', $paymentMethod->payment_type_id) == $paymentType->id ? 'selected' : '' }}>
                                            {{ $paymentType->name }}
                                            @if($paymentType->description)
                                                - {{ $paymentType->description }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Choose the type of payment method you want to use
                                </small>
                            </div>

                            <!-- Account Name -->
                            <div class="mb-3">
                                <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                       id="account_name" name="account_name" 
                                       value="{{ old('account_name', $paymentMethod->account_name) }}" 
                                       placeholder="Enter the account holder name" required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    The name that appears on the account (e.g., John Doe)
                                </small>
                            </div>

                            <!-- Account Number -->
                            <div class="mb-4">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" name="account_number" 
                                       value="{{ old('account_number', $paymentMethod->account_number) }}" 
                                       placeholder="Enter account number, email, or phone number" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    This could be a bank account number, PayPal email, phone number, etc.
                                </small>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Bank Transfer / SWIFT (Optional)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="bank_name">Bank Name</label>
                                    <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name', $paymentMethod->bank_name) }}">
                                    @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="bank_country">Bank Country</label>
                                    <input type="text" class="form-control @error('bank_country') is-invalid @enderror" id="bank_country" name="bank_country" value="{{ old('bank_country', $paymentMethod->bank_country) }}" placeholder="e.g. US">
                                    @error('bank_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="bank_currency">Currency</label>
                                    <input type="text" class="form-control @error('bank_currency') is-invalid @enderror" id="bank_currency" name="bank_currency" value="{{ old('bank_currency', $paymentMethod->bank_currency) }}" placeholder="e.g. USD">
                                    @error('bank_currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="bank_routing_number">Routing/Sort Code</label>
                                    <input type="text" class="form-control @error('bank_routing_number') is-invalid @enderror" id="bank_routing_number" name="bank_routing_number" value="{{ old('bank_routing_number', $paymentMethod->bank_routing_number) }}">
                                    @error('bank_routing_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="swift_bic">SWIFT/BIC</label>
                                    <input type="text" class="form-control @error('swift_bic') is-invalid @enderror" id="swift_bic" name="swift_bic" value="{{ old('swift_bic', $paymentMethod->swift_bic) }}">
                                    @error('swift_bic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="iban">IBAN</label>
                                    <input type="text" class="form-control @error('iban') is-invalid @enderror" id="iban" name="iban" value="{{ old('iban', $paymentMethod->iban) }}">
                                    @error('iban')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="bank_address">Bank Address</label>
                                    <input type="text" class="form-control @error('bank_address') is-invalid @enderror" id="bank_address" name="bank_address" value="{{ old('bank_address', $paymentMethod->bank_address) }}">
                                    @error('bank_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Wise (Optional)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="wise_email">Wise Email</label>
                                    <input type="email" class="form-control @error('wise_email') is-invalid @enderror" id="wise_email" name="wise_email" value="{{ old('wise_email', $paymentMethod->wise_email) }}">
                                    @error('wise_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="wise_recipient_id">Wise Recipient ID</label>
                                    <input type="text" class="form-control @error('wise_recipient_id') is-invalid @enderror" id="wise_recipient_id" name="wise_recipient_id" value="{{ old('wise_recipient_id', $paymentMethod->wise_recipient_id) }}">
                                    @error('wise_recipient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="wise_profile_id">Wise Profile ID</label>
                                    <input type="text" class="form-control @error('wise_profile_id') is-invalid @enderror" id="wise_profile_id" name="wise_profile_id" value="{{ old('wise_profile_id', $paymentMethod->wise_profile_id) }}">
                                    @error('wise_profile_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Payment Method
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Current Payment Method Info -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Current Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Payment Type:</strong><br>
                            <span class="text-muted">{{ $paymentMethod->paymentType->name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Account Name:</strong><br>
                            <span class="text-muted">{{ $paymentMethod->account_name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Account Number:</strong><br>
                            <code class="text-muted">{{ $paymentMethod->account_number }}</code>
                        </div>
                        <div class="mb-3">
                            <strong>Created:</strong><br>
                            <span class="text-muted">{{ $paymentMethod->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div>
                            <strong>Last Updated:</strong><br>
                            <span class="text-muted">{{ $paymentMethod->updated_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Type Preview -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Selected Payment Type</h5>
                    </div>
                    <div class="card-body">
                        <div id="paymentTypePreview" class="text-center py-4" style="display: none;">
                            <div id="paymentTypeImage" class="mb-3">
                                <!-- Payment type image will be displayed here -->
                            </div>
                            <h6 id="paymentTypeName" class="mb-2"></h6>
                            <p id="paymentTypeDescription" class="text-muted small mb-0"></p>
                        </div>
                        <div id="noPaymentTypeSelected" class="text-center py-4">
                            <i class="fas fa-credit-card text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">Select a payment type to see details</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('seller.payment-methods.show', $paymentMethod->id) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>View Details
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
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentTypeSelect = document.getElementById('payment_type_id');
    const paymentTypePreview = document.getElementById('paymentTypePreview');
    const noPaymentTypeSelected = document.getElementById('noPaymentTypeSelected');
    const paymentTypeImage = document.getElementById('paymentTypeImage');
    const paymentTypeName = document.getElementById('paymentTypeName');
    const paymentTypeDescription = document.getElementById('paymentTypeDescription');

    // Payment types data
    const paymentTypes = @json($paymentTypes);

    paymentTypeSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        
        if (selectedValue) {
            const selectedPaymentType = paymentTypes.find(pt => pt.id == selectedValue);
            
            if (selectedPaymentType) {
                // Update preview content
                if (selectedPaymentType.image) {
                    paymentTypeImage.innerHTML = `
                        <img src="/storage/${selectedPaymentType.image}" 
                             alt="${selectedPaymentType.name}" 
                             class="img-fluid rounded" 
                             style="max-height: 80px;">
                    `;
                } else {
                    paymentTypeImage.innerHTML = `
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-credit-card text-white" style="font-size: 2rem;"></i>
                        </div>
                    `;
                }
                
                paymentTypeName.textContent = selectedPaymentType.name;
                paymentTypeDescription.textContent = selectedPaymentType.description || 'No description available';
                
                // Show preview, hide placeholder
                paymentTypePreview.style.display = 'block';
                noPaymentTypeSelected.style.display = 'none';
            }
        } else {
            // Hide preview, show placeholder
            paymentTypePreview.style.display = 'none';
            noPaymentTypeSelected.style.display = 'block';
        }
    });

    // Trigger change event on page load if there's a selected value
    if (paymentTypeSelect.value) {
        paymentTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush 
