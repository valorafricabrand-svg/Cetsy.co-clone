{{-- resources/views/seller/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Details')

@push('styles')
<style>
  /* Optional: minor tweaks */
  .badge.text-capitalize { text-transform: capitalize; }
  .order-detail-icon { font-size: 1.25rem; }
</style>
@endpush

@section('content')
@php $symbol = get_currency(); @endphp

<div class="content">
    {{-- HEADER & ACTIONS --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h2 class="h4 text-success mb-0">
            <i class="fa-solid fa-receipt order-detail-icon me-2"></i>
            Order #{{ $order->id }} Details
        </h2>

        <div class="btn-toolbar gap-2 flex-wrap">
            <a href="{{ route('orders.chat.show', $order->id) }}"
               class="btn btn-outline-info btn-sm d-flex align-items-center gap-1">
                <i class="fa-solid fa-comments"></i> Messages
            </a>

            @if($order->status === \App\Models\Order::STATUS_PENDING)
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                        data-bs-toggle="modal"
                        data-bs-target="#processModal-{{ $order->id }}">
                    <i class="fa-solid fa-gear"></i> Process
                </button>
                @include('seller.orders.modals.process')
            @elseif($order->status === \App\Models\Order::STATUS_PROCESSING)
                <button class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1"
                        data-bs-toggle="modal"
                        data-bs-target="#shipModal">
                    <i class="fa-solid fa-truck"></i> Ship
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY & CUSTOMER --}}
    <div class="row g-4 mb-4">
        {{-- Order Summary --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check"></i> Order Summary
                </div>
                <div class="card-body">
                    @foreach ([
                        'Tracking No'    => $order->tracking_no ?? '—',
                        'Courier'        => $order->courier ?? '—',
                        'Items'          => $order->items->sum('quantity'),
                        'Subtotal'       => "{$symbol} ".number_format($order->subtotal,2),
                        ] as $label => $value)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">{{ $label }}:</span>
                            <span>{{ $value }}</span>
                        </div>
                    @endforeach

                    @if($order->shipping_cost)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Shipping Fee:</span>
                            <span>{{ $symbol }} {{ number_format($order->shipping_cost,2) }}</span>
                        </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-2 fw-bold">
                        <span>Total Amount:</span>
                        <span>{{ $symbol }} {{ number_format($order->total_amount,2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Status:</span>
                        <span>
                            <span class="badge {{ $order->getStatusBadgeClass() }} text-capitalize">
                                {{ $order->status }}
                            </span>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Created:</span>
                        <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user"></i> Customer Info
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

    {{-- ITEMS --}}
    @if($order->items->isNotEmpty())
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-stacked"></i> Order Items
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Variation</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th>Shipping Profile</th>
                            <th class="text-end">Shipping Cost</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $subtotal = $item->quantity * $item->price + ($item->shipping_cost ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('listing.show', $item->product->slug) }}" target="_blank">
                                        <img src="{{ $item->product->featured_image 
                                          ?? asset('storage/' . ($item->product->media->first()->url ?? 'placeholder.jpg')) }}"
                                             alt="{{ $item->product->name }}"
                                             class="img-fluid rounded"
                                             style="max-width: 80px; object-fit: cover;">
                                    </a>
                                </td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->variation->type ?? '-' }}: {{ $item->variation->variation_option ?? '-' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $symbol }} {{ number_format($item->price,2) }}</td>
                                <td>{{ optional($item->shippingProfile)->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ $symbol }} {{ number_format($item->shipping_cost ?? 0,2) }}</td>
                                <td class="text-end">{{ $symbol }} {{ number_format($subtotal,2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- PAYMENTS --}}
    @if($order->payments->isNotEmpty())
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                <i class="fa-solid fa-wallet"></i> Payments
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Paid On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->payments as $payment)
                            @php
                                $statusColor = match(strtolower($payment->status)) {
                                    'pending' => 'secondary',
                                    'success' => 'success',
                                    'failed'  => 'danger',
                                    default   => 'dark',
                                };
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment->local_transaction_id ?? 'N/A' }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td class="text-end">{{ $symbol }} {{ number_format($payment->total_amount,2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }} text-capitalize">
                                        {{ $payment->status }}
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
            <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
                <i class="fa-solid fa-info-circle"></i> Additional Information
            </div>
            <div class="card-body">
                @if($order->order_notes)
                    <p><strong>Order Notes:</strong> {{ $order->order_notes }}</p>
                @endif
                @if($order->promo_code)
                    <p><strong>Promo Code:</strong> {{ $order->promo_code }}</p>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- SHIPPING MODAL --}}
@if($order->status === \App\Models\Order::STATUS_PROCESSING)
    <div class="modal fade" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('seller.orders.ship', $order) }}"
                  method="POST"
                  class="modal-content needs-validation"
                  novalidate>
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="shipModalLabel">
                        <i class="fa-solid fa-truck-fast me-2"></i>
                        Ship Order #{{ $order->id }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="px-4 pt-3">
                    <div class="alert alert-info small mb-0">
                        <strong>Customer:</strong> {{ $order->full_name }} &nbsp;|&nbsp;
                        <strong>Total:</strong> {{ $symbol }} {{ number_format($order->total_amount,2) }}
                    </div>
                </div>

                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="courierSelect" name="courier" required>
                                    <option value="" disabled selected>Select courier…</option>
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
                                <input type="text"
                                       class="form-control"
                                       id="trackingInput"
                                       name="tracking_no"
                                       placeholder="ABC123"
                                       required>
                                <label for="trackingInput">Tracking number *</label>
                                <div class="invalid-feedback">Tracking number required.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date"
                                       class="form-control"
                                       id="shipDateInput"
                                       name="shipping_date"
                                       value="{{ now()->toDateString() }}">
                                <label for="shipDateInput">Shipping date</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <textarea class="form-control"
                                          id="shipNotes"
                                          name="ship_notes"
                                          style="height: 100px;"></textarea>
                                <label for="shipNotes">Notes (optional)</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3 fw-semibold">Items & Shipping Profiles</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th>Shipping Profile</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td>
                                            <input type="hidden"
                                                   name="order_items[{{ $item->id }}][id]"
                                                   value="{{ $item->id }}">
                                            <select name="order_items[{{ $item->id }}][shipping_profile_id]"
                                                    class="form-select">
                                                @foreach($item->product->shippingProfiles as $profile)
                                                    <option value="{{ $profile->id }}"
                                                        @selected($item->shipping_profile_id == $profile->id)>
                                                        {{ $profile->name }}
                                                        ({{ $symbol }} {{ number_format($profile->base_rate,2) }})
                                                    </option>
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
                        <i class="fa-solid fa-truck me-1"></i> Mark as Shipped
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
    // Auto-show ship modal if URL contains ?ship=1
    const params = new URLSearchParams(window.location.search);
    if (params.get('ship') === '1') {
        const modalEl = document.getElementById('shipModal');
        modalEl && new bootstrap.Modal(modalEl).show();
    }

    // Bootstrap validation
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>
@endpush
