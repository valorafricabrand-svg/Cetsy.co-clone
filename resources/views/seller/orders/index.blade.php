@extends('layouts.app')

@section('title', 'Shop Orders')

@push('styles')
<style>
    .badge.text-capitalize { text-transform: capitalize; }
    .status-pill.active { text-decoration: none; }
    /* Clickable rows */
    tr.order-row { cursor: pointer; }
    
    /* Dispute and Appeal Status Pills */
    .status-pill[href*="disputes"] {
        background-color: #ffc107 !important;
        color: #000 !important;
        border: 1px solid #ffc107;
    }
    
    .status-pill[href*="disputes"]:hover {
        background-color: #e0a800 !important;
        border-color: #e0a800;
    }
    
    .status-pill[href*="appeals"] {
        background-color: #dc3545 !important;
        color: #fff !important;
        border: 1px solid #dc3545;
    }
    
    .status-pill[href*="appeals"]:hover {
        background-color: #c82333 !important;
        border-color: #c82333;
    }
    
    /* Active states for dispute/appeal pills */
    .status-pill[href*="disputes"].active,
    .status-pill[href*="disputes"]:active {
        background-color: #e0a800 !important;
        border-color: #e0a800;
    }
    
    .status-pill[href*="appeals"].active,
    .status-pill[href*="appeals"]:active {
        background-color: #c82333 !important;
        border-color: #c82333;
    }
    
    /* Dispute/Appeal table column styling */
    .dispute-appeal-cell {
        min-width: 120px;
    }
    
    .dispute-appeal-cell .badge {
        font-size: 0.75rem;
    }
    
    /* Minimal Action Buttons (only View is used) */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: center;
        min-width: 120px;
    }
    
    .action-btn {
        width: 100%;
        max-width: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
        border-width: 1.5px;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .action-btn:active {
        transform: translateY(0);
    }
    
    /* Button-specific styling (only primary outline used) */
    .action-btn.btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .action-btn.btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
    
    /* Removed: warning/danger/success/info variants no longer used */
    
    /* Button text styling */
    .btn-text {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
        }
        
        .action-btn {
            width: auto;
            min-width: 80px;
            padding: 0.375rem 0.5rem;
        }
        
        .btn-text {
            display: none;
        }
        
        .action-btn i {
            font-size: 1rem;
        }
    }
    
    /* Removed: hover styles for legacy actions (dispute/cancel/process/ship/deliver) */
    
    /* Status Progress Indicator Styles */
    .status-progress {
        margin-bottom: 12px;
        padding: 8px 0;
    }
    
    .progress-indicator {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        max-width: 80px;
        margin: 0 auto;
    }
    
    .progress-step {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }
    
    .step-dot {
        width: 8px;
        height: 8px;
        background-color: #e9ecef;
        border-radius: 50%;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
        z-index: 2;
    }
    
    .step-line {
        position: absolute;
        top: 4px;
        left: 50%;
        width: 100%;
        height: 2px;
        background-color: #dee2e6;
        transform: translateX(-50%);
        z-index: 1;
    }
    
    .progress-step.completed .step-dot {
        background-color: #198754;
        border-color: #198754;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.2);
    }
    
    .progress-step.current .step-dot {
        background-color: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
        transform: scale(1.2);
    }
    
    .progress-step.completed .step-line {
        background-color: #198754;
    }
    
    .progress-step.current .step-line {
        background-color: #0d6efd;
    }
    
    /* Responsive adjustments for status progress */
    @media (max-width: 768px) {
        .status-progress {
            margin-bottom: 8px;
        }
        
        .progress-indicator {
            max-width: 60px;
        }
        
        .step-dot {
            width: 6px;
            height: 6px;
        }
        
        .step-line {
            top: 3px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add click tracking for action buttons
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            // Add a small visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // Track button clicks (you can add analytics here)
            const action = this.textContent.trim();
            const orderId = this.closest('tr').querySelector('th').textContent.replace('#', '');
            console.log(`Action "${action}" clicked for Order #${orderId}`);
        });
    });
    
    // Add hover effects for dispute buttons
    document.querySelectorAll('.dispute-btn').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 4px 12px rgba(255, 193, 7, 0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
        });
    });
    
    // Add confirmation for destructive actions
    document.querySelectorAll('.cancel-btn, .process-btn, .ship-btn, .deliver-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const action = this.textContent.trim();
            const orderId = this.closest('tr').querySelector('th').textContent.replace('#', '');
            
            // Show confirmation for important actions
            if (action === 'Cancel') {
                if (!confirm(`Are you sure you want to cancel Order #${orderId}?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
    
    // Add keyboard navigation support
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Add loading states for buttons that trigger modals
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
            this.disabled = true;
            
            // Reset button after modal is shown
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 1000);
        });
    });

    // Make table rows clickable (navigate to order details)
    const isInteractive = (el) => {
        if (!el) return false;
        const selector = 'a,button,input,select,textarea,.action-buttons *,[data-bs-toggle]';
        return el.closest(selector) !== null;
    };
    document.querySelectorAll('tr.order-row').forEach(row => {
        row.addEventListener('click', (e) => {
            if (isInteractive(e.target)) return; // ignore clicks on controls/links
            const href = row.getAttribute('data-href');
            if (href) window.location.href = href;
        });
        row.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const href = row.getAttribute('data-href');
                if (href) window.location.href = href;
            }
        });
    });
});
</script>
@endpush

@section('content')
<div class="content">
    @php
        // Define dispute and appeal counts at the top so they're available throughout the view
        $disputeCount = $statusCounts['disputes'] ?? 0;
        $appealCount = $statusCounts['appeals'] ?? 0;
        $totalOrders = $statusCounts['all'] ?? 0;
    @endphp

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2 class="mb-0 text-success">
            <i class="fa-solid fa-cart-shopping me-2"></i>
            Orders for {{ $user->name }}
        </h2>
        
        @if($disputeCount > 0 || $appealCount > 0)
        <div class="d-flex gap-2">
            @if($disputeCount > 0)
            <a href="{{ route('orders.index', ['status' => 'disputes']) }}" 
               class="btn btn-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                View Disputes ({{ $disputeCount }})
            </a>
            @endif
            
            @if($appealCount > 0)
            <a href="{{ route('orders.index', ['status' => 'appeals']) }}" 
               class="btn btn-danger">
                <i class="bi bi-gavel me-1"></i>
                View Appeals ({{ $appealCount }})
            </a>
            @endif
        </div>
        @endif
    </div>

    {{-- DISPUTE & APPEAL OVERVIEW --}}
    @if($disputeCount > 0 || $appealCount > 0)
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-exclamation-triangle text-warning me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="mb-0 text-warning">{{ $disputeCount }}</h5>
                    </div>
                    <p class="mb-0 text-muted">Active Disputes</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-gavel text-danger me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="mb-0 text-danger">{{ $appealCount }}</h5>
                    </div>
                    <p class="mb-0 text-muted">Pending Appeals</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-box text-info me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="mb-0 text-info">{{ $totalOrders }}</h5>
                    </div>
                    <p class="mb-0 text-muted">Total Orders</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- FILTERS: Status pills + Search by ID --}}
    <form method="GET" action="{{ route('orders.index') }}" class="row gx-2 gy-2 align-items-center mb-4">
        {{-- Preserve status --}}
        <input type="hidden" name="status" value="{{ $currentStatus }}">

        <div class="col-auto">
            <div class="btn-group" role="group" aria-label="Order status filters">
                @php
                    $statuses = [
                        'all'        => 'All',
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'completed'  => 'Completed',
                        'cancelled'  => 'Cancelled',
                    ];
                @endphp
                @foreach($statuses as $key => $label)
                    @php
                        $count    = $statusCounts[$key] ?? 0;
                        $isActive = $currentStatus === $key;
                    @endphp
                    <a href="{{ route('orders.index', ['status'=>$key, 'search'=>$searchId]) }}"
                       class="badge {{ $isActive ? 'bg-primary' : 'bg-secondary' }} text-capitalize me-1 status-pill">
                        {{ $label }} ({{ $count }})
                    </a>
                @endforeach

                {{-- Dispute and Appeal Status Pills --}}
                @php
                    $disputeCount = $statusCounts['disputes'] ?? 0;
                    $appealCount = $statusCounts['appeals'] ?? 0;
                @endphp
                
                <a href="{{ route('orders.index', ['status'=>'disputes', 'search'=>$searchId]) }}"
                   class="badge {{ $currentStatus === 'disputes' ? 'bg-warning' : 'bg-warning' }} text-dark me-1 status-pill">
                    <i class="bi bi-exclamation-triangle"></i> Disputes ({{ $disputeCount }})
                </a>
                
                <a href="{{ route('orders.index', ['status'=>'appeals', 'search'=>$searchId]) }}"
                   class="badge {{ $currentStatus === 'appeals' ? 'bg-danger' : 'bg-danger' }} text-white me-1 status-pill">
                    <i class="bi bi-gavel"></i> Appeals ({{ $appealCount }})
                </a>
            </div>
        </div>

        <div class="col-auto">
            <div class="input-group">
                <input type="search"
                       name="search"
                       class="form-control"
                       placeholder="Search by Order ID"
                       value="{{ $searchId }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fa-solid fa-search"></i>
                </button>
            </div>
        </div>
    </form>

    @if ($orders->isNotEmpty())
        {{-- ORDERS TABLE --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th class="dispute-appeal-cell">Dispute/Appeal</th>
                                <th>Tracking No</th>
                                <!-- <th>Placed</th> -->
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $row      = $orders->firstItem() + $loop->index;
                                    $qtyTotal = $order->items->sum('quantity');
                                    $symbol   = shop_currency($order->shop ?? null);
                                    $dispute  = $order->disputes()->latest()->first();
                                @endphp
                                <tr class="order-row" data-href="{{ route('seller.orders.show', $order) }}" tabindex="0" aria-label="View order #{{ $order->id }} details">
                                    <th scope="row">{{ $row }}</th>
                                    <td>{{ $order->full_name }}</td>
                                    <td>{{ $order->phone ?? '—' }}</td>
                                    <td class="text-center">{{ $qtyTotal }}</td>
                                    <td class="text-end">{{ shop_currency($order->shop ?? null) }} {{ number_format($order->total_amount,2) }}</td>
                                    <td>
                                        <a href="{{ route('orders.index', ['status'=>$order->status, 'search'=>$searchId]) }}"
                                           class="badge {{ $order->getStatusBadgeClass() }} text-capitalize">
                                            {{ $order->status }}
                                        </a>
                                        @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                                            <br><small class="text-danger">{{ Str::limit($order->cancel_reason, 50) }}</small>
                                        @endif
                                        @php
                                            $minDays = null; $maxDays = null;
                                            foreach (($order->items ?? []) as $it) {
                                                $sp = $it->shippingProfile;
                                                $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                                                $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                                                if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin); }
                                                if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax); }
                                            }
                                            $placedAt = optional($order->created_at);
                                            $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
                                            $shipEnd   = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
                                            $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? 'today' : ($shipStart? $shipStart->format('M j') : null);
                                            $shipEndLabel   = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? 'today' : ($shipEnd? $shipEnd->format('M j') : null);
                                            $dispatchBy = $shipEndLabel ?? $shipStartLabel;
                                        @endphp
                                        <div class="small text-muted mt-1">
                                            @if($dispatchBy)
                                                Dispatch by {{ $dispatchBy }}
                                            @else
                                                Dispatch soon
                                            @endif
                                        </div>
                                    </td>
                                    <td class="dispute-appeal-cell">
                                        @if($dispute)
                                            @if($dispute->appeal)
                                                <div class="mb-1">
                                                    <span class="badge bg-danger text-white">
                                                        <i class="bi bi-gavel"></i> Appeal: {{ ucfirst($dispute->appeal->status) }}
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-1">
                                                @if($dispute->status === 'pending')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-exclamation-triangle"></i> Dispute: {{ ucfirst($dispute->status) }}
                                                </span>
                                                @elseif($dispute->status === 'under_review')
                                                <span class="badge bg-info text-dark">
                                                    <i class="bi bi-search"></i> Dispute: {{ ucfirst($dispute->status) }}
                                                </span>
                                                @elseif($dispute->status === 'resolved' || $dispute->status === 'mutually_resolved')
                                                <span class="badge bg-success text-dark">
                                                    <i class="bi bi-check-circle"></i> Dispute: {{ ucfirst($dispute->status) }}
                                                </span>
                                                @endif
                                            </div>
                                            
                                            @if($dispute->status === 'pending')
                                                <small class="text-warning d-block">
                                                    <i class="bi bi-clock"></i> Awaiting response
                                                </small>
                                            @elseif($dispute->status === 'under_review')
                                                <small class="text-info d-block">
                                                    <i class="bi bi-search"></i> Under review
                                                </small>
                                            @elseif($dispute->status === 'resolved')
                                                <small class="text-success d-block">
                                                    <i class="bi bi-check-circle"></i> Resolved
                                                </small>
                                            @elseif($dispute->status === 'mutually_resolved')
                                                <small class="text-success d-block">
                                                    <i class="bi bi-handshake"></i> Mutually Resolved
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->tracking_no ?? '—' }}</td>
                                    <!-- <td>{{ $order->created_at->format('d M Y') }}</td> -->
                                    <td class="text-center">
                                        {{-- Status Progress Indicator --}}
                                        <div class="status-progress mb-2">
                                            @php
                                                $statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
                                                $currentIndex = array_search($order->status, $statusOrder);
                                            @endphp
                                            
                                            @if($currentIndex !== false)
                                                <div class="progress-indicator">
                                                    @foreach($statusOrder as $index => $status)
                                                        <div class="progress-step {{ $index <= $currentIndex ? 'completed' : '' }} {{ $index === $currentIndex ? 'current' : '' }}">
                                                            <div class="step-dot"></div>
                                                            @if($index < count($statusOrder) - 1)
                                                                <div class="step-line"></div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ ucfirst($order->status) }}</small>
                                            @endif
                                        </div>
                                        
                                        <div class="action-buttons">
                                            {{-- View Order Only; manage actions on the Show page --}}
                                            <a href="{{ route('seller.orders.show', $order) }}"
                                               class="btn btn-sm btn-outline-primary action-btn"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="View Order Details">
                                                <i class="bi bi-eye"></i>
                                                <span class="btn-text">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION --}}
            @if ($orders->hasPages())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-center">
                        {{ $orders->appends(request()->only('status','search'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- EMPTY STATE --}}
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i>
            No orders found for your shop.
        </div>
    @endif
</div>
@endsection
