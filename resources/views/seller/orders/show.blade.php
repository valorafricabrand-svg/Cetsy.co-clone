{{-- resources/views/seller/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
@php
    $symbol = config('app.currency_symbol', 'KES');
@endphp

<div class="content">
    {{-- TITLE + ACTIONS --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h2 class="text-success mb-0">Order #{{ $order->id }} Details</h2>

        <div class="btn-toolbar gap-2 flex-wrap">
            <a href="{{ route('orders.chat.show', $order->id) }}"
               class="btn btn-outline-info btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-chat-dots"></i> Messages
            </a>

            @if ($order->status === \App\Models\Order::STATUS_PENDING)
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                        data-bs-toggle="modal"
                        data-bs-target="#processModal-{{ $order->id }}">
                    <i class="bi bi-gear"></i> Process
                </button>
                @include('seller.orders.modals.process')

            @elseif ($order->status === \App\Models\Order::STATUS_PROCESSING)
                <button class="btn btn-outline-info btn-sm d-flex align-items-center gap-1"
                        data-bs-toggle="modal"
                        data-bs-target="#shipModal">
                    <i class="bi bi-truck"></i> Ship
                </button>

           
            @endif
        </div>
    </div>

    {{-- ORDER SUMMARY & CUSTOMER INFO --}}
    <div class="row g-4">
        {{-- ORDER SUMMARY --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-receipt"></i> Order Summary
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6 fw-semibold">Tracking No:</div>
                        <div class="col-6 text-end">{{ $order->tracking_no ?? '—' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 fw-semibold">Courier:</div>
                        <div class="col-6 text-end">{{ $order->courier ?? '—' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 fw-semibold">Items:</div>
                        <div class="col-6 text-end">{{ $order->items->sum('quantity') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 fw-semibold">Subtotal:</div>
                        <div class="col-6 text-end">{{ $symbol }} {{ number_format($order->subtotal, 2) }}</div>
                    </div>
                    @if ($order->shipping_cost)
                        <div class="row mb-2">
                            <div class="col-6 fw-semibold">Shipping Fee:</div>
                            <div class="col-6 text-end">{{ $symbol }} {{ number_format($order->shipping_cost, 2) }}</div>
                        </div>
                    @endif
                    <div class="row mb-2 fw-bold">
                        <div class="col-6">Total Amount:</div>
                        <div class="col-6 text-end">{{ $symbol }} {{ number_format($order->total_amount, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 fw-semibold">Status:</div>
                        <div class="col-6 text-end">
                            <span class="badge {{ $order->getStatusBadgeClass() }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 fw-semibold">Created:</div>
                        <div class="col-6 text-end">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CUSTOMER INFO --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-person"></i> Customer Info
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $order->full_name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
                    <p class="mb-3"><strong>Phone:</strong> {{ $order->phone ?? '—' }}</p>

                    <p class="fw-semibold mb-1">Shipping Address</p>
                    <address class="mb-3">
                        {{ $order->shipping_address_1 }}<br>
                        @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                        {{ $order->shipping_city }}, {{ $order->shipping_state }}<br>
                        {{ $order->shipping_postal_code }}
                    </address>

                    <p class="mb-1"><strong>Shipping Method:</strong> {{ ucfirst($order->shipping_method) }}</p>
                    <p class="mb-0"><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ORDER ITEMS --}}
    @if($order->items->count())
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-box-seam"></i> Order Items
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Variation</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Shipping Profile</th>
                            <th>Shipping Cost</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $shippingProfileName = optional($item->shippingProfile)->name ?? 'N/A';
                                $shippingCostFormatted = $symbol . ' ' . number_format($item->shipping_cost ?? 0, 2);
                                $subtotal = $item->quantity * $item->price + ($item->shipping_cost ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                                <td>{{ $item->variation_details ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $symbol }} {{ number_format($item->price, 2) }}</td>
                                <td>{{ $shippingProfileName }}</td>
                                <td>{{ $shippingCostFormatted }}</td>
                                <td>{{ $symbol }} {{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- PAYMENTS --}}
    @if($order->payments->count())
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-wallet2"></i> Payments
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->payments as $payment)
                            @php
                                $statusClasses = [
                                    'pending' => 'secondary',
                                    'success' => 'success',
                                    'failed'  => 'danger',
                                ];
                                $statusClass = $statusClasses[strtolower($payment->status)] ?? 'dark';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment->local_transaction_id ?? 'N/A' }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ $symbol }} {{ number_format($payment->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ADDITIONAL INFO --}}
    @if($order->order_notes || $order->promo_code)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-info-circle"></i> Additional Information
            </div>
            <div class="card-body">
                @if($order->order_notes)
                    <p><strong>Order Notes:</strong><br>{{ $order->order_notes }}</p>
                @endif
                @if($order->promo_code)
                    <p><strong>Promo Code:</strong> {{ $order->promo_code }}</p>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- SHIPPING MODAL --}}
@if ($order->status === \App\Models\Order::STATUS_PROCESSING)
  <div class="modal fade" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('seller.orders.ship', $order) }}" method="post" class="modal-content needs-validation" novalidate>
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="shipModalLabel">Ship Order #{{ $order->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="px-4 pt-4">
                <div class="alert alert-info small py-2 mb-0">
                    <strong>Customer:</strong> {{ $order->full_name }} &nbsp;•&nbsp;
                    <strong>Total:</strong> {{ $symbol }} {{ number_format($order->total_amount, 2) }}
                </div>
            </div>

            <div class="modal-body">
                {{-- Shipping details for the whole order --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" name="courier" id="courierSelect" required>
                                <option value="" selected disabled>Select courier…</option>
                                <option>Wells Fargo</option>
                                <option>DHL</option>
                                <option>Fargo Courier</option>
                                <option>G4S</option>
                                <option value="other">Other / Manual</option>
                            </select>
                            <label for="courierSelect">Courier *</label>
                            <div class="invalid-feedback">Please select a courier.</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="trackingInput" name="tracking_no" placeholder="ABC123" required>
                            <label for="trackingInput">Tracking number *</label>
                            <div class="invalid-feedback">Tracking number required.</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="shipDateInput" name="shipping_date" value="{{ now()->toDateString() }}">
                            <label for="shipDateInput">Shipping date</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <textarea class="form-control" id="shipNotes" name="ship_notes" style="height: 100px;"></textarea>
                            <label for="shipNotes">Notes (optional)</label>
                        </div>
                    </div>
                </div>

                {{-- Ordered items with their shipping profiles --}}
                <h6 class="mb-3">Order Items Shipping Profiles</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Shipping Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        {{-- Optional: Show shipping profile name (readonly) --}}
                                        {{-- If you want to allow changes, use a select dropdown here --}}
                                        <input type="hidden" name="order_items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                        <select name="order_items[{{ $item->id }}][shipping_profile_id]" class="form-select" >
                                            @foreach($item->product->shippingProfiles as $profile)
                                                <option value="{{ $profile->id }}" @selected($item->shipping_profile_id == $profile->id)>{{ $profile->name }} (KES {{ number_format($profile->base_rate, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-truck me-1"></i> Mark as Shipped
                </button>
            </div>
        </form>
    </div>
</div>

@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Auto open modal if ?ship=1 in URL
    const params = new URLSearchParams(window.location.search);
    if (params.get('ship') === '1') {
        const shipModal = document.getElementById('shipModal');
        shipModal && new bootstrap.Modal(shipModal).show();
    }

    // Bootstrap 5 validation for forms with .needs-validation
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) e.preventDefault();
            form.classList.add('was-validated');
        });
    });
});
</script>
@endpush
