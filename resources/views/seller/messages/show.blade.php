@extends('layouts.app')
@section('title', 'Message Conversation')

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
                                        {{ strtoupper(substr($message->sender->name ?? 'B', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="mb-1 fw-bold">{{ $message->sender->name ?? 'Customer' }}</h4>
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $message->sender->email ?? 'No email' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2 mt-lg-0">
                                @if($message->product)
                                    <span class="badge bg-primary fs-6 px-3 py-2 mb-2 text-truncate product-badge" style="max-width: 250px;" title="{{ $message->product->name }}">
                                        <i class="bi bi-box me-1"></i>
                                        {{ \Illuminate\Support\Str::limit($message->product->name, 30) }}
                                    </span>
                                @endif
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i>
                                    Started {{ $message->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conversation Messages --}}
                <div class="card shadow-sm border-0 mb-4 conversation-card">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-chat-dots-fill me-2 text-primary"></i>
                            <h5 class="mb-0">Message History</h5>
                            <span class="badge bg-light text-dark ms-auto">{{ $conversationMessages->count() }} messages</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="conversation-container" id="conversationContainer">
                            @forelse($conversationMessages as $msg)
                                <div class="message-row d-flex {{ $msg->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                                    <div class="d-flex align-items-end {{ $msg->sender_id == auth()->id() ? 'flex-row-reverse' : '' }}">
                                        <div class="avatar-sm {{ $msg->sender_id == auth()->id() ? 'bg-success' : 'bg-primary' }} text-white rounded-circle d-flex align-items-center justify-content-center ms-2 me-2" style="width:32px;height:32px;font-size:0.9rem;">
                                            {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="bubble-wrap">
                                            <div class="message-bubble-compact {{ $msg->sender_id == auth()->id() ? 'outgoing' : 'incoming' }}">
                                                <div class="message-meta d-flex align-items-center mb-1">
                                                    <span class="fw-semibold small me-2">{{ $msg->sender->name ?? 'Unknown' }}</span>
                                                    <span class="text-muted small">{{ $msg->created_at->format('M j, Y g:i A') }}</span>
                                                </div>
                                                <div class="message-content">{{ $msg->body }}</div>
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
                        <form method="POST" action="{{ route('seller.messages.reply', $message->id) }}" id="replyForm">
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
                                >{{ old('message') }}</textarea>
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
                                        <small>Reply will be sent to {{ $message->sender->email ?? 'the customer' }}</small>
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
                                        <i class="bi bi-hash me-1"></i> Message ID
                                    </span>
                                    <span class="badge bg-secondary ms-2">#{{ $message->id }}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-box me-1"></i> Product
                                    </span>
                                    <span class="badge bg-primary ms-2 text-truncate" style="max-width: 160px;" title="{{ $message->product->name ?? '' }}">
                                        {{ $message->product->name ? \Illuminate\Support\Str::limit($message->product->name, 25) : 'No product specified' }}
                                    </span>
                                    @if($message->product_id)
                                        <span class="badge bg-light text-dark border ms-2">ID: #{{ $message->product_id }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-person me-1"></i> Customer
                                    </span>
                                    <span class="badge bg-success ms-2">{{ $message->sender->name ?? 'Unknown' }}</span>
                                    <span class="badge bg-light text-dark border ms-2" title="{{ $message->sender->email ?? '' }}">
                                        <i class="bi bi-envelope me-1"></i>{{ $message->sender->email ?? 'No email' }}
                                    </span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-calendar me-1"></i> Started
                                    </span>
                                    <span class="badge bg-info text-dark ms-2">{{ $message->created_at->format('F j, Y') }}</span>
                                    <span class="badge bg-light text-dark border ms-2">{{ $message->created_at->format('g:i A') }}</span>
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
    .product-badge {
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
    .conversation-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 1.5rem;
        background: #f8f9fa;
    }
    .message-row {
        width: 100%;
    }
    .bubble-wrap {
        max-width: 350px;
    }
    .message-bubble-compact {
        border-radius: 18px;
        padding: 0.75rem 1.1rem;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        margin-bottom: 2px;
        min-width: 120px;
        word-break: break-word;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .message-bubble-compact.incoming {
        background: #fff;
        color: #222;
        border-bottom-left-radius: 4px;
        border: 1px solid #e3e3e3;
    }
    .message-bubble-compact.outgoing {
        background: #e6f4ea;
        color: #1b5e20;
        border-bottom-right-radius: 4px;
        border: 1px solid #b7e4c7;
    }
    .message-bubble-compact:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        transform: translateY(-1px) scale(1.01);
    }
    .message-meta {
        font-size: 0.85rem;
        color: #888;
    }
    .avatar-lg {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        font-weight: 600;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .empty-conversation {
        padding: 2rem;
    }
    .empty-icon {
        font-size: 3rem;
        color: #dee2e6;
    }
    .reply-card {
        border: 2px solid #e9ecef;
        transition: border-color 0.2s ease;
    }
    .reply-card:focus-within {
        border-color: #007bff;
    }
    .form-control-lg {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .form-control-lg:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    .detail-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    .detail-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .detail-value {
        color: #212529;
        font-size: 1rem;
    }
    @media (max-width: 768px) {
        .bubble-wrap {
            max-width: 90vw;
        }
        .product-badge {
            max-width: 120px !important;
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
    const form = document.getElementById('replyForm');
    const conversationContainer = document.getElementById('conversationContainer');

    // Character counter
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length}/2000`;
        if (length > 1800) {
            charCount.style.color = '#dc3545';
        } else if (length > 1500) {
            charCount.style.color = '#ffc107';
        } else {
            charCount.style.color = '#6c757d';
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        const message = textarea.value.trim();
        if (!message) {
            e.preventDefault();
            return;
        }
        sendButton.disabled = true;
        sendButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Sending...';
    });

    // Auto-resize textarea
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 200) + 'px';
    });

    // Scroll to bottom of conversation
    function scrollToBottom() {
        conversationContainer.scrollTop = conversationContainer.scrollHeight;
    }
    scrollToBottom();

    window.clearForm = function() {
        textarea.value = '';
        textarea.style.height = 'auto';
        charCount.textContent = '0/2000';
        charCount.style.color = '#6c757d';
        textarea.focus();
    };

    textarea.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            form.submit();
        }
    });
    textarea.focus();
});
</script>
@endpush
@endsection 