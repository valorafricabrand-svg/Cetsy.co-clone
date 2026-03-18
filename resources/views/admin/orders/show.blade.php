@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">Order #{{ $order->id }}</h2>
@endsection

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h5 mb-0">Order Details</h3>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Shipping</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                @php
                                    $lineSubtotal = ((float) $item->price) * ((int) $item->quantity);
                                    $lineShipping = (float) ($item->shipping_cost ?? 0);
                                    $lineTotal = $lineSubtotal + $lineShipping;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ optional($item->product)->name ?? ('Product #' . $item->product_id) }}</div>
                                        @if(!empty($item->variation_summary))
                                            <small class="text-muted">{{ $item->variation_summary }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ (int) $item->quantity }}</td>
                                    <td class="text-end">{{ get_currency() }} {{ number_format((float) $item->price, 2) }}</td>
                                    <td class="text-end">{{ get_currency() }} {{ number_format($lineShipping, 2) }}</td>
                                    <td class="text-end fw-semibold">{{ get_currency() }} {{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No items on this order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    @php $badge = method_exists($order, 'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-secondary'; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge {{ $badge }} text-uppercase">{{ $order->status }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>{{ get_currency() }} {{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span>{{ get_currency() }} {{ number_format((float) ($order->shipping_cost ?? 0), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total</span>
                        <span class="fw-semibold">{{ get_currency() }} {{ number_format((float) $order->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Method</span>
                        <span>{{ payment_method_label($order->payment_method, 'N/A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Placed</span>
                        <span>{{ optional($order->created_at)->format('d M Y H:i') }}</span>
                    </div>
                    @if(!empty($order->tracking_no))
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tracking #</span>
                            <span>{{ $order->tracking_no }}</span>
                        </div>
                    @endif
                    @if(!empty($order->tracking_url))
                        <div class="mt-2">
                            <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-truck me-1"></i> Open Tracking Link
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Customer & Shop</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small text-uppercase">Customer</div>
                        <div class="fw-semibold">{{ $order->full_name ?: (optional($order->customer)->name ?? 'N/A') }}</div>
                        <div>{{ $order->email ?: (optional($order->customer)->email ?? 'N/A') }}</div>
                        <div>{{ $order->phone ?: (optional($order->customer)->phone ?? 'N/A') }}</div>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Shop</div>
                        <div class="fw-semibold">{{ optional($order->shop)->name ?? ('Shop #' . $order->shop_id) }}</div>
                        <div>{{ optional(optional($order->shop)->user)->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Addresses</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small text-uppercase">Shipping</div>
                        <div>{{ $order->shipping_address_1 }}</div>
                        @if(!empty($order->shipping_address_2))
                            <div>{{ $order->shipping_address_2 }}</div>
                        @endif
                        <div>{{ $order->shipping_city }}{{ !empty($order->shipping_state) ? ', ' . $order->shipping_state : '' }} {{ $order->shipping_postal_code }}</div>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Billing</div>
                        @if((bool) $order->billing_same_as_shipping)
                            <div class="text-muted">Same as shipping address.</div>
                        @else
                            <div>{{ $order->billing_address_1 }}</div>
                            @if(!empty($order->billing_address_2))
                                <div>{{ $order->billing_address_2 }}</div>
                            @endif
                            <div>{{ $order->billing_city }}{{ !empty($order->billing_state) ? ', ' . $order->billing_state : '' }} {{ $order->billing_postal_code }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($order->disputes->isNotEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Related Disputes</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->disputes as $dispute)
                            <tr>
                                <td>#{{ $dispute->id }}</td>
                                <td><span class="badge bg-warning text-dark text-uppercase">{{ $dispute->status }}</span></td>
                                <td>{{ optional($dispute->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
