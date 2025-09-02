@extends('layouts.app')

@section('title', 'Dispute Details')

@section('content')
<div class="content">
    {{-- Dispute Summary Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <h2 class="mb-0 me-3">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                    Dispute #{{ $dispute->id }}
                                </h2>
                                <span class="badge {{ $dispute->getStatusBadgeClass() }} fs-6 px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                </span>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar3"></i> Created {{ $dispute->created_at->diffForHumans() }}
                                @if($dispute->isResolved())
                                    • <i class="bi bi-check-circle text-success"></i> Resolved {{ $dispute->resolved_at->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-md-end">
                                <a href="{{ route('disputes.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> Back to Disputes
                                </a>
                                @if($dispute->canBeAppealed())
                                    <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-gavel"></i> Appeal
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dispute Statistics --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $allMessages->count() }}</h4>
                    <p class="mb-0 small">Total Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-chat fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $orderMessages->count() }}</h4>
                    <p class="mb-0 small">Order Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $disputeMessages->count() }}</h4>
                    <p class="mb-0 small">Dispute Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-paperclip fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $dispute->evidence ? count($dispute->evidence) : 0 }}</h4>
                    <p class="mb-0 small">Evidence Files</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Evidence Files Summary Section --}}
    @php
        $totalEvidenceFiles = 0;
        $totalEvidenceSize = 0;
        
        // Count initial dispute evidence
        if ($dispute->evidence && count($dispute->evidence) > 0) {
            $totalEvidenceFiles += count($dispute->evidence);
            $totalEvidenceSize += collect($dispute->evidence)->sum('size');
        }
        
        // Count appeal evidence
        if ($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty()) {
            foreach($dispute->appeal->evidenceRequests as $evidenceRequest) {
                if ($evidenceRequest->submitted_evidence && count($evidenceRequest->submitted_evidence) > 0) {
                    $totalEvidenceFiles += count($evidenceRequest->submitted_evidence);
                    $totalEvidenceSize += collect($evidenceRequest->submitted_evidence)->sum('size');
                }
            }
        }
        
        // Count message attachments
        foreach($allMessages as $message) {
            if (isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0) {
                $totalEvidenceFiles += count($message->attachments);
                $totalEvidenceSize += collect($message->attachments)->sum('size');
            }
        }
    @endphp
    
    @if($totalEvidenceFiles > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="bi bi-files text-primary"></i> 
                                    Evidence Files Overview
                                </h5>
                                <p class="text-muted mb-0">
                                    Total {{ $totalEvidenceFiles }} files ({{ number_format($totalEvidenceSize / 1024 / 1024, 2) }} MB) available for review
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="d-flex gap-2 justify-content-md-end">
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        <i class="bi bi-paperclip"></i> {{ $dispute->evidence ? count($dispute->evidence) : 0 }} Initial
                                    </span>
                                    @if($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty())
                                        @php
                                            $appealEvidenceCount = 0;
                                            foreach($dispute->appeal->evidenceRequests as $evidenceRequest) {
                                                if ($evidenceRequest->submitted_evidence) {
                                                    $appealEvidenceCount += count($evidenceRequest->submitted_evidence);
                                                }
                                            }
                                        @endphp
                                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                            <i class="bi bi-gavel"></i> {{ $appealEvidenceCount }} Appeal
                                        </span>
                                    @endif
                                    @php
                                        $messageAttachmentsCount = 0;
                                        foreach($allMessages as $message) {
                                            if (isset($message->attachments) && is_array($message->attachments)) {
                                                $messageAttachmentsCount += count($message->attachments);
                                            }
                                        }
                                    @endphp
                                    <span class="badge bg-info text-dark fs-6 px-3 py-2">
                                        <i class="bi bi-chat"></i> {{ $messageAttachmentsCount }} Messages
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Order Context Header -->
            @if($order)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-box"></i> Order Context
                            </h5>
                            <small class="text-muted">
                                This dispute is related to Order #{{ $order->id }} from {{ $order->shop->name ?? 'the shop' }}
                            </small>
                        </div>
                        <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View Full Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p class="mb-2">
                                <strong>Order #:</strong> {{ $order->id }}<br>
                                <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                                <strong>Status:</strong> 
                                <span class="badge {{ $order->getStatusBadgeClass() }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Items</h6>
                            @if($orderItems->isNotEmpty())
                                @foreach($orderItems->take(3) as $item)
                                    <div class="d-flex align-items-center mb-2">
                                        @if($item->product && $item->product->featured_image)
                                            <img src="{{ Storage::url($item->product->featured_image) }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="rounded me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <small class="d-block">{{ Str::limit($item->product->name ?? 'Product', 30) }}</small>
                                            <small class="text-muted">Qty: {{ $item->quantity }}</small>
                                        </div>
                                    </div>
                                @endforeach
                                @if($orderItems->count() > 3)
                                    <small class="text-muted">+{{ $orderItems->count() - 3 }} more items</small>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
                    {{-- Initial Dispute Description - Prominently Displayed --}}
                    <div class="alert alert-warning mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-3" style="font-size: 1.5rem; margin-top: 2px;"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-2">
                                    <strong>Initial Dispute Description</strong>
                                    <small class="text-muted ms-2">by {{ $dispute->buyer_id === auth()->id() ? 'You' : $dispute->buyer->name }}</small>
                                </h6>
                                <p class="mb-3 fs-6">{{ $dispute->description }}</p>
                                
                                {{-- Initial Evidence Display --}}
                                @if($dispute->evidence && count($dispute->evidence) > 0)
                                    <div class="mt-3">
                                        <h6 class="mb-2"><i class="bi bi-paperclip"></i> Initial Evidence ({{ count($dispute->evidence) }})</h6>
                                        <div class="row g-2">
                                            @foreach($dispute->evidence as $file)
                                                <div class="col-md-3 col-sm-4 col-6">
                                                    <div class="evidence-item border rounded p-2 text-center">
                                                        @if(in_array($file['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                            <img src="{{ Storage::url($file['path']) }}" 
                                                                 alt="{{ $file['filename'] }}" 
                                                                 class="img-fluid rounded mb-2" 
                                                                 style="max-height: 80px; width: 100%; object-fit: cover;"
                                                                 onclick="openImageModal('{{ Storage::url($file['path']) }}', '{{ $file['filename'] }}')">
                                                        @elseif(in_array($file['mime_type'], ['application/pdf']))
                                                            <div class="bg-danger text-white rounded p-3 mb-2">
                                                                <i class="bi bi-file-pdf fs-4"></i>
                                                            </div>
                                                        @elseif(in_array($file['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                            <div class="bg-primary text-white rounded p-3 mb-2">
                                                                <i class="bi bi-file-word fs-4"></i>
                                                            </div>
                                                        @else
                                                            <div class="bg-secondary text-white rounded p-3 mb-2">
                                                                <i class="bi bi-file-earmark fs-4"></i>
                                                            </div>
                                                        @endif
                                                        <div class="small text-truncate" title="{{ $file['filename'] }}">
                                                            {{ Str::limit($file['filename'], 20) }}
                                                        </div>
                                                        <div class="small text-muted">
                                                            {{ number_format($file['size'] / 1024, 1) }} KB
                                                        </div>
                                                        <a href="{{ Storage::url($file['path']) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary mt-1">
                                                            <i class="bi bi-download"></i> View
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Dispute Type</h6>
                            <p class="mb-3">{{ $dispute->getTypeLabel() }}</p>
                            
                            <h6>Order</h6>
                            <p class="mb-3">
                                <a href="{{ route('buyer.orders.show', $dispute->order->id) }}" class="text-decoration-none">
                                    Order #{{ $dispute->order->id }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Created</h6>
                            <p class="mb-3">{{ $dispute->created_at->format('M d, Y \a\t g:i A') }}</p>
                            
                                                         <h6>Parties</h6>
                             <p class="mb-3">
                                 <strong>Buyer:</strong> {{ $dispute->buyer->name }}<br>
                                 <strong>Shop:</strong> {{ $dispute->order->shop->name ?? $dispute->seller->name }}
                             </p>
                        </div>
                    </div>

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
                        
                        {{-- Appeal Evidence Files Section --}}
                        @if($dispute->appeal->new_evidence && count($dispute->appeal->new_evidence) > 0)
                            <div class="card mb-4 border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="bi bi-paperclip"></i> Appeal Evidence Files
                                        <span class="badge bg-dark ms-2">{{ count($dispute->appeal->new_evidence) }} file(s)</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Appeal Evidence</h6>
                                        <p class="mb-0">These files were submitted with the appeal by <strong>{{ $dispute->appeal->appealedBy->name }}</strong> on {{ $dispute->appeal->created_at->format('M d, Y \a\t g:i A') }}.</p>
                                    </div>
                                    
                                    <div class="row g-3">
                                        @foreach($dispute->appeal->new_evidence as $index => $file)
                                            <div class="col-md-4 col-sm-6 col-12">
                                                <div class="evidence-item border rounded p-3 text-center h-100">
                                                    @if(in_array($file['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                        <img src="{{ Storage::url($file['path']) }}" 
                                                             alt="{{ $file['filename'] }}" 
                                                             class="img-fluid rounded mb-2" 
                                                             style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                                             onclick="openImageModal('{{ Storage::url($file['path']) }}', '{{ $file['filename'] }}')"
                                                             title="Click to view full size">
                                                    @elseif(in_array($file['mime_type'], ['application/pdf']))
                                                        <div class="bg-danger text-white rounded p-3 mb-2">
                                                            <i class="bi bi-file-pdf fs-1"></i>
                                                        </div>
                                                    @elseif(in_array($file['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                        <div class="bg-primary text-white rounded p-3 mb-2">
                                                            <i class="bi bi-file-word fs-1"></i>
                                                        </div>
                                                    @else
                                                        <div class="bg-secondary text-white rounded p-3 mb-2">
                                                            <i class="bi bi-file-earmark fs-1"></i>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="evidence-info">
                                                        <div class="fw-bold text-truncate mb-2" title="{{ $file['filename'] }}">
                                                            {{ Str::limit($file['filename'], 25) }}
                                                        </div>
                                                        <div class="small text-muted mb-2">
                                                            {{ number_format($file['size'] / 1024, 1) }} KB
                                                        </div>
                                                        <div class="d-flex gap-1 justify-content-center">
                                                            <a href="{{ Storage::url($file['path']) }}" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                            <a href="{{ Storage::url($file['path']) }}" 
                                                               download="{{ $file['filename'] }}"
                                                               class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    {{-- Evidence descriptions removed as field doesn't exist in Appeal model --}}
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($dispute->canBeAppealed())
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Appeal Available</h6>
                            @if($dispute->appeal_deadline)
                                <p class="mb-2">You have {{ $dispute->getAppealDeadlineDaysLeft() }} days to appeal this decision.</p>
                            @else
                                <p class="mb-2">You can submit an appeal for immediate review.</p>
                            @endif
                            <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#appealModal">
                                    <i class="bi bi-gavel"></i> Submit Appeal
                                </button>
                        </div>
                    @endif

                    {{-- Appeal Button Section --}}
                    @if($dispute->canBeAppealed() || 
                         ($dispute->status === 'under_review' && $dispute->created_at->diffInMinutes(now()) >= 5) ||
                         $dispute->status === 'pending')
                        <div class="card mb-4 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-gavel"></i> Submit Appeal
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                @if($dispute->status === 'resolved')
                                    <p class="mb-3">If you disagree with the decision, you can submit an appeal for review.</p>
                                    @if($dispute->appeal_deadline)
                                        <small class="d-block text-muted mb-3">
                                            Appeal deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}
                                        </small>
                                    @endif
                                @elseif($dispute->status === 'under_review')
                                    <p class="mb-3">If the admin review is taking too long or you have concerns about the process, you can submit an appeal.</p>
                                    <small class="d-block text-muted mb-3">
                                        Available after 5 minutes of admin review
                                    </small>
                                @elseif($dispute->status === 'pending')
                                    <p class="mb-3">If the seller is not responding to your dispute, you can submit an appeal for immediate review.</p>
                                    <small class="d-block text-muted mb-3">
                                        Available immediately - no waiting period required
                                    </small>
                                @endif
                                
                                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#appealModal">
                                    <i class="bi bi-gavel"></i> Submit Appeal
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($dispute->isAppealDeadlineExpired() && $dispute->can_appeal)
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Appeal Deadline Expired</h6>
                            <p class="mb-0">The appeal deadline has passed. This decision is now final.</p>
                        </div>
                    @endif

                    {{-- Evidence Requests Section --}}
                    @if($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty())
                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-file-earmark-text"></i> Evidence Requests
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($dispute->appeal->evidenceRequests as $evidenceRequest)
                                    <div class="evidence-request-item border rounded p-3 mb-3 {{ $evidenceRequest->isOverdue() ? 'border-danger' : 'border-info' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">
                                                Evidence Request #{{ $evidenceRequest->id }}
                                                @if($evidenceRequest->isOverdue())
                                                    <span class="badge bg-danger ms-2">Overdue</span>
                                                @elseif($evidenceRequest->status === 'pending')
                                                    <span class="badge bg-warning ms-2">Pending</span>
                                                @elseif($evidenceRequest->status === 'responded')
                                                    <span class="badge bg-success ms-2">Responded</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                Deadline: {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Requested From:</strong> {{ $evidenceRequest->requestedFrom->name }}
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Message:</strong>
                                            <div class="mt-2 p-3 bg-light rounded">
                                                {!! nl2br(e($evidenceRequest->message)) !!}
                                            </div>
                                        </div>
                                        
                                        @if($evidenceRequest->required_evidence_types)
                                            <div class="mb-3">
                                                <strong>Required Evidence Types:</strong>
                                                <div class="mt-2">
                                                    @foreach($evidenceRequest->required_evidence_types as $type)
                                                        <span class="badge bg-secondary me-1">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($evidenceRequest->status === 'responded' && $evidenceRequest->submitted_evidence)
                                            <div class="mb-3">
                                                <strong>Submitted Evidence:</strong>
                                                <div class="mt-2">
                                                    @foreach($evidenceRequest->submitted_evidence as $evidence)
                                                        <div class="evidence-item border rounded p-2 mb-2">
                                                            <strong>{{ $evidence['filename'] }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $evidence['description'] ?? 'No description' }}</small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($evidenceRequest->status === 'pending' && $evidenceRequest->requested_from === auth()->id())
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#evidenceResponseModal-{{ $evidenceRequest->id }}">
                                                    <i class="bi bi-upload"></i> Submit Evidence Response
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- Evidence Responses Section (Organized by Party) --}}
                    @if($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty())
                        @php
                            $respondedRequests = $dispute->appeal->evidenceRequests->where('status', 'responded');
                            $buyerEvidenceResponses = collect();
                            $sellerEvidenceResponses = collect();
                            
                            // Organize evidence responses by party
                            foreach($respondedRequests as $evidenceRequest) {
                                if ($evidenceRequest->submitted_evidence && count($evidenceRequest->submitted_evidence) > 0) {
                                    $evidenceData = [
                                        'evidenceRequest' => $evidenceRequest,
                                        'evidence' => $evidenceRequest->submitted_evidence,
                                        'response_notes' => $evidenceRequest->response_notes,
                                        'responded_at' => $evidenceRequest->responded_at,
                                        'requested_from' => $evidenceRequest->requestedFrom
                                    ];
                                    
                                    // Determine if it's buyer or seller evidence
                                    if ($evidenceRequest->requested_from === $dispute->buyer_id) {
                                        $buyerEvidenceResponses->push($evidenceData);
                                    } else {
                                        $sellerEvidenceResponses->push($evidenceData);
                                    }
                                }
                            }
                        @endphp
                        
                        {{-- Buyer Evidence Responses --}}
                        @if($buyerEvidenceResponses->isNotEmpty())
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-person-fill"></i> Buyer Evidence Responses
                                        <span class="badge bg-light text-dark ms-2">{{ $buyerEvidenceResponses->count() }} response(s)</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-primary">
                                        <h6 class="alert-heading">Evidence Responses from {{ $dispute->buyer->name }}</h6>
                                        <p class="mb-0">Evidence files submitted by the buyer in response to evidence requests.</p>
                                    </div>
                                    
                                    @foreach($buyerEvidenceResponses as $evidenceData)
                                        <div class="evidence-response-item border rounded p-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-person-circle"></i> 
                                                    Response from {{ $evidenceData['requested_from']->name }}
                                                </h6>
                                                <span class="badge bg-success">Responded {{ $evidenceData['responded_at']->format('M d, Y \a\t g:i A') }}</span>
                                            </div>
                                            
                                            @if($evidenceData['response_notes'])
                                                <div class="mb-3">
                                                    <strong>Response Notes:</strong>
                                                    <div class="mt-2 p-3 bg-light rounded">
                                                        {{ $evidenceData['response_notes'] }}
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($evidenceData['evidence'] && count($evidenceData['evidence']) > 0)
                                                <div class="mb-3">
                                                    <strong>Submitted Evidence Files:</strong>
                                                    <div class="row g-3 mt-2">
                                                        @foreach($evidenceData['evidence'] as $evidence)
                                                            <div class="col-md-4 col-sm-6 col-12">
                                                                <div class="evidence-item border rounded p-3 text-center h-100">
                                                                    @if(in_array($evidence['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                                        <img src="{{ Storage::url($evidence['path']) }}" 
                                                                             alt="{{ $evidence['filename'] }}" 
                                                                             class="img-fluid rounded mb-2" 
                                                                             style="max-height: 100px; width: 100%; object-fit: cover; cursor: pointer;"
                                                                             onclick="openImageModal('{{ Storage::url($evidence['path']) }}', '{{ $evidence['filename'] }}')"
                                                                             title="Click to view full size">
                                                                    @elseif(in_array($evidence['mime_type'], ['application/pdf']))
                                                                        <div class="bg-danger text-white rounded p-2 mb-2">
                                                                            <i class="bi bi-file-pdf fs-4"></i>
                                                                        </div>
                                                                    @elseif(in_array($evidence['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                                        <div class="bg-primary text-white rounded p-2 mb-2">
                                                                            <i class="bi bi-file-word fs-4"></i>
                                                                        </div>
                                                                    @else
                                                                        <div class="bg-secondary text-white rounded p-2 mb-2">
                                                                            <i class="bi bi-file-earmark fs-4"></i>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    <div class="evidence-info">
                                                                        <div class="fw-bold text-truncate mb-2" title="{{ $evidence['filename'] }}">
                                                                            {{ Str::limit($evidence['filename'], 20) }}
                                                                        </div>
                                                                        <div class="small text-muted mb-2">
                                                                            {{ number_format($evidence['size'] / 1024, 1) }} KB
                                                                        </div>
                                                                        <div class="d-flex gap-1 justify-content-center">
                                                                            <a href="{{ Storage::url($evidence['path']) }}" 
                                                                               target="_blank" 
                                                                               class="btn btn-sm btn-outline-primary">
                                                                                <i class="bi bi-eye"></i> View
                                                                            </a>
                                                                            <a href="{{ Storage::url($evidence['path']) }}" 
                                                                               download="{{ $evidence['filename'] }}"
                                                                               class="btn btn-sm btn-outline-secondary">
                                                                                <i class="bi bi-download"></i> Download
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    
                                    {{-- Buyer Evidence Summary --}}
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <h6 class="mb-2">Buyer Evidence Summary</h6>
                                        @php
                                            $buyerTotalFiles = $buyerEvidenceResponses->sum(function($data) {
                                                return count($data['evidence']);
                                            });
                                            $buyerTotalSize = $buyerEvidenceResponses->sum(function($data) {
                                                return collect($data['evidence'])->sum('size');
                                            });
                                        @endphp
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Total Files:</strong> {{ $buyerTotalFiles }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Total Size:</strong> {{ number_format($buyerTotalSize / 1024 / 1024, 2) }} MB
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        {{-- Seller Evidence Responses --}}
                        @if($sellerEvidenceResponses->isNotEmpty())
                            <div class="card mb-4 border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-shop"></i> Seller Evidence Responses
                                        <span class="badge bg-light text-dark ms-2">{{ $sellerEvidenceResponses->count() }} response(s)</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-success">
                                        <h6 class="alert-heading">Evidence Responses from {{ $order && $order->shop && $order->shop->name ? $order->shop->name : $dispute->seller->name }}</h6>
                                        <p class="mb-0">Evidence files submitted by the seller in response to evidence requests.</p>
                                    </div>
                                    
                                    @foreach($sellerEvidenceResponses as $evidenceData)
                                        <div class="evidence-response-item border rounded p-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-shop"></i> 
                                                    Response from {{ $evidenceData['requested_from']->name }}
                                                </h6>
                                                <span class="badge bg-success">Responded {{ $evidenceData['responded_at']->format('M d, Y \a\t g:i A') }}</span>
                                            </div>
                                            
                                            @if($evidenceData['response_notes'])
                                                <div class="mb-3">
                                                    <strong>Response Notes:</strong>
                                                    <div class="mt-2 p-3 bg-light rounded">
                                                        {{ $evidenceData['response_notes'] }}
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($evidenceData['evidence'] && count($evidenceData['evidence']) > 0)
                                                <div class="mb-3">
                                                    <strong>Submitted Evidence Files:</strong>
                                                    <div class="row g-3 mt-2">
                                                        @foreach($evidenceData['evidence'] as $evidence)
                                                            <div class="col-md-4 col-sm-6 col-12">
                                                                <div class="evidence-item border rounded p-3 text-center h-100">
                                                                    @if(in_array($evidence['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                                        <img src="{{ Storage::url($evidence['path']) }}" 
                                                                             alt="{{ $evidence['filename'] }}" 
                                                                             class="img-fluid rounded mb-2" 
                                                                             style="max-height: 100px; width: 100%; object-fit: cover; cursor: pointer;"
                                                                             onclick="openImageModal('{{ Storage::url($evidence['path']) }}', '{{ $evidence['filename'] }}')"
                                                                             title="Click to view full size">
                                                                    @elseif(in_array($evidence['mime_type'], ['application/pdf']))
                                                                        <div class="bg-danger text-white rounded p-2 mb-2">
                                                                            <i class="bi bi-file-pdf fs-4"></i>
                                                                        </div>
                                                                    @elseif(in_array($evidence['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                                        <div class="bg-primary text-white rounded p-2 mb-2">
                                                                            <i class="bi bi-file-word fs-4"></i>
                                                                        </div>
                                                                    @else
                                                                        <div class="bg-secondary text-white rounded p-2 mb-2">
                                                                            <i class="bi bi-file-earmark fs-4"></i>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    <div class="evidence-info">
                                                                        <div class="fw-bold text-truncate mb-2" title="{{ $evidence['filename'] }}">
                                                                            {{ Str::limit($evidence['filename'], 20) }}
                                                                        </div>
                                                                        <div class="small text-muted mb-2">
                                                                            {{ number_format($evidence['size'] / 1024, 1) }} KB
                                                                        </div>
                                                                        <div class="d-flex gap-1 justify-content-center">
                                                                            <a href="{{ Storage::url($evidence['path']) }}" 
                                                                               target="_blank" 
                                                                               class="btn btn-sm btn-outline-primary">
                                                                                <i class="bi bi-eye"></i> View
                                                                            </a>
                                                                            <a href="{{ Storage::url($evidence['path']) }}" 
                                                                               download="{{ $evidence['filename'] }}"
                                                                               class="btn btn-sm btn-outline-secondary">
                                                                                <i class="bi bi-download"></i> Download
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    
                                    {{-- Seller Evidence Summary --}}
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <h6 class="mb-2">Seller Evidence Summary</h6>
                                        @php
                                            $sellerTotalFiles = $sellerEvidenceResponses->sum(function($data) {
                                                return count($data['evidence']);
                                            });
                                            $sellerTotalSize = $sellerEvidenceResponses->sum(function($data) {
                                                return collect($data['evidence'])->sum('size');
                                            });
                                        @endphp
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Total Files:</strong> {{ $sellerTotalFiles }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Total Size:</strong> {{ number_format($sellerTotalSize / 1024 / 1024, 2) }} MB
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- General Appeal Section --}}
            @if($dispute->canBeAppealed() && (auth()->id() === $dispute->buyer_id || auth()->id() === $dispute->seller_id))
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-gavel"></i> Need Support Team Intervention?
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-2">If you need assistance from our support team, you can submit an appeal at any time.</p>
                                <small class="text-muted">This will allow our support team to review your case and provide assistance.</small>
                            </div>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#appealModal">
                                <i class="bi bi-gavel"></i> Appeal to Support Team
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Appeal Progress Section (Binance-style) --}}
            @if($dispute->appeal)
                <div class="card mb-4 border-info" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-balance-scale"></i> Appeal Progress
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Appeal Status</h6>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $dispute->appeal->status === 'pending' ? 'warning' : ($dispute->appeal->status === 'evidence_requested' ? 'info' : ($dispute->appeal->status === 'approved' ? 'success' : 'danger')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $dispute->appeal->status)) }}
                                    </span>
                                </p>
                                <p><strong>Appealed By:</strong> {{ $dispute->appeal->appealedBy->name }}</p>
                                <p><strong>Appealed At:</strong> {{ $dispute->appeal->created_at->format('M d, Y \a\t g:i A') }}</p>
                                <p><strong>Reason:</strong> {{ $dispute->appeal->reason }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Evidence Requests</h6>
                                @if($dispute->appeal->status === 'evidence_requested')
                                    @php
                                        $userEvidenceRequest = null;
                                        if (auth()->id() === $dispute->buyer_id) {
                                            $userEvidenceRequest = $dispute->appeal->buyerEvidenceRequest;
                                        } elseif (auth()->id() === $dispute->seller_id) {
                                            $userEvidenceRequest = $dispute->appeal->sellerEvidenceRequest;
                                        }
                                    @endphp
                                    
                                    {{-- Debug Information --}}
                                    
                                    
                                    @if($userEvidenceRequest)
                                        <div class="alert alert-{{ $userEvidenceRequest->status === 'submitted' ? 'success' : 'warning' }}">
                                            <h6 class="alert-heading">
                                                @if($userEvidenceRequest->status === 'submitted')
                                                    <i class="bi bi-check-circle"></i> Evidence Submitted
                                                @else
                                                    <i class="bi bi-clock"></i> Evidence Required
                                                @endif
                                            </h6>
                                            
                                            @if($userEvidenceRequest->status === 'pending')
                                                <p class="mb-2"><strong>Deadline:</strong> {{ $userEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                                <p class="mb-2"><strong>Days Left:</strong> {{ $userEvidenceRequest->getDaysUntilDeadline() }}</p>
                                                <p class="mb-3">{{ $userEvidenceRequest->request_message }}</p>
                                                
                                                <div class="d-grid">
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitEvidenceModal">
                                                        <i class="bi bi-upload"></i> Submit Evidence
                                                    </button>
                                                </div>
                                            @elseif($userEvidenceRequest->status === 'submitted')
                                                <p class="mb-2"><strong>Submitted:</strong> {{ $userEvidenceRequest->submitted_at->format('M d, Y \a\t g:i A') }}</p>
                                                <p class="mb-2"><strong>Description:</strong> {{ $userEvidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                                
                                                <div class="d-grid">
                                                    <a href="{{ route('evidence-requests.show', $userEvidenceRequest->id) }}" class="btn btn-outline-success">
                                                        <i class="bi bi-eye"></i> View Submitted Evidence
                                                    </a>
                                                </div>
                                            @else
                                                {{-- Show submit button for any status that's not submitted --}}
                                                <p class="mb-2"><strong>Deadline:</strong> {{ $userEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                                <p class="mb-2"><strong>Days Left:</strong> {{ $userEvidenceRequest->getDaysUntilDeadline() }}</p>
                                                <p class="mb-3">{{ $userEvidenceRequest->request_message }}</p>
                                                <p class="mb-3 text-muted"><strong>Current Status:</strong> {{ ucfirst($userEvidenceRequest->status) }}</p>
                                                
                                                <div class="d-grid">
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitEvidenceModal">
                                                        <i class="bi bi-upload"></i> Submit Evidence
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Show both parties' evidence status -->
                                    <div class="mt-3">
                                        <h6>Both Parties Evidence Status</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="evidence-status-card border rounded p-2 text-center">
                                                    <strong>Buyer</strong>
                                                    @if($dispute->appeal->buyerEvidenceRequest)
                                                        <div class="mt-1">
                                                            <span class="badge bg-{{ $dispute->appeal->buyerEvidenceRequest->getStatusBadgeClass() }}">
                                                                {{ ucfirst($dispute->appeal->buyerEvidenceRequest->status) }}
                                                            </span>
                                                        </div>
                                                        @if($dispute->appeal->buyerEvidenceRequest->status === 'submitted')
                                                            <small class="text-muted d-block mt-1">
                                                                Submitted: {{ $dispute->appeal->buyerEvidenceRequest->submitted_at->format('M d, g:i A') }}
                                                            </small>
                                                        @endif
                                                    @else
                                                        <div class="mt-1">
                                                            <span class="badge bg-secondary">Pending</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="evidence-status-card border rounded p-2 text-center">
                                                    <strong>Seller</strong>
                                                    @if($dispute->appeal->sellerEvidenceRequest)
                                                        <div class="mt-1">
                                                            <span class="badge bg-{{ $dispute->appeal->sellerEvidenceRequest->getStatusBadgeClass() }}">
                                                                {{ ucfirst($dispute->appeal->sellerEvidenceRequest->status) }}
                                                            </span>
                                                        </div>
                                                        @if($dispute->appeal->sellerEvidenceRequest->status === 'submitted')
                                                            <small class="text-muted d-block mt-1">
                                                                Submitted: {{ $dispute->appeal->sellerEvidenceRequest->submitted_at->format('M d, g:i A') }}
                                                            </small>
                                                        @endif
                                                    @else
                                                        <div class="mt-1">
                                                            <span class="badge bg-secondary">Pending</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- View All Evidence Button -->
                                        @if(($dispute->appeal->buyerEvidenceRequest && $dispute->appeal->buyerEvidenceRequest->status === 'submitted') || 
                                             ($dispute->appeal->sellerEvidenceRequest && $dispute->appeal->sellerEvidenceRequest->status === 'submitted'))
                                            <div class="mt-3 text-center">
                                                <a href="{{ route('evidence-requests.show', $userEvidenceRequest ? $userEvidenceRequest->id : $dispute->appeal->buyerEvidenceRequest->id) }}" 
                                                   class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye"></i> View All Evidence & Progress
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted">Evidence requests will appear here when the support team requests them.</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Appeal Timeline -->
                        <div class="mt-4">
                            <h6>Appeal Timeline</h6>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Appeal Submitted</h6>
                                        <p class="timeline-text">{{ $dispute->appeal->created_at->format('M d, Y \a\t g:i A') }}</p>
                                        <p class="timeline-text">{{ $dispute->appeal->appealedBy->name }} submitted an appeal</p>
                                    </div>
                                </div>

                                @if($dispute->appeal->status === 'evidence_requested')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Evidence Requested</h6>
                                            <p class="timeline-text">Cetsy support team requested evidence from both parties</p>
                                            @if($dispute->appeal->buyerEvidenceRequest)
                                                <p class="timeline-text">Deadline: {{ $dispute->appeal->buyerEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->appeal->status === 'approved' || $dispute->appeal->status === 'rejected')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $dispute->appeal->status === 'approved' ? 'success' : 'danger' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Appeal {{ ucfirst($dispute->appeal->status) }}</h6>
                                            <p class="timeline-text">{{ $dispute->appeal->reviewed_at->format('M d, Y \a\t g:i A') }}</p>
                                            @if($dispute->appeal->review_notes)
                                                <p class="timeline-text">{{ $dispute->appeal->review_notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Mutual Resolution Section --}}
            @if($dispute->canBeMutuallyResolved())
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-handshake"></i> Mutual Resolution
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($dispute->mutual_resolution_terms)
                            {{-- Show existing mutual resolution proposal --}}
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading">Proposed Resolution Terms</h6>
                                <p class="mb-2">{{ $dispute->mutual_resolution_terms }}</p>
                                <small class="text-muted">
                                    Proposed by: {{ $dispute->buyer_agreed_at && !$dispute->seller_agreed_at ? $dispute->buyer->name : $dispute->seller->name }}
                                </small>
                            </div>

                            {{-- Show agreement status --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2"></i>
                                        <span>Buyer ({{ $dispute->buyer->name }})</span>
                                        @if($dispute->buyer_agreed_at)
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-check-circle"></i> Agreed
                                            </span>
                                        @else
                                            <span class="badge bg-warning ms-2">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-shop me-2"></i>
                                        <span>Seller ({{ $dispute->seller->name }})</span>
                                        @if($dispute->seller_agreed_at)
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-check-circle"></i> Agreed
                                            </span>
                                        @else
                                            <span class="badge bg-warning ms-2">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Agree button for the party who hasn't agreed yet --}}
                            @if(($dispute->buyer_id === auth()->id() && !$dispute->buyer_agreed_at) || 
                                 ($dispute->seller_id === auth()->id() && !$dispute->seller_agreed_at))
                                <form action="{{ route('disputes.mutual-resolution.agree', $dispute->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> I Agree to These Terms
                                    </button>
                                </form>
                            @endif

                            @if($dispute->buyer_agreed_at && $dispute->seller_agreed_at)
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i>
                                    <strong>Both parties have agreed!</strong> This dispute will be automatically resolved.
                                </div>
                            @else
                                {{-- Appeal Button for Mutual Resolution Failure --}}
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Mutual Resolution Pending</strong>
                                    <p class="mb-0 mt-2">Waiting for both parties to agree on the proposed terms.</p>
                                </div>
                            @endif
                        @else
                            {{-- Show form to propose mutual resolution --}}
                            <p class="text-muted mb-3">
                                If you and the other party have reached an agreement, you can propose mutual resolution terms here.
                            </p>
                            
                            <form action="{{ route('disputes.mutual-resolution.initiate', $dispute->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="terms" class="form-label">Resolution Terms</label>
                                    <textarea name="terms" id="terms" rows="3" class="form-control" 
                                        placeholder="Describe the agreed resolution terms..." required></textarea>
                                    <div class="form-text">
                                        Clearly state what both parties have agreed to resolve this dispute.
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-handshake"></i> Propose Mutual Resolution
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Show mutual resolution status if already resolved --}}
            @if($dispute->isMutuallyResolved())
                <div class="alert alert-success mb-4">
                    <h6 class="alert-heading">
                        <i class="bi bi-handshake"></i> Mutually Resolved
                    </h6>
                    <p class="mb-2"><strong>Agreed Terms:</strong> {{ $dispute->mutual_resolution_terms }}</p>
                    <p class="mb-0">
                        <strong>Resolved:</strong> {{ $dispute->resolved_at->format('M d, Y \a\t g:i A') }}
                    </p>
                </div>
            @endif

            {{-- All Evidence Files Section (Always Visible) --}}
            @if($dispute->evidence && count($dispute->evidence) > 0)
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-files"></i> All Evidence Files
                            <span class="badge bg-light text-dark ms-2">{{ count($dispute->evidence) }} file(s)</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <h6 class="alert-heading">Evidence Files Overview</h6>
                            <p class="mb-0">All evidence files submitted for this dispute. These files are visible to all parties involved.</p>
                        </div>
                        
                        <div class="row g-3">
                            @foreach($dispute->evidence as $file)
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="evidence-item border rounded p-3 text-center h-100">
                                        @if(in_array($file['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                            <img src="{{ Storage::url($file['path']) }}" 
                                                 alt="{{ $file['filename'] }}" 
                                                 class="img-fluid rounded mb-2" 
                                                 style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                                 onclick="openImageModal('{{ Storage::url($file['path']) }}', '{{ $file['filename'] }}')"
                                                 title="Click to view full size">
                                        @elseif(in_array($file['mime_type'], ['application/pdf']))
                                            <div class="bg-danger text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-pdf fs-1"></i>
                                            </div>
                                        @elseif(in_array($file['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                            <div class="bg-primary text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-word fs-1"></i>
                                            </div>
                                        @else
                                            <div class="bg-secondary text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-earmark fs-1"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="evidence-info">
                                            <div class="fw-bold text-truncate mb-2" title="{{ $file['filename'] }}">
                                                {{ Str::limit($file['filename'], 25) }}
                                            </div>
                                            <div class="small text-muted mb-2">
                                                {{ number_format($file['size'] / 1024, 1) }} KB
                                            </div>
                                            <div class="small text-primary mb-2">
                                                <i class="bi bi-upload"></i> Initial Evidence
                                            </div>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ Storage::url($file['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ Storage::url($file['path']) }}" 
                                                   download="{{ $file['filename'] }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Evidence Files Summary --}}
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2">Evidence Files Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Total Files:</strong> {{ count($dispute->evidence) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Total Size:</strong> {{ number_format(collect($dispute->evidence)->sum('size') / 1024 / 1024, 2) }} MB
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>File Types:</strong>
                                @php
                                    $fileTypes = collect($dispute->evidence)->groupBy('mime_type')->map(function($group) {
                                        return $group->count() . ' ' . pathinfo($group->first()['filename'], PATHINFO_EXTENSION);
                                    })->join(', ');
                                @endphp
                                {{ $fileTypes }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- All Message Attachments Section -->
            @php
                $allAttachments = collect();
                foreach($allMessages as $message) {
                    if (isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0) {
                        foreach($message->attachments as $attachment) {
                            $attachment['message_sender'] = $message->user->name ?? 'Unknown User';
                            $attachment['message_date'] = $message->created_at;
                            $attachment['message_content'] = Str::limit($message->message ?? $message->body ?? '', 100);
                            $allAttachments->push($attachment);
                        }
                    }
                }
            @endphp
            
            @if($allAttachments->isNotEmpty())
                <div class="card mb-4 border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-paperclip"></i> All Message Attachments
                            <span class="badge bg-light text-dark ms-2">{{ $allAttachments->count() }} file(s)</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-secondary">
                            <h6 class="alert-heading">Message Attachments Overview</h6>
                            <p class="mb-0">All files attached to messages in this dispute. These files are visible to all parties involved.</p>
                        </div>
                        
                        <div class="row g-3">
                            @foreach($allAttachments as $attachment)
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="evidence-item border rounded p-3 text-center h-100">
                                        @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                            <img src="{{ Storage::url($attachment['path']) }}" 
                                                 alt="{{ $attachment['filename'] }}" 
                                                 class="img-fluid rounded mb-2" 
                                                 style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                                 onclick="openImageModal('{{ Storage::url($attachment['path']) }}', '{{ $attachment['filename'] }}')"
                                                 title="Click to view full size">
                                        @elseif(in_array($attachment['mime_type'], ['application/pdf']))
                                            <div class="bg-danger text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-pdf fs-1"></i>
                                            </div>
                                        @elseif(in_array($attachment['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                            <div class="bg-primary text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-word fs-1"></i>
                                            </div>
                                        @else
                                            <div class="bg-secondary text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-earmark fs-1"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="evidence-info">
                                            <div class="fw-bold text-truncate mb-2" title="{{ $attachment['filename'] }}">
                                                {{ Str::limit($attachment['filename'], 25) }}
                                            </div>
                                            <div class="small text-muted mb-2">
                                                {{ number_format($attachment['size'] / 1024, 1) }} KB
                                            </div>
                                            <div class="small text-secondary mb-2">
                                                <i class="bi bi-person"></i> {{ $attachment['message_sender'] }}
                                            </div>
                                            <div class="small text-muted mb-2" title="{{ $attachment['message_content'] }}">
                                                <i class="bi bi-chat"></i> {{ $attachment['message_content'] }}
                                            </div>
                                            <div class="small text-muted mb-2">
                                                <i class="bi bi-clock"></i> {{ $attachment['message_date']->format('M d, Y') }}
                                            </div>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ Storage::url($attachment['path']) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ Storage::url($attachment['path']) }}" 
                                                   download="{{ $attachment['filename'] }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Attachments Summary --}}
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2">Attachments Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Total Files:</strong> {{ $allAttachments->count() }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Total Size:</strong> {{ number_format($allAttachments->sum('size') / 1024 / 1024, 2) }} MB
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>File Types:</strong>
                                @php
                                    $attachmentTypes = $allAttachments->groupBy('mime_type')->map(function($group) {
                                        return $group->count() . ' ' . pathinfo($group->first()['filename'], PATHINFO_EXTENSION);
                                    })->join(', ');
                                @endphp
                                {{ $attachmentTypes }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Unified Messages Section -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-chat-dots"></i> Complete Communication History
                                <span class="badge bg-secondary ms-2">{{ $allMessages->count() }} messages</span>
                            </h5>
                            @if($order)
                                <small class="text-muted">
                                    <i class="bi bi-box"></i> Order #{{ $order->id }} - {{ $order->shop->name ?? 'Shop' }}
                                </small>
                            @endif
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-filter="all">
                                <i class="bi bi-chat-dots"></i> All ({{ $allMessages->count() }})
                            </button>
                            <button type="button" class="btn btn-outline-info" data-filter="order">
                                <i class="bi bi-chat"></i> Order ({{ $orderMessages->count() }})
                            </button>
                            <button type="button" class="btn btn-outline-warning" data-filter="dispute">
                                <i class="bi bi-exclamation-triangle"></i> Dispute ({{ $disputeMessages->count() }})
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($order)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Communication Context:</strong> This communication history shows all messages related to <strong>Order #{{ $order->id }}</strong> 
                            from <strong>{{ $order->shop->name ?? 'the shop' }}</strong>. It includes both order-related communications and dispute-specific messages.
                            
                            <div class="mt-2 small">
                                <strong>Message Breakdown:</strong>
                                <span class="badge bg-info me-2">{{ $orderMessages->count() }} Order Messages</span>
                                <span class="badge bg-warning me-2">{{ $disputeMessages->count() }} Dispute Messages</span>
                                <span class="badge bg-secondary">{{ $allMessages->count() }} Total Messages</span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="messages-container" style="max-height: 600px; overflow-y: auto;">
                        @forelse($allMessages as $message)
                            @php
                                // Validate that this message belongs to the current dispute's order
                                if (isset($message->order_id) && $order && $message->order_id !== $order->id) {
                                    continue; // Skip messages from other orders
                                }
                                
                                // Validate dispute messages belong to this dispute
                                if (isset($message->dispute_id) && $message->dispute_id !== $dispute->id) {
                                    continue; // Skip messages from other disputes
                                }
                                
                                // Safely determine message type and class
                                $messageType = $message->type ?? 'unknown';
                                $messageClass = 'default-message';
                                if ($messageType === 'buyer_message') $messageClass = 'buyer-message';
                                elseif ($messageType === 'seller_message') $messageClass = 'seller-message';
                                elseif ($messageType === 'system_message') $messageClass = 'system-message';
                                
                                // Determine user role for proper class assignment
                                $userRoleForClass = 'User';
                                
                                // Check for system messages first (regardless of user_id)
                                if ($message->type === 'system_message') {
                                    $userRoleForClass = 'System';
                                } elseif ($message->user_id) {
                                    if ($message->user_id === $dispute->buyer_id) {
                                        $userRoleForClass = 'Buyer';
                                    } elseif ($message->user_id === $dispute->seller_id) {
                                        $userRoleForClass = 'Seller';
                                    } else {
                                        $userRoleForClass = 'Other';
                                    }
                                }
                                
                                // Assign message class based on user role
                                if ($userRoleForClass === 'Buyer') $messageClass = 'buyer-message';
                                elseif ($userRoleForClass === 'Seller') $messageClass = 'seller-message';
                                elseif ($userRoleForClass === 'System') $messageClass = 'system-message';
                                elseif ($userRoleForClass === 'Other') $messageClass = 'other-message';
                                
                                // Safely determine if it's a dispute or order message
                                $isDisputeMessage = isset($message->is_dispute_message) ? $message->is_dispute_message : false;
                                $isOrderMessage = !$isDisputeMessage;
                                
                                // Safely get user info and determine profile image
                                $userName = $message->user->name ?? 'Unknown User';
                                $userPhoto = null;
                                $userRole = 'User';
                                
                                // Determine user role and profile image based on dispute context
                                
                                // Check for system messages first (regardless of user_id)
                                if ($message->type === 'system_message') {
                                    $userRole = 'System';
                                    $userName = 'System';
                                    $userPhoto = null;
                                } elseif ($message->user_id) {
                                    if ($message->user_id === $dispute->buyer_id) {
                                        $userRole = 'Buyer';
                                        // Get buyer's profile photo using the correct method
                                        if ($message->user->photo) {
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            // Use gravatar as fallback
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    } elseif ($message->user_id === $dispute->seller_id) {
                                        // For sellers, always show shop name if available, otherwise show "Seller"
                                        if ($order && $order->shop && $order->shop->name) {
                                            $userName = $order->shop->name;
                                        } else {
                                            $userName = 'Seller';
                                        }
                                        $userRole = 'Seller';
                                        
                                        // Get shop's profile photo (shop logo/image)
                                        if ($order && $order->shop && $order->shop->logo) {
                                            $userPhoto = asset('storage/' . $order->shop->logo);
                                        } elseif ($message->user->photo) {
                                            // Fallback to user's personal photo if shop logo not available
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            // Use gravatar as final fallback
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    } else {
                                        // For any other users (shouldn't happen in normal disputes), show as "Other"
                                        $userRole = 'Other';
                                        $userName = $message->user->name ?? 'Unknown User';
                                        if ($message->user->photo) {
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    }
                                }
                                
                                // Safely get message content
                                $messageContent = $message->message ?? $message->body ?? 'No message content';
                                
                                // Safely get attachments
                                $hasAttachments = isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0;
                                $attachmentsCount = $hasAttachments ? count($message->attachments) : 0;
                                
                                // Debug: Display profile image info (temporary)
                                if (app()->environment('local')) {
                                    echo "<!-- DEBUG: userPhoto = $userPhoto, userRole = $userRole -->";
                                }
                            @endphp
                            
                            <div class="message mb-4 {{ $messageClass }} {{ $isDisputeMessage ? 'dispute-message' : 'order-message' }}" 
                                 data-message-type="{{ $isDisputeMessage ? 'dispute' : 'order' }}">
                                
                                {{-- Message Header with Source Badge --}}
                                <div class="message-header d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        @if($message->is_dispute_message)
                                            <span class="badge bg-warning text-dark me-2">
                                                <i class="bi bi-exclamation-triangle"></i> Dispute
                                            </span>
                                        @else
                                            <span class="badge bg-info text-dark me-2">
                                                <i class="bi bi-chat"></i> Order
                                            </span>
                                        @endif
                                        
                                                                                <div class="d-flex align-items-center">
                                            @if($userPhoto && $userName !== 'Unknown User')
                                                <img src="{{ $userPhoto }}" 
                                                     alt="{{ $userName }}" 
                                                     class="rounded-circle me-2" 
                                                     width="32" height="32"
                                                     style="object-fit: cover;"
                                                     onerror="this.style.display='none'; this.nextElementSibling.nextElementSibling.style.display='block';">
                                                <strong>{{ $userName }}</strong>
                                                <span class="badge {{ $userRole === 'Buyer' ? 'bg-primary' : ($userRole === 'System' ? 'bg-secondary' : ($userRole === 'Seller' ? 'bg-success' : 'bg-warning')) }} ms-2">
                                                    {{ $userRole }}
                                                </span>
                                            @else
                                                <div class="rounded-circle avatar-fallback me-2" 
                                                     style="width: 32px; height: 32px; {{ $userRole === 'Buyer' ? 'background-color: #e3f2fd; color: #1976d2;' : ($userRole === 'System' ? 'background-color: #6c757d; color: white;' : ($userRole === 'Seller' ? 'background-color: #f3e5f5; color: #7b1fa2;' : 'background-color: #fff3cd; color: #856404;')) }}">
                                                    @if($userRole === 'Buyer')
                                                        <i class="bi bi-person-fill"></i>
                                                    @elseif($userRole === 'System')
                                                        <i class="bi bi-robot"></i>
                                                    @elseif($userRole === 'Seller')
                                                        <i class="bi bi-shop"></i>
                                                    @else
                                                        <i class="bi bi-person"></i>
                                                    @endif
                                                </div>
                                                <strong>{{ $userName }}</strong>
                                                <span class="badge {{ $userRole === 'Buyer' ? 'bg-primary' : ($userRole === 'System' ? 'bg-secondary' : ($userRole === 'Seller' ? 'bg-success' : 'bg-warning')) }} ms-2">
                                                    {{ $userRole }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-muted small">
                                        <i class="bi bi-clock"></i>
                                        {{ $message->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </div>

                                {{-- Message Content --}}
                                <div class="message-content p-3 rounded">
                                    <p class="mb-3">
                                        {{ $messageContent }}
                                    </p>
                                    
                                    {{-- Attachments Display --}}
                                    @if($hasAttachments)
                                        <div class="attachments-section border-top pt-3">
                                            <h6 class="mb-3">
                                                <i class="bi bi-paperclip"></i> 
                                                Attachments ({{ $attachmentsCount }})
                                            </h6>
                                            <div class="row g-3">
                                                @foreach($message->attachments ?? [] as $attachment)
                                                    <div class="col-md-4 col-sm-6 col-12">
                                                        <div class="attachment-item border rounded p-3 text-center h-100">
                                                            @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                                <img src="{{ Storage::url($attachment['path']) }}" 
                                                                     alt="{{ $attachment['filename'] }}" 
                                                                     class="img-fluid rounded mb-2" 
                                                                     style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                                                     onclick="openImageModal('{{ Storage::url($attachment['path']) }}', '{{ $attachment['filename'] }}')"
                                                                     title="Click to view full size">
                                                            @elseif(in_array($attachment['mime_type'], ['application/pdf']))
                                                                <div class="bg-danger text-white rounded p-3 mb-2">
                                                                    <i class="bi bi-file-pdf fs-1"></i>
                                                                </div>
                                                            @elseif(in_array($attachment['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                                <div class="bg-primary text-white rounded p-3 mb-2">
                                                                    <i class="bi bi-file-word fs-1"></i>
                                                                </div>
                                                            @else
                                                                <div class="bg-secondary text-white rounded p-3 mb-2">
                                                                    <i class="bi bi-file-earmark fs-1"></i>
                                                                </div>
                                                            @endif
                                                            
                                                            <div class="attachment-info">
                                                                <div class="fw-bold text-truncate" title="{{ $attachment['filename'] }}">
                                                                    {{ Str::limit($attachment['filename'], 25) }}
                                                                </div>
                                                                <div class="small text-muted mb-2">
                                                                    {{ number_format($attachment['size'] / 1024, 1) }} KB
                                                                </div>
                                                                <div class="d-flex gap-1 justify-content-center">
                                                                    <a href="{{ Storage::url($attachment['path']) }}" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-outline-primary">
                                                                        <i class="bi bi-eye"></i> View
                                                                    </a>
                                                                    <a href="{{ Storage::url($attachment['path']) }}" 
                                                                       download="{{ $attachment['filename'] }}"
                                                                       class="btn btn-sm btn-outline-secondary">
                                                                        <i class="bi bi-download"></i> Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-chat-dots fs-1 mb-3"></i>
                                <h5>No messages yet</h5>
                                <p>
                                    @if($order)
                                        No communication history found for Order #{{ $order->id }}.
                                    @else
                                        No communication history found for this dispute.
                                    @endif
                                    Start the conversation by sending a message below.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Add Message Form -->
                    @if($dispute->status !== 'final')
                        <hr class="my-4">
                        <form action="{{ route('disputes.messages.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    <i class="bi bi-chat-dots"></i> Add Message to Dispute
                                </label>
                                <textarea name="message" id="message" rows="4" class="form-control @error('message') is-invalid @enderror" 
                                    placeholder="Type your message here... Be clear and provide any relevant details or evidence." required></textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="attachments" class="form-label">
                                    <i class="bi bi-paperclip"></i> Attachments (Optional)
                                </label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control" 
                                    multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    Max 10MB per file. Supported: JPG, PNG, WebP, PDF, DOC, DOCX
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Send Message
                                </button>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> 
                                    Messages are sent immediately and visible to both parties
                                </small>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i>
                            This dispute is final and no further messages can be added.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('disputes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Disputes
                        </a>
                        
                        @if($order)
                        <a href="{{ route('orders.chat.show', $order->id) }}" class="btn btn-outline-info">
                            <i class="bi bi-chat"></i> Order Chat
                        </a>
                        @endif
                        
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

                        @if($dispute->mutual_resolution_terms)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Mutual Resolution Proposed</h6>
                                    <small class="text-muted">Terms: {{ Str::limit($dispute->mutual_resolution_terms, 50) }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->buyer_agreed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Buyer Agreed</h6>
                                    <small class="text-muted">{{ $dispute->buyer_agreed_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->seller_agreed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Seller Agreed</h6>
                                    <small class="text-muted">{{ $dispute->seller_agreed_at->format('M d, Y g:i A') }}</small>
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

{{-- Appeal Modal --}}
<div class="modal fade" id="appealModal" tabindex="-1" aria-labelledby="appealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="appealModalLabel">
                    <i class="bi bi-gavel"></i> Submit Appeal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('disputes.appeal.store', $dispute->id) }}" method="POST" enctype="multipart/form-data" id="appealForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Appeal Information</h6>
                        @if($dispute->status === 'resolved')
                            <p class="mb-0">Please provide a detailed reason for your appeal and any new evidence to support your case.</p>
                        @elseif($dispute->status === 'under_review')
                            <p class="mb-0">Please provide a detailed reason for your appeal regarding the admin review process and any new evidence to support your case.</p>
                        @elseif($dispute->status === 'pending')
                            <p class="mb-0">Please provide a detailed reason for your appeal regarding the seller's lack of response and any new evidence to support your case.</p>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason_category" class="form-label">Appeal Reason Category *</label>
                        <select name="reason_category" id="reason_category" class="form-control" required>
                            <option value="">Select a reason category</option>
                            @if($dispute->status === 'resolved')
                                <option value="new_evidence">New Evidence Available</option>
                                <option value="decision_error">Decision Based on Incorrect Information</option>
                                <option value="procedural_error">Procedural Error in Review</option>
                                <option value="other">Other Reasons</option>
                            @elseif($dispute->status === 'under_review')
                                <option value="procedural_error">Admin Review Taking Too Long</option>
                                <option value="new_evidence">New Evidence Available</option>
                                <option value="review_concerns">Concerns About Review Process</option>
                                <option value="other">Other Reasons</option>
                            @elseif($dispute->status === 'pending')
                                <option value="seller_unresponsive">Seller Not Responding</option>
                                <option value="urgent_review">Urgent Review Required</option>
                                <option value="new_evidence">New Evidence Available</option>
                                <option value="other">Other Reasons</option>
                            @endif
                        </select>
                        <div class="form-text">
                            Choose the category that best describes your appeal reason.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Appeal Reason *</label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" 
                            placeholder="Please explain why you believe the decision should be reconsidered. Provide specific reasons and any new information..." required></textarea>
                        <div class="form-text">
                            Be specific about why you disagree with the decision. Provide new evidence or information that wasn't available during the initial review.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_evidence" class="form-label">New Evidence *</label>
                        <input type="file" name="new_evidence[]" id="new_evidence" class="form-control" 
                            multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                        <div class="form-text">
                            Upload supporting documents, screenshots, or photos for your appeal. 
                            <strong>Required:</strong> At least 1 file. Max 10MB per file. Total limit: 50MB. Supported: JPG, PNG, PDF, DOC, DOCX
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="evidence_descriptions" class="form-label">Evidence Descriptions (Optional)</label>
                        <div id="evidence-descriptions-container">
                            <div class="evidence-description-item mb-2">
                                <input type="text" name="evidence_descriptions[]" class="form-control" 
                                    placeholder="Describe what this evidence proves (optional)">
                            </div>
                        </div>
                        <div class="form-text">
                            Add descriptions for your evidence files to help reviewers understand their relevance.
                        </div>
                    </div>

                    @if($dispute->appeal_deadline)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Appeal Deadline</h6>
                            <p class="mb-0">
                                You have <strong>{{ $dispute->getAppealDeadlineDaysLeft() }} days</strong> remaining to submit your appeal.
                                <br>
                                <small class="text-muted">Deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}</small>
                            </p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-gavel"></i> Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Evidence Response Modals --}}
@if($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty())
    @foreach($dispute->appeal->evidenceRequests as $evidenceRequest)
        @if($evidenceRequest->status === 'pending' && $evidenceRequest->requested_from === auth()->id())
            <div class="modal fade" id="evidenceResponseModal-{{ $evidenceRequest->id }}" tabindex="-1" aria-labelledby="evidenceResponseModalLabel-{{ $evidenceRequest->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="evidenceResponseModalLabel-{{ $evidenceRequest->id }}">
                                <i class="bi bi-upload"></i> Submit Evidence Response
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('disputes.disputes.evidence-requests.respond', $evidenceRequest->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Evidence Request Details</h6>
                                    <p class="mb-0">{{ $evidenceRequest->message }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="response_notes" class="form-label">Response Notes</label>
                                    <textarea name="response_notes" id="response_notes" rows="4" class="form-control" 
                                        placeholder="Please provide any additional context or explanation for your evidence..." required></textarea>
                                    <div class="form-text">
                                        Explain how your evidence supports your position in this dispute.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="submitted_evidence" class="form-label">Evidence Files</label>
                                    <input type="file" name="submitted_evidence[]" id="submitted_evidence" class="form-control" 
                                        multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                                    <div class="form-text">
                                        Upload supporting documents, screenshots, or photos. Max 10MB per file. Supported: JPG, PNG, PDF, DOC, DOCX
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">Important</h6>
                                    <p class="mb-0">
                                        <strong>Deadline:</strong> {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                        <br>
                                        <strong>Time Remaining:</strong> {{ $evidenceRequest->getDeadlineDaysLeft() }} days
                                    </p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Submit Evidence Response
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

@push('styles')
<style>
.message.buyer-message { 
    background-color: #e3f2fd; 
    border-left: 4px solid #2196f3;
}
.message.seller-message { 
    background-color: #f3e5f5; 
    border-left: 4px solid #9c27b0;
}
.message.admin-message { 
    background-color: #fff3e0; 
    border-left: 4px solid #ff9800;
}
.message.system-message { 
    background-color: #f1f8e9; 
    border-left: 4px solid #4caf50;
}

.message.other-message { 
    background-color: #fff3cd; 
    border-left: 4px solid #ffc107;
}

.message.default-message {
    background-color: #f8f9fa;
    border-left: 4px solid #6c757d;
}

.message-source-badge {
    opacity: 0.8;
}

.dispute-message {
    border-left: 4px solid #ffc107;
}

.order-message {
    border-left: 4px solid #17a2b8;
}

/* Enhanced Message Styling */
.message {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

/* Profile Image Styling */
.message-header img.rounded-circle {
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.message-header img.rounded-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Hide fallback avatar by default when image is present */
.message-header img.rounded-circle + strong + .badge + .avatar-fallback {
    display: none;
}

/* Fallback Avatar Styling */
.avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.avatar-fallback:hover {
    transform: scale(1.05);
}

.message:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.message-header {
    background-color: rgba(255,255,255,0.8);
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
    margin: -12px -16px 16px -16px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.message-content {
    background-color: rgba(255,255,255,0.9);
    border-radius: 8px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

/* Attachment Styling */
.attachments-section {
    background-color: rgba(255,255,255,0.7);
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
}

.attachment-item {
    background-color: white;
    transition: all 0.3s ease;
    cursor: pointer;
}

.attachment-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.attachment-item img {
    transition: all 0.3s ease;
}

.attachment-item img:hover {
    transform: scale(1.05);
}

/* Evidence Item Styling */
.evidence-item {
    background-color: white;
    transition: all 0.3s ease;
}

 .evidence-item:hover {
     transform: translateY(-2px);
     box-shadow: 0 4px 12px rgba(0,0,0,0.15);
 }

 .evidence-status-card {
     background-color: #f8f9fa;
     transition: all 0.3s ease;
     min-height: 80px;
     display: flex;
     flex-direction: column;
     justify-content: center;
 }

 .evidence-status-card:hover {
     background-color: #e9ecef;
     box-shadow: 0 2px 8px rgba(0,0,0,0.1);
 }

/* Evidence Response Item Styling */
.evidence-response-item {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.evidence-response-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.evidence-response-item .badge {
    font-size: 0.75rem;
}

/* Timeline Styling */
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

/* Filter Button Styling */
.btn-group .btn {
    border-radius: 6px;
    margin-right: 4px;
}

.btn-group .btn.active {
    font-weight: 600;
}

/* Message Container */
.messages-container {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.messages-container::-webkit-scrollbar {
    width: 8px;
}

.messages-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.messages-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .attachment-item {
        margin-bottom: 16px;
    }
    
    .btn-group {
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 4px;
    }
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

    // Message filtering
    const filterButtons = document.querySelectorAll('[data-filter]');
    const messages = document.querySelectorAll('.message');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter messages
            messages.forEach(message => {
                const messageType = message.getAttribute('data-message-type');
                if (filter === 'all' || messageType === filter) {
                    message.style.display = 'block';
                    message.style.animation = 'fadeIn 0.3s ease-in';
                } else {
                    message.style.display = 'none';
                }
            });
        });
    });

    // File size validation
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        const maxSize = 10 * 1024 * 1024; // 10MB

        fileInput.addEventListener('change', function() {
            const files = this.files;
            let totalSize = 0;
            
            for (let i = 0; i < files.length; i++) {
                totalSize += files[i].size;
                
                if (files[i].size > maxSize) {
                    alert(`File "${files[i].name}" is too large. Maximum size is 10MB.`);
                    this.value = '';
                    return;
                }
            }
            
            // Show total size info
            if (files.length > 0) {
                const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);
                const infoText = this.nextElementSibling;
                infoText.innerHTML = `<i class="bi bi-info-circle"></i> ${files.length} file(s) selected. Total size: ${totalSizeMB}MB. Max 10MB per file.`;
            }
        });
    }

    // Enhanced message animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const messageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all messages for animation
    messages.forEach(message => {
        message.style.opacity = '0';
        message.style.transform = 'translateY(20px)';
        message.style.transition = 'all 0.5s ease';
        messageObserver.observe(message);
    });

    // Auto-scroll to new messages
    const scrollToBottom = () => {
        if (messagesContainer) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }
    };

    // Scroll to bottom when new message is added
    const messageForm = document.querySelector('form');
    if (messageForm) {
        messageForm.addEventListener('submit', () => {
            setTimeout(scrollToBottom, 100);
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit message
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const messageForm = document.querySelector('form');
            if (messageForm && document.activeElement.tagName === 'TEXTAREA') {
                e.preventDefault();
                messageForm.submit();
            }
        }
    });

    // Enhanced attachment preview
    const attachmentItems = document.querySelectorAll('.attachment-item img');
    attachmentItems.forEach(img => {
        img.addEventListener('click', function() {
            const src = this.src;
            const alt = this.alt;
            openImageModal(src, alt);
        });
    });

    // Evidence descriptions dynamic fields
    const evidenceInput = document.getElementById('new_evidence');
    const descriptionsContainer = document.getElementById('evidence-descriptions-container');
    
    if (evidenceInput && descriptionsContainer) {
        evidenceInput.addEventListener('change', function() {
            const files = this.files;
            const currentDescriptions = descriptionsContainer.querySelectorAll('.evidence-description-item');
            
            // Remove existing description fields
            currentDescriptions.forEach(item => item.remove());
            
            // Add new description fields for each file
            for (let i = 0; i < files.length; i++) {
                const descriptionItem = document.createElement('div');
                descriptionItem.className = 'evidence-description-item mb-2';
                descriptionItem.innerHTML = `
                    <div class="input-group">
                        <span class="input-group-text">${i + 1}</span>
                        <input type="text" name="evidence_descriptions[]" class="form-control" 
                            placeholder="Describe what ${files[i].name} proves (optional)">
                    </div>
                `;
                descriptionsContainer.appendChild(descriptionItem);
            }
        });
    }
});

// Image Modal Function
function openImageModal(imageSrc, imageAlt) {
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel">${imageAlt}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageSrc}" alt="${imageAlt}" class="img-fluid" style="max-height: 70vh;">
                    </div>
                    <div class="modal-footer">
                        <a href="${imageSrc}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                        </a>
                        <a href="${imageSrc}" download="${imageAlt}" class="btn btn-secondary">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();

    // Clean up modal after it's hidden
    document.getElementById('imageModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message {
        animation: fadeIn 0.5s ease-out;
    }
    
    .attachment-item img {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .attachment-item img:hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);

// Evidence submission form handling
document.addEventListener('DOMContentLoaded', function() {
    const evidenceForm = document.getElementById('submitEvidenceModal')?.querySelector('form');
    if (evidenceForm) {
        evidenceForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
            submitBtn.disabled = true;
            
            // Re-enable button after a delay (in case of errors)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
    }
});
</script>
@endpush

{{-- Appeal Modal --}}
@if($dispute->canBeAppealed() && (auth()->id() === $dispute->buyer_id || auth()->id() === $dispute->seller_id))
<div class="modal fade" id="appealModal" tabindex="-1" aria-labelledby="appealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appealModalLabel">
                    <i class="bi bi-gavel"></i> Appeal to Support Team
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('disputes.appeal.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Appeal Process:</strong> You can appeal to our support team for intervention at any time. Please provide a clear reason and supporting evidence for your appeal.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Appeal Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" 
                            placeholder="Please explain why you need support team intervention and what you hope to achieve..." required></textarea>
                        <div class="form-text">
                            Clearly state your reasons for appealing and what you hope to achieve.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_evidence" class="form-label">Supporting Evidence</label>
                        <input type="file" name="new_evidence[]" id="new_evidence" class="form-control" 
                            multiple accept="image/*,.pdf,.doc,.docx">
                        <div class="form-text">
                            Upload screenshots, documents, or any other evidence to support your appeal. (Max 5 files, 5MB each)
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-gavel"></i> Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Evidence Submission Modal --}}
@if($dispute->appeal && $dispute->appeal->status === 'evidence_requested')
    @php
        $userEvidenceRequest = null;
        if (auth()->id() === $dispute->buyer_id) {
            $userEvidenceRequest = $dispute->appeal->buyerEvidenceRequest;
        } elseif (auth()->id() === $dispute->seller_id) {
            $userEvidenceRequest = $dispute->appeal->sellerEvidenceRequest;
        }
    @endphp
    
    @if($userEvidenceRequest && $userEvidenceRequest->status !== 'submitted')
        <div class="modal fade" id="submitEvidenceModal" tabindex="-1" aria-labelledby="submitEvidenceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="submitEvidenceModalLabel">
                            <i class="bi bi-upload"></i> Submit Evidence
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('evidence-requests.submit', $userEvidenceRequest->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Evidence Request:</strong> {{ $userEvidenceRequest->request_message }}
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Required Evidence Types</h6>
                                    @foreach($userEvidenceRequest->getRequiredEvidenceTypesList() as $evidenceType)
                                        <span class="badge bg-primary me-2 mb-2">{{ $evidenceType }}</span>
                                    @endforeach
                                </div>
                                <div class="col-md-6">
                                    <h6>Deadline Information</h6>
                                    <p class="mb-1"><strong>Deadline:</strong> {{ $userEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="mb-0">
                                        <span class="text-{{ $userEvidenceRequest->getDaysUntilDeadline() <= 3 ? 'warning' : 'success' }}">
                                            {{ $userEvidenceRequest->getDaysUntilDeadline() }} days remaining
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="evidence_description" class="form-label">Evidence Description <span class="text-danger">*</span></label>
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
                                <label for="evidence_files" class="form-label">Evidence Files <span class="text-danger">*</span></label>
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
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Important:</strong> Once you submit evidence, you cannot modify it. Please ensure all files are correct before submission.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Submit Evidence
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif
@endsection
