@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        Conversation with {{ $shop ? $shop->name : ($otherUser->name ?? 'Seller') }}
        @if($product)
            <span class="badge bg-secondary ms-2">{{ $product->name }}</span>
        @endif
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <!-- Product Info Card -->
                @if($product)
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            @if($product->media && $product->media->count() > 0)
                                <img src="{{ $product->media->first()->getUrl() }}" alt="{{ $product->name }}" 
                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <p class="text-muted mb-0 small">{{ $product->description ? \Illuminate\Support\Str::limit($product->description, 100) : 'No description available' }}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $product->price_formatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Messages Container -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 40px; height: 40px; font-size: 1.1rem;">
                                    {{ strtoupper(substr(($shop ? $shop->name : ($otherUser->name ?? 'U')), 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $shop ? $shop->name : ($otherUser->name ?? 'Unknown') }}</h6>
                                    <small class="text-muted">{{ $messages->count() }} message{{ $messages->count() > 1 ? 's' : '' }}</small>
                                </div>
                            </div>
                            <a href="{{ route('buyer.messages.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Back to Conversations
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        @forelse($messages as $message)
                            <div class="mb-3 {{ $message->sender_id == auth()->id() ? 'text-end' : 'text-start' }}">
                                <div class="d-inline-block p-3 rounded {{ $message->sender_id == auth()->id() ? 'bg-success text-white' : 'bg-light' }}" 
                                     style="max-width: 75%;">
                                    <div class="d-flex align-items-center mb-1">
                                        <strong class="me-2">{{ $message->sender->name ?? 'Unknown' }}</strong>
                                        <small class="{{ $message->sender_id == auth()->id() ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message->created_at->format('M d, H:i') }}
                                        </small>
                                    </div>
                                    <div class="message-content">
                                        {{ $message->body }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info text-center">
                                <i class="bi bi-chat-dots mb-2" style="font-size: 2rem;"></i>
                                <div>No messages yet. Start the conversation!</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Message Form -->
                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" action="{{ route('messages.store') }}">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
                            
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="3" 
                                          placeholder="Type your message..." required 
                                          style="resize: none;"></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Press Enter to send, Shift+Enter for new line
                                </small>
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-send me-1"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar {
        font-weight: 600;
        letter-spacing: 1px;
    }
    .message-content {
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .card-body::-webkit-scrollbar {
        width: 6px;
    }
    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    .card-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    .card-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="message"]');
    const form = document.querySelector('form');
    
    // Auto-resize textarea
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Send on Enter (but allow Shift+Enter for new line)
    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.submit();
        }
    });
    
    // Scroll to bottom of messages
    const messagesContainer = document.querySelector('.card-body');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endpush
@endsection
