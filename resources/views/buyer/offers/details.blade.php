@extends('layouts.app')
@section('title', 'Offer Details')

@section('content')
<div class="content">
<div class="container-xxl">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Offer Details</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('buyer.offers') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Offers
            </a>
            @if(($offer->product->shop->user_id ?? null))
                <a href="{{ route('buyer.messages.show', $offer->product_id . '-' . $offer->product->shop->user_id) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-chat-dots me-1"></i>Message Seller
                </a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
<div class="row">
    <div class="col-md-6">
        <h6 class="mb-3">Product Information</h6>
        <div class="d-flex align-items-center mb-3">
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
                     class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
            @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-image text-muted"></i>
                </div>
            @endif
            <div>
                <h6 class="mb-1">{{ $offer->product->name }}</h6>
                <p class="text-muted mb-1">{{ $offer->product->shop->name ?? 'Unknown Shop' }}</p>
                <span class="badge bg-primary">{{ get_currency() }} {{ number_format($offer->product->price, 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="mb-3">Offer Details</h6>
        <div class="row">
            <div class="col-6">
                <small class="text-muted">Your Offer</small>
                <div class="fw-bold text-success">{{ $offer->formatted_price }}</div>
            </div>
            <div class="col-6">
                <small class="text-muted">Savings</small>
                <div class="fw-bold text-success">{{ get_currency() }} {{ number_format($offer->product->price - $offer->offer_price, 2) }}</div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-6">
                <small class="text-muted">Status</small>
                <div>
                    <span class="badge {{ $offer->status_badge_class }}">{{ $offer->status_label }}</span>
                </div>
            </div>
            <div class="col-6">
                <small class="text-muted">Date</small>
                <div class="fw-bold">{{ $offer->created_at->format('M d, Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>

@if($offer->buyer_notes)
<div class="mt-3">
    <h6 class="mb-2">Your Notes</h6>
    <div class="bg-light p-3 rounded">
        {{ $offer->buyer_notes }}
    </div>
</div>
@endif

@if($offer->seller_notes)
<div class="mt-3">
    <h6 class="mb-2">Seller Notes</h6>
    <div class="bg-light p-3 rounded">
        {{ $offer->seller_notes }}
    </div>
</div>
@endif

@if($offer->counterOffers->count() > 0)
<div class="mt-3">
    <h6 class="mb-2">Counter Offers</h6>
    @foreach($offer->counterOffers as $counterOffer)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong class="text-info">Counter Offer</strong>
                    <div class="fw-bold">{{ $counterOffer->formatted_price }}</div>
                    @if($counterOffer->seller_notes)
                        <div class="text-muted small mt-1">{{ $counterOffer->seller_notes }}</div>
                    @endif
                </div>
                <div class="text-end">
                    <span class="badge {{ $counterOffer->status_badge_class }}">{{ $counterOffer->status_label }}</span>
                    <div class="text-muted small">{{ $counterOffer->created_at->format('M d, H:i') }}</div>
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
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('listing.show', $offer->product->slug ?? $offer->product->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye me-1"></i>View Product
        </a>

        @if($canRespond)
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#respondSection">
            <i class="bi bi-check-circle me-1"></i>Respond to Counter Offer
        </button>
        @endif

        @if(($offer->status === 'accepted') && $offer->order)
            <a href="{{ route('pay_now', $offer->order->id) }}" class="btn btn-success btn-sm">
                <i class="bi bi-credit-card me-1"></i>Pay Now
            </a>
            <a href="{{ route('buyer.orders.show', $offer->order->id) }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-receipt me-1"></i>View Order
            </a>
        @endif
    </div>
</div>

@if($canRespond)
<div id="respondSection" class="collapse mt-3">
    <div class="card border">
        <div class="card-body">
            <form method="POST" action="{{ route('buyer.offers.respond', ['offerId' => $offer->id]) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Your Response</label>
                    <select name="response" class="form-select" required onchange="document.getElementById('counterPriceWrap').style.display = (this.value==='counter') ? 'block' : 'none';">
                        <option value="">Choose response...</option>
                        <option value="accept">Accept Counter Offer</option>
                        <option value="decline">Decline Counter Offer</option>
                        <option value="counter">Make New Counter Offer</option>
                    </select>
                </div>
                <div class="mb-3" id="counterPriceWrap" style="display:none;">
                    <label class="form-label">Your Counter Offer Price</label>
                    <input type="number" name="counter_price" class="form-control" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Message (Optional)</label>
                    <textarea name="message" class="form-control" rows="3" placeholder="Add a message to your response..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Response</button>
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
