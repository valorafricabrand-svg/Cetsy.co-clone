@extends('layouts.app')
@section('title', 'My Offers')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">My Offers Received</h1>
            <p class="text-muted mb-0">Manage offers from potential buyers for your products</p>
        </div>
        <div class="d-flex gap-2">
            @if($stats['pending'] > 0)
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                    <i class="bi bi-check-all me-1"></i>Bulk Actions
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="bi bi-hand-holding-dollar text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Total Offers</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="bi bi-clock text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Pending</h6>
                            <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Accepted</h6>
                            <h4 class="mb-0">{{ $stats['accepted'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="bi bi-currency-dollar text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Avg Value</h6>
                            <h4 class="mb-0">{{ get_currency() }} {{ number_format($stats['avg_value'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('seller.offers.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="original" {{ request('type') === 'original' ? 'selected' : '' }}>Original Offers</option>
                            <option value="counter" {{ request('type') === 'counter' ? 'selected' : '' }}>Counter Offers</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Product</label>
                        <select name="product" class="form-select form-select-sm">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                    {{ \Illuminate\Support\Str::limit($product->name, 25) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Price Min</label>
                        <input type="number" name="price_min" class="form-control form-control-sm" value="{{ request('price_min') }}" placeholder="Min">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Price Max</label>
                        <input type="number" name="price_max" class="form-control form-control-sm" value="{{ request('price_max') }}" placeholder="Max">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('seller.offers.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Offers Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Offers ({{ $offers->total() }})</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label small" for="selectAll">Select All</label>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="30">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>Product</th>
                        <th>Buyer</th>
                        <th>Offer</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td>
                                @if($offer->is_negotiable)
                                    <input type="checkbox" class="form-check-input offer-checkbox" value="{{ $offer->id }}">
                                @endif
                            </td>
                            <td style="min-width:200px;">
                                <div class="d-flex align-items-center gap-2">
                                    @if($offer->product && $offer->product->media->first())
                                        <img src="{{ asset('storage/' . $offer->product->media->first()->file_path) }}" 
                                             alt="Product" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                    @else
                                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                             style="width:40px;height:40px;">
                                            <i class="bi bi-box text-secondary"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold text-dark d-block" title="{{ $offer->product->name ?? '-' }}">
                                            {{ \Illuminate\Support\Str::limit($offer->product->name ?? '-', 25) }}
                                        </span>
                                        <span class="badge bg-light text-muted border small">#{{ $offer->product_id }}</span>
                                        @if($offer->is_counter_offer)
                                            <span class="badge bg-info small ms-1">Counter</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="min-width:150px;">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold small text-dark">{{ $offer->buyer->name ?? '-' }}</span>
                                    <span class="text-muted small" title="{{ $offer->buyer->email ?? '' }}">
                                        <i class="bi bi-envelope me-1"></i>{{ \Illuminate\Support\Str::limit($offer->buyer->email ?? '', 20) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $offer->formatted_price }}</span>
                                    @if($offer->is_counter_offer)
                                        @php
                                            $diff = $offer->getPriceDifference();
                                            $diffPercent = $offer->getPriceDifferencePercentage();
                                        @endphp
                                        @if($diff != 0)
                                            <span class="small {{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="bi {{ $diff > 0 ? 'bi-arrow-up' : 'bi-arrow-down' }} me-1"></i>
                                                {{ $diff > 0 ? '+' : '' }}{{ get_currency() }} {{ number_format(abs($diff), 2) }}
                                                ({{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%)
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $offer->status_badge_class }}">{{ $offer->status_label }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="small text-dark">{{ $offer->created_at->format('d M Y') }}</span>
                                    <span class="text-muted small">{{ $offer->time_ago }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('seller.offers.show', $offer->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                    @if($offer->is_negotiable)
                                        <div class="dropdown">
                                            <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-gear me-1"></i>Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" action="{{ route('seller.offers.accept', $offer->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Accept this offer?')">
                                                            <i class="bi bi-check-circle text-success me-2"></i>Accept
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" 
                                                            data-bs-target="#declineModal" data-offer-id="{{ $offer->id }}">
                                                        <i class="bi bi-x-circle text-danger me-2"></i>Decline
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" 
                                                            data-bs-target="#counterModal" data-offer-id="{{ $offer->id }}"
                                                            data-original-price="{{ $offer->offer_price }}">
                                                        <i class="bi bi-arrow-left-right text-warning me-2"></i>Counter
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox me-2"></i> No offers found for your products.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())
            <div class="card-footer">
                {{ $offers->links() }}
            </div>
        @endif
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
            <form method="POST" id="declineForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to decline this offer?</p>
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
            <form method="POST" id="counterForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Counter Price</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ get_currency() }}</span>
                            <input type="number" name="counter_price" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text">Enter your counter offer price</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Add a message to your counter offer..."></textarea>
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

{{-- Bulk Action Modal --}}
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('seller.offers.bulk-action') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="">Select Action</option>
                            <option value="accept">Accept Selected</option>
                            <option value="decline">Decline Selected</option>
                            <option value="expire">Mark as Expired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (for decline)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason for declining..."></textarea>
                    </div>
                    <div id="selectedOffersInfo" class="alert alert-info d-none">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="selectedCount">0</span> offers selected
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
                <input type="hidden" name="offer_ids" id="selectedOfferIds">
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const offerCheckboxes = document.querySelectorAll('.offer-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        offerCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });
    
    offerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updateSelectAllState();
        });
    });
    
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.offer-checkbox:checked');
        const count = selected.length;
        const info = document.getElementById('selectedOffersInfo');
        const countSpan = document.getElementById('selectedCount');
        const idsInput = document.getElementById('selectedOfferIds');
        
        if (count > 0) {
            info.classList.remove('d-none');
            countSpan.textContent = count;
            idsInput.value = Array.from(selected).map(cb => cb.value).join(',');
        } else {
            info.classList.add('d-none');
            idsInput.value = '';
        }
    }
    
    function updateSelectAllState() {
        const total = offerCheckboxes.length;
        const checked = document.querySelectorAll('.offer-checkbox:checked').length;
        selectAllCheckbox.checked = checked === total && total > 0;
        selectAllCheckbox.indeterminate = checked > 0 && checked < total;
    }
    
    // Modal handlers
    const declineModal = document.getElementById('declineModal');
    const counterModal = document.getElementById('counterModal');
    
    declineModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const offerId = button.getAttribute('data-offer-id');
        const form = document.getElementById('declineForm');
        form.action = `/seller/offers/${offerId}/decline`;
    });
    
    counterModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const offerId = button.getAttribute('data-offer-id');
        const originalPrice = button.getAttribute('data-original-price');
        const form = document.getElementById('counterForm');
        const priceInput = form.querySelector('input[name="counter_price"]');
        
        form.action = `/seller/offers/${offerId}/counter`;
        priceInput.value = originalPrice;
        priceInput.focus();
    });
});
</script>
@endpush

@push('styles')
<style>
.table th, .table td {
    vertical-align: middle;
}
.badge {
    font-size: 0.75rem;
}
.dropdown-item {
    cursor: pointer;
}
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush
@endsection 