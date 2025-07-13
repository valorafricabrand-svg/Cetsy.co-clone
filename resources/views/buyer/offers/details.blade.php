<div class="row">
    <div class="col-md-6">
        <h6 class="mb-3">Product Information</h6>
        <div class="d-flex align-items-center mb-3">
            @if($offer->product->media && $offer->product->media->count() > 0)
                <img src="{{ $offer->product->media->first()->getUrl() }}" 
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