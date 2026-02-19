@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        {{ __('Your Offers') }}
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
        <!-- Summary Cards -->
        <div class="mb-4 grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-sky-600 text-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <i class="fa-regular fa-clock text-4xl"></i>
                            </div>
                            <div class="ml-3 grow">
                                <h4 class="mb-0 text-2xl font-bold">{{ $offers->count() }}</h4>
                                <small>Total Products</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 shadow-sm">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <i class="fa-solid fa-hourglass-half text-4xl"></i>
                            </div>
                            <div class="ml-3 grow">
                                <h4 class="mb-0 text-2xl font-bold">{{ $offers->sum('status_summary.pending') }}</h4>
                                <small>Pending Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-600 text-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <i class="fa-regular fa-circle-check text-4xl"></i>
                            </div>
                            <div class="ml-3 grow">
                                <h4 class="mb-0 text-2xl font-bold">{{ $offers->sum('status_summary.accepted') }}</h4>
                                <small>Accepted Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                <div class="rounded-2xl border border-indigo-200 bg-indigo-600 text-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <i class="fa-solid fa-right-left text-4xl"></i>
                            </div>
                            <div class="ml-3 grow">
                                <h4 class="mb-0 text-2xl font-bold">{{ $offers->where('has_counter_offers', true)->count() }}</h4>
                                <small>Counter Offers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make New Offer Section -->
        <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3 bg-slate-100">
                <h6 class="mb-0">
                    <i class="fa-regular fa-circle-plus mr-2"></i>Make New Offer
                </h6>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-8">
                        <p class="text-slate-500 mb-3">Make an offer on a product you're interested in. Browse products and submit your best offer!</p>
                    </div>
                    <div class="col-span-12 text-left md:col-span-4 md:text-right">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" onclick="showNewOfferModal()">
                            <i class="fa-regular fa-circle-plus mr-1"></i>Make New Offer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($offers->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-4 sm:p-5 text-center py-5">
                    <i class="fa-solid fa-inbox mb-3 text-5xl text-slate-400"></i>
                    <h5 class="text-slate-500">No Offers Yet</h5>
                    <p class="text-slate-500">You haven't made any offers yet. Start browsing products to make your first offer!</p>
                    <a href="{{ route('listings') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i>Browse Products
                    </a>
                </div>
            </div>
        @else
            @foreach($offers as $productId => $offerData)
                @php
                    $latestStatusClass = match($offerData['latest_offer']->status) {
                        'accepted' => 'bg-emerald-100 text-emerald-700',
                        'declined' => 'bg-rose-100 text-rose-700',
                        'countered' => 'bg-indigo-100 text-indigo-700',
                        default => 'bg-amber-100 text-amber-700',
                    };
                @endphp
                <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm offer-card @if($offerData['latest_offer']->status === 'accepted') border-emerald-400 ring-2 ring-emerald-200 accepted-offer-card @endif">
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
                                         class="mr-3 h-[50px] w-[50px] rounded object-cover">
                                @else
                                    <div class="mr-3 flex h-[50px] w-[50px] items-center justify-center rounded bg-slate-100">
                                        <i class="fa-regular fa-image text-slate-500"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $offerData['product']->name }}</h6>
                                    <small class="text-slate-500">
                                        <i class="fa-solid fa-store mr-1"></i>{{ $offerData['product']->shop->name ?? 'Unknown Shop' }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $latestStatusClass }}">
                                    {{ $offerData['latest_offer']->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 sm:p-5">
                        @if($offerData['latest_offer']->status === 'accepted')
                            <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 flex items-center mb-4 p-3 shadow-sm accepted-offer-alert">
                                <i class="fa-solid fa-circle-check mr-3 text-3xl text-emerald-600"></i>
                                <div class="grow">
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
                                                   class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                                    <i class="fa-regular fa-credit-card mr-1"></i>Pay Now
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <!-- Latest Offer Summary -->
                        <div class="grid grid-cols-12 gap-4 mb-3">
                            <div class="col-span-12 md:col-span-6">
                                <div class="flex items-center">
                                    <div class="mr-3">
                                        <i class="fa-solid fa-dollar-sign text-2xl text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Your Latest Offer</h6>
                                        <span class="text-xl font-bold text-emerald-600">{{ $offerData['latest_offer']->formatted_price }}</span>
                                        <div class="text-slate-500 text-xs">
                                            <i class="fa-regular fa-clock mr-1"></i>{{ $offerData['latest_offer']->time_ago }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <div class="flex items-center">
                                    <div class="mr-3">
                                        <i class="fa-solid fa-tag text-2xl text-sky-600"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Original Price</h6>
                                        <span class="text-xl font-bold text-sky-600">{{ get_currency() }} {{ number_format($offerData['product']->price, 2) }}</span>
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
                                    <i class="fa-regular fa-clock mr-1"></i>Offer History
                                </h6>
                                <div class="timeline">
                                    @foreach($offerData['offer_history'] as $index => $offer)
                                        @php
                                            $isCounter = isset($offer->is_counter_offer) ? (bool) $offer->is_counter_offer : false;
                                            $isOriginal = isset($offer->is_original) ? (bool) $offer->is_original : false;
                                            $statusBadge = match($offer->status ?? null) {
                                                'accepted' => 'bg-emerald-100 text-emerald-700',
                                                'declined' => 'bg-rose-100 text-rose-700',
                                                'countered' => 'bg-indigo-100 text-indigo-700',
                                                default => 'bg-amber-100 text-amber-700',
                                            };
                                            $statusLabel = $offer->status_label ?? ($isOriginal ? 'Original Offer' : (isset($offer->status) ? ucfirst((string)$offer->status) : ''));
                                            $formattedPrice = $offer->formatted_price ?? (isset($offer->offer_price) ? (get_currency().' '.number_format((float)$offer->offer_price, 2)) : '');
                                            try {
                                                $createdLabel = isset($offer->created_at) ? ( ($offer->created_at instanceof \Carbon\Carbon) ? $offer->created_at->format('M d, H:i') : (\Carbon\Carbon::parse($offer->created_at))->format('M d, H:i') ) : '';
                                            } catch (\Throwable $e) { $createdLabel = ''; }
                                        @endphp
                                        @php
                                            $labelText = $isCounter ? 'Counter Offer' : ($isOriginal ? 'Original Offer' : 'Your Offer');
                                            $iconClass = $isCounter ? 'fa-solid fa-right-left text-indigo-600' : ($isOriginal ? 'fa-regular fa-flag text-slate-600' : 'fa-solid fa-arrow-up text-sky-600');
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-marker {{ $isCounter ? 'bg-indigo-500' : 'bg-sky-500' }}"></div>
                                            <div class="timeline-content">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <strong>
                                                            <i class="{{ $iconClass }} mr-1"></i>{{ $labelText }}
                                                        </strong>
                                                        @if($formattedPrice)
                                                            <div class="text-slate-500 text-xs">{{ $formattedPrice }}</div>
                                                        @endif
                                                        @if(!empty($offer->buyer_notes))
                                                            <div class="text-slate-500 text-xs mt-1">{{ $offer->buyer_notes }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
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
                                <i class="fa-regular fa-eye mr-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
            </div>
        </div>
    </div>
</div>

<!-- New Offer Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" id="newOfferModal">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h5 class="text-base font-semibold text-slate-900">Make New Offer</h5>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" onclick="hideNewOfferModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="max-h-[70vh] overflow-y-auto px-4 py-4">
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
                                    <div class="col-span-12 md:col-span-3">
                                        <img id="productImage" src="" alt="Product" class="h-20 w-20 rounded object-cover">
                                    </div>
                                    <div class="col-span-12 md:col-span-9">
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
                                <i class="fa-regular fa-circle-check mr-1"></i>
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
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" onclick="hideNewOfferModal()">Cancel</button>
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" onclick="submitNewOffer()">Submit Offer</button>
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
    background: #e2e8f0;
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
    background: #f8fafc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #0ea5e9;
}
.accepted-offer-card {
    box-shadow: 0 0 0 0.2rem rgba(34,197,94,.12) !important;
}
.accepted-offer-alert {
    background: linear-gradient(90deg, #e9fbe7 0%, #d4f5e9 100%);
    border: 1.5px solid #22c55e;
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
            const modal = document.getElementById('newOfferModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}

function hideNewOfferModal() {
    const modal = document.getElementById('newOfferModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
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

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('newOfferModal');
    if (!modal) return;

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            hideNewOfferModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('flex')) {
            hideNewOfferModal();
        }
    });
});
</script>
@endpush
@endsection 




