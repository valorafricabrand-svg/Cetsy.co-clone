@extends('theme.'.theme().'.layouts.app')
@section('title', 'Offer Details')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
 <div class="min-w-0">
 <h2 class="mb-1">Offer Details</h2>
 <p class="text-slate-500 mb-0">Manage and respond to this offer from {{ $offer->buyer->name ?? 'Buyer' }}</p>
 </div>
 <div class="flex flex-wrap gap-2">
 @if($offer && $offer->buyer)
 <a href="{{ route('seller.messages.show', $offer->product_id . '-' . $offer->buyer_id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
 <i class="fa-regular fa-comments mr-1"></i> Message Buyer
 </a>
 @endif
 <a href="{{ route('seller.offers.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">
 <i class="fa-solid fa-arrow-left mr-1"></i> Back to Offers
 </a>
 @if($offer->is_negotiable)
 <div class="relative inline-block">
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700" type="button" data-ui-toggle="dropdown">
 <i class="fa-solid fa-gear mr-1"></i> Actions
 </button>
 <ul class="tw-dropdown-menu">
 <li>
 <form method="POST" action="{{ route('seller.offers.accept', $offer->id) }}" class="inline">
 @csrf
 <button type="submit" class="tw-dropdown-item" onclick="return confirm('Accept this offer?')">
 <i class="fa-regular fa-circle-check text-emerald-600 mr-2"></i>Accept Offer
 </button>
 </form>
 </li>
 <li>
 <button type="button" class="tw-dropdown-item" data-ui-toggle="modal" data-target="#declineModal">
 <i class="fa-solid fa-xmark-circle text-rose-600 mr-2"></i>Decline Offer
 </button>
 </li>
 <li>
 <button type="button" class="tw-dropdown-item" data-ui-toggle="modal" data-target="#counterModal">
 <i class="fa-solid fa-right-left text-amber-600 mr-2"></i>Make Counter Offer
 </button>
 </li>
 </ul>
 </div>
 @endif
 </div>
 </div>

 @if(session('success'))
 <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
 @endif
 @if(session('warning'))
 <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">{{ session('warning') }}</div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
 {{-- Main Offer Details --}}
 <div class="col-span-12 lg:col-span-8">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Offer Information</h5>
 </div>
 <div class="p-4">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
 <div class="col-span-12 md:col-span-6">
 <dl class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-0">
 <dt class="col-span-12 md:col-span-4">Offer ID</dt>
 <dd class="col-span-12 md:col-span-8">{{ $offer->id }}</dd>

 <dt class="col-span-12 md:col-span-4">Status</dt>
 <dd class="col-span-12 md:col-span-8">
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $offer->status_badge_class }}">{{ $offer->status_label }}</span>
 </dd>

 <dt class="col-span-12 md:col-span-4">Offer Price</dt>
 <dd class="col-span-12 md:col-span-8 font-bold text-emerald-600">{{ $offer->formatted_price }}</dd>

 <dt class="col-span-12 md:col-span-4">Date</dt>
 <dd class="col-span-12 md:col-span-8">{{ $offer->created_at ? $offer->created_at->format('d M Y, H:i') : '-' }}</dd>

 @if($offer->is_counter_offer)
 <dt class="col-span-12 md:col-span-4">Counter Offer</dt>
 <dd class="col-span-12 md:col-span-8">
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200">Yes</span>
 @if($offer->originalOffer)
 <br><span class="text-slate-500 text-xs">Original: {{ shop_currency() }} {{ number_format($offer->originalOffer->offer_price, 2) }}</span>
 @endif
 </dd>
 @endif
 </dl>
 </div>
 <div class="col-span-12 md:col-span-6">
 <dl class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-0">
 <dt class="col-span-12 md:col-span-4">Product</dt>
 <dd class="col-span-12 md:col-span-8">
 <div class="flex min-w-0 items-center gap-2">
 @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($offer->product) : (optional($offer->product->media->first())->url ? asset('storage/' . $offer->product->media->first()->url) : null); @endphp
 @if($thumb)
 <img src="{{ $thumb }}" 
 alt="{{ $offer->product->name }}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
 @else
 <div class="bg-slate-50 border rounded flex items-center justify-center" 
 style="width:40px;height:40px;">
 <i class="fa-solid fa-box text-slate-500"></i>
 </div>
 @endif
 <div class="min-w-0">
 <span class="font-semibold break-words">{{ $offer->product->name ?? '-' }}</span><br>
 <span class="text-slate-500 text-xs">#{{ $offer->product_id }}</span>
 </div>
 </div>
 </dd>

 <dt class="col-span-12 md:col-span-4">Buyer</dt>
 <dd class="col-span-12 md:col-span-8">
 <div class="flex min-w-0 items-center gap-2">
 <div class="bg-emerald-600 text-white border-emerald-600 bg-opacity-10 rounded-full flex items-center justify-center" 
 style="width:40px;height:40px;">
 <i class="fa-regular fa-user text-emerald-600"></i>
 </div>
 <div class="min-w-0">
 <span class="font-semibold break-words">{{ $offer->buyer->name ?? '-' }}</span><br>
 <span class="text-slate-500 text-xs break-all">{{ $offer->buyer->email ?? '' }}</span>
 </div>
 </div>
 </dd>

 @if($offer->seller_notes)
 <dt class="col-span-12 md:col-span-4">Seller Notes</dt>
 <dd class="col-span-12 md:col-span-8 break-words">{{ $offer->seller_notes }}</dd>
 @endif

 @if($offer->buyer_notes)
 <dt class="col-span-12 md:col-span-4">Buyer Notes</dt>
 <dd class="col-span-12 md:col-span-8 break-words">{{ $offer->buyer_notes }}</dd>
 @endif
 </dl>
 </div>
 </div>
 </div>
 </div>

 {{-- Offer History --}}
 @if($offerHistory->count() > 1)
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Offer History</h5>
 </div>
 <div class="p-4">
 <div class="timeline">
 @php
 $lastDisplayedPrice = null;
 @endphp
 @foreach($offerHistory as $index => $historyOffer)
 @php
 // Get the current price for comparison
 $currentPrice = is_object($historyOffer) && isset($historyOffer->offer_price) 
 ? (float) $historyOffer->offer_price 
 : null;
 
 // Skip if this price is the same as the last displayed price
 if ($currentPrice !== null && $lastDisplayedPrice !== null && $currentPrice === $lastDisplayedPrice) {
 continue;
 }
 
 // Update last displayed price
 $lastDisplayedPrice = $currentPrice;
 @endphp
 <div class="timeline-item {{ isset($historyOffer->id) && $historyOffer->id === $offer->id ? 'active' : '' }}">
 <div class="timeline-marker {{ isset($historyOffer->id) && $historyOffer->id === $offer->id ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-200 text-slate-700 border-slate-300' }}"></div>
 <div class="timeline-content">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
 <div class="min-w-0">
 <h6 class="mb-1">
 {{ isset($historyOffer->is_original) && $historyOffer->is_original ? 'Original Offer' : (isset($historyOffer->is_counter_offer) && $historyOffer->is_counter_offer ? 'Counter Offer' : 'Original Offer') }}
 @if(isset($historyOffer->id) && $historyOffer->id === $offer->id)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600 ml-2">Current</span>
 @endif
 </h6>
 <p class="mb-1">
 <strong>{{ isset($historyOffer->formatted_price) ? $historyOffer->formatted_price : (shop_currency() . ' ' . number_format($historyOffer->offer_price, 2)) }}</strong>
 @if(isset($historyOffer->is_counter_offer) && $historyOffer->is_counter_offer && isset($historyOffer->originalOffer) && $historyOffer->originalOffer)
 @php
 $diff = $historyOffer->getPriceDifference();
 $diffPercent = $historyOffer->getPriceDifferencePercentage();
 @endphp
 <span class="text-xs {{ $diff > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
 ({{ $diff > 0 ? '+' : '' }}{{ shop_currency() }} {{ number_format(abs($diff), 2) }}, 
 {{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%)
 </span>
 @endif
 </p>
 <span class="text-slate-500 text-xs">{{ $historyOffer->created_at->format('d M Y, H:i') }}</span>
 </div>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ isset($historyOffer->status_badge_class) ? $historyOffer->status_badge_class : 'bg-slate-200 text-slate-700 border-slate-300' }}">{{ isset($historyOffer->status_label) ? $historyOffer->status_label : $historyOffer->status }}</span>
 </div>
 @if(isset($historyOffer->seller_notes) && $historyOffer->seller_notes)
 <div class="mt-2 p-2 bg-slate-50 rounded">
 <span class="text-slate-500 text-xs">Seller Notes:</span><br>
 <span class="text-xs">{{ $historyOffer->seller_notes }}</span>
 </div>
 @endif
 </div>
 </div>
 @endforeach
 </div>
 </div>
 </div>
 @endif
 </div>

 {{-- Sidebar --}}
 <div class="col-span-12 lg:col-span-4">
 {{-- Quick Actions --}}
 @if($offer->is_negotiable)
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h6 class="mb-0">Quick Actions</h6>
 </div>
 <div class="p-4">
 <div class="grid gap-2">
 <form method="POST" action="{{ route('seller.offers.accept', $offer->id) }}">
 @csrf
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 w-full" onclick="return confirm('Accept this offer?')">
 <i class="fa-regular fa-circle-check mr-2"></i>Accept Offer
 </button>
 </form>
 
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-rose-600 bg-rose-600 text-white hover:bg-rose-700 w-full" data-ui-toggle="modal" data-target="#declineModal">
 <i class="fa-solid fa-xmark-circle mr-2"></i>Decline Offer
 </button>
 
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400 w-full" data-ui-toggle="modal" data-target="#counterModal">
 <i class="fa-solid fa-right-left mr-2"></i>Make Counter Offer
 </button>
 </div>
 </div>
 </div>
 @endif

 {{-- Product Details --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h6 class="mb-0">Product Details</h6>
 </div>
 <div class="p-4">
 @if($offer->product)
 <div class="flex min-w-0 items-center gap-3 mb-3">
 @php $thumb2 = function_exists('product_thumb_url') ? product_thumb_url($offer->product) : (optional($offer->product->media->first())->url ? asset('storage/' . $offer->product->media->first()->url) : null); @endphp
 @if($thumb2)
 <img src="{{ $thumb2 }}" 
 alt="{{ $offer->product->name }}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
 @else
 <div class="bg-slate-50 border rounded flex items-center justify-center" 
 style="width:60px;height:60px;">
 <i class="fa-solid fa-box text-slate-500"></i>
 </div>
 @endif
 <div class="min-w-0">
 <h6 class="mb-1 break-words">{{ $offer->product->name }}</h6>
 <span class="text-slate-500 text-xs">#{{ $offer->product_id }}</span>
 </div>
 </div>
 
 <dl class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-0">
 <dt class="col-span-12 md:col-span-6">Listed Price</dt>
 <dd class="col-span-12 md:col-span-6">{{ shop_currency() }} {{ number_format($offer->product->price ?? 0, 2) }}</dd>
 
 <dt class="col-span-12 md:col-span-6">Offer Price</dt>
 <dd class="col-span-12 md:col-span-6 font-bold text-emerald-600">{{ $offer->formatted_price }}</dd>
 
 @if($offer->product->price && $offer->product->price > 0)
 @php
 $discount = (($offer->product->price - $offer->offer_price) / $offer->product->price) * 100;
 @endphp
 <dt class="col-span-12 md:col-span-6">Discount</dt>
 <dd class="col-span-12 md:col-span-6 {{ $discount > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
 {{ $discount > 0 ? '-' : '+' }}{{ number_format(abs($discount), 1) }}%
 </dd>
 @endif
 </dl>
 @else
 <p class="text-slate-500 mb-0">Product not found</p>
 @endif
 </div>
 </div>

 {{-- Buyer Information --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h6 class="mb-0">Buyer Information</h6>
 </div>
 <div class="p-4">
 @if($offer->buyer)
 <div class="flex min-w-0 items-center gap-3 mb-3">
 @php
 $buyerPhotoUrl = null;
 if (!empty($offer->buyer->photo)) {
 if ($offer->buyer->photo_storage === 's3') {
 try {
 $buyerPhotoUrl = \Illuminate\Support\Facades\Storage::disk('s3')->url($offer->buyer->photo);
 } catch (\Throwable $e) {
 $buyerPhotoUrl = null;
 }
 } else {
 // Default to public storage
 $buyerPhotoUrl = asset('storage/' . $offer->buyer->photo);
 }
 }
 @endphp

 @if($buyerPhotoUrl)
 <img src="{{ $buyerPhotoUrl }}" alt="{{ $offer->buyer->name }}" 
 class="rounded-full" style="width:50px;height:50px;object-fit:cover;">
 @else
 <div class="bg-emerald-600 text-white border-emerald-600 bg-opacity-10 rounded-full flex items-center justify-center" 
 style="width:50px;height:50px;">
 <i class="fa-regular fa-user text-emerald-600 text-xl"></i>
 </div>
 @endif
 <div class="min-w-0">
 <h6 class="mb-1 break-words">{{ $offer->buyer->name }}</h6>
 <span class="text-slate-500 text-xs break-all">{{ $offer->buyer->email }}</span>
 </div>
 </div>
 
 <dl class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-0">
 <dt class="col-span-12 md:col-span-6">Member Since</dt>
 <dd class="col-span-12 md:col-span-6">{{ $offer->buyer->created_at ? $offer->buyer->created_at->format('M Y') : '-' }}</dd>
 
 <dt class="col-span-12 md:col-span-6">Location</dt>
 <dd class="col-span-12 md:col-span-6 break-words">{{ $offer->buyer->address ?? '-' }}</dd>
 </dl>
 @else
 <p class="text-slate-500 mb-0">Buyer information not available</p>
 @endif
 </div>
 </div>
 </div>
 </div>
 </div>
</div>

{{-- Decline Modal --}}
<div class="tw-modal" id="declineModal" tabindex="-1">
 <div class="tw-modal-dialog">
 <div class="tw-modal-content">
 <div class="tw-modal-header">
 <h5 class="tw-modal-title">Decline Offer</h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>
 <form method="POST" action="{{ route('seller.offers.decline', $offer->id) }}">
 @csrf
 <div class="tw-modal-body">
 <p>Are you sure you want to decline this offer from <strong>{{ $offer->buyer->name ?? 'Buyer' }}</strong>?</p>
 <div class="mb-3">
 <label class="form-label">Reason (Optional)</label>
 <textarea name="reason" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" rows="3" placeholder="Provide a reason for declining..."></textarea>
 </div>
 </div>
 <div class="tw-modal-footer">
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-700 bg-slate-700 text-white hover:bg-slate-800" data-ui-dismiss="modal">Cancel</button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-rose-600 bg-rose-600 text-white hover:bg-rose-700">Decline Offer</button>
 </div>
 </form>
 </div>
 </div>
</div>

{{-- Counter Offer Modal --}}
<div class="tw-modal" id="counterModal" tabindex="-1">
 <div class="tw-modal-dialog">
 <div class="tw-modal-content">
 <div class="tw-modal-header">
 <h5 class="tw-modal-title">Make Counter Offer</h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>
 <form method="POST" action="{{ route('seller.offers.counter', $offer->id) }}">
 @csrf
 <div class="tw-modal-body">
 <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
 <i class="fa-regular fa-circle-info mr-2"></i>
 <strong>Original Offer:</strong> {{ $offer->formatted_price }}
 </div>
 
 <div class="mb-3">
 <label class="form-label">Counter Price <span class="text-rose-600">*</span></label>
 <div class="flex items-stretch gap-2">
 <span class="inline-flex items-center rounded-xl border border-slate-300 bg-slate-100 px-3 text-sm font-semibold text-slate-700">{{ shop_currency() }}</span>
 <input type="number" name="counter_price" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" step="0.01" min="0.01" 
 value="{{ $offer->offer_price }}" required>
 </div>
 <div class="form-text">Enter your counter offer price</div>
 </div>
 
 <div class="mb-3">
 <label class="form-label">Message (Optional)</label>
 <textarea name="message" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" rows="3" 
 placeholder="Add a message to your counter offer..."></textarea>
 </div>
 </div>
 <div class="tw-modal-footer">
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-700 bg-slate-700 text-white hover:bg-slate-800" data-ui-dismiss="modal">Cancel</button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400">Send Counter Offer</button>
 </div>
 </form>
 </div>
 </div>
</div>

@push('styles')
<style>
.timeline {
 position: relative;
 padding-left: 30px;
}

.timeline::before {
 content: '';
 position: absolute;
 left: 15px;
 top: 0;
 bottom: 0;
 width: 2px;
 background: #e9ecef;
}

.timeline-item {
 position: relative;
 margin-bottom: 20px;
}

.timeline-marker {
 position: absolute;
 left: -22px;
 top: 0;
 width: 12px;
 height: 12px;
 border-radius: 50%;
 border: 2px solid #fff;
 box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
 background: #f8f9fa;
 padding: 15px;
 border-radius: 8px;
 border-left: 3px solid #e9ecef;
}

.timeline-item.active .timeline-content {
 background: #fff;
 border-left-color: #0d6efd;
 box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
 border-radius: 10px;
}

.badge {
 font-size: 0.75rem;
}
</style>
@endpush
 </div>
 </div>
 </div>
</section>
@endsection 







