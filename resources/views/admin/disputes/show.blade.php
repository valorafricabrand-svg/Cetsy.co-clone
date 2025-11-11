@extends('layouts.app')

@section('title', 'Dispute Details')

@section('content')
<div class="content {{ $dispute->isClosed() ? 'dispute-closed' : '' }}">
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Dispute Closed Notice --}}
    @if($dispute->isClosed())
        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
            <i class="bi bi-lock me-2"></i>
            <strong>This dispute has been closed.</strong>
            <p class="mb-0 mt-2">
                Closed on {{ $dispute->closed_at->format('M d, Y \a\t g:i A') }}
                @if($dispute->closedBy)
                    by {{ $dispute->closedBy->name }}
                @endif
                . No further actions can be taken on this dispute.
            </p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
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
                                @if($dispute->isClosed())
                                    - <i class="bi bi-lock text-secondary"></i> Closed {{ optional($dispute->closed_at)->diffForHumans() }}
                                @elseif($dispute->isResolved())
                                    - <i class="bi bi-check-circle text-success"></i> Resolved {{ optional($dispute->resolved_at)->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-md-end">
                                <a href="{{ auth()->user() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin() ? route('admin.admin-disputes.index') : route('disputes.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> Back to Disputes
                                </a>
                                
                                {{-- Mark as Closed Button for Dispute Initiator --}}
                                @if($dispute->buyer_id === auth()->id() && $dispute->status !== 'closed' && $dispute->status !== 'resolved')
                                    <form action="{{ route('disputes.mark-closed', $dispute->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Are you sure you want to mark this dispute as closed? This action cannot be undone.')">
                                            <i class="bi bi-check-circle"></i> Mark as Closed
                                        </button>
                                    </form>
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
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $disputeMessages->count() }}</h4>
                    <p class="mb-0 small">Dispute Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-paperclip fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $dispute->evidence ? count($dispute->evidence) : 0 }}</h4>
                    <p class="mb-0 small">Evidence Files</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-chat fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $orderMessages->count() }}</h4>
                    <p class="mb-0 small">Order Context</p>
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
                        @php
                            $authUser  = auth()->user();
                            $sellerId  = optional($order->shop)->user_id;
                            $orderHref = (\Illuminate\Support\Facades\Route::has('seller.orders.show') && $authUser && (int)$authUser->id === (int)$sellerId)
                                ? route('seller.orders.show', $order->id)
                                : (\Illuminate\Support\Facades\Route::has('buyer.orders.show')
                                    ? route('buyer.orders.show', $order->id)
                                    : route('orders.show', $order->id));
                        @endphp
                        <a href="{{ $orderHref }}" class="btn btn-sm btn-outline-primary">
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
                                    <small class="text-muted ms-2">by {{ $dispute->buyer_id === auth()->id() ? 'You' : ($dispute->buyer->name ?? 'Unknown User') }}</small>
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
                                @if($dispute->order)
                                    <a href="{{ route('buyer.orders.show', $dispute->order->id) }}" class="text-decoration-none">
                                        Order #{{ $dispute->order->id }}
                                    </a>
                                @else
                                    <span class="text-muted">Order not available</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Created</h6>
                            <p class="mb-3">{{ $dispute->created_at->format('M d, Y \a\t g:i A') }}</p>
                            
                            <h6>Parties</h6>
                            <p class="mb-3">
                                <strong>Buyer:</strong> {{ $dispute->buyer->name ?? 'Unknown User' }}<br>
                                <strong>Shop:</strong> {{ optional($dispute->order)->shop->name ?? ($dispute->seller->name ?? 'Unknown Seller') }}
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
                </div>
            </div>

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
                                    Proposed by: {{ $dispute->buyer_agreed_at && !$dispute->seller_agreed_at ? ($dispute->buyer->name ?? 'Unknown') : ($dispute->seller->name ?? 'Unknown') }}
                                </small>
                            </div>

                            {{-- Show agreement status --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2"></i>
                                        <span>Buyer ({{ $dispute->buyer->name ?? 'Unknown' }})</span>
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
                                        <span>Seller ({{ $dispute->seller->name ?? 'Unknown' }})</span>
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
                        foreach($message->attachments as$allAttachments as $attachment)
                                    $attachment['message_sender'] = $message->user->name ?? 'Unknown User';
                                    $attachment['message_date'] = $message->created_at;
                                    $attachment['message_content'] = Str::limit($message->message ?? $message->body ?? '', 100);
                                    $allAttachments->push($attachment);
                                }
                            }
                        }
                    @endphp
                    
                    @if($allAttachments->isNotEmpty())
                        <div class="card mb-4 border-secondary" style="display:none;">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0" style="color: white !important;">
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

                    <!-- Dispute Messages Section -->
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> Dispute Communication History
                                        <span class="badge bg-dark ms-2">{{ $disputeMessages->count() }} dispute messages</span>
                                    </h5>
                                    <small class="text-muted">
                                        <i class="bi bi-exclamation-circle"></i> Dispute #{{ $dispute->id }} - {{ $dispute->getTypeLabel() }}
                                    </small>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-warning active" data-filter="dispute">
                                        <i class="bi bi-exclamation-triangle"></i> Dispute ({{ $disputeMessages->count() }})
                                    </button>
                                    @if($orderMessages->count() > 0)
                                        <button type="button" class="btn btn-outline-info" data-filter="order">
                                            <i class="bi bi-chat"></i> Order Context ({{ $orderMessages->count() }})
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Dispute Communication:</strong> This section shows all messages specifically related to <strong>Dispute #{{ $dispute->id }}</strong> 
                                regarding <strong>{{ $dispute->getTypeLabel() }}</strong>.
                                
                                <div class="mt-2 small">
                                    <strong>Message Breakdown:</strong>
                                    <span class="badge bg-warning me-2">{{ $disputeMessages->count() }} Dispute Messages</span>
                                    @if($orderMessages->count() > 0)
                                        <span class="badge bg-info me-2">{{ $orderMessages->count() }} Order Context Messages</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="messages-container" style="max-height: 600px; overflow-y: auto;">
                                @forelse($disputeMessages as $message)
                                    @php
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
                                        
                                        // This is always a dispute message
                                        $isDisputeMessage = true;
                                        $isOrderMessage = false;
                                        
                                        // Safely get user info and determine profile image
                                        $userName = 'Unknown User';
                                        $userPhoto = null;
                                        $userRole = 'User';
                                        
                                        // Check for system messages first (regardless of user_id)
                                        if ($message->type === 'system_message') {
                                            $userRole = 'System';
                                            $userName = 'System';
                                            $userPhoto = null;
                                        } elseif ($message->user_id && $message->user) {
                                            // User exists
                                            if ($message->user_id === $dispute->buyer_id) {
                                                $userRole = 'Buyer';
                                                $userName = $message->user->name;
                                                
                                                // Get buyer's profile photo
                                                if ($message->user->photo) {
                                                    $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                                } else {
                                                    $userPhoto = $message->user->get_gravatar(32);
                                                }
                                            } elseif ($message->user_id === $dispute->seller_id) {
                                                $userRole = 'Seller';
                                                // Show shop name if available
                                                if ($order && $order->shop && $order->shop->name) {
                                                    $userName = $order->shop->name;
                                                } else {
                                                    $userName = $message->user->name;
                                                }
                                                
                                                // Get shop logo or user photo
                                                if ($order && $order->shop && $order->shop->logo) {
                                                    $userPhoto = asset('storage/' . $order->shop->logo);
                                                } elseif ($message->user->photo) {
                                                    $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                                } else {
                                                    $userPhoto = $message->user->get_gravatar(32);
                                                }
                                            } else {
                                                $userRole = 'Other';
                                                $userName = $message->user->name;
                                                
                                                if ($message->user->photo) {
                                                    $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                                } else {
                                                    $userPhoto = $message->user->get_gravatar(32);
                                                }
                                            }
                                        } else {
                                            // User doesn't exist (deleted user)
                                            $userRole = 'Deleted User';
                                            $userName = 'Deleted User';
                                            $userPhoto = null;
                                        }
                                        
                                        // Assign message class based on user role
                                        if ($userRole === 'Buyer') $messageClass = 'buyer-message';
                                        elseif ($userRole === 'Seller') $messageClass = 'seller-message';
                                        elseif ($userRole === 'System') $messageClass = 'system-message';
                                        elseif ($userRole === 'Other') $messageClass = 'other-message';
                                        elseif ($userRole === 'Deleted User') $messageClass = 'default-message';
                                        
                                        // Safely get message content
                                        $messageContent = $message->message ?? $message->body ?? 'No message content';
                                        
                                        // Safely get attachments
                                        $hasAttachments = isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0;
                                        $attachmentsCount = $hasAttachments ? count($message->attachments) : 0;
                                    @endphp
                                    
                                    <div class="message mb-4 {{ $messageClass }} {{ $isDisputeMessage ? 'dispute-message' : 'order-message' }}" 
                                        data-message-type="{{ $isDisputeMessage ? 'dispute' : 'order' }}">
                                        
                                        {{-- Message Header with Source Badge --}}
                                        <div class="message-header d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                @if($message->is_dispute_message ?? true)
                                                    <span class="badge bg-warning text-dark me-2">
                                                        <i class="bi bi-exclamation-triangle"></i> Dispute
                                                    </span>
                                                @else
                                                    <span class="badge bg-info text-dark me-2">
                                                        <i class="bi bi-chat"></i> Order
                                                    </span>
                                                @endif
                                                
                                                <div class="d-flex align-items-center">
                                                    @if($userPhoto && $userName !== 'Unknown User' && $userName !== 'Deleted User')
                                                        <img src="{{ $userPhoto }}" 
                                                            alt="{{ $userName }}" 
                                                            class="rounded-circle me-2" 
                                                            width="32" height="32"
                                                            style="object-fit: cover;"
                                                            onerror="this.style.display='none';">
                                                    @else
                                                        <div class="rounded-circle avatar-fallback me-2 d-flex align-items-center justify-content-center" 
                                                            style="width: 32px; height: 32px; {{ $userRole === 'Buyer' ? 'background-color: #e3f2fd; color: #1976d2;' : ($userRole === 'System' ? 'background-color: #6c757d; color: white;' : ($userRole === 'Seller' ? 'background-color: #f3e5f5; color: #7b1fa2;' : 'background-color: #f5f5f5; color: #999;')) }}">
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
                                                    @endif
                                                    <strong>{{ $userName }}</strong>
                                                    <span class="badge {{ $userRole === 'Buyer' ? 'bg-primary' : ($userRole === 'System' ? 'bg-secondary' : ($userRole === 'Seller' ? 'bg-success' : 'bg-secondary')) }} ms-2">
                                                        {{ $userRole }}
                                                    </span>
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
                                        <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
                                        <h5>No dispute messages yet</h5>
                                        <p>
                                            No dispute-specific messages found for Dispute #{{ $dispute->id }}.
                                            Start the dispute conversation by sending a message below.
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

                                @if($dispute->status !== 'resolved' && $dispute->status !== 'closed' && $dispute->status !== 'final' && (auth()->id() === $dispute->created_by || (auth()->user() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())))
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#closeDisputeModal">
                                        <i class="bi bi-check-circle"></i> 
                                        @if(auth()->user() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                                            Close Dispute (Admin)
                                        @elseif(auth()->id() === $dispute->created_by)
                                            Close Dispute 
                                        @endif
                                    </button>
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
                                        @if($dispute->createdBy)
                                            <br><small class="text-muted">by {{ $dispute->createdBy->name }}</small>
                                        @endif
                                    </div>
                                </div>

                                @if($dispute->status === 'pending')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Status: Pending</h6>
                                            <small class="text-muted">Awaiting response from the other party</small>
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->status === 'under_review')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Status: Under Review</h6>
                                            <small class="text-muted">Being reviewed by Cetsy support team</small>
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
                                            <h6 class="mb-1">Buyer Agreed to Resolution</h6>
                                            <small class="text-muted">{{ $dispute->buyer_agreed_at->format('M d, Y g:i A') }}</small>
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->seller_agreed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Seller Agreed to Resolution</h6>
                                            <small class="text-muted">{{ $dispute->seller_agreed_at->format('M d, Y g:i A') }}</small>
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->status === 'mutually_resolved')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Status: Mutually Resolved</h6>
                                            <small class="text-muted">Both parties agreed to resolution terms</small>
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->status === 'resolved')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Status: Resolved</h6>
                                            <small class="text-muted">{{ $dispute->resolved_at ? $dispute->resolved_at->format('M d, Y g:i A') : 'Resolution completed' }}</small>
                                            @if($dispute->resolvedBy)
                                                <br><small class="text-muted">by {{ $dispute->resolvedBy->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->status === 'closed')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Status: Closed</h6>
                                            <small class="text-muted">{{ $dispute->closed_at ? $dispute->closed_at->format('M d, Y g:i A') : 'Dispute closed' }}</small>
                                            @if($dispute->closedBy)
                                                <br><small class="text-muted">by {{ $dispute->closedBy->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($dispute->status === 'final')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-dark"></div>
                                        <div<div class="timeline-content">
                                            <h6 class="mb-1">Status: Final Decision</h6>
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

        .dispute-closed {
            opacity: 0.7;
            background-color: #f8f9fa;
        }

        .dispute-closed .card {
            background-color: #f8f9fa;
        }

        .dispute-closed .btn:not(.btn-outline-secondary) {
            pointer-events: none;
            opacity: 0.5;
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

        .message {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .message-header img.rounded-circle {
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .message-header img.rounded-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

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

        .evidence-item {
            background-color: white;
            transition: all 0.3s ease;
        }

        .evidence-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

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

        .btn-group .btn {
            border-radius: 6px;
            margin-right: 4px;
        }

        .btn-group .btn.active {
            font-weight: 600;
        }

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
            const messagesContainer = document.querySelector('.messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            const filterButtons = document.querySelectorAll('[data-filter]');
            const disputeMessages = document.querySelectorAll('.dispute-message');
            const orderMessages = document.querySelectorAll('.order-message');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    if (filter === 'dispute') {
                        disputeMessages.forEach(message => {
                            message.style.display = 'block';
                            message.style.animation = 'fadeIn 0.3s ease-in';
                        });
                        orderMessages.forEach(message => {
                            message.style.display = 'none';
                        });
                    } else if (filter === 'order') {
                        disputeMessages.forEach(message => {
                            message.style.display = 'none';
                        });
                        orderMessages.forEach(message => {
                            message.style.display = 'block';
                            message.style.animation = 'fadeIn 0.3s ease-in';
                        });
                    }
                });
            });

            const fileInput = document.getElementById('attachments');
            if (fileInput) {
                const maxSize = 10 * 1024 * 1024;

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
                    
                    if (files.length > 0) {
                        const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);
                        const infoText = this.nextElementSibling;
                        infoText.innerHTML = `<i class="bi bi-info-circle"></i> ${files.length} file(s) selected. Total size: ${totalSizeMB}MB. Max 10MB per file.`;
                    }
                });
            }

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

            const allMessages = document.querySelectorAll('.message');
            allMessages.forEach(message => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(20px)';
                message.style.transition = 'all 0.5s ease';
                messageObserver.observe(message);
            });

            const scrollToBottom = () => {
                if (messagesContainer) {
                    messagesContainer.scrollTo({
                        top: messagesContainer.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            };

            const messageForm = document.querySelector('form');
            if (messageForm) {
                messageForm.addEventListener('submit', () => {
                    setTimeout(scrollToBottom, 100);
                });
            }

            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    const messageForm = document.querySelector('form');
                    if (messageForm && document.activeElement.tagName === 'TEXTAREA') {
                        e.preventDefault();
                        messageForm.submit();
                    }
                }
            });

            const attachmentItems = document.querySelectorAll('.attachment-item img');
            attachmentItems.forEach(img => {
                img.addEventListener('click', function() {
                    const src = this.src;
                    const alt = this.alt;
                    openImageModal(src, alt);
                });
            });
        });

        function openImageModal(imageSrc, imageAlt) {
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

            const existingModal = document.getElementById('imageModal');
            if (existingModal) {
                existingModal.remove();
            }

            document.body.insertAdjacentHTML('beforeend', modalHTML);

            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();

            document.getElementById('imageModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

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
        </script>
        @endpush

        {{-- Close Dispute Modal --}}
        <div class="modal fade" id="closeDisputeModal" tabindex="-1" aria-labelledby="closeDisputeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="closeDisputeModalLabel">
                            <i class="bi bi-check-circle"></i> Close Dispute
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('disputes.close', $dispute->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            @if(auth()->user() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                                <div class="alert alert-warning">
                                    <i class="bi bi-shield-check"></i>
                                    <strong>Admin Action:</strong> You are closing this dispute as an administrator.
                                </div>
                            @endif
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Closing Dispute:</strong> This action will mark the dispute as closed. Only the dispute creator or admin users can close disputes. Please ensure all issues have been resolved before proceeding.
                            </div>
                            
                            <div class="mb-3">
                                <label for="closure_notes" class="form-label">Additional Notes (Optional)</label>
                                <textarea name="closure_notes" id="closure_notes" rows="3" class="form-control" 
                                    placeholder="Provide any additional details about the resolution or closure..."></textarea>
                                <div class="form-text">
                                    Optional: Add any additional context about how the dispute was resolved.
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Important:</strong> Once closed, this dispute cannot be reopened. Make sure all parties are satisfied with the resolution.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Confirm Close Dispute
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endsection
