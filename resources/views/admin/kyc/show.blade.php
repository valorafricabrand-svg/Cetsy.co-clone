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

      {{-- Approval Actions --}}
      <div class="mb-4">
        <h3 class="h6 text-secondary mb-3">Approval Actions</h3>
        <div class="card border-0 bg-light">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <span class="me-2">Current status:</span>
              <span class="badge {{ $kyc->status==='approved' ? 'bg-success' : ($kyc->status==='rejected' ? 'bg-danger' : 'bg-warning text-dark') }} text-capitalize">{{ $kyc->status }}</span>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <form action="{{ route('admin.kyc.update', $kyc) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-check me-1"></i> Approve
                </button>
              </form>

              <button class="btn btn-danger" type="button" data-bs-toggle="collapse" data-bs-target="#rejectForm" aria-expanded="false" aria-controls="rejectForm">
                <i class="fas fa-times me-1"></i> Reject with Reason
              </button>

              <button class="btn btn-warning text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#correctionForm" aria-expanded="false" aria-controls="correctionForm">
                <i class="fas fa-edit me-1"></i> Request Correction
              </button>
            </div>

            <div class="collapse mt-3" id="rejectForm">
              <form action="{{ route('admin.kyc.update', $kyc) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <div class="mb-2">
                  <label for="admin_notes" class="form-label">Reason for rejection</label>
                  <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Explain what needs to be corrected..." required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Reject</button>
              </form>
            </div>

            <div class="collapse mt-3" id="correctionForm">
              <form action="{{ route('admin.kyc.update', $kyc) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="needs_correction">
                <div class="mb-2">
                  <label for="correction_notes" class="form-label">Correction details</label>
                  <div class="d-flex gap-2 mb-2">
                    <select id="correction_template" class="form-select form-select-sm" style="max-width:260px">
                      <option value="">Select template...</option>
                      <option>Document images are blurry. Please re-upload clearer scans.</option>
                      <option>Name on ID does not match your account. Please update details or provide matching ID.</option>
                      <option>ID appears expired. Please submit a valid, unexpired ID.</option>
                      <option>Missing required document pages. Please include both front and back.</option>
                    </select>
                    <button class="btn btn-sm btn-outline-secondary" type="button" onclick="(function(){ const sel=document.getElementById('correction_template'); const ta=document.getElementById('correction_notes'); if(sel && ta && sel.value){ ta.value=sel.value; } })()">Apply</button>
                  </div>
                  <textarea class="form-control" id="correction_notes" name="admin_notes" rows="3" placeholder="Describe what needs correction..." required></textarea>
                </div>
                <button type="submit" class="btn btn-warning text-dark">Send Request</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- Message Seller (Request Correction) --}}
      <div class="mb-2">
        <h3 class="h6 text-secondary mb-3">Message Seller (Request Correction)</h3>
        <div class="card border-0">
          <div class="card-body">
            <form action="{{ route('messages.store') }}" method="POST">
              @csrf
              <input type="hidden" name="receiver_id" value="{{ $kyc->user_id }}">
              <input type="hidden" name="product_id" value="">
              <div class="mb-2">
                <div class="d-flex gap-2 mb-2">
                  <select id="msg_template" class="form-select form-select-sm" style="max-width:260px">
                    <option value="">Select template...</option>
                    <option>Hi {{ $kyc->first_name }}, your ID images are blurry. Please re-upload clearer scans to proceed.</option>
                    <option>Hi {{ $kyc->first_name }}, your name on the ID doesn’t match the account. Please update your details or provide matching ID.</option>
                    <option>Hi {{ $kyc->first_name }}, the ID appears expired. Please submit a valid, unexpired ID.</option>
                    <option>Hi {{ $kyc->first_name }}, the back side of your ID is missing. Please upload both front and back.</option>
                  </select>
                  <button class="btn btn-sm btn-outline-secondary" type="button" onclick="(function(){ const sel=document.getElementById('msg_template'); const ta=document.getElementById('msg_body'); if(sel && ta && sel.value){ ta.value=sel.value; } })()">Apply</button>
                </div>
                <textarea id="msg_body" name="message" class="form-control" rows="3" placeholder="Hi {{ $kyc->first_name }}, please correct ..." required></textarea>
              </div>
              <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-paper-plane me-1"></i> Send Message
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
