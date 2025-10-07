{{-- resources/views/seller/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Seller Dashboard')

@section('content')
@php
    $brandColor = optional(auth()->user()->shop)->primary_color;
    // Basic sanitize: accept hex colors (#RGB, #RRGGBB, #RRGGBBAA)
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        // Fallback if not set or invalid
        $brandColor = '#27b105';
    }
@endphp
<style>
    :root { --cetsy-green: {{ $brandColor }}; }

    /* Brand utilities */
    .brand-text         { color:var(--cetsy-green)!important; }
    .brand-bg           { background:var(--cetsy-green)!important; color:#fff!important; }
    .brand-outline      { color:var(--cetsy-green)!important; border-color:var(--cetsy-green)!important; }
    .brand-outline:hover,
    .brand-outline:focus{ background:var(--cetsy-green)!important; color:#fff!important; }

    /* Cards & effects */
    .card.hover-lift      { transition:transform .2s,box-shadow .2s; }
    .card.hover-lift:hover{ transform:translateY(-4px); box-shadow:0 .5rem 1rem rgba(0,0,0,.15); }

    /* Summary cards */
    .summary-card {
        border-radius: 1.25rem !important;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 1.5rem 1rem 1.2rem 1rem;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #fff;
    }
    .summary-card .icon { font-size: 2.1rem; margin-bottom: .5rem; color: var(--cetsy-green); }
    .summary-card .card-value { font-size: 1.45rem; font-weight: 600; margin-bottom: .2rem; line-height: 1.1; }
    .summary-card .card-label { font-size: .98rem; color: #6c757d; font-weight: 400; margin-top: .1rem; }
    .summary-card .card-value small { font-size: .95rem; font-weight: 400; }

    /* Misc */
    .quick-actions .btn { border-radius: 9999px; }
    .table-compact td, .table-compact th { padding: .5rem .6rem; }
    .rounded-4 { border-radius: 1rem !important; }
    /* Use brand color for header icons */
    .card-header i { color: var(--cetsy-green) !important; }
</style>

<div class="content">

    {{-- Header: shop info + quick actions --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center brand-bg" style="width:48px;height:48px;">
                <i class="fas fa-store"></i>
            </div>
            <div>
                <h2 class="h4 fw-semibold mb-0">Seller Dashboard</h2>
                <div class="text-muted small">
                    {{ optional(auth()->user()->shop)->name ?? 'Your Shop' }}
                    @isset($activeProducts)
                        • <span class="text-success">Active: {{ $activeProducts }}</span>
                    @endisset
                    @isset($pausedProducts)
                        • <span class="text-secondary">Paused: {{ $pausedProducts }}</span>
                    @endisset
                </div>
            </div>
        </div>
        <div class="quick-actions d-flex flex-wrap gap-2">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Listing
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-receipt me-1"></i> Orders
            </a>
            <a href="{{ route('seller.offers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-handshake me-1"></i> Offers
            </a>
            <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-wallet me-1"></i> Wallet
            </a>
            <a href="{{ route('seller.analytics.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-line me-1"></i> Analytics
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row gy-4 mb-5">

        {{-- Card template --}}
        @php
            $cards = [
                [
                    'value' => $total_orders,
                    'label' => 'Total Orders',
                    'icon'  => 'fas fa-credit-card',
                    'class' => 'text-warning',
                    'href'  => route('seller.orders.index')
                ],
                [
                    'value' => $total_products,
                    'label' => 'Total Listings',
                    'icon'  => 'fas fa-box-open',
                    'class' => 'text-info',
                    'href'  => route('products.index')
                ],
                [
                    'value' => get_currency().' '.number_format(wallet(),2) . "<small class='text-muted ms-1'>(On Hold: ".get_currency().' '.number_format(wallet('on_hold'),2) . ")</small>",
                    'label' => 'Wallet Balance',
                    'icon'  => 'fas fa-wallet',
                    'class' => 'text-success',
                    'href'  => route('wallet.index')
                ],
                [
                    'value' => $total_offers . "<small class='text-success ms-1' title='Accepted'>(".$accepted_offers." +)</small> <small class='text-danger ms-1' title='Declined'>(".$declined_offers." -)</small>",
                    'label' => 'Offers Received',
                    'icon'  => 'fas fa-handshake',
                    'class' => 'text-primary',
                    'href'  => route('seller.offers.index')
                ],
                [
                    'value' => $favorites_messages_total . "<small class='text-muted ms-1'>(7d: ".$favorites_messages_week.")</small>",
                    'label' => 'Messages from Favorites',
                    'icon'  => 'fas fa-comments',
                    'class' => 'text-secondary',
                    'href'  => route('seller.favorites.index')
                ],
            ];
        @endphp

        @foreach($cards as $c)
            <div class="col-6 col-md-3">
                <a href="{{ $c['href'] }}" class="text-decoration-none text-dark">
                    <div class="card summary-card hover-lift border-0">
                        <div class="icon {{ $c['class'] }}">
                            <i class="{{ $c['icon'] }}"></i>
                        </div>
                        <div class="card-value">{!! $c['value'] !!}</div>
                        <div class="card-label">{{ $c['label'] }}</div>
                    </div>
                </a>
            </div>
        @endforeach

    </div>

    {{-- Two-column: Recent Orders + Reviews/Quick Links --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center rounded-top-4">
                    <div class="fw-semibold"><i class="fas fa-receipt me-2 text-primary"></i>Recent Orders</div>
                    <a href="{{ route('seller.orders.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="card-body">
                    @if($orders->count())
                        <div class="table-responsive">
                            <table class="table table-compact align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $o)
                                        <tr>
                                            <td>#{{ $o->id }}</td>
                                            <td>{{ optional($o->customer)->name ?? '—' }}</td>
                                            <td>{{ get_currency() }} {{ number_format($o->total ?? ($o->total_amount ?? 0), 2) }}</td>
                                        <td>
                                            <span class="badge {{ $o->getStatusBadgeClass() }}">{{ ucfirst($o->status) }}</span>
                                            @php
                                                $minDays = null; $maxDays = null;
                                                foreach (($o->items ?? []) as $it) {
                                                    $sp = $it->shippingProfile;
                                                    $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                                                    $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                                                    if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin); }
                                                    if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax); }
                                                }
                                                // Use Carbon instance directly (avoid Optional wrapper in comparisons)
                                                $placedAt = $o->created_at instanceof \Carbon\Carbon ? $o->created_at : ($o->created_at ? \Carbon\Carbon::parse($o->created_at) : null);
                                                $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
                                                $shipEnd   = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
                                                $shipStartLabel = ($shipStart && $placedAt && $shipStart->isSameDay($placedAt)) ? 'today' : ($shipStart ? $shipStart->format('M j') : null);
                                                $shipEndLabel   = ($shipEnd && $placedAt && $shipEnd->isSameDay($placedAt)) ? 'today' : ($shipEnd ? $shipEnd->format('M j') : null);
                                            @endphp
                                            @php $dispatchBy = $shipEndLabel ?? $shipStartLabel; @endphp
                                            <div class="small text-muted mt-1">
                                                @if($dispatchBy)
                                                    Dispatch by {{ $dispatchBy }}
                                                @else
                                                    Dispatch soon
                                                @endif
                                            </div>
                                        </td>
                                            <td class="text-muted small">{{ optional($o->created_at)->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted small">No recent orders.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 mb-4">
                <div class="card-header bg-white rounded-top-4 fw-semibold d-flex align-items-center gap-2">
                    <i class="fas fa-star text-warning"></i> Recent Reviews
                    <a href="{{ route('seller.reviews.index') }}" class="small ms-auto text-decoration-none">View all</a>
                </div>
                <div class="card-body">
                    @if(isset($recentReviews) && $recentReviews->count())
                        <ul class="list-group list-group-flush">
                            @foreach($recentReviews as $r)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ optional($r->orderItem?->product)->name ?? 'Product' }}</div>
                                            <div class="small text-muted">Order #{{ $r->order_id }} 
                                                • Rated: {{ $r->rating }} / 5
                                            </div>
                                            @if($r->comment)
                                                <div class="small mt-1">{{ \Illuminate\Support\Str::limit($r->comment, 120) }}</div>
                                            @endif
                                        </div>
                                        <div class="ms-3">
                                            <a href="{{ route('orders.chat.show', $r->order_id) }}" class="btn btn-sm btn-outline-primary">Respond</a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted small">No reviews yet.</div>
                    @endif
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white rounded-top-4 fw-semibold">
                    <i class="fas fa-bolt me-2 text-warning"></i>Quick Links
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a class="btn btn-outline-primary" href="{{ route('products.index') }}"><i class="fas fa-boxes-stacked me-2"></i> Manage Listings</a>
                        <a class="btn btn-outline-primary" href="{{ route('seller.analytics.index') }}"><i class="fas fa-chart-line me-2"></i> View Analytics</a>
                        <a class="btn btn-outline-primary" href="{{ route('seller.payouts.index') }}"><i class="fas fa-money-bill-transfer me-2"></i> Payouts</a>
                        <a class="btn btn-outline-primary" href="{{ route('seller.messages.index') }}"><i class="fas fa-comments me-2"></i> Messages</a>
                        <a class="btn btn-outline-primary" href="{{ route('seller.buyers.index') }}"><i class="fas fa-users me-2"></i> Buyers</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash success --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

</div>

@endsection
