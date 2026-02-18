@extends('theme.'.theme().'.layouts.app')
@section('title', 'Customer Conversation')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12 justify-center">
            <div class="-span-10 -span-8">
                @if(session('success'))
                    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill mr-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700 alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
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
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-info-circle-fill mr-2"></i>
                        <strong>General Inquiry</strong>
                        <p class="mb-0 mt-2">This conversation is not associated with a specific product. The customer may be asking general questions about your shop.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Header Section --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
                    <div class="p-4">
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <div class="flex items-center flex-wrap gap-3">
                                <a href="{{ route('seller.messages.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg mr-3">
                                    <i class="bi bi-arrow-left mr-1"></i> Back
                                </a>
                                <div class="flex items-center">
                                    <div class="avatar-lg bg-emerald-100 text-emerald-800 border-emerald-200 text-white rounded-full flex items-center justify-center mr-3">
                                        {{ strtoupper(substr($otherUser?->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="mb-1 font-bold">{{ $otherUser?->name ?? 'Customer' }}</h4>
                                        <p class="text-slate-500 mb-0 text-xs">
                                            <i class="bi bi-envelope mr-1"></i>
                                            {{ $otherUser?->email ?? 'No email' }}
                                        </p>
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200 text-xs mt-1">
                                            <i class="bi bi-chat-dots mr-1"></i>{{ $messages->count() }} message{{ $messages->count() > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-2 mt-lg-0">
                                @if($product)
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600 fs-6 px-3 py-2 mb-2 text-truncate product-badge" style="max-width: 250px;" title="{{ $product->name }}">
                                        <i class="bi bi-box mr-1"></i>
                                        {{ \Illuminate\Support\Str::limit($product->name, 30) }}
                                    </span>
                                @endif
                                <div class="text-slate-500 text-xs">
                                    <i class="bi bi-clock mr-1"></i>
                                    Started {!! $messages->first() ? $messages->first()->created_at->diffForHumans() : 'recently' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Info Card --}}
                @if($product)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                    <div class="p-4">
                        <div class="flex items-center">
                            @if($product->media && $product->media->count() > 0)
                                @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($product) : (optional($product->media->first())->url ? asset('storage/'.$product->media->first()->url) : null); @endphp
                                <img src="{{ $thumb }}" alt="{{ $product->name }}" 
                                     class="rounded mr-3" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-slate-50 rounded flex items-center justify-center mr-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-image text-slate-500"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <p class="text-slate-500 mb-0 text-xs">{!! $product->description ? \Illuminate\Support\Str::limit($product->description, 100) : 'No description available' !!}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600">{{ $product->price_formatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Buyer Favorites --}}
                @if(($showBuyerFavorites ?? false) && $otherUser)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4 buyer-favorites-card">
                    <div class="border-b border-slate-200 px-4 py-3 bg-white border-bottom">
                        <div class="flex items-center">
                            <i class="bi bi-heart-fill mr-2 text-rose-600"></i>
                            <h5 class="mb-0">Items {{ $otherUser?->name ?? 'this buyer' }} favorited</h5>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-auto">{{ $buyerFavorites->count() }} item{{ $buyerFavorites->count() === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    <div class="p-4">
                        @if($buyerFavorites->isEmpty())
                            <div class="flex items-center text-slate-500">
                                <i class="bi bi-emoji-neutral mr-2"></i>
                                <span>This buyer has not favorited any of your products yet.</span>
                            </div>
                        @else
                            @php
                                $currencySymbol = function_exists('shop_currency') ? shop_currency() : (function_exists('get_currency') ? get_currency() : '$');
                            @endphp
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
                                @foreach($buyerFavorites as $favorite)
                                    @php $favProduct = $favorite->product; @endphp
                                    <div class="-span-6">
                                        <div class="border rounded p-3 flex items-center gap-3 buyer-favorite-card h-full">
                                            <div class="favorite-thumb flex-shrink-0">
                                                @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($favProduct) : (optional($favProduct->media->first())->url ? asset('storage/'.$favProduct->media->first()->url) : null); @endphp
                                                @if($thumb)
                                                    <img src="{{ $thumb }}" alt="{{ $favProduct->name }}" class="rounded favorite-thumb-img">
                                                @else
                                                    <div class="bg-slate-50 rounded flex items-center justify-center favorite-thumb-img">
                                                        <i class="bi bi-image text-slate-500"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <h6 class="mb-1">{{ $favProduct->name }}</h6>
                                                        <div class="text-slate-500 text-xs mb-1">Favorited {{ $favorite->created_at->diffForHumans() }}</div>
                                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600">{{ $currencySymbol }} {{ number_format($favProduct->price ?? 0, 2) }}</span>
                                                    </div>
                                                    <div class="text-right flex flex-col gap-2 items-end">
                                                        <a href="{{ route('products.show', $favProduct->slug ?? $favProduct->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                            <i class="bi bi-eye mr-1"></i>View
                                                        </a>
                                                        <a href="{{ route('seller.messages.show', $favProduct->id . '-' . $otherUser->id) }}?prefill={{ urlencode('Hi '.($otherUser->name ?? 'there').', thanks for favoriting \"'.$favProduct->name.'\". Do you have any questions?') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                            <i class="bi bi-chat-dots mr-1"></i>Message
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Conversation Messages --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4 conversation-card">
                    <div class="border-b border-slate-200 px-4 py-3 bg-white border-bottom">
                        <div class="flex items-center">
                            <i class="bi bi-chat-dots-fill mr-2 text-primary"></i>
                            <h5 class="mb-0">Message History</h5>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-auto">{{ $messages->count() }} messages</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-slate-500 text-xs">
                                <i class="bi bi-info-circle mr-1"></i>
                                @if($product)
                                    This conversation is between you and {{ $otherUser?->name ?? 'the customer' }} about this specific product.
                                @else
                                    This conversation is between you and {{ $otherUser?->name ?? 'the customer' }}.
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="p-4 p-0">
                        <div class="conversation-container" id="conversationContainer">
                            @forelse($messages as $message)
                                <div class="message-row flex {{ $message->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                                    <div class="flex align-items-end {{ $message->sender_id == auth()->id() ? 'flex-row-reverse' : '' }}">
                                        <div class="avatar-sm {{ $message->sender_id == auth()->id() ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-emerald-600 text-white border-emerald-600' }} rounded-full flex align-items-center justify-content-center ml-2 mr-2" style="width:32px;height:32px;font-size:0.9rem;">
                                            {{ strtoupper(substr($message->sender->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="bubble-wrap">
                                            <div class="message-bubble-compact {{ $message->sender_id == auth()->id() ? 'outgoing' : 'incoming' }}">
                                                <div class="message-meta flex items-center mb-1">
                                                    <span class="font-semibold text-xs mr-2">{{ $message->sender->name ?? 'Unknown' }}</span>
                                                    <span class="text-slate-500 text-xs">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                                                </div>
                                                <div class="message-content">{{ $message->body }}</div>
                                                @if(!empty($message->attachment_path))
                                                    @php
                                                        $isImage = \Illuminate\Support\Str::endsWith(strtolower($message->attachment_path), ['.jpg','.jpeg','.png','.gif','.webp']);
                                                        $attachmentUrl = asset('storage/' . ltrim($message->attachment_path, '/'));
                                                    @endphp
                                                    <div class="mt-2">
                                                        @if($isImage)
                                                            <a href="{{ $attachmentUrl }}" target="_blank">
                                                                <img src="{{ $attachmentUrl }}" alt="Attachment" class="h-auto max-w-full rounded" style="max-width: 260px;">
                                                            </a>
                                                        @else
                                                            <a href="{{ $attachmentUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">
                                                                <i class="bi bi-paperclip mr-1"></i>View attachment
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
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
                                        <h5 class="text-slate-500">No messages yet</h5>
                                        <p class="text-slate-500">Start the conversation by sending a reply below.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Reply Form --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 reply-card">
                    <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
                        <div class="flex items-center">
                            <i class="bi bi-reply-fill mr-2 text-emerald-600"></i>
                            <h5 class="mb-0">Send Reply</h5>
                        </div>
                    </div>
                    <div class="p-4">
                        <form method="POST" action="{{ route('seller.messages.reply', $conversationId) }}" id="replyForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="form-label font-bold">
                                    <i class="bi bi-pencil mr-1"></i>
                                    Your Message
                                </label>
                                <textarea 
                                    name="message" 
                                    id="message" 
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 py-3 text-base @error('message') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" 
                                    rows="4" 
                                    placeholder="Type your professional reply here..." 
                                    required
                                    maxlength="2000"
                                >{{ old('message', request('prefill')) }}</textarea>
                                <div class="mt-3">
                                    <label for="attachment" class="form-label">Attachment (optional)</label>
                                    <input type="file" name="attachment" id="attachment" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                    <div class="form-text">Images or PDF, max 5MB.</div>
                                </div>
                                <div class="form-text flex justify-between">
                                    <span>Be professional and helpful in your response</span>
                                    <span id="charCount">0/2000</span>
                                </div>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="reply-info">
                                    <div class="flex items-center text-slate-500">
                                        <i class="bi bi-info-circle mr-1"></i>
                                        <span class="text-xs">Reply will be sent to {{ $otherUser?->email ?? 'the customer' }}</span>
                                    </div>
                                    <div class="flex items-center text-slate-500 mt-1">
                                        <i class="bi bi-clock mr-1"></i>
                                        <span class="text-xs">Customer will receive an email notification</span>
                                    </div>
                                </div>
                                <div class="reply-actions">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 mr-2" onclick="clearForm()">
                                        <i class="bi bi-x-circle mr-1"></i>
                                        Clear
                                    </button>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2.5 text-base" id="sendButton">
                                        <i class="bi bi-send mr-1"></i>
                                        Send Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Message Details Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 mt-4 conversation-details-card">
                    <div class="border-b border-slate-200 px-4 py-3 bg-white border-bottom">
                        <div class="flex items-center">
                            <i class="bi bi-info-circle mr-2 text-sky-600 fs-4"></i>
                            <h5 class="mb-0">Conversation Details</h5>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 bg-gradient">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-12 items-center">
                            <div class="-span-6">
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-hash mr-1"></i> Conversation ID
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200 ml-2">#{{ $conversationId }}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-box mr-1"></i> Product
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600 ml-2 text-truncate" style="max-width: 160px;" title="{{ $product?->name ?? '' }}">
                                        {{ $product?->name ? \Illuminate\Support\Str::limit($product?->name, 25) : 'No product specified' }}
                                    </span>
                                    @if($product)
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-2">ID: #{{ $product->id }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="-span-6">
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-person mr-1"></i> Customer
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200 ml-2">{{ $otherUser?->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label text-secondary">
                                        <i class="bi bi-calendar mr-1"></i> Started
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-2">
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
    .buyer-favorite-card {
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .buyer-favorite-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .favorite-thumb-img {
        width: 64px;
        height: 64px;
        object-fit: cover;
    }
    @media (max-width: 768px) {
        .reply-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        .reply-actions .btn {
            width: 100%;
        }
        .favorite-thumb-img {
            width: 56px;
            height: 56px;
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
      </div>
    </div>
  </div>
</section>
@endsection 








