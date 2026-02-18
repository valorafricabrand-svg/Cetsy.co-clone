@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        Conversation with {{ $shop ? $shop->name : ($otherUser->name ?? 'Seller') }}
        @if($product)
            <span class="ml-2 inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $product->name }}</span>
        @endif
    </h2>
@endsection

@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="mx-auto grid max-w-5xl grid-cols-12 gap-4">
            <div class="col-span-12">
                @if($product)
                <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            @if($product->media && $product->media->count() > 0)
                                @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($product) : (optional($product->media->first())->url ? asset('storage/'.$product->media->first()->url) : null); @endphp
                                <img src="{{ $thumb }}" alt="{{ $product->name }}" class="mr-3 h-[60px] w-[60px] rounded object-cover">
                            @else
                                <div class="mr-3 flex h-[60px] w-[60px] items-center justify-center rounded bg-slate-100">
                                    <i class="fa-regular fa-image text-slate-500"></i>
                                </div>
                            @endif
                            <div class="grow">
                                <h6 class="mb-1 text-base font-semibold text-slate-900">{{ $product->name }}</h6>
                                <p class="mb-0 text-xs text-slate-500">{!! $product->description ? \Illuminate\Support\Str::limit($product->description, 100) : 'No description available' !!}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">{{ $product->price_formatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="avatar mr-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-base font-semibold text-white">
                                    {{ strtoupper(substr(($shop ? $shop->name : ($otherUser->name ?? 'U')), 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 text-sm font-semibold text-slate-900">{{ $shop ? $shop->name : ($otherUser->name ?? 'Unknown') }}</h6>
                                    <small class="text-xs text-slate-500">{{ $messages->count() }} message{{ $messages->count() > 1 ? 's' : '' }}</small>
                                </div>
                            </div>
                            <a href="{{ route('buyer.messages.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fa-solid fa-arrow-left mr-1"></i>Back to Conversations
                            </a>
                        </div>
                    </div>

                    <div id="messagesContainer" class="max-h-[500px] overflow-y-auto p-4 sm:p-5">
                        @if($messages->count())
                            @foreach($messages as $message)
                                @php $isMine = $message->sender_id == auth()->id(); @endphp
                                <div class="mb-3 {{ $isMine ? 'text-right' : 'text-left' }}">
                                    <div class="inline-block max-w-[85%] rounded-xl p-3 {{ $isMine ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-900' }}">
                                        <div class="mb-1 flex items-center">
                                            <strong class="mr-2 text-xs">{{ $message->sender->shop->name ?? $message->sender->name }}</strong>
                                            <small class="text-xs {{ $isMine ? 'text-emerald-100' : 'text-slate-500' }}">{{ $message->created_at->format('M d, H:i') }}</small>
                                        </div>
                                        <div class="message-content text-sm">{{ $message->body }}</div>

                                        @if(!empty($message->attachment_path))
                                            @php
                                                $isImage = \Illuminate\Support\Str::endsWith(strtolower($message->attachment_path), ['.jpg','.jpeg','.png','.gif','.webp']);
                                                $attachmentUrl = asset('storage/' . ltrim($message->attachment_path, '/'));
                                            @endphp
                                            <div class="mt-2">
                                                @if($isImage)
                                                    <a href="{{ $attachmentUrl }}" target="_blank">
                                                        <img src="{{ $attachmentUrl }}" alt="Attachment" class="max-w-[240px] rounded">
                                                    </a>
                                                @else
                                                    <a href="{{ $attachmentUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                                        <i class="fa-solid fa-paperclip mr-1"></i>View attachment
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-center text-sm text-sky-800">
                                <i class="fa-regular fa-comments mb-2 text-3xl"></i>
                                <div>No messages yet. Start the conversation!</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <form method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data" id="buyerMessageForm">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">

                            <div class="mb-3">
                                <textarea name="message" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Type your message..." required style="resize:none;"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="buyerAttachment" class="mb-1 block text-sm font-medium text-slate-700">Attachment (optional)</label>
                                <input type="file" name="attachment" id="buyerAttachment" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                <small class="text-xs text-slate-500">Images or PDF, max 5MB.</small>
                            </div>

                            <div class="flex items-center justify-between">
                                <small class="text-xs text-slate-500">Press Enter to send, Shift+Enter for new line</small>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                    <i class="fa-regular fa-paper-plane mr-1"></i>Send Message
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
    .avatar { letter-spacing: 1px; }
    .message-content {
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    #messagesContainer::-webkit-scrollbar { width: 6px; }
    #messagesContainer::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
    #messagesContainer::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    #messagesContainer::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="message"]');
    const form = document.getElementById('buyerMessageForm');

    textarea?.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    textarea?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form?.submit();
        }
    });

    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endpush
@endsection
