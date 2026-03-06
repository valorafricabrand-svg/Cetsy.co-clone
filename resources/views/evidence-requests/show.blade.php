@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
    <div class="grid grid-cols-12 gap-4 justify-center">
        <div class="col-span-12 md:col-span-10">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <div class="flex justify-between items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-alt"></i> Evidence Request
                        </h4>
                        @php
                            $currentUser = auth()->user();
                            $appeal = $evidenceRequest->appeal;
                            $dispute = $appeal?->dispute;
                            $isBuyer = $dispute && $currentUser && $currentUser->id === $dispute->buyer_id;
                            $isSeller = $dispute && $currentUser && $currentUser->id === $dispute->seller_id;
                        @endphp
                        
                        @if($isBuyer || $isSeller)
                            <a href="{{ route('disputes.show', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500">
                                <i class="fas fa-arrow-left"></i> Back to Dispute #{{ $dispute->id }}
                            </a>
                        @else
                            <a href="{{ route('evidence-requests.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500">
                                <i class="fas fa-arrow-left"></i> Back to Evidence Requests
                            </a>
                        @endif
                    </div>
                </div>
                <div class="p-4 sm:p-5">
                    @if($appeal)
                    <!-- Appeal Information -->
                    <div class="grid grid-cols-12 gap-4 mb-4">
                        <div class="col-span-12 md:col-span-6">
                            <h6>Appeal Information</h6>
                            <p><strong>Appeal ID:</strong> #{{ $appeal->id }}</p>
                            <p><strong>Dispute ID:</strong> 
                                <a href="{{ route('disputes.show', $appeal->dispute_id) }}" class="no-underline">
                                    #{{ $appeal->dispute_id }}
                                </a>
                            </p>
                            <p><strong>Appealed By:</strong> {{ $appeal->appealedBy->name }}</p>
                            <p><strong>Appeal Reason:</strong> {{ $appeal->reason }}</p>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <h6>Evidence Request Details</h6>
                            <p><strong>Status:</strong> 
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $evidenceRequest->getStatusBadgeClass() }}">
                                    {{ $evidenceRequest->getStatusLabel() }}
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
                    @else
                    <div class="grid grid-cols-12 gap-4 mb-4">
                        <div class="col-span-12">
                            <h6>Evidence Request Details</h6>
                            <p><strong>Request ID:</strong> #{{ $evidenceRequest->id }}</p>
                            <p><strong>Status:</strong> 
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $evidenceRequest->getStatusBadgeClass() }}">
                                    {{ $evidenceRequest->getStatusLabel() }}
                                </span>
                            </p>
                            <p><strong>Party Type:</strong> {{ $evidenceRequest->getPartyTypeLabel() }}</p>
                            <p><strong>Deadline:</strong> 
                                <span class="text-{{ $evidenceRequest->isDeadlineExpired() ? 'danger' : 'primary' }}">
                                    {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Request Message -->
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                        <h6><i class="fas fa-info-circle"></i> Evidence Request from Cetsy Support Team</h6>
                        <p class="mb-0">{{ $evidenceRequest->request_message }}</p>
                    </div>

                    <!-- Required Evidence Types -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Required Evidence Types</h6>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($evidenceRequest->getRequiredEvidenceTypesList() as $evidenceType)
                                    <div class="col-span-12 md:col-span-4 mb-2">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary mr-2">{{ $evidenceType }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Evidence Submission Form -->
                    @if($evidenceRequest->isPending() && !$evidenceRequest->isDeadlineExpired())
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 px-4 py-3">
                                <h6 class="mb-0"><i class="fas fa-upload"></i> Submit Evidence</h6>
                            </div>
                            <div class="p-4 sm:p-5">
                                <form action="{{ route('evidence-requests.respond', $evidenceRequest->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="evidence_description" class="mb-1 block text-sm font-medium text-slate-700">Evidence Description *</label>
                                        <textarea name="evidence_description" id="evidence_description" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('evidence_description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                            placeholder="Please describe the evidence you are submitting and how it supports your case..." required>{{ old('evidence_description') }}</textarea>
                                        @error('evidence_description')
                                            <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                        @enderror
                                        <div class="mt-1 text-xs text-slate-500">
                                            Provide a clear description of your evidence and how it relates to the dispute.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="evidence_files" class="mb-1 block text-sm font-medium text-slate-700">Evidence Files *</label>
                                        <input type="file" name="evidence_files[]" id="evidence_files" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('evidence_files.*') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                            multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.mp4,.mov" required>
                                        @error('evidence_files.*')
                                            <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                        @enderror
                                        <div class="mt-1 text-xs text-slate-500">
                                            <strong>Accepted formats:</strong> Images (JPG, PNG), Documents (PDF, DOC, DOCX), Videos (MP4, MOV)<br>
                                            <strong>Maximum file size:</strong> 50MB per file<br>
                                            <strong>Multiple files:</strong> You can select multiple files at once
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="additional_notes" class="mb-1 block text-sm font-medium text-slate-700">Additional Notes</label>
                                        <textarea name="additional_notes" id="additional_notes" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('additional_notes') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                            placeholder="Any additional information or context you'd like to provide...">{{ old('additional_notes') }}</textarea>
                                        @error('additional_notes')
                                            <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Important:</strong> Once you submit evidence, you cannot modify it. Please ensure all files are correct before submission.
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base">
                                            <i class="fas fa-upload"></i> Submit Evidence
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($evidenceRequest->isSubmitted())
                        <!-- Submitted Evidence Display -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 px-4 py-3">
                                <h6 class="mb-0"><i class="fas fa-check-circle"></i> Evidence Submitted</h6>
                            </div>
                            <div class="p-4 sm:p-5">
                                <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Evidence submitted successfully!</strong> Our support team will review your evidence and get back to you.
                                </div>

                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-12 md:col-span-6">
                                        <h6>Submission Details</h6>
                                        <p><strong>Submitted At:</strong> {{ $evidenceRequest->submitted_at->format('M d, Y \a\t g:i A') }}</p>
                                        <p><strong>Description:</strong> {{ $evidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                        @if(isset($evidenceRequest->submitted_evidence['additional_notes']))
                                            <p><strong>Additional Notes:</strong> {{ $evidenceRequest->submitted_evidence['additional_notes'] }}</p>
                                        @endif
                                    </div>
                                    <div class="col-span-12 md:col-span-6">
                                        <h6>Submitted Files</h6>
                                        @if(isset($evidenceRequest->submitted_evidence['files']))
                                            <div class="evidence-files">
                                                @foreach($evidenceRequest->submitted_evidence['files'] as $file)
                                                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-2">
                                                        <div class="p-4 sm:p-5 p-2">
                                                            <div class="flex justify-between items-center">
                                                                <div>
                                                                    <small class="text-slate-500">{{ $file['filename'] }}</small><br>
                                                                    <small class="text-slate-500">{{ number_format($file['size'] / 1024, 1) }} KB</small>
                                                                </div>
                                                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                                    <i class="fas fa-download"></i> View
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-slate-500">No files submitted.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($evidenceRequest->status === 'overdue')
                        <!-- Overdue Notice -->
                        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Deadline Expired:</strong> The deadline for submitting evidence has passed. Please contact our support team for assistance.
                        </div>
                    @endif

                    @if($appeal)
                    <!-- Both Parties Evidence Status -->
                     <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
                         <div class="border-b border-slate-200 px-4 py-3">
                             <h6 class="mb-0"><i class="fas fa-users"></i> Both Parties Evidence Status</h6>
                         </div>
                         <div class="p-4 sm:p-5">
                             <div class="grid grid-cols-12 gap-4">
                                 <div class="col-span-12 md:col-span-6">
                                     <h6 class="mb-3">
                                         <i class="fas fa-user text-primary"></i> Buyer Evidence
                                         @if($appeal->buyerEvidenceRequest)
                                             <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $appeal->buyerEvidenceRequest->getStatusBadgeClass() }} ml-2">
                                                 {{ $appeal->buyerEvidenceRequest->getStatusLabel() }}
                                             </span>
                                         @else
                                             <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 ml-2">Pending</span>
                                         @endif
                                     </h6>
                                     
                                     @if($appeal->buyerEvidenceRequest && $appeal->buyerEvidenceRequest->isSubmitted())
                                         <div class="evidence-submission-card border rounded p-3 mb-3">
                                             <div class="flex justify-between items-start mb-2">
                                                 <strong>Submitted:</strong>
                                                 <small class="text-slate-500">{{ $appeal->buyerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</small>
                                             </div>
                                             <p class="mb-2"><strong>Description:</strong> {{ $appeal->buyerEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                             
                                             @if(isset($appeal->buyerEvidenceRequest->submitted_evidence['files']))
                                                 <div class="submitted-files">
                                                     <strong>Files:</strong>
                                                     @foreach($appeal->buyerEvidenceRequest->submitted_evidence['files'] as $file)
                                                         <div class="file-item flex justify-between items-center mt-1">
                                                             <small class="text-truncate">{{ $file['filename'] }}</small>
                                                             <a href="{{ Storage::url($file['path']) }}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                                 <i class="fas fa-eye"></i>
                                                             </a>
                                                         </div>
                                                     @endforeach
                                                 </div>
                                             @endif
                                         </div>
                                     @else
                                         <div class="text-slate-500">
                                             <i class="fas fa-clock"></i> Waiting for buyer to submit evidence
                                         </div>
                                     @endif
                                 </div>
                                 
                                 <div class="col-span-12 md:col-span-6">
                                     <h6 class="mb-3">
                                         <i class="fas fa-shop text-emerald-600"></i> Seller Evidence
                                         @if($appeal->sellerEvidenceRequest)
                                             <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $appeal->sellerEvidenceRequest->getStatusBadgeClass() }} ml-2">
                                                 {{ $appeal->sellerEvidenceRequest->getStatusLabel() }}
                                             </span>
                                         @else
                                             <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 ml-2">Pending</span>
                                         @endif
                                     </h6>
                                     
                                     @if($appeal->sellerEvidenceRequest && $appeal->sellerEvidenceRequest->isSubmitted())
                                         <div class="evidence-submission-card border rounded p-3 mb-3">
                                             <div class="flex justify-between items-start mb-2">
                                                 <strong>Submitted:</strong>
                                                 <small class="text-slate-500">{{ $appeal->sellerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</small>
                                             </div>
                                             <p class="mb-2"><strong>Description:</strong> {{ $appeal->sellerEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                             
                                             @if(isset($appeal->sellerEvidenceRequest->submitted_evidence['files']))
                                                 <div class="submitted-files">
                                                     <strong>Files:</strong>
                                                     @foreach($appeal->sellerEvidenceRequest->submitted_evidence['files'] as $file)
                                                         <div class="file-item flex justify-between items-center mt-1">
                                                             <small class="text-truncate">{{ $file['filename'] }}</small>
                                                             <a href="{{ Storage::url($file['path']) }}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                                 <i class="fas fa-eye"></i>
                                                             </a>
                                                         </div>
                                                     @endforeach
                                                 </div>
                                             @endif
                                         </div>
                                     @else
                                         <div class="text-slate-500">
                                             <i class="fas fa-clock"></i> Waiting for seller to submit evidence
                                         </div>
                                     @endif
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Appeal Progress Timeline -->
                     <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
                         <div class="border-b border-slate-200 px-4 py-3">
                             <h6 class="mb-0"><i class="fas fa-chart-line"></i> Appeal Progress Timeline</h6>
                         </div>
                         <div class="p-4 sm:p-5">
                             <div class="timeline">
                                 <div class="timeline-item">
                                     <div class="timeline-marker bg-primary"></div>
                                     <div class="timeline-content">
                                         <h6 class="timeline-title">Appeal Submitted</h6>
                                          <p class="timeline-text">{{ $appeal->created_at->format('M d, Y \a\t g:i A') }}</p>
                                          <p class="timeline-text">{{ $appeal->appealedBy->name }} submitted an appeal</p>
                                     </div>
                                 </div>

                                 <div class="timeline-item">
                                     <div class="timeline-marker bg-amber-100"></div>
                                     <div class="timeline-content">
                                         <h6 class="timeline-title">Evidence Requested</h6>
                                         <p class="timeline-text">Cetsy support team requested evidence from both parties</p>
                                         <p class="timeline-text">Deadline: {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                     </div>
                                 </div>

                                 @if($appeal->buyerEvidenceRequest && $appeal->buyerEvidenceRequest->isSubmitted())
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-success"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">Buyer Evidence Submitted</h6>
                                             <p class="timeline-text">Buyer submitted evidence on {{ $appeal->buyerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</p>
                                         </div>
                                     </div>
                                 @endif

                                 @if($appeal->sellerEvidenceRequest && $appeal->sellerEvidenceRequest->isSubmitted())
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-success"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">Seller Evidence Submitted</h6>
                                             <p class="timeline-text">Seller submitted evidence on {{ $appeal->sellerEvidenceRequest->submitted_at->format('M d, Y g:i A') }}</p>
                                         </div>
                                     </div>
                                 @endif

                                 @if($appeal->buyerEvidenceRequest && $appeal->buyerEvidenceRequest->isSubmitted() && 
                                     $appeal->sellerEvidenceRequest && $appeal->sellerEvidenceRequest->isSubmitted())
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-sky-100"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">All Evidence Received</h6>
                                             <p class="timeline-text">Both parties have submitted evidence. Cetsy support team is now reviewing.</p>
                                         </div>
                                     </div>
                                 @endif

                                 @if($appeal->status === 'approved' || $appeal->status === 'rejected')
                                     <div class="timeline-item">
                                         <div class="timeline-marker bg-{{ $appeal->status === 'approved' ? 'success' : 'danger' }}"></div>
                                         <div class="timeline-content">
                                             <h6 class="timeline-title">Appeal {{ ucfirst($appeal->status) }}</h6>
                                             <p class="timeline-text">{{ $appeal->reviewed_at->format('M d, Y g:i A') }}</p>
                                             @if($appeal->review_notes)
                                                 <p class="timeline-text">{{ $appeal->review_notes }}</p>
                                             @endif
                                         </div>
                                     </div>
                                 @endif
                             </div>
                         </div>
                     </div>
                    @endif
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
const evidenceFilesInput = document.getElementById('evidence_files');
if (evidenceFilesInput) {
    evidenceFilesInput.addEventListener('change', function(e) {
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
}

// Countdown timer for deadline
@if($evidenceRequest->isPending() && !$evidenceRequest->isDeadlineExpired())
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




