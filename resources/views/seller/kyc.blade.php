@extends('layouts.app')

@section('title','KYC Verification')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white border-0">
          <h2 class="h4 mb-0">KYC Verification</h2>
        </div>
        <div class="card-body">

          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if($kyc && $kyc->status !== 'rejected')
            <div class="d-flex align-items-center mb-3">
              <span class="me-2">Status:</span>
              <span class="badge @switch($kyc->status) @case('pending') bg-warning text-dark @break @case('approved') bg-success @break @case('needs_correction') bg-warning text-dark @break @default bg-secondary @break @endswitch">
                {{ ucfirst($kyc->status) }}
              </span>
            </div>

            @if($kyc->status === 'pending')
              <div class="alert alert-warning py-2 mb-3">
                Your KYC is under review.
              </div>
            @elseif($kyc->status === 'approved')
              <div class="alert alert-success py-2 mb-3">
                Your KYC is approved. You can now access all seller features.
              </div>
            @elseif($kyc->status === 'needs_correction')
              <div class="alert alert-warning py-2 mb-3">
                Action required: Your KYC needs corrections. Please review the notes below and resubmit.
              </div>
              <div class="mb-3">
                <a href="{{ route('seller.kyc.info') }}" class="btn btn-warning text-dark">
                  <i class="fas fa-edit me-1"></i> Fix and Resubmit
                </a>
              </div>
            @endif

            @if($kyc->admin_notes)
              <div class="alert alert-info py-2 mb-3">
                <strong>Admin notes:</strong> {{ $kyc->admin_notes }}
              </div>
            @endif
          @endif

          @if(!$kyc || $kyc->status === 'rejected')
            <form action="{{ route('seller.kyc.submit') }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="e.g., Alan" value="{{ old('first_name', $kyc->first_name ?? '') }}" required>
                @error('first_name')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="e.g., Smith" value="{{ old('last_name', $kyc->last_name ?? '') }}" required>
                @error('last_name')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $kyc->email ?? auth()->user()->email) }}" required readonly>
                <div class="form-text">Uses your account email. Contact support to change.</div>
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g., +2547XXXXXXXX" value="{{ old('phone', $kyc->phone ?? '') }}" required>
                @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="id_type" class="form-label">ID Type</label>
                @php $selectedIdType = old('id_type', $kyc->id_type ?? '') @endphp
                <select id="id_type" name="id_type" class="form-select" required>
                  <option value="" disabled {{ $selectedIdType ? '' : 'selected' }}>Select ID Type</option>
                  <option value="national_id" {{ $selectedIdType === 'national_id' ? 'selected' : '' }}>National ID</option>
                  <option value="passport" {{ $selectedIdType === 'passport' ? 'selected' : '' }}>Passport</option>
                  <option value="driver_license" {{ $selectedIdType === 'driver_license' ? 'selected' : '' }}>Driver's License</option>
                </select>
                @error('id_type')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="id_number" class="form-label">ID Number</label>
                <input type="text" id="id_number" name="id_number" class="form-control" placeholder="e.g., 12345678" value="{{ old('id_number', $kyc->id_number ?? '') }}" required>
                @error('id_number')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>

              <div class="mb-3">
                <label for="id_front" class="form-label">ID Front (PDF/JPG/PNG)</label>
                <input class="form-control" type="file" id="id_front" name="id_front" accept=".pdf,.jpg,.jpeg,.png" {{ $kyc?->id_front ? '' : 'required' }}>
                <div class="form-text">Max size 2MB. Clear image of the front side.</div>
                <div class="mt-2">
                  @if(!empty($kyc?->id_front))
                    <img id="preview-id_front" src="{{ Storage::url($kyc->id_front) }}" alt="ID front preview" style="max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
                  @else
                    <img id="preview-id_front" alt="ID front preview" style="display:none;max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
                  @endif
                </div>
                @error('id_front')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="id_back" class="form-label">ID Back (PDF/JPG/PNG)</label>
                <input class="form-control" type="file" id="id_back" name="id_back" accept=".pdf,.jpg,.jpeg,.png" {{ $kyc?->id_back ? '' : 'required' }}>
                <div class="form-text">Max size 2MB. Clear image of the back side.</div>
                <div class="mt-2">
                  @if(!empty($kyc?->id_back))
                    <img id="preview-id_back" src="{{ Storage::url($kyc->id_back) }}" alt="ID back preview" style="max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
                  @else
                    <img id="preview-id_back" alt="ID back preview" style="display:none;max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
                  @endif
                </div>
                @error('id_back')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="selfie" class="form-label">Selfie</label>
                <input class="form-control" type="file" id="selfie" name="selfie" accept=".jpg,.jpeg,.png" {{ $kyc?->selfie ? '' : 'required' }}>
                <div class="form-text">Max size 2MB. Hold your ID next to your face.</div>
                <div class="mt-2">
                  @if(!empty($kyc?->selfie))
                    <img id="preview-selfie" src="{{ Storage::url($kyc->selfie) }}" alt="Selfie preview" style="max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
                  @else
                    <img id="preview-selfie" alt="Selfie preview" style="display:none;max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
                  @endif
                </div>
                @error('selfie')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>

              <button class="btn btn-primary w-100" type="submit">Submit KYC</button>
            </form>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Minimal enhancement: scroll to errors if present
  document.addEventListener('DOMContentLoaded', function(){
    const firstError = document.querySelector('.text-danger');
    if (firstError) {
      firstError.scrollIntoView({behavior:'smooth', block:'center'});
    }

    // File previews
    function bindPreview(inputId, imgId) {
      const input = document.getElementById(inputId);
      const img = document.getElementById(imgId);
      if (!input || !img) return;
      input.addEventListener('change', function(e){
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        if (file.type.startsWith('image/')) {
          img.src = URL.createObjectURL(file);
          img.style.display = 'inline-block';
        } else {
          // Hide preview for non-image files (e.g., PDF)
          img.style.display = 'none';
          img.removeAttribute('src');
        }
      });
    }
    bindPreview('id_front','preview-id_front');
    bindPreview('id_back','preview-id_back');
    bindPreview('selfie','preview-selfie');
  });
</script>
@endpush

@endsection
