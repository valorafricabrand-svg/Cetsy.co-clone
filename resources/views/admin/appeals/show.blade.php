@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-balance-scale"></i> Appeal #{{ $appeal->id }}
        </h1>
        <div>
            <a href="{{ route('admin.appeals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Appeals
            </a>
            <a href="{{ route('admin.admin-disputes.show', $appeal->dispute->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Dispute
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Appeal Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Appeal Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Appeal Information</h6>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $appeal->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $appeal->status)) }}
                                </span>
                            </p>
                            <p><strong>Appealed By:</strong> {{ $appeal->appealedBy->name }}</p>
                            <p><strong>Appealed At:</strong> {{ $appeal->created_at->format('M d, Y \a\t g:i A') }}</p>
                            <p><strong>Reason:</strong> {{ $appeal->reason }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Dispute Information</h6>
                            <p><strong>Dispute ID:</strong> #{{ $appeal->dispute->id }}</p>
                            <p><strong>Type:</strong> {{ $appeal->dispute->getTypeLabel() }}</p>
                            <p><strong>Buyer:</strong> {{ $appeal->dispute->buyer->name }}</p>
                            <p><strong>Seller:</strong> {{ $appeal->dispute->seller->name }}</p>
                        </div>
                    </div>

                    @if($appeal->new_evidence)
                        <div class="mt-3">
                            <h6>Additional Evidence Submitted</h6>
                            <p><strong>Description:</strong> {{ $appeal->new_evidence['description'] ?? 'N/A' }}</p>
                            @if(isset($appeal->new_evidence['files']))
                                <div class="evidence-files">
                                    <strong>Files:</strong>
                                    <div class="row mt-2">
                                        @foreach($appeal->new_evidence['files'] as $file)
                                            <div class="col-md-4 mb-2">
                                                <div class="card">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">{{ $file['filename'] }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ number_format($file['size'] / 1024, 1) }} KB</small>
                                                        <br>
                                                        <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                            <i class="fas fa-download"></i> View
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Evidence Requests -->
            @if($appeal->status === 'evidence_requested')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Evidence Requests</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Buyer Evidence -->
                            <div class="col-md-6">
                                <div class="card border-{{ $appeal->buyerEvidenceRequest ? ($appeal->buyerEvidenceRequest->status === 'submitted' ? 'success' : 'warning') : 'secondary' }}">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user"></i> Buyer Evidence
                                            @if($appeal->buyerEvidenceRequest)
                                                <span class="badge {{ $appeal->buyerEvidenceRequest->getStatusBadgeClass() }} ms-2">
                                                    {{ ucfirst($appeal->buyerEvidenceRequest->status) }}
                                                </span>
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($appeal->buyerEvidenceRequest)
                                            <p><strong>Deadline:</strong> {{ $appeal->buyerEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                            <p><strong>Days Left:</strong> {{ $appeal->buyerEvidenceRequest->getDaysUntilDeadline() }}</p>
                                            
                                            @if($appeal->buyerEvidenceRequest->status === 'submitted')
                                                <div class="mt-3">
                                                    <h6>Submitted Evidence</h6>
                                                    <p><strong>Description:</strong> {{ $appeal->buyerEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                                    @if(isset($appeal->buyerEvidenceRequest->submitted_evidence['files']))
                                                        <div class="evidence-files">
                                                            <strong>Files:</strong>
                                                            <div class="row mt-2">
                                                                @foreach($appeal->buyerEvidenceRequest->submitted_evidence['files'] as $file)
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="card">
                                                                            <div class="card-body p-2">
                                                                                <small class="text-muted">{{ $file['filename'] }}</small>
                                                                                <br>
                                                                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                                                    <i class="fas fa-download"></i> View
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-muted">Waiting for evidence submission...</p>
                                            @endif
                                        @else
                                            <p class="text-muted">No evidence request created yet.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Seller Evidence -->
                            <div class="col-md-6">
                                <div class="card border-{{ $appeal->sellerEvidenceRequest ? ($appeal->sellerEvidenceRequest->status === 'submitted' ? 'success' : 'warning') : 'secondary' }}">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-shop"></i> Seller Evidence
                                            @if($appeal->sellerEvidenceRequest)
                                                <span class="badge {{ $appeal->sellerEvidenceRequest->getStatusBadgeClass() }} ms-2">
                                                    {{ ucfirst($appeal->sellerEvidenceRequest->status) }}
                                                </span>
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($appeal->sellerEvidenceRequest)
                                            <p><strong>Deadline:</strong> {{ $appeal->sellerEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                            <p><strong>Days Left:</strong> {{ $appeal->sellerEvidenceRequest->getDaysUntilDeadline() }}</p>
                                            
                                            @if($appeal->sellerEvidenceRequest->status === 'submitted')
                                                <div class="mt-3">
                                                    <h6>Submitted Evidence</h6>
                                                    <p><strong>Description:</strong> {{ $appeal->sellerEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                                    @if(isset($appeal->sellerEvidenceRequest->submitted_evidence['files']))
                                                        <div class="evidence-files">
                                                            <strong>Files:</strong>
                                                            <div class="row mt-2">
                                                                @foreach($appeal->sellerEvidenceRequest->submitted_evidence['files'] as $file)
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="card">
                                                                            <div class="card-body p-2">
                                                                                <small class="text-muted">{{ $file['filename'] }}</small>
                                                                                <br>
                                                                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                                                    <i class="fas fa-download"></i> View
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-muted">Waiting for evidence submission...</p>
                                            @endif
                                        @else
                                            <p class="text-muted">No evidence request created yet.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Appeal Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Appeal Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Appeal Submitted</h6>
                                <p class="timeline-text">{{ $appeal->created_at->format('M d, Y \a\t g:i A') }}</p>
                                <p class="timeline-text">{{ $appeal->appealedBy->name }} submitted an appeal</p>
                            </div>
                        </div>

                        @if($appeal->status === 'evidence_requested')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Evidence Requested</h6>
                                    <p class="timeline-text">Evidence requested from both parties</p>
                                    @if($appeal->buyerEvidenceRequest)
                                        <p class="timeline-text">Deadline: {{ $appeal->buyerEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($appeal->status === 'approved' || $appeal->status === 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $appeal->status === 'approved' ? 'success' : 'danger' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Appeal {{ ucfirst($appeal->status) }}</h6>
                                    <p class="timeline-text">{{ $appeal->reviewed_at->format('M d, Y \a\t g:i A') }}</p>
                                    @if($appeal->review_notes)
                                        <p class="timeline-text">{{ $appeal->review_notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <!-- Appeal Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    @if($appeal->status === 'pending')
                        <button type="button" class="btn btn-warning btn-block mb-2" 
                                data-bs-toggle="modal" data-bs-target="#requestEvidenceModal">
                            <i class="fas fa-file-alt"></i> Request Evidence
                        </button>
                        <button type="button" class="btn btn-success btn-block mb-2" 
                                data-bs-toggle="modal" data-bs-target="#approveAppealModal">
                            <i class="fas fa-check"></i> Approve Appeal
                        </button>
                        <button type="button" class="btn btn-danger btn-block mb-2" 
                                data-bs-toggle="modal" data-bs-target="#rejectAppealModal">
                            <i class="fas fa-times"></i> Reject Appeal
                        </button>
                        <button type="button" class="btn btn-secondary btn-block mb-2" 
                                data-bs-toggle="modal" data-bs-target="#closeAppealModal">
                            <i class="fas fa-times-circle"></i> Close Appeal
                        </button>
                    @elseif($appeal->status === 'evidence_requested')
                        @php
                            $buyerEvidence = $appeal->buyerEvidenceRequest;
                            $sellerEvidence = $appeal->sellerEvidenceRequest;
                            $bothSubmitted = $buyerEvidence && $sellerEvidence && 
                                           $buyerEvidence->status === 'submitted' && 
                                           $sellerEvidence->status === 'submitted';
                        @endphp
                        
                        @if($bothSubmitted)
                            <button type="button" class="btn btn-success btn-block mb-2" 
                                    data-bs-toggle="modal" data-bs-target="#approveAppealModal">
                                <i class="fas fa-check"></i> Approve Appeal
                            </button>
                            <button type="button" class="btn btn-danger btn-block mb-2" 
                                    data-bs-toggle="modal" data-bs-target="#rejectAppealModal">
                                <i class="fas fa-times"></i> Reject Appeal
                            </button>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Waiting for both parties to submit evidence before making a decision.
                            </div>
                        @endif
                        
                        <button type="button" class="btn btn-secondary btn-block mb-2" 
                                data-bs-toggle="modal" data-bs-target="#closeAppealModal">
                            <i class="fas fa-times-circle"></i> Close Appeal
                        </button>
                    @else
                        <div class="alert alert-{{ $appeal->status === 'approved' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $appeal->status === 'approved' ? 'check' : 'times' }}-circle"></i>
                            Appeal has been {{ $appeal->status }}.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appeal Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h4 mb-0">{{ $appeal->created_at->diffInDays(now()) }}</div>
                                <div class="text-xs text-muted">Days Since Appeal</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-left">
                                <div class="h4 mb-0">{{ $appeal->dispute->messages->count() }}</div>
                                <div class="text-xs text-muted">Total Messages</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evidence Request Modal -->
@if($appeal->status === 'pending')
    <div class="modal fade" id="requestEvidenceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt"></i> Request Evidence from Both Parties
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.appeals.request-evidence', $appeal->id) }}" method="POST" id="evidenceRequestForm">
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Evidence Request Process:</strong> This will send evidence requests to both the buyer and seller with a deadline. Both parties will be able to see the appeal progress and submit their evidence.
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Request Message *</label>
                            <textarea name="message" id="message" rows="4" class="form-control" 
                                placeholder="Please explain what evidence you need from both parties..." required></textarea>
                            <div class="form-text">
                                This message will be sent to both the buyer and seller explaining what evidence is needed.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="evidence_types" class="form-label">Required Evidence Types *</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="screenshots" id="screenshots">
                                        <label class="form-check-label" for="screenshots">Screenshots</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="documents" id="documents">
                                        <label class="form-check-label" for="documents">Documents</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="photos" id="photos">
                                        <label class="form-check-label" for="photos">Photos</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="receipts" id="receipts">
                                        <label class="form-check-label" for="receipts">Receipts</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="communication_logs" id="communication_logs">
                                        <label class="form-check-label" for="communication_logs">Communication Logs</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="bank_statements" id="bank_statements">
                                        <label class="form-check-label" for="bank_statements">Bank Statements</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="tracking_info" id="tracking_info">
                                        <label class="form-check-label" for="tracking_info">Tracking Information</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="evidence_types[]" value="other" id="other">
                                        <label class="form-check-label" for="other">Other Evidence</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deadline_days" class="form-label">Deadline (Days) *</label>
                            <select name="deadline_days" id="deadline_days" class="form-select" required>
                                <option value="">Select deadline</option>
                                <option value="3">3 days</option>
                                <option value="5">5 days</option>
                                <option value="7">7 days</option>
                                <option value="10">10 days</option>
                                <option value="14">14 days</option>
                                <option value="21">21 days</option>
                                <option value="30">30 days</option>
                            </select>
                            <div class="form-text">
                                Both parties will have this amount of time to submit their evidence.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="submitEvidenceBtn">
                            <i class="fas fa-file-alt"></i> Request Evidence
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('evidenceRequestForm').addEventListener('submit', function(e) {
            console.log('Form submission started');
            
            // Check if required fields are filled
            const message = document.getElementById('message').value.trim();
            const evidenceTypes = document.querySelectorAll('input[name="evidence_types[]"]:checked');
            const deadlineDays = document.getElementById('deadline_days').value;
            
            console.log('Form data:', {
                message: message,
                evidenceTypes: Array.from(evidenceTypes).map(cb => cb.value),
                deadlineDays: deadlineDays
            });
            
            if (!message) {
                e.preventDefault();
                alert('Please enter a request message');
                return false;
            }
            
            if (evidenceTypes.length === 0) {
                e.preventDefault();
                alert('Please select at least one evidence type');
                return false;
            }
            
            if (!deadlineDays) {
                e.preventDefault();
                alert('Please select a deadline');
                return false;
            }
            
            console.log('Form validation passed, submitting...');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Re-enable button after a delay (in case of errors)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Prevent modal from closing on validation errors
        const modal = document.getElementById('requestEvidenceModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                // Clear any validation errors when modal is closed
                const errorAlerts = this.querySelectorAll('.alert-danger');
                errorAlerts.forEach(alert => alert.remove());
            });
        }
    </script>
@endif

<!-- Approve Appeal Modal -->
<div class="modal fade" id="approveAppealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check"></i> Approve Appeal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.appeals.review.store', $appeal->id) }}" method="POST">
                @csrf
                <input type="hidden" name="decision" value="approved">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        <strong>Approve Appeal:</strong> This will approve the appeal and resolve the dispute in favor of the appealing party.
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_notes" class="form-label">Review Notes *</label>
                        <textarea name="review_notes" id="review_notes" rows="4" class="form-control" 
                            placeholder="Please explain why the appeal was approved and what evidence supported this decision..." required></textarea>
                        <div class="form-text">
                            This explanation will be visible to both parties and recorded in the system.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispute_resolution" class="form-label">Dispute Resolution</label>
                        <textarea name="dispute_resolution" id="dispute_resolution" rows="3" class="form-control" 
                            placeholder="Optional: Specific resolution details or instructions for both parties..."></textarea>
                        <div class="form-text">
                            This will be the final resolution text visible to both parties.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="refund_amount" id="refund_amount" class="form-control" 
                                placeholder="0.00" step="0.01" min="0" max="999999.99">
                        </div>
                        <div class="form-text">
                            If a refund is applicable, specify the amount here.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resolution_notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea name="resolution_notes" id="resolution_notes" rows="2" class="form-control" 
                            placeholder="Any additional notes about the resolution..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Appeal Modal -->
<div class="modal fade" id="rejectAppealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times"></i> Reject Appeal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.appeals.review.store', $appeal->id) }}" method="POST">
                @csrf
                <input type="hidden" name="decision" value="rejected">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-info-circle"></i>
                        <strong>Reject Appeal:</strong> This will reject the appeal and the dispute decision will become final.
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_notes" class="form-label">Review Notes *</label>
                        <textarea name="review_notes" id="review_notes" rows="4" class="form-control" 
                            placeholder="Please explain why the appeal was rejected and what evidence supported this decision..." required></textarea>
                        <div class="form-text">
                            This explanation will be visible to both parties and recorded in the system.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispute_resolution" class="form-label">Dispute Resolution</label>
                        <textarea name="dispute_resolution" id="dispute_resolution" rows="3" class="form-control" 
                            placeholder="Optional: Final resolution details or instructions for both parties..."></textarea>
                        <div class="form-text">
                            This will be the final resolution text visible to both parties.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resolution_notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea name="resolution_notes" id="resolution_notes" rows="2" class="form-control" 
                            placeholder="Any additional notes about the rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Appeal Modal -->
<div class="modal fade" id="closeAppealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Close Appeal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.appeals.close', $appeal->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Close Appeal:</strong> This will close the appeal without making a final decision. Use this option when evidence is insufficient or the case cannot be properly resolved.
                    </div>
                    
                    <div class="mb-3">
                        <label for="closure_reason" class="form-label">Closure Reason *</label>
                        <textarea name="closure_reason" id="closure_reason" rows="4" class="form-control" 
                            placeholder="Please explain why the appeal is being closed..." required></textarea>
                        <div class="form-text">
                            This reason will be visible to both parties and will be recorded in the system.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="closure_notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea name="closure_notes" id="closure_notes" rows="3" class="form-control" 
                            placeholder="Any additional internal notes..."></textarea>
                        <div class="form-text">
                            These notes are for internal use only and will not be visible to users.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-times-circle"></i> Close Appeal
                    </button>
                </div>
            </form>
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
</style>
@endpush
@endsection
