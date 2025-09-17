@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $currency = get_currency();
    $statusCollection = collect($statusBreakdown ?? []);
    $statusLabels = $statusCollection->keys()->values()->all();
    $statusValues = $statusCollection->values()->values()->all();
    $payoutCollection = collect($payoutStatusBreakdown ?? []);
    $payoutLabels = $payoutCollection->keys()->values()->all();
    $payoutValues = $payoutCollection->values()->values()->all();
    $trendLabelsArray = collect($trendLabels ?? [])->values()->all();
    $trendRevenueArray = collect($trendRevenue ?? [])->values()->all();
    $trendOrdersArray = collect($trendOrders ?? [])->values()->all();
    $ordersIndexRoute = Route::has('admin.orders.index') ? route('admin.orders.index') : (Route::has('orders.index') ? route('orders.index') : '#');
@endphp
<div class="content">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1">Business Performance Overview</h1>
            <p classs="text-muted mb-0">Real-time insight across revenue, orders, sellers, customers, and payouts.</p>
        </div>
        <div class="text-lg-end">
            <span class="badge bg-primary text-white fw-semibold py-2 px-3">Updated {{ now()->format('d M Y, H:i') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-uppercase text-muted fw-semibold mb-0">Gross Revenue</h6>
                        @php $revDelta = $metrics['revenueGrowthPct'] ?? null; @endphp
                        <span class="badge {{ is_null($revDelta) ? 'bg-secondary' : ($revDelta >= 0 ? 'bg-success' : 'bg-danger') }} text-white">
                            @if(is_null($revDelta))
                                N/A
                            @else
                                {{ $revDelta >= 0 ? '+' : '' }}{{ $revDelta }}%
                            @endif
                        </span>
                    </div>
                    <div class="display-6 fw-semibold">{{ $currency }} {{ number_format($metrics['totalRevenue'] ?? 0, 2) }}</div>
                    <small class="text-muted">{{ $currency }} {{ number_format($metrics['revenueToday'] ?? 0, 2) }} today</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-uppercase text-muted fw-semibold mb-0">Orders</h6>
                        @php $orderDelta = $metrics['ordersGrowthPct'] ?? null; @endphp
                        <span class="badge {{ is_null($orderDelta) ? 'bg-secondary' : ($orderDelta >= 0 ? 'bg-success' : 'bg-danger') }} text-white">
                            @if(is_null($orderDelta))
                                N/A
                            @else
                                {{ $orderDelta >= 0 ? '+' : '' }}{{ $orderDelta }}%
                            @endif
                        </span>
                    </div>
                    <div class="display-6 fw-semibold">{{ number_format($metrics['ordersTotal'] ?? 0) }}</div>
                    <small class="text-muted">{{ number_format($metrics['ordersThisMonth'] ?? 0) }} this month</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Average Order Value</h6>
                    <div class="display-6 fw-semibold">{{ $currency }} {{ number_format($metrics['averageOrderValue'] ?? 0, 2) }}</div>
                    <small class="text-muted">Based on {{ number_format($metrics['fulfilledOrders'] ?? 0) }} fulfilled orders</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Seller Network</h6>
                    <div class="display-6 fw-semibold">{{ number_format($metrics['activeSellers'] ?? 0) }}</div>
                    <small class="text-muted">{{ number_format($metrics['newSellersThisMonth'] ?? 0) }} joined this month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Customers</h6>
                    <div class="h3 fw-semibold mb-1">{{ number_format($metrics['activeCustomers'] ?? 0) }}</div>
                    <small class="text-muted">{{ number_format($metrics['newCustomers30'] ?? 0) }} new in the last 30 days</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Listings</h6>
                    <div class="h3 fw-semibold mb-1">{{ number_format($metrics['activeListings'] ?? 0) }}</div>
                    <small class="text-muted">{{ number_format($metrics['totalListings'] ?? 0) }} total products</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Shops</h6>
                    <div class="h3 fw-semibold mb-1">{{ number_format($metrics['shopsTotal'] ?? 0) }}</div>
                    <small class="text-muted">Active storefronts onboarding and selling</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-semibold mb-2">Payout Exposure</h6>
                    <div class="h3 fw-semibold mb-1">{{ $currency }} {{ number_format($metrics['payoutsPendingTotal'] ?? 0, 2) }}</div>
                    <small class="text-muted">{{ $currency }} {{ number_format($metrics['payoutsPaid30'] ?? 0, 2) }} paid in the last 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Revenue &amp; Orders</h5>
                        <small class="text-muted">Trailing 12 months</small>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueOrdersChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Status (30 days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="180"></canvas>
                </div>
            </div>
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Payout Mix (30 days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="payoutStatusChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                    <a href="{{ $ordersIndexRoute }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Shop</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Placed</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="fw-semibold">#{{ $order->id }}</td>
                                <td>{{ optional($order->customer)->name ?? 'Customer #'.$order->user_id }}</td>
                                <td>{{ optional($order->shop)->name ?? 'Shop #'.$order->shop_id }}</td>
                                <td class="text-end">{{ $currency }} {{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @php $badge = method_exists($order, 'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-secondary'; @endphp
                                    <span class="badge {{ $badge }} text-uppercase">{{ $order->status }}</span>
                                </td>
                                <td>{{ $order->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No orders recorded yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pending Payouts</h5>
                    <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Seller</th>
                                <th class="text-end">Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($pendingPayouts as $payout)
                            <tr>
                                <td>{{ optional($payout->user)->name ?? 'User #'.$payout->user_id }}</td>
                                <td class="text-end">{{ $currency }} {{ number_format($payout->amount, 2) }}</td>
                                <td>{{ optional(optional($payout->paymentMethod)->paymentType)->name ?? '�' }}</td>
                                <td><span class="badge bg-warning text-dark text-uppercase">{{ $payout->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No payouts waiting on approval.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Top Products (30 days)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-end">Units</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($topProducts as $index => $product)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    @if(Route::has('products.show') && !empty($product['slug']))
                                        <a href="{{ route('products.show', $product['slug']) }}" class="text-decoration-none">{{ $product['name'] }}</a>
                                    @else
                                        {{ $product['name'] }}
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($product['units']) }}</td>
                                <td class="text-end">{{ $currency }} {{ number_format($product['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No product sales recorded in the last 30 days.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Top Shops (30 days)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Shop</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($topShops as $index => $shop)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>{{ $shop['name'] }}</td>
                                <td class="text-end">{{ number_format($shop['orders']) }}</td>
                                <td class="text-end">{{ $currency }} {{ number_format($shop['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No shop revenue recorded in the last 30 days.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" integrity="sha384-p7gMp8BxBPJesBOXEENabSJXvz2ped6SghmbupOr5KoVnLIev9EKf48xj+YrnLhF" crossorigin="anonymous"></script>
<script>
    const trendLabels = @json($trendLabelsArray);
    const revenueSeries = @json($trendRevenueArray);
    const orderSeries = @json($trendOrdersArray);
    const orderStatusLabels = @json($statusLabels);
    const orderStatusValues = @json($statusValues);
    const payoutStatusLabels = @json($payoutLabels);
    const payoutStatusValues = @json($payoutValues);

    const palette = [
        '#0d6efd', '#198754', '#6610f2', '#fd7e14', '#adb5bd', '#f03e3e', '#20c997', '#6c757d'
    ];

    const revenueOrdersCtx = document.getElementById('revenueOrdersChart');
    if (revenueOrdersCtx) {
        new Chart(revenueOrdersCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenueSeries,
                        borderColor: palette[0],
                        backgroundColor: 'rgba(13, 110, 253, 0.15)',
                        borderWidth: 3,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: orderSeries,
                        borderColor: palette[1],
                        backgroundColor: 'rgba(25, 135, 84, 0.15)',
                        borderWidth: 3,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        ticks: { callback: value => new Intl.NumberFormat().format(value) },
                        grid: { drawOnChartArea: false }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        ticks: { callback: value => new Intl.NumberFormat().format(value) },
                        grid: { drawOnChartArea: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: orderStatusLabels,
                datasets: [{
                    data: orderStatusValues,
                    backgroundColor: palette,
                    borderWidth: 0
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    const payoutStatusCtx = document.getElementById('payoutStatusChart');
    if (payoutStatusCtx) {
        new Chart(payoutStatusCtx, {
            type: 'doughnut',
            data: {
                labels: payoutStatusLabels,
                datasets: [{
                    data: payoutStatusValues,
                    backgroundColor: palette,
                    borderWidth: 0
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }
</script>
@endpush
