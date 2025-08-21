@extends('layouts.app')

@section('title', 'Submit Appeal')

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Submit Appeal</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Dispute Summary -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Dispute Summary</h6>
                        <p class="mb-2"><strong>Type:</strong> {{ $dispute->getTypeLabel() }}</p>
                        <p class="mb-2"><strong>Order:</strong> #{{ $dispute->order->order_number }}</p>
                        <p class="mb-2"><strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}</p>
                        @if($dispute->refund_amount)
                            <p class="mb-0"><strong>Refund Amount:</strong> ${{ number_format($dispute->refund_amount, 2) }}</p>
                        @endif
                    </div>

                    <!-- Appeal Deadline Warning -->
                    @if($dispute->isAppealDeadlineExpired())
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Appeal Deadline Expired</h6>
                            <p class="mb-0">The appeal deadline has passed. You can no longer appeal this decision.</p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Appeal Deadline</h6>
                            <p class="mb-0">
                                You have <strong>{{ $dispute->getAppealDeadlineDaysLeft() }} days</strong> remaining to submit your appeal.
                                <br>
                                <small class="text-muted">Deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}</small>
                            </p>
                        </div>

                        <form action="{{ route('disputes.appeal.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Appeal Reason -->
                            <div class="mb-3">
                                <label for="reason" class="form-label">Appeal Reason *</label>
                                <textarea name="reason" id="reason" rows="5" class="form-control @error('reason') is-invalid @enderror" 
                                    placeholder="Please explain why you believe the decision should be reconsidered. Provide specific reasons and any new information..." required>{{ old('reason') }}</textarea>
                                <div class="form-text">
                                    Be specific about why you disagree with the decision. Provide new evidence or information that wasn't available during the initial review.
                                </div>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- New Evidence Upload -->
                            <div class="mb-3">
                                <label for="new_evidence" class="form-label">New Evidence (Optional)</label>
                                <input type="file" name="new_evidence[]" id="new_evidence" class="form-control @error('new_evidence.*') is-invalid @enderror" 
                                    multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <div class="form-text">
                                    Upload any new documents, screenshots, or photos that support your appeal. 
                                    Max 10MB per file. Supported formats: JPG, PNG, PDF, DOC, DOCX
                                </div>
                                @error('new_evidence.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Appeal Process Information -->
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Appeal Process</h6>
                                <ul class="mb-0">
                                    <li>Your appeal will be reviewed by a senior support team member</li>
                                    <li>Review process takes up to 48 hours</li>
                                    <li>If approved, the dispute will be reopened for further review</li>
                                    <li>If rejected, the original decision becomes final</li>
                                    <li>This is your only opportunity to appeal</li>
                                </ul>
                            </div>

                            <!-- Important Notes -->
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">Important Notes</h6>
                                <ul class="mb-0">
                                    <li>Appeals are only considered for new evidence or procedural errors</li>
                                    <li>Simply disagreeing with the decision is not sufficient grounds</li>
                                    <li>All communications must remain professional and respectful</li>
                                    <li>The appeal decision is final and binding</li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('disputes.show', $dispute->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dispute
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-gavel"></i> Submit Appeal
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File size validation
    const fileInput = document.getElementById('new_evidence');
    if (fileInput) {
        const maxSize = 10 * 1024 * 1024; // 10MB

        fileInput.addEventListener('change', function() {
            const files = this.files;
            for (let i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    alert(`File "${files[i].name}" is too large. Maximum size is 10MB.`);
                    this.value = '';
                    return;
                }
            }
        });
    }

    // Character counter for reason
    const reason = document.getElementById('reason');
    if (reason) {
        const maxLength = 1000;
        
        reason.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            const counter = this.parentNode.querySelector('.form-text');
            if (remaining < 100) {
                counter.innerHTML = `<span class="text-${remaining < 0 ? 'danger' : 'warning'}">${remaining} characters remaining</span>`;
            }
        });
    }

    // Countdown timer for appeal deadline
    @if(!$dispute->isAppealDeadlineExpired())
        const deadline = new Date('{{ $dispute->appeal_deadline }}').getTime();
        
        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            
            const deadlineElement = document.querySelector('.alert-warning p');
            if (deadlineElement) {
                if (distance > 0) {
                    deadlineElement.innerHTML = `
                        You have <strong>${days} days, ${hours} hours, ${minutes} minutes</strong> remaining to submit your appeal.
                        <br>
                        <small class="text-muted">Deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}</small>
                    `;
                } else {
                    deadlineElement.innerHTML = '<strong>Appeal deadline has expired!</strong>';
                    clearInterval(countdown);
                    location.reload(); // Refresh to show expired state
                }
            }
        }, 60000); // Update every minute
    @endif
});
</script>
@endpush
@endsection
