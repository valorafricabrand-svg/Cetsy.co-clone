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
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-span-12 lg:col-span-9">
                @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ $errors->first() }}
                </div>
                @endif

                @php
                    $currencySymbol = function_exists('shop_currency') ? shop_currency() : get_currency();
                    $listingPrice = $product ? (float) ($product->price ?? 0) : 0;
                    $currentPrice = $product ? (float) ($product->discounted_price ?? $listingPrice) : 0;
                    $lowestVariantPrice = null;
                    if ($product) {
                        if ($product->relationLoaded('variations') && $product->variations) {
                            $lowestVariantPrice = $product->variations
                                ->filter(fn ($variant) => ($variant->options->count() ?? 0) > 0)
                                ->pluck('price')
                                ->filter(fn ($value) => $value !== null)
                                ->min();
                        } elseif (method_exists($product, 'variations')) {
                            $lowestVariantPrice = $product->variations()
                                ->whereHas('options')
                                ->whereNotNull('price')
                                ->min('price');
                        }
                    }
                    $displayPrice = $lowestVariantPrice !== null
                        ? (float) $lowestVariantPrice
                        : ($currentPrice > 0 ? $currentPrice : ($listingPrice > 0 ? $listingPrice : null));
                    $priceLabel = $lowestVariantPrice !== null
                        ? (strtolower((string) ($product->type ?? '')) === 'service' ? 'Priced from' : 'From')
                        : 'Posted price';
                    $offerInputValue = old('offer_price', $latestOffer->offer_price ?? '');
                    $listingRouteParam = $product ? ($product->slug ?: $product->id) : null;
                    $showOfferPanel = $product && ($errors->has('offer_price') || (string) old('product_id') === (string) $product->id);
                @endphp

                @if($product)
                <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <a href="{{ route('listing.show', $listingRouteParam) }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-2xl transition hover:bg-slate-50">
                                @if($product->media && $product->media->count() > 0)
                                    @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($product) : (optional($product->media->first())->url ? asset('storage/'.$product->media->first()->url) : null); @endphp
                                    <img src="{{ $thumb }}" alt="{{ $product->name }}" class="h-[72px] w-[72px] rounded-xl object-cover">
                                @else
                                    <div class="flex h-[72px] w-[72px] items-center justify-center rounded-xl bg-slate-100">
                                        <i class="fa-regular fa-image text-slate-500"></i>
                                    </div>
                                @endif
                                <div class="min-w-0 grow">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h6 class="text-base font-semibold text-slate-900">{{ $product->name }}</h6>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Tap to view listing</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{!! $product->description ? \Illuminate\Support\Str::limit($product->description, 110) : 'No description available' !!}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if($displayPrice !== null)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $priceLabel }} {{ money($displayPrice) }}</span>
                                        @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Contact for price</span>
                                        @endif
                                        @if($currentPrice > 0 && abs($currentPrice - $listingPrice) > 0.009)
                                        <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">Current listing price {{ $currencySymbol }} {{ number_format($currentPrice, 2) }}</span>
                                        @endif
                                        @if($latestOffer)
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Latest offer {{ $latestOffer->formatted_price }} · {{ $latestOffer->status_label }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            <div class="flex flex-wrap gap-2 lg:w-auto lg:flex-col lg:items-end">
                                <a href="{{ route('listing.show', $listingRouteParam) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                    <i class="fa-regular fa-eye mr-1"></i>View Item
                                </a>
                                @if($latestOffer)
                                <a href="{{ route('buyer.offers.details', $latestOffer->id) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-600 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                                    <i class="fa-regular fa-file-lines mr-1"></i>View Offer
                                </a>
                                @endif
                                <button type="button" id="toggleBuyerOffer" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                    <i class="fa-solid fa-tag mr-1"></i>{{ $latestOffer ? 'Update Offer' : 'Make Offer' }}
                                </button>
                            </div>
                        </div>

                        <div id="buyerOfferPanel" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 {{ $showOfferPanel ? '' : 'hidden' }}">
                            <div class="mb-3">
                                <h6 class="mb-1 text-sm font-semibold text-slate-900">Send an offer to the seller</h6>
                                <p class="mb-0 text-xs text-slate-500">Use the posted price above as your reference. Your offer cannot exceed the current listing price.</p>
                            </div>
                            <form method="POST" action="{{ route('offers.store') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div class="w-full sm:max-w-xs">
                                    <label for="buyerOfferPrice" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Offer Price</label>
                                    <input type="number"
                                           name="offer_price"
                                           id="buyerOfferPrice"
                                           min="1"
                                           max="{{ number_format($displayPrice ?? ($currentPrice > 0 ? $currentPrice : $listingPrice), 2, '.', '') }}"
                                           step="0.01"
                                           value="{{ $offerInputValue }}"
                                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                                           placeholder="{{ number_format($displayPrice ?? ($currentPrice > 0 ? $currentPrice : $listingPrice), 2) }}"
                                           required>
                                    @error('offer_price')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                    <i class="fa-regular fa-paper-plane mr-1"></i>Send Offer
                                </button>
                            </form>
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
    const offerToggle = document.getElementById('toggleBuyerOffer');
    const offerPanel = document.getElementById('buyerOfferPanel');

    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (form) {
                    form.submit();
                }
            }
        });
    }

    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    if (offerToggle && offerPanel) {
        offerToggle.addEventListener('click', function() {
            offerPanel.classList.toggle('hidden');
        });
    }
});
</script>
@endpush
@endsection
