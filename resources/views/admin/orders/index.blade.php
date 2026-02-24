@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">Orders</h2>
@endsection

@section('content')
<div class="content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Order ID, customer, email, phone"
                    >
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="all">All statuses</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="shop_id" class="form-label">Shop</label>
                    <select id="shop_id" name="shop_id" class="form-select">
                        <option value="">All shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ (string) request('shop_id') === (string) $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from" class="form-label">From</label>
                    <input type="date" id="from" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label for="to" class="form-label">To</label>
                    <input type="date" id="to" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Filtered Orders</h6>
                    <div class="display-6 fw-semibold">{{ number_format($summary['filteredCount'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Filtered Gross</h6>
                    <div class="display-6 fw-semibold">{{ get_currency() }} {{ number_format((float) ($summary['filteredGrossAmount'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Pending (Filtered)</h6>
                    <div class="display-6 fw-semibold">{{ number_format((int) ($summary['pendingCount'] ?? 0)) }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">In Transit (Filtered)</h6>
                    <div class="display-6 fw-semibold">{{ number_format((int) ($summary['inTransitCount'] ?? 0)) }}</div>
                    <small class="text-muted">Processing + Shipped</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Orders</h5>
            <small class="text-muted">
                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} results
            </small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Shop</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Placed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-semibold">#{{ $order->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $order->full_name ?: (optional($order->customer)->name ?? 'N/A') }}</div>
                                <small class="text-muted">{{ $order->email ?: (optional($order->customer)->email ?? 'N/A') }}</small>
                            </td>
                            <td>{{ optional($order->shop)->name ?? ('Shop #' . $order->shop_id) }}</td>
                            <td class="text-center">{{ (int) ($order->items_count ?? 0) }}</td>
                            <td class="text-end">{{ get_currency() }} {{ number_format((float) $order->total_amount, 2) }}</td>
                            <td>
                                @php $badge = method_exists($order, 'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-secondary'; @endphp
                                <span class="badge {{ $badge }} text-uppercase">{{ $order->status }}</span>
                                @if(($order->disputes_count ?? 0) > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $order->disputes_count }} dispute{{ ($order->disputes_count ?? 0) > 1 ? 's' : '' }}</span>
                                @endif
                            </td>
                            <td class="text-uppercase">{{ $order->payment_method ?: 'N/A' }}</td>
                            <td>
                                <div>{{ optional($order->created_at)->format('d M Y') }}</div>
                                <small class="text-muted">{{ optional($order->created_at)->format('H:i') }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No orders match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

