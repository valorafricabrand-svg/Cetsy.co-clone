@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold fs-3 text-slate-900">
        Conversation with {{ $shop ? $shop->name : ($otherUser->name ?? 'Seller') }}
        @if($product)
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 ml-2">{{ $product->name }}</span>
        @endif
    </h2>
@endsection

@section('main')
<div class="content">
    <div class="container-xxl">
        <div class="grid grid-cols-12 gap-4 justify-center">
            <div class="lg:col-span-8 md:col-span-10">
                <!-- Product Info Card -->
                @if($product)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            @if($product->media && $product->media->count() > 0)
                                @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($product) : (optional($product->media->first())->url ? asset('storage/'.$product->media->first()->url) : null); @endphp
                                <img src="{{ $thumb }}" alt="{{ $product->name }}" 
                                     class="rounded mr-3" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-slate-100 rounded flex items-center justify-center mr-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-image text-slate-500"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <p class="text-slate-500 mb-0 text-xs">{!! $product->description ? \Illuminate\Support\Str::limit($product->description, 100) : 'No description available' !!}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary">{{ $product->price_formatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Messages Container -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow mb-4">
                    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="avatar bg-success text-white rounded-full flex items-center justify-center mr-2" 
                                     style="width: 40px; height: 40px; font-size: 1.1rem;">
                                    {{ strtoupper(substr(($shop ? $shop->name : ($otherUser->name ?? 'U')), 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $shop ? $shop->name : ($otherUser->name ?? 'Unknown') }}</h6>
                                    <small class="text-slate-500">{{ $messages->count() }} message{{ $messages->count() > 1 ? 's' : '' }}</small>
                                </div>
                            </div>
                            <a href="{{ route('buyer.messages.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs">
                                <i class="bi bi-arrow-left mr-1"></i>Back to Conversations
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-4 sm:p-5" style="max-height: 500px; overflow-y: auto;">
                        @if($messages->count())
                            @foreach($messages as $message)
                                <div class="mb-3 {{ $message->sender_id == auth()->id() ? 'text-end' : 'text-start' }}">
                                    <div class="inline-block p-3 rounded {{ $message->sender_id == auth()->id() ? 'bg-success text-white' : 'bg-light' }}" 
                                         style="max-width: 75%;">
                                        <div class="flex items-center mb-1">
                                            <strong class="mr-2">{{ $message->sender->shop->name ?? $message->sender->name }}</strong>
                                            <small class="{{ $message->sender_id == auth()->id() ? 'text-white-50' : 'text-muted' }}">
                                                {{ $message->created_at->format('M d, H:i') }}
                                            </small>
                                        </div>
                                        <div class="message-content">
                                            {{ $message->body }}
                                        </div>
                                        @if(!empty($message->attachment_path))
                                            @php
                                                $isImage = \Illuminate\Support\Str::endsWith(strtolower($message->attachment_path), ['.jpg','.jpeg','.png','.gif','.webp']);
                                                $attachmentUrl = asset('storage/' . ltrim($message->attachment_path, '/'));
                                            @endphp
                                            <div class="mt-2">
                                                @if($isImage)
                                                    <a href="{{ $attachmentUrl }}" target="_blank">
                                                        <img src="{{ $attachmentUrl }}" alt="Attachment" class="img-fluid rounded" style="max-width: 240px;">
                                                    </a>
                                                @else
                                                    <a href="{{ $attachmentUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50">
                                                        <i class="bi bi-paperclip mr-1"></i>View attachment
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-center">
                                <i class="bi bi-chat-dots mb-2" style="font-size: 2rem;"></i>
                                <div>No messages yet. Start the conversation!</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Message Form -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow">
                    <div class="p-4 sm:p-5">
                        <form method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
                            
                            <div class="mb-3">
                                <textarea name="message" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" 
                                          placeholder="Type your message..." required 
                                          style="resize: none;"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="buyerAttachment" class="mb-1 block text-sm font-medium text-slate-700">Attachment (optional)</label>
                                <input type="file" name="attachment" id="buyerAttachment" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                <small class="text-slate-500">Images or PDF, max 5MB.</small>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <small class="text-slate-500">
                                    Press Enter to send, Shift+Enter for new line
                                </small>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                    <i class="bi bi-send mr-1"></i>Send Message
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




