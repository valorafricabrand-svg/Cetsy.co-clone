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
            <div class="mb-4">
              <h5>Status:</h5>
              <span class="badge 
                @switch($kyc->status)
                  @case('pending') bg-warning text-dark @break
                  @case('approved') bg-success @break
                  @default bg-secondary @break
                @endswitch
              ">
                {{ ucfirst($kyc->status) }}
              </span>
            </div>

            @if($kyc->status === 'pending')
              <div class="alert alert-warning">
                Your KYC is under review.
              </div>
            @elseif($kyc->status === 'approved')
              <div class="alert alert-success">
                Your KYC is approved. You can now access all seller features.
              </div>
            @endif

            @if($kyc->admin_notes)
              <div class="card bg-light border-info mb-4">
                <div class="card-body">
                  <h6 class="card-title">Admin Notes</h6>
                  <p class="card-text">{{ $kyc->admin_notes }}</p>
                </div>
              </div>
            @endif
          @endif

          @if(!$kyc || $kyc->status === 'rejected')
            <form action="{{ route('seller.kyc.submit') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
              @csrf

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="first_name" class="form-label">First Name</label>
                  <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $kyc->first_name ?? '') }}" required>
                  <div class="invalid-feedback">Please enter your first name.</div>
                </div>
                <div class="col-md-6">
                  <label for="last_name" class="form-label">Last Name</label>
                  <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $kyc->last_name ?? '') }}" required>
                  <div class="invalid-feedback">Please enter your last name.</div>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $kyc->email ?? auth()->user()->email) }}" required>
                  <div class="invalid-feedback">Please enter a valid email.</div>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Phone</label>
                  <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $kyc->phone ?? '') }}" required>
                  <div class="invalid-feedback">Please enter your phone number.</div>
                </div>
                <div class="col-md-6">
                  <label for="id_number" class="form-label">ID Number</label>
                  <input type="text" id="id_number" name="id_number" class="form-control" value="{{ old('id_number', $kyc->id_number ?? '') }}" required>
                  <div class="invalid-feedback">Please enter your ID number.</div>
                </div>
                <div class="col-md-6">
                  <label for="id_type" class="form-label">ID Type</label>
                  @php $selectedIdType = old('id_type', $kyc->id_type ?? '') @endphp
                  <select id="id_type" name="id_type" class="form-select" required>
                    <option value="" disabled {{ $selectedIdType ? '' : 'selected' }}>Select ID Type</option>
                    <option value="national_id" {{ $selectedIdType === 'national_id' ? 'selected' : '' }}>National ID</option>
                    <option value="passport" {{ $selectedIdType === 'passport' ? 'selected' : '' }}>Passport</option>
                    <option value="driver_license" {{ $selectedIdType === 'driver_license' ? 'selected' : '' }}>Driver's License</option>
                  </select>
                  <div class="invalid-feedback">Please select an ID type.</div>
                </div>

                <div class="col-12">
                  <label for="id_front" class="form-label">Upload ID Front (PDF/JPG/PNG)</label>
                  <input class="form-control" type="file" id="id_front" name="id_front" accept=".pdf,.jpg,.jpeg,.png" {{ $kyc?->id_front ? '' : 'required' }}>
                  <div class="invalid-feedback">Please upload the front of your ID.</div>
                </div>
                <div class="col-12">
                  <label for="id_back" class="form-label">Upload ID Back (PDF/JPG/PNG)</label>
                  <input class="form-control" type="file" id="id_back" name="id_back" accept=".pdf,.jpg,.jpeg,.png" {{ $kyc?->id_back ? '' : 'required' }}>
                  <div class="invalid-feedback">Please upload the back of your ID.</div>
                </div>
                <div class="col-12">
                  <label for="selfie" class="form-label">Upload Selfie</label>
                  <input class="form-control" type="file" id="selfie" name="selfie" accept=".jpg,.jpeg,.png" {{ $kyc?->selfie ? '' : 'required' }}>
                  <div class="invalid-feedback">Please upload a selfie.</div>
                </div>
              </div>

              <div class="mt-4">
                <button class="btn btn-primary w-100" type="submit">Submit KYC</button>
              </div>
            </form>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Bootstrap validation script --}}
@push('scripts')
<script>
  // Example starter JavaScript for disabling form submissions if there are invalid fields
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
  })()
</script>
@endpush

@endsection
