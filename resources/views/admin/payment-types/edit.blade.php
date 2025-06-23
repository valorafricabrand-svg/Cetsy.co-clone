@extends('layouts.app')

@section('title', 'Edit Payment Type')

@section('content')
<div class="content">
    <div class="container-xxl">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Payment Type</h2>
            <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Payment Types
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('admin.payment-types.update', $paymentType->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Name Field -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $paymentType->name) }}" 
                                       placeholder="Enter payment type name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description Field -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Enter payment type description">{{ old('description', $paymentType->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Field -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="active" {{ old('status', $paymentType->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $paymentType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Image Upload Field -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <div class="border rounded p-3 text-center">
                                    <!-- Current Image Display -->
                                    @if($paymentType->image)
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Current Image:</label>
                                            <img src="{{ asset('storage/' . $paymentType->image) }}" 
                                                 alt="{{ $paymentType->name }}" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px;">
                                        </div>
                                    @endif
                                    
                                    <div id="imagePreview" class="mb-2" style="display: none;">
                                        <label class="form-label text-muted">New Image Preview:</label>
                                        <img id="preview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 150px;">
                                    </div>
                                    
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    <small class="form-text text-muted">
                                        @if($paymentType->image)
                                            Leave empty to keep current image
                                        @else
                                            Upload an image for this payment type (optional)
                                        @endif
                                    </small>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Payment Type
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const preview = document.getElementById('preview');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
});
</script>
@endpush 