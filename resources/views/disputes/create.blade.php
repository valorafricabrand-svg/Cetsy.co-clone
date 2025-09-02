@extends('layouts.app')

@section('title', 'Create New Dispute')

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New Dispute</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($error)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $error }}
                        </div>
                    @endif

                

                    <form action="{{ route('disputes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Order Selection -->
                        <div class="mb-3">
                            <label for="order_id" class="form-label">Select Order *</label>
                            @if($order)
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <div class="form-control-plaintext">
                                    <strong>Order #{{ $order->order_number }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $order->items->count() }} item(s) - 
                                        Total: ${{ number_format($order->total_amount, 2) }}
                                    </small>
                                </div>
                            @else
                                <select name="order_id" id="order_id" class="form-select @error('order_id') is-invalid @enderror" required>
                                    <option value="">Select an order...</option>
                                    @foreach(auth()->user()->orders()->where('status', '!=', 'cancelled')->get() as $userOrder)
                                        <option value="{{ $userOrder->id }}" {{ old('order_id') == $userOrder->id ? 'selected' : '' }}>
                                            Order #{{ $userOrder->order_number }} - 
                                            {{ $userOrder->items->count() }} item(s) - 
                                            ${{ number_format($userOrder->total_amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('order_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <!-- Dispute Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Dispute Type *</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select dispute type...</option>
                                @foreach($disputeTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" 
                                placeholder="Please provide a detailed description of the issue..." required>{{ old('description') }}</textarea>
                            <div class="form-text">
                                Be specific about what went wrong and provide as much detail as possible.
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Evidence Upload -->
                        <div class="mb-3">
                            <label for="evidence" class="form-label">Supporting Evidence</label>
                            <input type="file" name="evidence[]" id="evidence" class="form-control @error('evidence.*') is-invalid @enderror" 
                                multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <div class="form-text">
                                Upload relevant documents, screenshots, or photos. Max 10MB per file. 
                                Supported formats: JPG, PNG, PDF, DOC, DOCX
                            </div>
                            @error('evidence.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Important Information -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Important Information</h6>
                            <ul class="mb-0">
                                <li>All communications must be conducted through Cetsy's messaging system</li>
                                <li>Disputes will be reviewed within 5 minutes</li>
                                <li>You have 7 days to appeal a resolution decision</li>
                                <li>Provide clear evidence to support your case</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('disputes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Disputes
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Dispute
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File size validation
    const fileInput = document.getElementById('evidence');
    const maxSize = 10 * 1024 * 1024; // 10MB

    fileInput.addEventListener('change', function() {
        const files = this.files;
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                alert(`File "${files[i].name}" is too large. Maximum size is 10MB.`);
                this.value = '';
                return;
            }
        }
    });

    // Character counter for description
    const description = document.getElementById('description');
    const maxLength = 2000;
    
    description.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        const counter = this.parentNode.querySelector('.form-text');
        if (remaining < 100) {
            counter.innerHTML = `<span class="text-${remaining < 0 ? 'danger' : 'warning'}">${remaining} characters remaining</span>`;
        }
    });
});
</script>
@endpush
@endsection
