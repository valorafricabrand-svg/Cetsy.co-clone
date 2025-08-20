@extends('layouts.app')

@section('title', 'Dispute Details')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Dispute Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Dispute #{{ $dispute->id }}</h4>
                        <span class="badge {{ $dispute->getStatusBadgeClass() }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Dispute Type</h6>
                            <p class="mb-3">{{ $dispute->getTypeLabel() }}</p>
                            
                            <h6>Order</h6>
                            <p class="mb-3">
                                <a href="{{ route('orders.show', $dispute->order->id) }}" class="text-decoration-none">
                                    Order #{{ $dispute->order->order_number }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Created</h6>
                            <p class="mb-3">{{ $dispute->created_at->format('M d, Y \a\t g:i A') }}</p>
                            
                            <h6>Parties</h6>
                            <p class="mb-3">
                                <strong>Buyer:</strong> {{ $dispute->buyer->name }}<br>
                                <strong>Seller:</strong> {{ $dispute->seller->name }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6>Description</h6>
                        <p class="mb-0">{{ $dispute->description }}</p>
                    </div>

                    @if($dispute->evidence && count($dispute->evidence) > 0)
                        <div class="mb-3">
                            <h6>Evidence</h6>
                            <div class="row">
                                @foreach($dispute->evidence as $file)
                                    <div class="col-md-4 mb-2">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                @if(in_array($file['mime_type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                    <img src="{{ Storage::url($file['path']) }}" 
                                                         alt="{{ $file['filename'] }}" 
                                                         class="img-fluid mb-2" style="max-height: 100px;">
                                                @else
                                                    <i class="fas fa-file fa-3x text-muted mb-2"></i>
                                                @endif
                                                <p class="small mb-1">{{ $file['filename'] }}</p>
                                                <a href="{{ Storage::url($file['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($dispute->isResolved())
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Resolution</h6>
                            <p class="mb-2">{{ $dispute->resolution }}</p>
                            <strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}
                            @if($dispute->refund_amount)
                                <br><strong>Refund Amount:</strong> ${{ number_format($dispute->refund_amount, 2) }}
                            @endif
                            <br><strong>Resolved:</strong> {{ $dispute->resolved_at->format('M d, Y \a\t g:i A') }}
                        </div>
                    @endif

                    @if($dispute->appeal)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Appeal Status</h6>
                            <p class="mb-2"><strong>Reason:</strong> {{ $dispute->appeal->reason }}</p>
                            <p class="mb-2"><strong>Status:</strong> {{ ucfirst($dispute->appeal->status) }}</p>
                            @if($dispute->appeal->review_notes)
                                <p class="mb-0"><strong>Review Notes:</strong> {{ $dispute->appeal->review_notes }}</p>
                            @endif
                        </div>
                    @endif

                    @if($dispute->canBeAppealed() && !$dispute->isAppealDeadlineExpired())
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Appeal Available</h6>
                            <p class="mb-2">You have {{ $dispute->getAppealDeadlineDaysLeft() }} days to appeal this decision.</p>
                            <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="btn btn-warning">
                                Submit Appeal
                            </a>
                        </div>
                    @endif

                    @if($dispute->isAppealDeadlineExpired() && $dispute->can_appeal)
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Appeal Deadline Expired</h6>
                            <p class="mb-0">The appeal deadline has passed. This decision is now final.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Messages -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Messages</h5>
                </div>
                <div class="card-body">
                    <div class="messages-container" style="max-height: 400px; overflow-y: auto;">
                        @foreach($messages as $message)
                            <div class="message mb-3 {{ $message->getMessageTypeClass() }}">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        @if($message->user)
                                            <img src="{{ $message->user->profile_photo_url }}" 
                                                 alt="{{ $message->user->name }}" 
                                                 class="rounded-circle" 
                                                 width="40" height="40">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-robot text-white"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong>{{ $message->user ? $message->user->name : 'System' }}</strong>
                                            <small class="text-muted">{{ $message->created_at->format('M d, Y g:i A') }}</small>
                                        </div>
                                        <p class="mb-1">{{ $message->message }}</p>
                                        
                                        @if($message->hasAttachments())
                                            <div class="attachments">
                                                <small class="text-muted">Attachments ({{ $message->getAttachmentsCount() }})</small>
                                                <div class="row mt-2">
                                                    @foreach($message->attachments as $attachment)
                                                        <div class="col-md-3 mb-2">
                                                            @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                                <img src="{{ Storage::url($attachment['path']) }}" 
                                                                     alt="{{ $attachment['filename'] }}" 
                                                                     class="img-fluid rounded" 
                                                                     style="max-height: 80px;">
                                                            @else
                                                                <div class="text-center p-2 border rounded">
                                                                    <i class="fas fa-file fa-2x text-muted"></i>
                                                                    <p class="small mb-0">{{ $attachment['filename'] }}</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Add Message Form -->
                    @if($dispute->status !== 'final')
                        <hr>
                        <form action="{{ route('disputes.messages.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="form-label">Add Message</label>
                                <textarea name="message" id="message" rows="3" class="form-control @error('message') is-invalid @enderror" 
                                    placeholder="Type your message here..." required></textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="attachments" class="form-label">Attachments (Optional)</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control" 
                                    multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <div class="form-text">Max 10MB per file. Supported: JPG, PNG, PDF, DOC, DOCX</div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Dispute Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('disputes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Disputes
                        </a>
                        
                        @if($dispute->canBeAppealed())
                            <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="btn btn-warning">
                                <i class="fas fa-gavel"></i> Submit Appeal
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dispute Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Dispute Created</h6>
                                <small class="text-muted">{{ $dispute->created_at->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>

                        @if($dispute->isUnderReview())
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Under Review</h6>
                                    <small class="text-muted">Being reviewed by Cetsy support</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->isResolved())
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Resolved</h6>
                                    <small class="text-muted">{{ $dispute->resolved_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->appeal)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Appeal Submitted</h6>
                                    <small class="text-muted">{{ $dispute->appeal->created_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->isFinal())
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Final Decision</h6>
                                    <small class="text-muted">No further appeals possible</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.message.buyer-message { background-color: #e3f2fd; }
.message.seller-message { background-color: #f3e5f5; }
.message.admin-message { background-color: #fff3e0; }
.message.system-message { background-color: #f1f8e9; }

.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to bottom of messages
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // File size validation
    const fileInput = document.getElementById('attachments');
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
});
</script>
@endpush
@endsection
