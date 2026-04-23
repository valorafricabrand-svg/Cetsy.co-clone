@extends('theme.'.theme().'.layouts.app')
@section('title', 'Customer Conversation')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">
 <div class="w-full">
 <div class="grid grid-cols-1 gap-4">
 <div class="col-span-1">
 @if(session('success'))
 <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 mb-4" role="alert">
 <i class="fa-solid fa-circle-check mr-2"></i>
 {{ session('success') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert">&times;</button>
 </div>
 @endif

 @if($errors->any())
 <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700 mb-4" role="alert">
 <i class="fa-solid fa-triangle-exclamation mr-2"></i>
 <strong>Error:</strong>
 <ul class="mb-0 mt-2">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert">&times;</button>
 </div>
 @endif

 @if(!$product)
 <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-4" role="alert">
 <i class="fa-solid fa-circle-info mr-2"></i>
 <strong>General Inquiry</strong>
 <p class="mb-0 mt-2">This conversation is not associated with a specific product. The customer may be asking general questions about your shop.</p>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert">&times;</button>
 </div>
 @endif

 {{-- Header Section --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="p-4">
 <div class="flex justify-between items-center flex-wrap gap-2">
 <div class="flex items-center flex-wrap gap-3">
 <a href="{{ route('seller.messages.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg mr-3">
 <i class="fa-solid fa-arrow-left mr-1"></i> Back
 </a>
 <div class="flex items-center">
 <div class="avatar-lg bg-emerald-100 text-emerald-800 border-emerald-200 text-white rounded-full flex items-center justify-center mr-3">
 {{ strtoupper(substr($otherUser?->name ?? 'C', 0, 1)) }}
 </div>
 <div>
 <h4 class="mb-1 font-bold">{{ $otherUser?->name ?? 'Customer' }}</h4>
 <p class="text-slate-500 mb-0 text-xs">
 <i class="fa-regular fa-envelope mr-1"></i>
 {{ $otherUser?->email ?? 'No email' }}
 </p>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200 text-xs mt-1">
 <i class="fa-regular fa-comments mr-1"></i>{{ $messages->count() }} message{{ $messages->count() > 1 ? 's' : '' }}
 </span>
 </div>
 </div>
 </div>
 <div class="text-right mt-2 lg:mt-0">
 @if($product)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600 text-base px-3 py-2 mb-2 truncate product-badge" style="max-width: 250px;" title="{{ $product->name }}">
 <i class="fa-solid fa-box mr-1"></i>
 {{ \Illuminate\Support\Str::limit($product->name, 30) }}
 </span>
 @endif
 <div class="text-slate-500 text-xs">
 <i class="fa-regular fa-clock mr-1"></i>
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
 <i class="fa-regular fa-image text-slate-500"></i>
 </div>
 @endif
 <div class="flex-1">
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
 <div class="border-b border-slate-200 px-4 py-3 bg-white">
 <div class="flex items-center">
 <i class="fa-solid fa-heart mr-2 text-rose-600"></i>
 <h5 class="mb-0">Items {{ $otherUser?->name ?? 'this buyer' }} favorited</h5>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-auto">{{ $buyerFavorites->count() }} item{{ $buyerFavorites->count() === 1 ? '' : 's' }}</span>
 </div>
 </div>
 <div class="p-4">
 @if($buyerFavorites->isEmpty())
 <div class="flex items-center text-slate-500">
 <i class="fa-regular fa-face-meh mr-2"></i>
 <span>This buyer has not favorited any of your products yet.</span>
 </div>
 @else
 @php
 $currencySymbol = function_exists('shop_currency') ? shop_currency() : (function_exists('get_currency') ? get_currency() : '$');
 @endphp
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
 @foreach($buyerFavorites as $favorite)
 @php $favProduct = $favorite->product; @endphp
 <div class="col-span-12 md:col-span-6">
 <div class="border rounded p-3 flex items-center gap-3 buyer-favorite-card h-full">
 <div class="favorite-thumb shrink-0">
 @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($favProduct) : (optional($favProduct->media->first())->url ? asset('storage/'.$favProduct->media->first()->url) : null); @endphp
 @if($thumb)
 <img src="{{ $thumb }}" alt="{{ $favProduct->name }}" class="rounded favorite-thumb-img">
 @else
 <div class="bg-slate-50 rounded flex items-center justify-center favorite-thumb-img">
 <i class="fa-regular fa-image text-slate-500"></i>
 </div>
 @endif
 </div>
 <div class="flex-1">
 <div class="flex items-start justify-between gap-2">
 <div>
 <h6 class="mb-1">{{ $favProduct->name }}</h6>
 <div class="text-slate-500 text-xs mb-1">Favorited {{ $favorite->created_at->diffForHumans() }}</div>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600">{{ $currencySymbol }} {{ number_format($favProduct->price ?? 0, 2) }}</span>
 </div>
 <div class="text-right flex flex-col gap-2 items-end">
 <a href="{{ localized_route('listing.show', $favProduct->slug ?? $favProduct->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
 <i class="fa-regular fa-eye mr-1"></i>View
 </a>
 <a href="{{ route('seller.messages.show', $favProduct->id . '-' . $otherUser->id) }}?prefill={{ urlencode('Hi '.($otherUser->name ?? 'there').', thanks for favoriting \"'.$favProduct->name.'\". Do you have any questions?') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
 <i class="fa-regular fa-comments mr-1"></i>Message
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
 <div class="border-b border-slate-200 px-4 py-3 bg-white">
 <div class="flex items-center">
 <i class="fa-regular fa-comments-fill mr-2 text-emerald-600"></i>
 <h5 class="mb-0">Message History</h5>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-auto">{{ $messages->count() }} messages</span>
 </div>
 <div class="mt-2">
 <span class="text-slate-500 text-xs">
 <i class="fa-regular fa-circle-info mr-1"></i>
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
 @php
 $sharedProductsForMessage = $message->sharedProducts ?? collect();
 @endphp
 <div class="message-row flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }} mb-3">
 <div class="message-shell flex max-w-full items-end gap-2 {{ $message->sender_id == auth()->id() ? 'flex-row-reverse' : '' }}">
 <div class="avatar-sm {{ $message->sender_id == auth()->id() ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-emerald-600 text-white border-emerald-600' }} shrink-0 rounded-full flex items-center justify-center" style="width:32px;height:32px;font-size:0.9rem;">
 {{ strtoupper(substr($message->sender->name ?? 'U', 0, 1)) }}
 </div>
 <div class="bubble-wrap">
 <div class="message-bubble-compact {{ $message->sender_id == auth()->id() ? 'outgoing' : 'incoming' }} {{ $sharedProductsForMessage->isNotEmpty() ? 'has-shared-listings' : '' }}">
 <div class="message-meta mb-1 flex flex-wrap items-center gap-x-2 gap-y-1">
 <span class="min-w-0 break-words text-xs font-semibold">{{ $message->sender->name ?? 'Unknown' }}</span>
 <span class="text-xs {{ $message->sender_id == auth()->id() ? 'text-emerald-100/90' : 'text-slate-500' }}">{{ $message->created_at->format('M j, Y g:i A') }}</span>
 </div>
 <div class="message-content">{{ $message->body }}</div>
 @include('messages.partials.shared-listings', [
   'sharedProducts' => $sharedProductsForMessage,
   'isOutgoing' => $message->sender_id == auth()->id(),
 ])
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
 <i class="fa-solid fa-paperclip mr-1"></i>View attachment
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
 <i class="fa-regular fa-comments"></i>
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
 @php
 $selectedSharedListingIds = collect(old('shared_listing_ids', []))
 ->map(fn ($id) => (int) $id)
 ->filter()
 ->unique()
 ->values();
 @endphp
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 reply-card">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <div class="flex items-center">
 <i class="fa-solid fa-reply mr-2 text-emerald-600"></i>
 <h5 class="mb-0">Send Reply</h5>
 </div>
 @if(($shareableProducts ?? collect())->isNotEmpty())
 <button type="button" id="openListingShareModal" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
 <i class="fa-solid fa-share-nodes mr-1"></i>
 Choose Listings
 </button>
 @endif
 </div>
 </div>
 <div class="p-4">
 <form method="POST" action="{{ route('seller.messages.reply', $conversationId) }}" id="replyForm" enctype="multipart/form-data">
 @csrf
 @if(($shareableProducts ?? collect())->isNotEmpty())
 <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <div>
 <div class="text-sm font-semibold text-slate-900">Share listings in this conversation</div>
 <p class="mt-1 text-xs text-slate-500">Select one or more of your active listings and send them with an optional note.</p>
 </div>
 <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50" id="openListingShareModalInline">
 <i class="fa-solid fa-list-check mr-1"></i>Pick Listings
 </button>
 </div>
 <div class="mt-3">
 <div id="selectedListingEmpty" class="text-xs text-slate-500">No listings selected yet.</div>
 <div id="selectedListingState" class="hidden">
 <div class="mb-2 flex items-center gap-2 text-xs text-slate-600">
 <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
 <span id="selectedListingCount">0</span>&nbsp;selected
 </span>
 </div>
 <div id="selectedListingPreview" class="flex flex-wrap gap-2"></div>
 </div>
 </div>
 @error('shared_listing_ids')
 <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
 @enderror
 </div>
 @endif
 <div class="mb-3">
 <label for="message" class="form-label font-bold">
 <i class="fa-solid fa-pen mr-1"></i>
 Your Message
 </label>
 <textarea 
 name="message" 
 id="message" 
 class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 py-3 text-base @error('message') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" 
 rows="4" 
 placeholder="Write a reply, or just send selected listings..." 
 maxlength="2000"
 >{{ old('message', request('prefill')) }}</textarea>
 <div class="mt-3">
 <label for="attachment" class="form-label">Attachment (optional)</label>
 <input type="file" name="attachment" id="attachment" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
 <div class="form-text">Images or PDF, max 5MB.</div>
 </div>
 <div class="form-text flex justify-between">
 <span>Be professional and helpful. You can send a note, listings, an attachment, or a combination.</span>
 <span id="charCount">0/2000</span>
 </div>
 @error('message')
 <div class="invalid-feedback">{{ $message }}</div>
 @enderror
 </div>
 <div class="flex justify-between items-center">
 <div class="reply-info">
 <div class="flex items-center text-slate-500">
 <i class="fa-regular fa-circle-info mr-1"></i>
 <span class="text-xs">Reply will be sent to {{ $otherUser?->email ?? 'the customer' }}</span>
 </div>
 <div class="flex items-center text-slate-500 mt-1">
 <i class="fa-regular fa-clock mr-1"></i>
 <span class="text-xs">Customer will receive an email notification</span>
 </div>
 </div>
 <div class="reply-actions">
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 mr-2" onclick="clearForm()">
 <i class="fa-solid fa-xmark-circle mr-1"></i>
 Clear
 </button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2.5 text-base" id="sendButton">
 <i class="fa-solid fa-paper-plane mr-1"></i>
 Send Message
 </button>
 </div>
 </div>
 </form>
 </div>
 </div>

 @if(($shareableProducts ?? collect())->isNotEmpty())
 <div id="listingShareModal" class="fixed inset-0 z-[90] hidden">
 <div class="absolute inset-0 bg-slate-950/80" data-close-listing-share></div>
 <div class="relative mx-auto flex h-full w-full max-w-3xl flex-col p-4 sm:p-6">
 <div class="flex h-full flex-col overflow-hidden rounded-[28px] bg-[#111111] text-white shadow-2xl">
 <div class="flex items-center justify-between border-b border-white/10 px-4 py-4 sm:px-6">
 <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/10 text-white transition hover:bg-white/10" data-close-listing-share aria-label="Close send listings">
 <i class="fa-solid fa-xmark text-lg"></i>
 </button>
 <h3 class="text-lg font-semibold sm:text-2xl">Send listings</h3>
 <button type="button" id="submitListingShare" class="rounded-full bg-white/15 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/20 disabled:cursor-not-allowed disabled:opacity-40">
 Send
 </button>
 </div>

 <div class="px-4 py-4 sm:px-6">
 <label class="flex items-center gap-3 rounded-full border border-white/15 bg-white/5 px-4 py-3">
 <i class="fa-solid fa-magnifying-glass text-slate-300"></i>
 <input type="search" id="listingShareSearch" placeholder="Search your listings" class="w-full bg-transparent text-base text-white placeholder:text-slate-400 focus:outline-none">
 <button type="button" id="clearListingShareSearch" class="hidden text-slate-300 transition hover:text-white" aria-label="Clear listing search">
 <i class="fa-solid fa-xmark text-xl"></i>
 </button>
 </label>
 <div class="mt-3 flex items-center justify-between text-xs text-slate-300">
 <span id="listingShareResultsMeta">{{ $shareableProducts->count() }} listings available</span>
 <span>Selected: <span id="modalSelectedCount">{{ $selectedSharedListingIds->count() }}</span></span>
 </div>
 </div>

 <div class="flex-1 overflow-y-auto px-4 pb-6 sm:px-6">
 <div class="space-y-3">
 @foreach($shareableProducts as $shareListing)
 @php
 $shareThumb = function_exists('product_thumb_url') ? product_thumb_url($shareListing) : media_url($shareListing->featured_image ?? null);
 $shareType = strtolower((string) ($shareListing->type ?? ''));
 $shareStock = is_numeric($shareListing->stock ?? null) ? (int) $shareListing->stock : null;
 $shareStockLabel = match ($shareType) {
 'service' => 'Service listing',
 'digital' => 'Digital listing',
 default => ($shareStock !== null ? $shareStock . ' in stock' : 'Stock unavailable'),
 };
 @endphp
 <label class="listing-share-option group flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 transition hover:border-white/20 hover:bg-white/10" data-listing-option data-name="{{ strtolower($shareListing->name) }}" data-sku="{{ strtolower((string) ($shareListing->sku ?? '')) }}">
 <input type="checkbox" class="listing-share-checkbox peer sr-only" name="shared_listing_ids[]" value="{{ $shareListing->id }}" form="replyForm" data-listing-name="{{ $shareListing->name }}" {{ $selectedSharedListingIds->contains((int) $shareListing->id) ? 'checked' : '' }}>
 <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/20 bg-transparent text-transparent transition peer-checked:border-emerald-400 peer-checked:bg-emerald-500 peer-checked:text-white">
 <i class="fa-solid fa-check text-xs"></i>
 </span>
 @if($shareThumb)
 <img src="{{ $shareThumb }}" alt="{{ $shareListing->name }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover">
 @else
 <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/5">
 <i class="fa-regular fa-image text-slate-400"></i>
 </div>
 @endif
 <div class="min-w-0 flex-1">
 <div class="truncate text-base font-semibold text-white">{{ $shareListing->name }}</div>
 <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-300">
 <span>{{ $shareStockLabel }}</span>
 <span>&bull;</span>
 <span>{{ money((float) ($shareListing->discounted_price ?? $shareListing->price ?? 0), null) }}</span>
 </div>
 </div>
 </label>
 @endforeach
 <div id="listingShareNoResults" class="hidden rounded-2xl border border-dashed border-white/15 px-4 py-6 text-center text-sm text-slate-300">
 No listings match your search.
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 @endif

 {{-- Message Details Card --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 mt-4 conversation-details-card">
 <div class="border-b border-slate-200 px-4 py-3 bg-white">
 <div class="flex items-center">
 <i class="fa-regular fa-circle-info mr-2 text-sky-600 text-xl"></i>
 <h5 class="mb-0">Conversation Details</h5>
 </div>
 </div>
 <div class="p-4 bg-slate-50 bg-gradient">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 items-center">
 <div class="col-span-12 md:col-span-6">
 <div class="detail-item mb-3">
 <span class="detail-label text-slate-500">
 <i class="fa-solid fa-hashtag mr-1"></i> Conversation ID
 </span>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200 ml-2">#{{ $conversationId }}</span>
 </div>
 <div class="detail-item mb-3">
 <span class="detail-label text-slate-500">
 <i class="fa-solid fa-box mr-1"></i> Product
 </span>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600 ml-2 truncate" style="max-width: 160px;" title="{{ $product?->name ?? '' }}">
 {{ $product?->name ? \Illuminate\Support\Str::limit($product?->name, 25) : 'No product specified' }}
 </span>
 @if($product)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900 ml-2">ID: #{{ $product->id }}</span>
 @endif
 </div>
 </div>
 <div class="col-span-12 md:col-span-6">
 <div class="detail-item mb-3">
 <span class="detail-label text-slate-500">
 <i class="fa-regular fa-user mr-1"></i> Customer
 </span>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200 ml-2">{{ $otherUser?->name ?? 'Unknown' }}</span>
 </div>
 <div class="detail-item mb-3">
 <span class="detail-label text-slate-500">
 <i class="fa-regular fa-calendar mr-1"></i> Started
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
 .message-row {
 width: 100%;
 }
 .message-shell {
 max-width: 100%;
 }
 .message-shell.flex-row-reverse .bubble-wrap {
 display: flex;
 justify-content: flex-end;
 }
 .bubble-wrap {
 flex: 0 1 auto;
 max-width: 82%;
 min-width: 0;
 }
 .message-bubble-compact {
 display: inline-flex;
 flex-direction: column;
 max-width: 100%;
 min-width: 0;
 padding: 0.75rem 1rem;
 border-radius: 1rem;
 position: relative;
 }
 .message-bubble-compact.has-shared-listings {
 display: flex;
 width: 100%;
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
 overflow-wrap: anywhere;
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
 .conversation-container {
 padding: 0.75rem;
 }
 .bubble-wrap {
 max-width: calc(100% - 2.5rem);
 }
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
 const replyForm = document.getElementById('replyForm');
 const attachmentInput = document.getElementById('attachment');
 const listingModal = document.getElementById('listingShareModal');
 const openListingButtons = [
 document.getElementById('openListingShareModal'),
 document.getElementById('openListingShareModalInline'),
 ].filter(Boolean);
 const closeListingButtons = document.querySelectorAll('[data-close-listing-share]');
 const submitListingShare = document.getElementById('submitListingShare');
 const listingSearch = document.getElementById('listingShareSearch');
 const clearListingSearch = document.getElementById('clearListingShareSearch');
 const listingOptions = Array.from(document.querySelectorAll('[data-listing-option]'));
 const listingCheckboxes = Array.from(document.querySelectorAll('.listing-share-checkbox'));
 const selectedListingCount = document.getElementById('selectedListingCount');
 const selectedListingState = document.getElementById('selectedListingState');
 const selectedListingEmpty = document.getElementById('selectedListingEmpty');
 const selectedListingPreview = document.getElementById('selectedListingPreview');
 const modalSelectedCount = document.getElementById('modalSelectedCount');
 const listingShareResultsMeta = document.getElementById('listingShareResultsMeta');
 const listingShareNoResults = document.getElementById('listingShareNoResults');
 
 // Character counter
 const updateCharCount = () => {
 if (!textarea || !charCount) return;
 const length = textarea.value.length;
 charCount.textContent = `${length}/2000`;

 if (length > 1900) {
 charCount.style.color = '#dc3545';
 } else if (length > 1500) {
 charCount.style.color = '#ffc107';
 } else {
 charCount.style.color = '#6c757d';
 }
 };

 if (textarea) {
 textarea.addEventListener('input', function() {
 updateCharCount();
 this.style.height = 'auto';
 this.style.height = Math.min(this.scrollHeight, 200) + 'px';
 });
 }
 
 // Send on Enter (but allow Shift+Enter for new line)
 if (textarea && sendButton) {
 textarea.addEventListener('keydown', function(e) {
 if (e.key === 'Enter' && !e.shiftKey) {
 e.preventDefault();
 sendButton.click();
 }
 });
 }
 
 // Scroll to bottom of conversation
 const container = document.getElementById('conversationContainer');
 if (container) {
 container.scrollTop = container.scrollHeight;
 }

 const updateSelectedListingsUI = () => {
 if (!listingCheckboxes.length) return;

 const selected = listingCheckboxes.filter(checkbox => checkbox.checked);

 if (selectedListingCount) {
 selectedListingCount.textContent = selected.length;
 }

 if (modalSelectedCount) {
 modalSelectedCount.textContent = selected.length;
 }

 if (submitListingShare) {
 submitListingShare.disabled = selected.length === 0;
 }

 if (selectedListingPreview) {
 selectedListingPreview.innerHTML = '';
 selected.forEach(checkbox => {
 const chip = document.createElement('span');
 chip.className = 'inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700';
 chip.textContent = checkbox.dataset.listingName || 'Listing';
 selectedListingPreview.appendChild(chip);
 });
 }

 if (selectedListingState && selectedListingEmpty) {
 selectedListingState.classList.toggle('hidden', selected.length === 0);
 selectedListingEmpty.classList.toggle('hidden', selected.length > 0);
 }
 };

 const updateListingResults = () => {
 if (!listingOptions.length || !listingSearch) return;

 const query = listingSearch.value.trim().toLowerCase();
 let visibleCount = 0;

 listingOptions.forEach(option => {
 const haystack = `${option.dataset.name || ''} ${option.dataset.sku || ''}`;
 const matches = haystack.includes(query);
 option.classList.toggle('hidden', !matches);
 if (matches) {
 visibleCount += 1;
 }
 });

 if (clearListingSearch) {
 clearListingSearch.classList.toggle('hidden', query.length === 0);
 }

 if (listingShareResultsMeta) {
 listingShareResultsMeta.textContent = `${visibleCount} listing${visibleCount === 1 ? '' : 's'} available`;
 }

 if (listingShareNoResults) {
 listingShareNoResults.classList.toggle('hidden', visibleCount !== 0);
 }
 };

 const openListingModal = () => {
 if (!listingModal) return;
 listingModal.classList.remove('hidden');
 document.body.style.overflow = 'hidden';
 if (listingSearch) {
 listingSearch.focus();
 listingSearch.select();
 }
 };

 const closeListingModal = () => {
 if (!listingModal) return;
 listingModal.classList.add('hidden');
 document.body.style.overflow = '';
 };

 openListingButtons.forEach(button => {
 button.addEventListener('click', openListingModal);
 });

 closeListingButtons.forEach(button => {
 button.addEventListener('click', closeListingModal);
 });

 if (listingSearch) {
 listingSearch.addEventListener('input', updateListingResults);
 }

 if (clearListingSearch) {
 clearListingSearch.addEventListener('click', function() {
 if (!listingSearch) return;
 listingSearch.value = '';
 updateListingResults();
 listingSearch.focus();
 });
 }

 listingCheckboxes.forEach(checkbox => {
 checkbox.addEventListener('change', updateSelectedListingsUI);
 });

 if (submitListingShare && replyForm) {
 submitListingShare.addEventListener('click', function() {
 if (listingCheckboxes.every(checkbox => !checkbox.checked)) {
 return;
 }
 replyForm.submit();
 });
 }

 document.addEventListener('keydown', function(e) {
 if (e.key === 'Escape') {
 closeListingModal();
 }
 });

 updateCharCount();
 updateSelectedListingsUI();
 updateListingResults();
});

function clearForm() {
 const messageField = document.getElementById('message');
 const charCountField = document.getElementById('charCount');
 const attachmentField = document.getElementById('attachment');
 const listingSearch = document.getElementById('listingShareSearch');
 const listingCheckboxes = document.querySelectorAll('.listing-share-checkbox');
 messageField.value = '';
 charCountField.textContent = '0/2000';
 charCountField.style.color = '#6c757d';
 messageField.style.height = 'auto';
 if (attachmentField) {
 attachmentField.value = '';
 }
 listingCheckboxes.forEach(checkbox => {
 checkbox.checked = false;
 checkbox.dispatchEvent(new Event('change'));
 });
 if (listingSearch) {
 listingSearch.value = '';
 listingSearch.dispatchEvent(new Event('input'));
 }
}
</script>
@endpush
 </div>
 </div>
 </div>
</section>
@endsection 


