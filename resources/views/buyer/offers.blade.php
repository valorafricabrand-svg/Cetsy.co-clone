@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold fs-3 text-slate-900">
        {{ __('Your Offers') }}
    </h2>
@endsection

@section('main')
<div class="content">
    <div class="container-xxl">
        <!-- Summary Cards -->
        <div class="grid grid-cols-12 gap-4 mb-4">
            <div class="md:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm bg-primary text-white">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock-history fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h4 class="mb-0">{{ $offers->count() }}</h4>
                                <small>Total Products</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm bg-amber-100 text-slate-900">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-hourglass-split fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h4 class="mb-0">{{ $offers->sum('status_summary.pending') }}</h4>
                                <small>Pending Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm bg-success text-white">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h4 class="mb-0">{{ $offers->sum('status_summary.accepted') }}</h4>
                                <small>Accepted Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm bg-sky-100 text-white">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-arrow-left-right fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h4 class="mb-0">{{ $offers->where('has_counter_offers', true)->count() }}</h4>
                                <small>Counter Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make New Offer Section -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
            <div class="border-b border-slate-200 px-4 py-3 bg-slate-100">
                <h6 class="mb-0">
                    <i class="bi bi-plus-circle mr-2"></i>Make New Offer
                </h6>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="md:col-span-8">
                        <p class="text-slate-500 mb-3">Make an offer on a product you're interested in. Browse products and submit your best offer!</p>
                    </div>
                    <div class="md:col-span-4 text-right">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" onclick="showNewOfferModal()">
                            <i class="bi bi-plus-circle mr-1"></i>Make New Offer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($offers->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                <div class="p-4 sm:p-5 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-slate-500 mb-3"></i>
                    <h5 class="text-slate-500">No Offers Yet</h5>
                    <p class="text-slate-500">You haven't made any offers yet. Start browsing products to make your first offer!</p>
                    <a href="{{ route('listings') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                        <i class="bi bi-search mr-1"></i>Browse Products
                    </a>
                </div>
            </div>
        @else
            @foreach($offers as $productId => $offerData)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4 offer-card @if($offerData['latest_offer']->status === 'accepted') border-success border-3 accepted-offer-card @endif">
                    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @if($offerData['product']->media && $offerData['product']->media->count() > 0)
                                    @php
                                        $thumb = function_exists('product_thumb_url')
                                            ? product_thumb_url($offerData['product'])
                                            : (optional($offerData['product']->media->first())->url
                                                ? asset('storage/'.$offerData['product']->media->first()->url)
                                                : null);
                                    @endphp
                                    <img src="{{ $thumb }}" 
                                         alt="{{ $offerData['product']->name }}" 
                                         class="rounded mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-slate-100 rounded flex items-center justify-center mr-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-slate-500"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $offerData['product']->name }}</h6>
                                    <small class="text-slate-500">
                                        <i class="bi bi-shop mr-1"></i>{{ $offerData['product']->shop->name ?? 'Unknown Shop' }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $offerData['latest_offer']->status_badge_class }} fs-6">
                                    {{ $offerData['latest_offer']->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 sm:p-5">
                        @if($offerData['latest_offer']->status === 'accepted')
                            <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 flex items-center mb-4 p-3 shadow-sm accepted-offer-alert">
                                <i class="bi bi-patch-check-fill fs-3 mr-3 text-emerald-600"></i>
                                <div class="flex-grow-1">
                                    <strong class="text-emerald-600">Congratulations!</strong> Your offer was <b>accepted</b> by the seller.<br>
                                    <span class="text-xs text-emerald-600">You can now proceed to checkout or contact the seller for next steps.</span>
                                    
                                    @if($offerData['latest_offer']->order)
                                        <div class="mt-3 p-3 bg-white rounded border">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <strong class="text-emerald-600">Order #{{ $offerData['latest_offer']->order->id }} Created</strong><br>
                                                    <small class="text-slate-500">Total: {{ get_currency() }} {{ number_format($offerData['latest_offer']->order->total_amount, 2) }}</small>
                                                </div>
                                                <a href="{{ route('pay_now', $offerData['latest_offer']->order->id) }}" 
                                                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-1.5 text-xs">
                                                    <i class="bi bi-credit-card mr-1"></i>Pay Now
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <!-- Latest Offer Summary -->
                        <div class="grid grid-cols-12 gap-4 mb-3">
                            <div class="md:col-span-6">
                                <div class="flex items-center">
                                    <div class="mr-3">
                                        <i class="bi bi-currency-dollar fs-4 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Your Latest Offer</h6>
                                        <span class="fs-5 font-bold text-emerald-600">{{ $offerData['latest_offer']->formatted_price }}</span>
                                        <div class="text-slate-500 text-xs">
                                            <i class="bi bi-clock mr-1"></i>{{ $offerData['latest_offer']->time_ago }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-6">
                                <div class="flex items-center">
                                    <div class="mr-3">
                                        <i class="bi bi-tag fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Original Price</h6>
                                        <span class="fs-5 font-bold text-primary">{{ get_currency() }} {{ number_format($offerData['product']->price, 2) }}</span>
                                        <div class="text-slate-500 text-xs">
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
                                    <i class="bi bi-clock-history mr-1"></i>Offer History
                                </h6>
                                <div class="timeline">
                                    @foreach($offerData['offer_history'] as $index => $offer)
                                        @php
                                            $isCounter = isset($offer->is_counter_offer) ? (bool) $offer->is_counter_offer : false;
                                            $isOriginal = isset($offer->is_original) ? (bool) $offer->is_original : false;
                                            $statusBadge = $offer->status_badge_class ?? 'bg-secondary';
                                            $statusLabel = $offer->status_label ?? ($isOriginal ? 'Original Offer' : (isset($offer->status) ? ucfirst((string)$offer->status) : ''));
                                            $formattedPrice = $offer->formatted_price ?? (isset($offer->offer_price) ? (get_currency().' '.number_format((float)$offer->offer_price, 2)) : '');
                                            try {
                                                $createdLabel = isset($offer->created_at) ? ( ($offer->created_at instanceof \Carbon\Carbon) ? $offer->created_at->format('M d, H:i') : (\Carbon\Carbon::parse($offer->created_at))->format('M d, H:i') ) : '';
                                            } catch (\Throwable $e) { $createdLabel = ''; }
                                        @endphp
                                        @php
                                            $labelText = $isCounter ? 'Counter Offer' : ($isOriginal ? 'Original Offer' : 'Your Offer');
                                            $iconClass = $isCounter ? 'bi-arrow-left-right text-info' : ($isOriginal ? 'bi-flag text-secondary' : 'bi-arrow-up text-primary');
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-marker {{ $isCounter ? 'bg-info' : 'bg-primary' }}"></div>
                                            <div class="timeline-content">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <strong>
                                                            <i class="bi {{ $iconClass }} mr-1"></i>{{ $labelText }}
                                                        </strong>
                                                        @if($formattedPrice)
                                                            <div class="text-slate-500 text-xs">{{ $formattedPrice }}</div>
                                                        @endif
                                                        @if(!empty($offer->buyer_notes))
                                                            <div class="text-slate-500 text-xs mt-1">{{ $offer->buyer_notes }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadge }} small">{{ $statusLabel }}</span>
                                                        @if($createdLabel)
                                                            <div class="text-slate-500 text-xs">{{ $createdLabel }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons (only View) -->
                        <div class="mt-3">
                            <a href="{{ route('buyer.offers.details', $offerData['latest_offer']->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs">
                                <i class="bi bi-eye mr-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- New Offer Modal -->
<div class="modal" id="newOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h5 class="text-base font-semibold text-slate-900">Make New Offer</h5>
                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal"></button>
            </div>
            <div class="px-4 py-4">
                <form id="newOfferForm">
                    @csrf
                    <div class="mb-3">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Select Product</label>
                        <select name="product_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required onchange="updateProductInfo()">
                            <option value="">Choose a product...</option>
                            <!-- Products will be loaded here -->
                        </select>
                    </div>
                    
                    <!-- Product Info Display -->
                    <div id="productInfo" style="display: none;" class="mb-3">
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="p-4 sm:p-5">
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <img id="productImage" src="" alt="Product" class="img-fluid rounded" style="max-width: 100px;">
                                    </div>
                                    <div class="md:col-span-9">
                                        <h6 id="productName" class="mb-1"></h6>
                                        <p id="productPrice" class="text-slate-500 mb-2"></p>
                                        <small class="text-slate-500">Original Price</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Your Offer Price</label>
                        <div class="flex w-full items-stretch">
                            <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">{{ get_currency() }}</span>
                            <input type="number" name="offer_price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" step="0.01" min="0" required onchange="calculateSavings()">
                        </div>
                        <div class="mt-1 text-xs text-slate-500">Enter your best offer price</div>
                        <div id="savingsInfo" class="mt-2" style="display: none;">
                            <small class="text-emerald-600">
                                <i class="bi bi-check-circle mr-1"></i>
                                You'll save: <span id="savingsAmount"></span> (<span id="savingsPercentage"></span>)
                            </small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Message (Optional)</label>
                        <textarea name="message" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Add a message to your offer..."></textarea>
                    </div>
                </form>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" onclick="submitNewOffer()">Submit Offer</button>
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
        const selectedProduct = window.availableProducts.find(p => p.id == productId);
        if (selectedProduct) {
            document.getElementById('productInfo').style.display = 'block';
            document.getElementById('productImage').src = selectedProduct.image || '/path/to/default-image.jpg';
            document.getElementById('productName').textContent = selectedProduct.name;
            document.getElementById('productPrice').textContent = `${selectedProduct.currency} ${selectedProduct.price}`;
            document.getElementById('productInfo').dataset.originalPrice = selectedProduct.price;
        }
    } else {
        document.getElementById('productInfo').style.display = 'none';
    }
}

function calculateSavings() {
    const originalPrice = parseFloat(document.getElementById('productInfo').dataset.originalPrice || 0);
    const offerPriceInput = document.querySelector('input[name="offer_price"]');
    const offerPrice = offerPriceInput ? parseFloat(offerPriceInput.value || 0) : 0;
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
</script>
@endpush
@endsection 




