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
                                @if($dispute->canBeAppealed() && !$dispute->isAppealDeadlineExpired())
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

    <div class="row">
        <div class="col-md-8">
            <!-- Order Context Header -->
            @if($order)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-box"></i> Order Context
                        </h5>
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

            <!-- Unified Messages Section -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-dots"></i> Complete Communication History
                            <span class="badge bg-secondary ms-2">{{ $allMessages->count() }} messages</span>
                        </h5>
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
                    <div class="messages-container" style="max-height: 600px; overflow-y: auto;">
                        @forelse($allMessages as $message)
                            @php
                                // Safely determine message type and class
                                $messageType = $message->type ?? 'unknown';
                                $messageClass = 'default-message';
                                if ($messageType === 'buyer_message') $messageClass = 'buyer-message';
                                elseif ($messageType === 'seller_message') $messageClass = 'seller-message';
                                elseif ($messageType === 'system_message') $messageClass = 'system-message';
                                
                                // Safely determine if it's a dispute or order message
                                $isDisputeMessage = isset($message->is_dispute_message) ? $message->is_dispute_message : false;
                                $isOrderMessage = !$isDisputeMessage;
                                
                                // Safely get user info and determine profile image
                                $userName = $message->user->name ?? 'Unknown User';
                                $userPhoto = null;
                                $userRole = 'User';
                                
                                // Determine user role and profile image based on dispute context
                                if ($message->user_id) {
                                    if ($message->user_id === $dispute->buyer_id) {
                                        $userRole = 'Buyer';
                                        // Get buyer's profile photo using the correct method
                                        if ($message->user->photo) {
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            // Use gravatar as fallback
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                        // Debug: Log buyer profile info
                                        \Log::info('Buyer profile found', [
                                            'buyer_id' => $message->user_id,
                                            'buyer_name' => $message->user->name,
                                            'photo' => $message->user->photo,
                                            'photo_storage' => $message->user->photo_storage,
                                            'profile_photo_url' => $userPhoto
                                        ]);
                                    } elseif ($message->user_id === $dispute->seller_id) {
                                        $userRole = $order && $order->shop ? $order->shop->name : 'Seller';
                                        // Get shop's profile photo (shop logo/image)
                                        if ($order && $order->shop && $order->shop->logo) {
                                            $userPhoto = asset('storage/' . $order->shop->logo);
                                            // Debug: Log shop logo info
                                            \Log::info('Shop logo found', [
                                                'shop_id' => $order->shop->id,
                                                'shop_name' => $order->shop->name,
                                                'logo_path' => $order->shop->logo,
                                                'full_url' => $userPhoto
                                            ]);
                                        } elseif ($message->user->photo) {
                                            // Fallback to user's personal photo if shop logo not available
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            // Use gravatar as final fallback
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    } elseif ($message->type === 'system_message') {
                                        $userRole = 'System';
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
                                                 <span class="badge {{ $userRole === 'Buyer' ? 'bg-primary' : ($userRole === 'System' ? 'bg-secondary' : 'bg-success') }} ms-2">
                                                     {{ $userRole }}
                                                 </span>
                                            @else
                                                <div class="rounded-circle avatar-fallback me-2" 
                                                     style="width: 32px; height: 32px; {{ $userRole === 'Buyer' ? 'background-color: #e3f2fd; color: #1976d2;' : ($userRole === 'System' ? 'background-color: #6c757d; color: white;' : 'background-color: #f3e5f5; color: #7b1fa2;') }}">
                                                    @if($userRole === 'Buyer')
                                                        <i class="bi bi-person-fill"></i>
                                                    @elseif($userRole === 'System')
                                                        <i class="bi bi-robot"></i>
                                                    @else
                                                        <i class="bi bi-shop"></i>
                                                    @endif
                                                </div>
                                                <strong>{{ $userRole === 'System' ? 'System' : $userName }}</strong>
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
                                <p>Start the conversation by sending a message below.</p>
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
</script>
@endpush
@endsection
