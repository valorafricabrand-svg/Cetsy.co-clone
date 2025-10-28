@extends('layouts.app')
@section('title', 'Offer Details')

@section('content')
<div class="content">
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Offer Details</h2>
                <p class="text-muted mb-0">Manage and respond to this offer from {{ $offer->buyer->name ?? 'Buyer' }}</p>
            </div>
            <div class="d-flex gap-2">
                @if($offer && $offer->buyer)
                    <a href="{{ route('seller.messages.show', $offer->product_id . '-' . $offer->buyer_id) }}" class="btn btn-outline-success">
                        <i class="bi bi-chat-dots me-1"></i> Message Buyer
                    </a>
                @endif
                <a href="{{ route('seller.offers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Offers
                </a>
                @if($offer->is_negotiable)
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form method="POST" action="{{ route('seller.offers.accept', $offer->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item" onclick="return confirm('Accept this offer?')">
                                        <i class="bi bi-check-circle text-success me-2"></i>Accept Offer
                                    </button>
                                </form>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#declineModal">
                                    <i class="bi bi-x-circle text-danger me-2"></i>Decline Offer
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#counterModal">
                                    <i class="bi bi-arrow-left-right text-warning me-2"></i>Make Counter Offer
                                </button>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <div class="row">
            {{-- Main Offer Details --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Offer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Offer ID</dt>
                                    <dd class="col-sm-8">{{ $offer->id }}</dd>

                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge {{ $offer->status_badge_class }}">{{ $offer->status_label }}</span>
                                    </dd>

                                    <dt class="col-sm-4">Offer Price</dt>
                                    <dd class="col-sm-8 fw-bold text-success">{{ $offer->formatted_price }}</dd>

                                    <dt class="col-sm-4">Date</dt>
                                    <dd class="col-sm-8">{{ $offer->created_at ? $offer->created_at->format('d M Y, H:i') : '-' }}</dd>

                                    @if($offer->is_counter_offer)
                                        <dt class="col-sm-4">Counter Offer</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-info">Yes</span>
                                            @if($offer->originalOffer)
                                                <br><small class="text-muted">Original: {{ shop_currency() }} {{ number_format($offer->originalOffer->offer_price, 2) }}</small>
                                            @endif
                                        </dd>
                                    @endif
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Product</dt>
                                    <dd class="col-sm-8">
                                        <div class="d-flex align-items-center gap-2">
                                            @php $thumb = function_exists('product_thumb_url') ? product_thumb_url($offer->product) : (optional($offer->product->media->first())->url ? asset('storage/' . $offer->product->media->first()->url) : null); @endphp
                                            @if($thumb)
                                                <img src="{{ $thumb }}" 
                                                     alt="{{ $offer->product->name }}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                            @else
                                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                     style="width:40px;height:40px;">
                                                    <i class="bi bi-box text-secondary"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <span class="fw-semibold">{{ $offer->product->name ?? '-' }}</span><br>
                                                <small class="text-muted">#{{ $offer->product_id }}</small>
                                            </div>
                                        </div>
                                    </dd>

                                    <dt class="col-sm-4">Buyer</dt>
                                    <dd class="col-sm-8">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width:40px;height:40px;">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold">{{ $offer->buyer->name ?? '-' }}</span><br>
                                                <small class="text-muted">{{ $offer->buyer->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </dd>

                                    @if($offer->seller_notes)
                                        <dt class="col-sm-4">Seller Notes</dt>
                                        <dd class="col-sm-8">{{ $offer->seller_notes }}</dd>
                                    @endif

                                    @if($offer->buyer_notes)
                                        <dt class="col-sm-4">Buyer Notes</dt>
                                        <dd class="col-sm-8">{{ $offer->buyer_notes }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Offer History --}}
                @if($offerHistory->count() > 1)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Offer History</h5>
                        </div>
                        <div class="card-body">
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
                                        <div class="timeline-marker {{ isset($historyOffer->id) && $historyOffer->id === $offer->id ? 'bg-primary' : 'bg-secondary' }}"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        {{ isset($historyOffer->is_original) && $historyOffer->is_original ? 'Original Offer' : (isset($historyOffer->is_counter_offer) && $historyOffer->is_counter_offer ? 'Counter Offer' : 'Original Offer') }}
                                                        @if(isset($historyOffer->id) && $historyOffer->id === $offer->id)
                                                            <span class="badge bg-primary ms-2">Current</span>
                                                        @endif
                                                    </h6>
                                                    <p class="mb-1">
                                                        <strong>{{ isset($historyOffer->formatted_price) ? $historyOffer->formatted_price : (shop_currency() . ' ' . number_format($historyOffer->offer_price, 2)) }}</strong>
                                                        @if(isset($historyOffer->is_counter_offer) && $historyOffer->is_counter_offer && isset($historyOffer->originalOffer) && $historyOffer->originalOffer)
                                                            @php
                                                                $diff = $historyOffer->getPriceDifference();
                                                                $diffPercent = $historyOffer->getPriceDifferencePercentage();
                                                            @endphp
                                                            <span class="small {{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                                                                ({{ $diff > 0 ? '+' : '' }}{{ shop_currency() }} {{ number_format(abs($diff), 2) }}, 
                                                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%)
                                                            </span>
                                                        @endif
                                                    </p>
                                                    <small class="text-muted">{{ $historyOffer->created_at->format('d M Y, H:i') }}</small>
                                                </div>
                                                <span class="badge {{ isset($historyOffer->status_badge_class) ? $historyOffer->status_badge_class : 'bg-secondary' }}">{{ isset($historyOffer->status_label) ? $historyOffer->status_label : $historyOffer->status }}</span>
                                            </div>
                                            @if(isset($historyOffer->seller_notes) && $historyOffer->seller_notes)
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <small class="text-muted">Seller Notes:</small><br>
                                                    <small>{{ $historyOffer->seller_notes }}</small>
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
            <div class="col-lg-4">
                {{-- Quick Actions --}}
                @if($offer->is_negotiable)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <form method="POST" action="{{ route('seller.offers.accept', $offer->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Accept this offer?')">
                                        <i class="bi bi-check-circle me-2"></i>Accept Offer
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#declineModal">
                                    <i class="bi bi-x-circle me-2"></i>Decline Offer
                                </button>
                                
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#counterModal">
                                    <i class="bi bi-arrow-left-right me-2"></i>Make Counter Offer
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Product Details --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Product Details</h6>
                    </div>
                    <div class="card-body">
                        @if($offer->product)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                @php $thumb2 = function_exists('product_thumb_url') ? product_thumb_url($offer->product) : (optional($offer->product->media->first())->url ? asset('storage/' . $offer->product->media->first()->url) : null); @endphp
                                @if($thumb2)
                                    <img src="{{ $thumb2 }}" 
                                         alt="{{ $offer->product->name }}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                @else
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                         style="width:60px;height:60px;">
                                        <i class="bi bi-box text-secondary"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $offer->product->name }}</h6>
                                    <small class="text-muted">#{{ $offer->product_id }}</small>
                                </div>
                            </div>
                            
                            <dl class="row mb-0">
                                <dt class="col-6">Listed Price</dt>
                                <dd class="col-6">{{ shop_currency() }} {{ number_format($offer->product->price ?? 0, 2) }}</dd>
                                
                                <dt class="col-6">Offer Price</dt>
                                <dd class="col-6 fw-bold text-success">{{ $offer->formatted_price }}</dd>
                                
                                @if($offer->product->price)
                                    @php
                                        $discount = (($offer->product->price - $offer->offer_price) / $offer->product->price) * 100;
                                    @endphp
                                    <dt class="col-6">Discount</dt>
                                    <dd class="col-6 {{ $discount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $discount > 0 ? '-' : '+' }}{{ number_format(abs($discount), 1) }}%
                                    </dd>
                                @endif
                            </dl>
                        @else
                            <p class="text-muted mb-0">Product not found</p>
                        @endif
                    </div>
                </div>

                {{-- Buyer Information --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Buyer Information</h6>
                    </div>
                    <div class="card-body">
                        @if($offer->buyer)
                            <div class="d-flex align-items-center gap-3 mb-3">
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
                                         class="rounded-circle" style="width:50px;height:50px;object-fit:cover;">
                                @else
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width:50px;height:50px;">
                                        <i class="bi bi-person text-primary fs-4"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $offer->buyer->name }}</h6>
                                    <small class="text-muted">{{ $offer->buyer->email }}</small>
                                </div>
                            </div>
                            
                            <dl class="row mb-0">
                                <dt class="col-6">Member Since</dt>
                                <dd class="col-6">{{ $offer->buyer->created_at ? $offer->buyer->created_at->format('M Y') : '-' }}</dd>
                                
                                <dt class="col-6">Location</dt>
                                <dd class="col-6">{{ $offer->buyer->address ?? '-' }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">Buyer information not available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Decline Modal --}}
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decline Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('seller.offers.decline', $offer->id) }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to decline this offer from <strong>{{ $offer->buyer->name ?? 'Buyer' }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide a reason for declining..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Decline Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Counter Offer Modal --}}
<div class="modal fade" id="counterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Counter Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('seller.offers.counter', $offer->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Original Offer:</strong> {{ $offer->formatted_price }}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Counter Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ shop_currency() }}</span>
                            <input type="number" name="counter_price" class="form-control" step="0.01" min="0.01" 
                                   value="{{ $offer->offer_price }}" required>
                        </div>
                        <div class="form-text">Enter your counter offer price</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" 
                                  placeholder="Add a message to your counter offer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send Counter Offer</button>
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
@endsection 
