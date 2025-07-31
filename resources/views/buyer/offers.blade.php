@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Offers') }}
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock-history fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $offers->count() }}</h4>
                                <small>Total Products</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-hourglass-split fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $offers->sum('status_summary.pending') }}</h4>
                                <small>Pending Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $offers->sum('status_summary.accepted') }}</h4>
                                <small>Accepted Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-arrow-left-right fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">{{ $offers->where('has_counter_offers', true)->count() }}</h4>
                                <small>Counter Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make New Offer Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Make New Offer
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="text-muted mb-3">Make an offer on a product you're interested in. Browse products and submit your best offer!</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary" onclick="showNewOfferModal()">
                            <i class="bi bi-plus-circle me-1"></i>Make New Offer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($offers->isEmpty())
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No Offers Yet</h5>
                    <p class="text-muted">You haven't made any offers yet. Start browsing products to make your first offer!</p>
                    <a href="{{ route('listings') }}" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Browse Products
                    </a>
                </div>
            </div>
        @else
            @foreach($offers as $productId => $offerData)
                <div class="card shadow-sm border-0 mb-4 offer-card @if($offerData['latest_offer']->status === 'accepted') border-success border-3 accepted-offer-card @endif">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                @if($offerData['product']->media && $offerData['product']->media->count() > 0)
                                    <img src="{{ $offerData['product']->media->first()->getUrl() }}" 
                                         alt="{{ $offerData['product']->name }}" 
                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $offerData['product']->name }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-shop me-1"></i>{{ $offerData['product']->shop->name ?? 'Unknown Shop' }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $offerData['latest_offer']->status_badge_class }} fs-6">
                                    {{ $offerData['latest_offer']->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if($offerData['latest_offer']->status === 'accepted')
                            <div class="alert alert-success d-flex align-items-center mb-4 p-3 shadow-sm accepted-offer-alert">
                                <i class="bi bi-patch-check-fill fs-3 me-3 text-success"></i>
                                <div class="flex-grow-1">
                                    <strong class="text-success">Congratulations!</strong> Your offer was <b>accepted</b> by the seller.<br>
                                    <span class="small text-success">You can now proceed to checkout or contact the seller for next steps.</span>
                                    
                                    @if($offerData['latest_offer']->order)
                                        <div class="mt-3 p-3 bg-white rounded border">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-success">Order #{{ $offerData['latest_offer']->order->id }} Created</strong><br>
                                                    <small class="text-muted">Total: {{ get_currency() }} {{ number_format($offerData['latest_offer']->order->total_amount, 2) }}</small>
                                                </div>
                                                <a href="{{ route('pay_now', $offerData['latest_offer']->order->id) }}" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="bi bi-credit-card me-1"></i>Pay Now
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <!-- Latest Offer Summary -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-currency-dollar fs-4 text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Your Latest Offer</h6>
                                        <span class="fs-5 fw-bold text-success">{{ $offerData['latest_offer']->formatted_price }}</span>
                                        <div class="text-muted small">
                                            <i class="bi bi-clock me-1"></i>{{ $offerData['latest_offer']->time_ago }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-tag fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Original Price</h6>
                                        <span class="fs-5 fw-bold text-primary">{{ get_currency() }} {{ number_format($offerData['product']->price, 2) }}</span>
                                        <div class="text-muted small">
                                            Savings: {{ get_currency() }} {{ number_format($offerData['product']->price - $offerData['latest_offer']->offer_price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Offer History -->
                        @if($offerData['offer_history']->count() > 1)
                            <div class="mb-3">
                                <h6 class="mb-2">
                                    <i class="bi bi-clock-history me-1"></i>Offer History
                                </h6>
                                <div class="timeline">
                                    @foreach($offerData['offer_history'] as $index => $offer)
                                        <div class="timeline-item">
                                            <div class="timeline-marker {{ $offer->is_counter_offer ? 'bg-info' : 'bg-primary' }}"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong>
                                                            @if($offer->is_counter_offer)
                                                                <i class="bi bi-arrow-left-right text-info me-1"></i>Counter Offer
                                                            @else
                                                                <i class="bi bi-arrow-up text-primary me-1"></i>Your Offer
                                                            @endif
                                                        </strong>
                                                        <div class="text-muted small">{{ $offer->formatted_price }}</div>
                                                        @if($offer->buyer_notes)
                                                            <div class="text-muted small mt-1">{{ $offer->buyer_notes }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge {{ $offer->status_badge_class ?? 'bg-secondary' }} small">
                                                            {{ $offer->status_label ?? ucfirst($offer->status) }}
                                                        </span>
                                                        <div class="text-muted small">{{ $offer->created_at ? $offer->created_at->format('M d, H:i') : '' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('listing.show', $offerData['product']->slug ?? $offerData['product']->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>View Product
                            </a>
                            
                            @if($offerData['latest_offer']->status === 'pending')
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                        onclick="showOfferDetails({{ $offerData['latest_offer']->id }})">
                                    <i class="bi bi-info-circle me-1"></i>Details
                                </button>
                            @endif
                            
                            @if($offerData['has_counter_offers'] && $offerData['latest_offer']->status === 'pending')
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="respondToCounterOffer({{ $offerData['latest_offer']->id }})">
                                    <i class="bi bi-check-circle me-1"></i>Respond
                                </button>
                            @endif
                            
                            @if($offerData['latest_offer']->status === 'accepted' && $offerData['latest_offer']->order)
                                <a href="{{ route('pay_now', $offerData['latest_offer']->order->id) }}" 
                                   class="btn btn-success btn-sm">
                                    <i class="bi bi-credit-card me-1"></i>Pay Now
                                </a>
                                <a href="{{ route('buyer.orders.show', $offerData['latest_offer']->order->id) }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-receipt me-1"></i>View Order
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- New Offer Modal -->
<div class="modal fade" id="newOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make New Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newOfferForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Product</label>
                        <select name="product_id" class="form-select" required onchange="updateProductInfo()">
                            <option value="">Choose a product...</option>
                            <!-- Products will be loaded here -->
                        </select>
                    </div>
                    
                    <!-- Product Info Display -->
                    <div id="productInfo" style="display: none;" class="mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <img id="productImage" src="" alt="Product" class="img-fluid rounded" style="max-width: 100px;">
                                    </div>
                                    <div class="col-md-9">
                                        <h6 id="productName" class="mb-1"></h6>
                                        <p id="productPrice" class="text-muted mb-2"></p>
                                        <small class="text-muted">Original Price</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Your Offer Price</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ get_currency() }}</span>
                            <input type="number" name="offer_price" class="form-control" step="0.01" min="0" required onchange="calculateSavings()">
                        </div>
                        <div class="form-text">Enter your best offer price</div>
                        <div id="savingsInfo" class="mt-2" style="display: none;">
                            <small class="text-success">
                                <i class="bi bi-check-circle me-1"></i>
                                You'll save: <span id="savingsAmount"></span> (<span id="savingsPercentage"></span>)
                            </small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Add a message to your offer..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitNewOffer()">Submit Offer</button>
            </div>
        </div>
    </div>
</div>

<!-- Offer Details Modal -->
<div class="modal fade" id="offerDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Offer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="offerDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Counter Offer Response Modal -->
<div class="modal fade" id="counterOfferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respond to Counter Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="counterOfferForm" method="POST" action="{{ route('buyer.offers.respond', ['offerId' => 'OFFER_ID_PLACEHOLDER']) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Your Response</label>
                        <select name="response" class="form-select" required onchange="toggleCounterPriceField(this)">
                            <option value="">Choose response...</option>
                            <option value="accept">Accept Counter Offer</option>
                            <option value="decline">Decline Counter Offer</option>
                            <option value="counter">Make New Counter Offer</option>
                        </select>
                    </div>
                    <div class="mb-3" id="counterPriceField" style="display: none;">
                        <label class="form-label">Your Counter Offer Price</label>
                        <input type="number" name="counter_price" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Add a message to your response..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="counterOfferForm" class="btn btn-primary">Submit Response</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.offer-card {
    transition: box-shadow 0.2s;
}
.offer-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}
.accepted-offer-card {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40,167,69,.15) !important;
}
.accepted-offer-alert {
    background: linear-gradient(90deg, #e9fbe7 0%, #d4f5e9 100%);
    border: 1.5px solid #28a745;
    border-radius: 0.75rem;
    font-size: 1.05rem;
}
</style>
@endpush

@push('scripts')
<script>
function showNewOfferModal() {
    // Load available products for making offers
    fetch('/buyer/offers/available-products')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="product_id"]');
            select.innerHTML = '<option value="">Choose a product...</option>';
            
            // Store products globally for use in other functions
            window.availableProducts = data.products;
            
            data.products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} - ${product.currency} ${product.price}`;
                select.appendChild(option);
            });
            
            new bootstrap.Modal(document.getElementById('newOfferModal')).show();
        });
}

function updateProductInfo() {
    const productId = document.querySelector('select[name="product_id"]').value;
    if (productId) {
        // Find the selected product from the loaded products
        const selectedProduct = window.availableProducts.find(p => p.id == productId);
        if (selectedProduct) {
            document.getElementById('productInfo').style.display = 'block';
            document.getElementById('productImage').src = selectedProduct.image || '/path/to/default-image.jpg';
            document.getElementById('productName').textContent = selectedProduct.name;
            document.getElementById('productPrice').textContent = `${selectedProduct.currency} ${selectedProduct.price}`;
            
            // Store original price for savings calculation
            document.getElementById('productInfo').dataset.originalPrice = selectedProduct.price;
        }
    } else {
        document.getElementById('productInfo').style.display = 'none';
    }
}

function calculateSavings() {
    const originalPrice = parseFloat(document.getElementById('productInfo').dataset.originalPrice || 0);
    const offerPrice = parseFloat(document.querySelector('input[name="offer_price"]').val() || 0);

    if (originalPrice > 0 && offerPrice > 0) {
        const savings = originalPrice - offerPrice;
        const savingsPercentage = ((savings / originalPrice) * 100).toFixed(1);
        
        document.getElementById('savingsAmount').textContent = '{{ get_currency() }} ' + savings.toFixed(2);
        document.getElementById('savingsPercentage').textContent = savingsPercentage + '%';
        document.getElementById('savingsInfo').style.display = 'block';
    } else {
        document.getElementById('savingsInfo').style.display = 'none';
    }
}

function submitNewOffer() {
    const form = document.getElementById('newOfferForm');
    const formData = new FormData(form);
    const productId = formData.get('product_id');
    
    if (!productId) {
        alert('Please select a product');
        return;
    }
    
    fetch(`/buyer/offers/${productId}/create`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function showOfferDetails(offerId) {
    // Load offer details via AJAX
    fetch(`/buyer/offers/${offerId}/details`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('offerDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('offerDetailsModal')).show();
        });
}

function respondToCounterOffer(offerId) {
    const form = document.getElementById('counterOfferForm');
    form.action = form.action.replace('OFFER_ID_PLACEHOLDER', offerId);
    form.reset();
    document.getElementById('counterPriceField').style.display = 'none';
    new bootstrap.Modal(document.getElementById('counterOfferModal')).show();
}

function toggleCounterPriceField(select) {
    const counterPriceField = document.getElementById('counterPriceField');
    if (select.value === 'counter') {
        counterPriceField.style.display = 'block';
    } else {
        counterPriceField.style.display = 'none';
    }
}
</script>
@endpush
@endsection 