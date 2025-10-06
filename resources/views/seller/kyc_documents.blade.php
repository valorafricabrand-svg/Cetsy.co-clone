@extends('layouts.app')

@section('title','KYC - Step 2 of 2')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h2 class="h5 mb-0">KYC Verification</h2>
          <span class="text-muted">Step 2 of 2</span>
        </div>
        <div class="card-body">
          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger" role="alert">
              <strong>There were problems with your submission:</strong>
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="mb-3">
            <div class="alert alert-info text-dark border" role="alert">
              <strong class="text-dark">Heads up:</strong> Accepted formats: PDF/JPG/PNG for ID, JPG/PNG for selfie. Max 2MB each.
            </div>
          </div>

          <form action="{{ route('seller.kyc.documents.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- Hidden Step 1 fields (fallback if session is lost) --}}
            <input type="hidden" name="first_name" value="{{ $step1['first_name'] ?? ($kyc->first_name ?? '') }}">
            <input type="hidden" name="last_name" value="{{ $step1['last_name'] ?? ($kyc->last_name ?? '') }}">
            <input type="hidden" name="email" value="{{ $step1['email'] ?? ($kyc->email ?? (auth()->user()->email ?? '')) }}">
            <input type="hidden" name="phone" value="{{ $step1['phone'] ?? ($kyc->phone ?? '') }}">
            <input type="hidden" name="id_type" value="{{ $step1['id_type'] ?? ($kyc->id_type ?? '') }}">
            <input type="hidden" name="id_number" value="{{ $step1['id_number'] ?? ($kyc->id_number ?? '') }}">

            <div class="mb-3">
              <label for="id_front" class="form-label">ID Front (PDF/JPG/PNG)</label>
              <input class="form-control" type="file" id="id_front" name="id_front" accept=".pdf,.jpg,.jpeg,.png" required>
              <div class="form-text">Clear image of the front side.</div>
              <div class="mt-2">
                <img id="preview-id_front" alt="ID front preview" style="display:none;max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
              </div>
              @error('id_front')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label for="id_back" class="form-label">ID Back (PDF/JPG/PNG)</label>
              <input class="form-control" type="file" id="id_back" name="id_back" accept=".pdf,.jpg,.jpeg,.png" required>
              <div class="form-text">Clear image of the back side.</div>
              <div class="mt-2">
                <img id="preview-id_back" alt="ID back preview" style="display:none;max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
              </div>
              @error('id_back')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label for="selfie" class="form-label">Selfie</label>
              <input class="form-control" type="file" id="selfie" name="selfie" accept=".jpg,.jpeg,.png" required>
              <div class="form-text">Hold your ID next to your face.</div>
              <div class="mt-2">
                <img id="preview-selfie" alt="Selfie preview" style="display:none;max-height:120px;border:1px solid #e5e7eb;border-radius:6px;">
              </div>
              @error('selfie')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-between">
              <a class="btn btn-outline-secondary" href="{{ route('seller.kyc.info') }}">Back</a>
              <button class="btn btn-primary" type="submit">Submit KYC</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
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
