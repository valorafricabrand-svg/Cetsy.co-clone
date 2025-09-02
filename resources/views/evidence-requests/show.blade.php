@extends('layouts.app')

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-alt"></i> Evidence Request
                        </h4>
                        @php
                            // Determine if current user is buyer or seller and redirect accordingly
                            $currentUser = auth()->user();
                            $dispute = $evidenceRequest->appeal->dispute;
                            $isBuyer = $currentUser->id === $dispute->buyer_id;
                            $isSeller = $currentUser->id === $dispute->seller_id;
                        @endphp
                        
                        @if($isBuyer || $isSeller)
                            <a href="{{ route('disputes.show', $dispute->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dispute #{{ $dispute->id }}
                            </a>
                        @else
                            <a href="{{ route('evidence-requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Evidence Requests
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Appeal Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Appeal Information</h6>
                            <p><strong>Appeal ID:</strong> #{{ $evidenceRequest->appeal->id }}</p>
                            <p><strong>Dispute ID:</strong> 
                                <a href="{{ route('disputes.show', $evidenceRequest->appeal->dispute_id) }}" class="text-decoration-none">
                                    #{{ $evidenceRequest->appeal->dispute_id }}
                                </a>
                            </p>
                            <p><strong>Appealed By:</strong> {{ $evidenceRequest->appeal->appealedBy->name }}</p>
                            <p><strong>Appeal Reason:</strong> {{ $evidenceRequest->appeal->reason }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Evidence Request Details</h6>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $evidenceRequest->getStatusBadgeClass() }}">
                                    {{ ucfirst($evidenceRequest->status) }}
                                </span>
                            </p>
                            <p><strong>Party Type:</strong> {{ $evidenceRequest->getPartyTypeLabel() }}</p>
                            <p><strong>Deadline:</strong> 
                                <span class="text-{{ $evidenceRequest->isDeadlineExpired() ? 'danger' : 'primary' }}">
                                    {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                </span>
                            </p>
                            <p><strong>Days Remaining:</strong> 
                                <span class="text-{{ $evidenceRequest->getDaysUntilDeadline() <= 3 ? 'warning' : 'success' }}">
                                    {{ $evidenceRequest->getDaysUntilDeadline() }} days
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Request Message -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Evidence Request from Cetsy Support Team</h6>
                        <p class="mb-0">{{ $evidenceRequest->request_message }}</p>
                    </div>

                    <!-- Required Evidence Types -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Required Evidence Types</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($evidenceRequest->getRequiredEvidenceTypesList() as $evidenceType)
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-primary me-2">{{ $evidenceType }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Evidence Submission Form -->
                    @if($evidenceRequest->status === 'pending' && !$evidenceRequest->isDeadlineExpired())
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-upload"></i> Submit Evidence</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('evidence-requests.submit', $evidenceRequest->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="evidence_description" class="form-label">Evidence Description *</label>
                                        <textarea name="evidence_description" id="evidence_description" rows="4" class="form-control @error('evidence_description') is-invalid @enderror" 
                                            placeholder="Please describe the evidence you are submitting and how it supports your case..." required>{{ old('evidence_description') }}</textarea>
                                        @error('evidence_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Provide a clear description of your evidence and how it relates to the dispute.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="evidence_files" class="form-label">Evidence Files *</label>
                                        <input type="file" name="evidence_files[]" id="evidence_files" class="form-control @error('evidence_files.*') is-invalid @enderror" 
                                            multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.mp4,.mov" required>
                                        @error('evidence_files.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <strong>Accepted formats:</strong> Images (JPG, PNG), Documents (PDF, DOC, DOCX), Videos (MP4, MOV)<br>
                                            <strong>Maximum file size:</strong> 50MB per file<br>
                                            <strong>Multiple files:</strong> You can select multiple files at once
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="additional_notes" class="form-label">Additional Notes</label>
                                        <textarea name="additional_notes" id="additional_notes" rows="3" class="form-control @error('additional_notes') is-invalid @enderror" 
                                            placeholder="Any additional information or context you'd like to provide...">{{ old('additional_notes') }}</textarea>
                                        @error('additional_notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Important:</strong> Once you submit evidence, you cannot modify it. Please ensure all files are correct before submission.
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-upload"></i> Submit Evidence
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($evidenceRequest->status === 'submitted')
                        <!-- Submitted Evidence Display -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-check-circle"></i> Evidence Submitted</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Evidence submitted successfully!</strong> Our support team will review your evidence and get back to you.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Submission Details</h6>
                                        <p><strong>Submitted At:</strong> {{ $evidenceRequest->submitted_at->format('M d, Y \a\t g:i A') }}</p>
                                        <p><strong>Description:</strong> {{ $evidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                        @if(isset($evidenceRequest->submitted_evidence['additional_notes']))
                                            <p><strong>Additional Notes:</strong> {{ $evidenceRequest->submitted_evidence['additional_notes'] }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Submitted Files</h6>
                                        @if(isset($evidenceRequest->submitted_evidence['files']))
                                            <div class="evidence-files">
                                                @foreach($evidenceRequest->submitted_evidence['files'] as $file)
                                                    <div class="card mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <small class="text-muted">{{ $file['filename'] }}</small><br>
                                                                    <small class="text-muted">{{ number_format($file['size'] / 1024, 1) }} KB</small>
                                                                </div>
                                                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-download"></i> View
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted">No files submitted.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($evidenceRequest->status === 'overdue')
                        <!-- Overdue Notice -->
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Deadline Expired:</strong> The deadline for submitting evidence has passed. Please contact our support team for assistance.
                        </div>
                    @endif

                                         <!-- Both Parties Evidence Status -->
                     <div class="card mt-4">
                         <div class="card-header">
                             <h6 class="mb-0"><i class="fas fa-users"></i> Both Parties Evidence Status</h6>
                         </div>
                         <div class="card-body">
                             <div class="row">
                                 <div class="col-md-6">
                                     <h6 class="mb-3">
                                         <i class="fas fa-user text-primary"></i> Buyer Evidence
                                         @if($evidenceRequest->appeal->buyerEvidenceRequest)
                                             <span class="badge {{ $evidenceRequest->appeal->buyerEvidenceRequest->getStatusBadgeClass() }} ms-2">
                                                 {{ ucfirst($evidenceRequest->appeal->buyerEvidenceRequest->status) }}
                                             </span>
                                         @else
                                             <span class="badge bg-secondary ms-2">Pending</span>
                                         @endif
                                     </h6>
                                     
                                     @if($evidenceRequest->appeal->buyerEvidenceRequest && $evidenceRequest->appeal->buyerEvidenceRequest->status === 'submitted')
                                         <div class="evidence-submission-card border rounded p-3 mb-3">
                                             <div class="d-flex justify-content-between align-items-start mb-2">
                                                 <strong>Submitted:</strong>
                                                 <small class="text-muted">{{ $evidenceRequest->appeal->buyerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</small>
                                             </div>
                                             <p class="mb-2"><strong>Description:</strong> {{ $evidenceRequest->appeal->buyerEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                             
                                             @if(isset($evidenceRequest->appeal->buyerEvidenceRequest->submitted_evidence['files']))
                                                 <div class="submitted-files">
                                                     <strong>Files:</strong>
                                                     @foreach($evidenceRequest->appeal->buyerEvidenceRequest->submitted_evidence['files'] as $file)
                                                         <div class="file-item d-flex justify-content-between align-items-center mt-1">
                                                             <small class="text-truncate">{{ $file['filename'] }}</small>
                                                             <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-sm">
                                                                 <i class="fas fa-eye"></i>
                                                             </a>
                                                         </div>
                                                     @endforeach
                                                 </div>
                                             @endif
                                         </div>
                                     @else
                                         <div class="text-muted">
                                             <i class="fas fa-clock"></i> Waiting for buyer to submit evidence
                                         </div>
                                     @endif
                                 </div>
                                 
                                 <div class="col-md-6">
                                     <h6 class="mb-3">
                                         <i class="fas fa-shop text-success"></i> Seller Evidence
                                         @if($evidenceRequest->appeal->sellerEvidenceRequest)
                                             <span class="badge {{ $evidenceRequest->appeal->sellerEvidenceRequest->getStatusBadgeClass() }} ms-2">
                                                 {{ ucfirst($evidenceRequest->appeal->sellerEvidenceRequest->status) }}
                                             </span>
                                         @else
                                             <span class="badge bg-secondary ms-2">Pending</span>
                                         @endif
                                     </h6>
                                     
                                     @if($evidenceRequest->appeal->sellerEvidenceRequest && $evidenceRequest->appeal->sellerEvidenceRequest->status === 'submitted')
                                         <div class="evidence-submission-card border rounded p-3 mb-3">
                                             <div class="d-flex justify-content-between align-items-start mb-2">
                                                 <strong>Submitted:</strong>
                                                 <small class="text-muted">{{ $evidenceRequest->appeal->sellerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</small>
                                             </div>
                                             <p class="mb-2"><strong>Description:</strong> {{ $evidenceRequest->appeal->sellerEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                             
                                             @if(isset($evidenceRequest->appeal->sellerEvidenceRequest->submitted_evidence['files']))
                                                 <div class="submitted-files">
                                                     <strong>Files:</strong>
                                                     @foreach($evidenceRequest->appeal->sellerEvidenceRequest->submitted_evidence['files'] as $file)
                                                         <div class="file-item d-flex justify-content-between align-items-center mt-1">
                                                             <small class="text-truncate">{{ $file['filename'] }}</small>
                                                             <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-sm">
                                                                 <i class="fas fa-eye"></i>
                                                             </a>
                                                         </div>
                                                     @endforeach
                                                 </div>
                                             @endif
                                         </div>
                                     @else
                                         <div class="text-muted">
                                             <i class="fas fa-clock"></i> Waiting for seller to submit evidence
                                         </div>
                                     @endif
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Appeal Progress Timeline -->
                     <div class="card mt-4">
                         <div class="card-header">
                             <h6 class="mb-0"><i class="fas fa-chart-line"></i> Appeal Progress Timeline</h6>
                         </div>
                         <div class="card-body">
                             <div class="timeline">
                                 <div class="timeline-item">
                                     <div class="timeline-marker bg-primary"></div>
                                     <div class="timeline-content">
                                         <h6 class="timeline-title">Appeal Submitted</h6>
                                         <p class="timeline-text">{{ $evidenceRequest->appeal->created_at->format('M d, Y \a\t g:i A') }}</p>
                                         <p class="timeline-text">{{ $evidenceRequest->appeal->appealedBy->name }} submitted an appeal</p>
                                     </div>
                                 </div>

                                 <div class="timeline-item">
                                     <div class="timeline-marker bg-warning"></div>
                                     <div class="timeline-content">
                                         <h6 class="timeline-title">Evidence Requested</h6>
                                         <p class="timeline-text">Cetsy support team requested evidence from both parties</p>
                                         <p class="timeline-text">Deadline: {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                     </div>
                                 </div>

                                 @if($evidenceRequest->appeal->buyerEvidenceRequest && $evidenceRequest->appeal->buyerEvidenceRequest->status === 'submitted')
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-success"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">Buyer Evidence Submitted</h6>
                                             <p class="timeline-text">Buyer submitted evidence on {{ $evidenceRequest->appeal->buyerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</p>
                                         </div>
                                     </div>
                                 @endif

                                 @if($evidenceRequest->appeal->sellerEvidenceRequest && $evidenceRequest->appeal->sellerEvidenceRequest->status === 'submitted')
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-success"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">Seller Evidence Submitted</h6>
                                             <p class="timeline-text">Seller submitted evidence on {{ $evidenceRequest->appeal->sellerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</p>
                                         </div>
                                     </div>
                                 @endif

                                 @if($evidenceRequest->appeal->buyerEvidenceRequest && $evidenceRequest->appeal->buyerEvidenceRequest->status === 'submitted' && 
                                     $evidenceRequest->appeal->sellerEvidenceRequest && $evidenceRequest->appeal->sellerEvidenceRequest->status === 'submitted')
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-info"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">All Evidence Received</h6>
                                             <p class="timeline-text">Both parties have submitted evidence. Cetsy support team is now reviewing.</p>
                                         </div>
                                     </div>
                                 @endif

                                 @if($evidenceRequest->appeal->status === 'approved' || $evidenceRequest->appeal->status === 'rejected')
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-{{ $evidenceRequest->appeal->status === 'approved' ? 'success' : 'danger' }}"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">Appeal {{ ucfirst($evidenceRequest->appeal->status) }}</h6>
                                             <p class="timeline-text">{{ $evidenceRequest->appeal->reviewed_at->format('M d, Y g:i A') }}</p>
                                             @if($evidenceRequest->appeal->review_notes)
                                                 <p class="timeline-text">{{ $evidenceRequest->appeal->review_notes }}</p>
                                             @endif
                                         </div>
                                     </div>
                                 @endif
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 5px;
}

.timeline-title {
    margin: 0 0 10px 0;
    font-weight: 600;
}

.timeline-text {
    margin: 0;
    color: #666;
}

.evidence-files .card {
    border: 1px solid #dee2e6;
}

 .evidence-files .card:hover {
     border-color: #007bff;
 }

 .evidence-submission-card {
     background-color: #f8f9fa;
     transition: all 0.3s ease;
 }

 .evidence-submission-card:hover {
     background-color: #e9ecef;
     box-shadow: 0 2px 8px rgba(0,0,0,0.1);
 }

 .file-item {
     background-color: white;
     padding: 8px 12px;
     border-radius: 4px;
     border: 1px solid #dee2e6;
     margin-bottom: 4px;
 }

 .file-item:hover {
     background-color: #f8f9fa;
     border-color: #007bff;
 }

 .submitted-files {
     background-color: white;
     padding: 12px;
     border-radius: 4px;
     border: 1px solid #dee2e6;
 }
</style>
@endpush

@push('scripts')
<script>
// File input validation
document.getElementById('evidence_files').addEventListener('change', function(e) {
    const files = e.target.files;
    const maxSize = 50 * 1024 * 1024; // 50MB
    
    for (let i = 0; i < files.length; i++) {
        if (files[i].size > maxSize) {
            alert(`File "${files[i].name}" is too large. Maximum size is 50MB.`);
            e.target.value = '';
            return;
        }
    }
});

// Countdown timer for deadline
@if($evidenceRequest->status === 'pending' && !$evidenceRequest->isDeadlineExpired())
    const deadline = new Date('{{ $evidenceRequest->deadline }}').getTime();
    
    const countdown = setInterval(function() {
        const now = new Date().getTime();
        const distance = deadline - now;
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        
        if (distance < 0) {
            clearInterval(countdown);
            location.reload(); // Reload to show expired status
        }
    }, 60000); // Update every minute
@endif
</script>
@endpush
@endsection
