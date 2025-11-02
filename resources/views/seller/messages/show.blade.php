@extends('layouts.app')
@section('title', 'Customer Conversation')

@section('content')
<div class="content">
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(!$product)
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>General Inquiry</strong>
                        <p class="mb-0 mt-2">This conversation is not associated with a specific product. The customer may be asking general questions about your shop.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Header Section --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <a href="{{ route('seller.messages.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </a>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-lg bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                        {{ strtoupper(substr($otherUser?->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="mb-1 fw-bold">{{ $otherUser?->name ?? 'Customer' }}</h4>
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $otherUser?->email ?? 'No email' }}
                                        </p>
                                        <span class="badge bg-info small mt-1">
                                            <i class="bi bi-chat-dots me-1"></i>{{ $messages->count() }} message{{ $messages->count() > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2 mt-lg-0">
                                @if($product)
                                    <span class="badge bg-primary fs-6 px-3 py-2 mb-2 text-truncate product-badge" style="max-width: 250px;" title="{{ $product->name }}">
                                        <i class="bi bi-box me-1"></i>
                                        {{ \Illuminate\Support\Str::limit($product->name, 30) }}
                                    </span>
                                @endif
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i>
                                    Started {!! $messages->first() ? $messages->first()->created_at->diffForHumans() : 'recently' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Info Card --}}
                @if($product)
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            @if($product->media && $product->media->count() > 0)
                                @php($thumb = function_exists('product_thumb_url') ? product_thumb_url($product) : (optional($product->media->first())->url ? asset('storage/'.$product->media->first()->url) : null))
                                <img src="{{ $thumb }}" alt="{{ $product->name }}" 
                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <p class="text-muted mb-0 small">{!! $product->description ? \Illuminate\Support\Str::limit($product->description, 100) : 'No description available' !!}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $product->price_formatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Conversation Messages --}}
                <div class="card shadow-sm border-0 mb-4 conversation-card">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-chat-dots-fill me-2 text-primary"></i>
                            <h5 class="mb-0">Message History</h5>
                            <span class="badge bg-light text-dark ms-auto">{{ $messages->count() }} messages</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                This conversation is between you and {{ $otherUser?->name ?? 'the customer' }} about this specific product.
                            </small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="conversation-container" id="conversationContainer">
                            @forelse($messages as $message)
                                <div class="message-row d-flex {{ $message->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                                    <div class="d-flex align-items-end {{ $message->sender_id == auth()->id() ? 'flex-row-reverse' : '' }}">
                                        <div class="avatar-sm {{ $message->sender_id == auth()->id() ? 'bg-success' : 'bg-primary' }} text-white rounded-circle d-flex align-items-center justify-content-center ms-2 me-2" style="width:32px;height:32px;font-size:0.9rem;">
                                            {{ strtoupper(substr($message->sender->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="bubble-wrap">
                                            <div class="message-bubble-compact {{ $message->sender_id == auth()->id() ? 'outgoing' : 'incoming' }}">
                                                <div class="message-meta d-flex align-items-center mb-1">
                                                    <span class="fw-semibold small me-2">{{ $message->sender->name ?? 'Unknown' }}</span>
                                                    <span class="text-muted small">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                                                </div>
                                                <div class="message-content">{{ $message->body }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-conversation">
                                    <div class="text-center py-5">
                                        <div class="empty-icon mb-3">
                                            <i class="bi bi-chat-dots"></i>
                                        </div>
                                        <h5 class="text-muted">No messages yet</h5>
                                        <p class="text-muted">Start the conversation by sending a reply below.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Reply Form --}}
                <div class="card shadow-sm border-0 reply-card">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-reply-fill me-2 text-success"></i>
                            <h5 class="mb-0">Send Reply</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('seller.messages.reply', $conversationId) }}" id="replyForm">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="form-label fw-bold">
                                    <i class="bi bi-pencil me-1"></i>
                                    Your Message
                                </label>
                                <textarea 
                                    name="message" 
                                    id="message" 
                                    class="form-control form-control-lg @error('message') is-invalid @enderror" 
                                    rows="4" 
                                    placeholder="Type your professional reply here..." 
                                    required
                                    maxlength="2000"
                                >{{ old('message', request('prefill')) }}</textarea>
                                <div class="form-text d-flex justify-content-between">
                                    <span>Be professional and helpful in your response</span>
                                    <span id="charCount">0/2000</span>
                                </div>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="reply-info">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <small>Reply will be sent to {{ $otherUser?->email ?? 'the customer' }}</small>
                                    </div>
                                    <div class="d-flex align-items-center text-muted mt-1">
                                        <i class="bi bi-clock me-1"></i>
                                        <small>Customer will receive an email notification</small>
                                    </div>
                                </div>
                                <div class="reply-actions">
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="clearForm()">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Clear
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg px-4" id="sendButton">
                                        <i class="bi bi-send me-1"></i>
                                        Send Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Message Details Card --}}
                <div class="card shadow border-0 mt-4 conversation-details-card">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2 text-info fs-4"></i>
                            <h5 class="mb-0">Conversation Details</h5>
                        </div>
                    </div>
                    <div class="card-body bg-light bg-gradient p-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-hash me-1"></i> Conversation ID
                                    </span>
                                    <span class="badge bg-secondary ms-2">#{{ $conversationId }}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-box me-1"></i> Product
                                    </span>
                                    <span class="badge bg-primary ms-2 text-truncate" style="max-width: 160px;" title="{{ $product?->name ?? '' }}">
                                        {{ $product?->name ? \Illuminate\Support\Str::limit($product?->name, 25) : 'No product specified' }}
                                    </span>
                                    @if($product)
                                        <span class="badge bg-light text-dark border ms-2">ID: #{{ $product->id }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-person me-1"></i> Customer
                                    </span>
                                    <span class="badge bg-info ms-2">{{ $otherUser?->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-calendar me-1"></i> Started
                                    </span>
                                    <span class="badge bg-light text-dark ms-2">
                                        {{ $messages->first() ? $messages->first()->created_at->format('M j, Y') : 'Recently' }}
                                    </span>
                                </div>
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
    .avatar-lg {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        font-weight: 600;
    }
    .avatar-sm {
        font-weight: 600;
    }
    .conversation-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 1rem;
    }
    .message-bubble-compact {
        max-width: 75%;
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        position: relative;
    }
    .message-bubble-compact.outgoing {
        background: #28a745;
        color: white;
        border-bottom-right-radius: 0.25rem;
    }
    .message-bubble-compact.incoming {
        background: #f8f9fa;
        color: #212529;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 0.25rem;
    }
    .message-content {
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .empty-icon {
        font-size: 3rem;
        color: #dee2e6;
    }
    .product-badge {
        font-size: 0.9rem;
    }
    .detail-label {
        font-size: 0.9rem;
        font-weight: 500;
    }
    .reply-info {
        font-size: 0.9rem;
    }
    .conversation-container::-webkit-scrollbar {
        width: 6px;
    }
    .conversation-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    .conversation-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    .conversation-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    @media (max-width: 768px) {
        .reply-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        .reply-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const sendButton = document.getElementById('sendButton');
    
    // Character counter
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length}/2000`;
        
        if (length > 1900) {
            charCount.style.color = '#dc3545';
        } else if (length > 1500) {
            charCount.style.color = '#ffc107';
        } else {
            charCount.style.color = '#6c757d';
        }
    });
    
    // Auto-resize textarea
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 200) + 'px';
    });
    
    // Send on Enter (but allow Shift+Enter for new line)
    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendButton.click();
        }
    });
    
    // Scroll to bottom of conversation
    const container = document.getElementById('conversationContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});

function clearForm() {
    const messageField = document.getElementById('message');
    const charCountField = document.getElementById('charCount');
    messageField.value = '';
    charCountField.textContent = '0/2000';
    charCountField.style.color = '#6c757d';
}
</script>
@endpush
@endsection 
