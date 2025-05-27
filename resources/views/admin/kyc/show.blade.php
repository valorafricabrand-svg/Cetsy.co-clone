@extends('layouts.app')

@section('title', 'KYC Details')

@section('content')
<div class="content">
  <div class="card shadow-sm mx-auto" style="max-width: 40rem;">
    <div class="card-body">

      <a href="{{ route('admin.kyc.index') }}" class="text-primary mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> Back to KYC List
      </a>

      <h2 class="h4 mb-4">KYC Details</h2>

      {{-- Seller Information --}}
      <div class="mb-5">
        <h3 class="h6 text-secondary mb-3">Seller Information</h3>
        <div class="row">
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">Name</small>
            <div class="fw-semibold">{{ $kyc->first_name }} {{ $kyc->last_name }}</div>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">Email</small>
            <div class="fw-semibold">{{ $kyc->email }}</div>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">Phone</small>
            <div class="fw-semibold">{{ $kyc->phone }}</div>
          </div>
        </div>
      </div>

      {{-- KYC Information --}}
      <div class="mb-5">
        <h3 class="h6 text-secondary mb-3">KYC Information</h3>
        <div class="row">
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">ID Type</small>
            <div class="fw-semibold">{{ ucfirst($kyc->id_type) }}</div>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">ID Number</small>
            <div class="fw-semibold">{{ $kyc->id_number }}</div>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">Status</small>
            <div class="fw-semibold text-capitalize">{{ $kyc->status }}</div>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <small class="text-muted">Submitted At</small>
            <div class="fw-semibold">{{ $kyc->created_at->format('M d, Y H:i') }}</div>
          </div>
          @if($kyc->admin_notes)
            <div class="col-12 mb-3">
              <small class="text-muted">Admin Notes</small>
              <div class="fw-semibold">{{ $kyc->admin_notes }}</div>
            </div>
          @endif
        </div>
      </div>

      {{-- Documents --}}
      <div class="mb-4">
        <h3 class="h6 text-secondary mb-3">Documents</h3>
        <div class="row g-3">
          @foreach (['id_front' => 'ID Front', 'id_back' => 'ID Back', 'selfie' => 'Selfie'] as $field => $label)
            <div class="col-12 col-sm-4">
              <small class="text-muted d-block mb-1">{{ $label }}</small>
              <a 
                href="{{ Storage::url($kyc->$field) }}" 
                target="_blank" 
                class="border rounded p-2 d-block text-center text-decoration-none"
              >
                <img 
                  src="{{ Storage::url($kyc->$field) }}" 
                  alt="{{ $label }}" 
                  class="img-fluid mb-2" 
                  style="height: 8rem; object-fit: contain;" 
                  onerror="this.style.display='none'"
                >
                <div class="text-primary">View File</div>
              </a>
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
