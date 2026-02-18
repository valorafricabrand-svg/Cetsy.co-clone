@extends('theme.'.theme().'.layouts.app')
@section('title', 'Offer Details')

@section('main')
<div class="content">
<div class="container-xxl">
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-base font-semibold mb-0">Offer Details</h2>
        <div class="flex gap-2">
            <a href="{{ route('buyer.offers') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs">
                <i class="bi bi-arrow-left mr-1"></i>Back to Offers
            </a>
            @if(($offer->product->shop->user_id ?? null))
                <a href="{{ route('buyer.messages.show', $offer->product_id . '-' . $offer->product->shop->user_id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs">
                    <i class="bi bi-chat-dots mr-1"></i>Message Seller
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
        <div class="p-4 sm:p-5">
<div class="grid grid-cols-12 gap-4">
    <div class="md:col-span-6">
        <h6 class="mb-3">Product Information</h6>
        <div class="flex items-center mb-3">
            @if($offer->product->media && $offer->product->media->count() > 0)
                @php
                    $thumb = function_exists('product_thumb_url')
                        ? product_thumb_url($offer->product)
                        : (optional($offer->product->media->first())->url
                            ? asset('storage/'.$offer->product->media->first()->url)
                            : null);
                @endphp
                <img src="{{ $thumb }}" 
                     alt="{{ $offer->product->name }}" 
                     class="rounded mr-3" style="width: 80px; height: 80px; object-fit: cover;">
            @else
                <div class="bg-slate-100 rounded flex items-center justify-center mr-3" 
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-image text-slate-500"></i>
                </div>
            @endif
            <div>
                <h6 class="mb-1">{{ $offer->product->name }}</h6>
                <p class="text-slate-500 mb-1">{{ $offer->product->shop->name ?? 'Unknown Shop' }}</p>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary">{{ get_currency() }} {{ number_format($offer->product->price, 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="md:col-span-6">
        <h6 class="mb-3">Offer Details</h6>
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-6">
                <small class="text-slate-500">Your Offer</small>
                <div class="font-bold text-emerald-600">{{ $offer->formatted_price }}</div>
            </div>
            <div class="col-span-6">
                <small class="text-slate-500">Savings</small>
                <div class="font-bold text-emerald-600">{{ get_currency() }} {{ number_format($offer->product->price - $offer->offer_price, 2) }}</div>
            </div>
        </div>
        <div class="grid grid-cols-12 gap-4 mt-2">
            <div class="col-span-6">
                <small class="text-slate-500">Status</small>
                <div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $offer->status_badge_class }}">{{ $offer->status_label }}</span>
                </div>
            </div>
            <div class="col-span-6">
                <small class="text-slate-500">Date</small>
                <div class="font-bold">{{ $offer->created_at->format('M d, Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>

@if($offer->buyer_notes)
<div class="mt-3">
    <h6 class="mb-2">Your Notes</h6>
    <div class="bg-slate-100 p-3 rounded">
        {{ $offer->buyer_notes }}
    </div>
</div>
@endif

@if($offer->seller_notes)
<div class="mt-3">
    <h6 class="mb-2">Seller Notes</h6>
    <div class="bg-slate-100 p-3 rounded">
        {{ $offer->seller_notes }}
    </div>
</div>
@endif

@if($offer->counterOffers->count() > 0)
<div class="mt-3">
    <h6 class="mb-2">Counter Offers</h6>
    @foreach($offer->counterOffers as $counterOffer)
        <div class="border rounded p-3 mb-2">
            <div class="flex justify-between items-start">
                <div>
                    <strong class="text-sky-600">Counter Offer</strong>
                    <div class="font-bold">{{ $counterOffer->formatted_price }}</div>
                    @if($counterOffer->seller_notes)
                        <div class="text-slate-500 text-xs mt-1">{{ $counterOffer->seller_notes }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $counterOffer->status_badge_class }}">{{ $counterOffer->status_label }}</span>
                    <div class="text-slate-500 text-xs">{{ $counterOffer->created_at->format('M d, H:i') }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif 

@php
    $hasCounterOffers = $offer->counterOffers && $offer->counterOffers->count() > 0;
    $canRespond = $offer->status === 'pending' && $hasCounterOffers;
@endphp

<div class="mt-4">
    <h6 class="mb-2">Actions</h6>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('listing.show', $offer->product->slug ?? $offer->product->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs">
            <i class="bi bi-eye mr-1"></i>View Product
        </a>

        @if($canRespond)
        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-1.5 text-xs" data-bs-toggle="collapse" data-bs-target="#respondSection">
            <i class="bi bi-check-circle mr-1"></i>Respond to Counter Offer
        </button>
        @endif

        @if(($offer->status === 'accepted') && $offer->order)
            <a href="{{ route('pay_now', $offer->order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-1.5 text-xs">
                <i class="bi bi-credit-card mr-1"></i>Pay Now
            </a>
            <a href="{{ route('buyer.orders.show', $offer->order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition btn-outline-info px-3 py-1.5 text-xs">
                <i class="bi bi-receipt mr-1"></i>View Order
            </a>
        @endif
    </div>
</div>

@if($canRespond)
<div id="respondSection" class="collapse mt-3">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4 sm:p-5">
            <form method="POST" action="{{ route('buyer.offers.respond', ['offerId' => $offer->id]) }}">
                @csrf
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Your Response</label>
                    <select name="response" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required onchange="document.getElementById('counterPriceWrap').style.display = (this.value==='counter') ? 'block' : 'none';">
                        <option value="">Choose response...</option>
                        <option value="accept">Accept Counter Offer</option>
                        <option value="decline">Decline Counter Offer</option>
                        <option value="counter">Make New Counter Offer</option>
                    </select>
                </div>
                <div class="mb-3" id="counterPriceWrap" style="display:none;">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Your Counter Offer Price</label>
                    <input type="number" name="counter_price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Message (Optional)</label>
                    <textarea name="message" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Add a message to your response..."></textarea>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Submit Response</button>
            </form>
        </div>
    </div>
</div>
@endif

        </div>
    </div>
</div>
</div>
@endsection




