@extends('layouts.app')

@section('title','KYC - Step 1 of 2')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h2 class="h5 mb-0">KYC Verification</h2>
          <span class="text-muted">Step 1 of 2</span>
        </div>
        <div class="card-body">
          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <form action="{{ route('seller.kyc.info.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label" for="first_name">First Name</label>
              <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $step1['first_name'] ?? '') }}" required>
              @error('first_name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label" for="last_name">Last Name</label>
              <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $step1['last_name'] ?? '') }}" required>
              @error('last_name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $step1['email'] ?? auth()->user()->email) }}" required readonly>
              <div class="form-text">Uses your account email. Contact support to change.</div>
              @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label" for="phone">Phone</label>
              <input class="form-control" id="phone" name="phone" value="{{ old('phone', $step1['phone'] ?? '') }}" placeholder="e.g., +2547XXXXXXXX" required>
              @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label" for="id_type">ID Type</label>
              @php $selectedIdType = old('id_type', $step1['id_type'] ?? '') @endphp
              <select id="id_type" name="id_type" class="form-select" required>
                <option value="" disabled {{ $selectedIdType ? '' : 'selected' }}>Select ID Type</option>
                <option value="national_id" {{ $selectedIdType === 'national_id' ? 'selected' : '' }}>National ID</option>
                <option value="passport" {{ $selectedIdType === 'passport' ? 'selected' : '' }}>Passport</option>
                <option value="driver_license" {{ $selectedIdType === 'driver_license' ? 'selected' : '' }}>Driver's License</option>
              </select>
              @error('id_type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
              <label class="form-label" for="id_number">ID Number</label>
              <input class="form-control" id="id_number" name="id_number" value="{{ old('id_number', $step1['id_number'] ?? '') }}" placeholder="e.g., 12345678" required>
              @error('id_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end">
              <button class="btn btn-primary" type="submit">Continue to Documents</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

