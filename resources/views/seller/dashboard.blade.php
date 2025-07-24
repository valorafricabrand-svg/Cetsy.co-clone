{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Seller Dashboard')

@section('content')
<style>
    :root { --cetsy-green:#27b105; }

    /* Brand utilities */
    .brand-text         { color:var(--cetsy-green)!important; }
    .brand-bg           { background:var(--cetsy-green)!important; color:#fff!important; }
    .brand-outline      { color:var(--cetsy-green)!important; border-color:var(--cetsy-green)!important; }
    .brand-outline:hover,
    .brand-outline:focus{ background:var(--cetsy-green)!important; color:#fff!important; }

    /* Cards & effects */
    .card.hover-lift      { transition:transform .2s,box-shadow .2s; }
    .card.hover-lift:hover{ transform:translateY(-4px); box-shadow:0 .5rem 1rem rgba(0,0,0,.15); }

    .status-badge{
        position:absolute;top:.5rem;left:.5rem;
        font-size:.75rem;padding:.15rem .5rem;
        border-radius:.25rem;background:#ffc107;color:#000;
    }

    /* Improved summary card styles */
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
    .summary-card .icon {
        font-size: 2.1rem;
        margin-bottom: 0.5rem;
    }
    .summary-card .card-value {
        font-size: 1.45rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        line-height: 1.1;
    }
    .summary-card .card-label {
        font-size: 0.98rem;
        color: #6c757d;
        font-weight: 400;
        margin-top: 0.1rem;
    }
    .summary-card .card-value small {
        font-size: 0.95rem;
        font-weight: 400;
    }
</style>

<div class="content">

    {{-- ───────── Header ───────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-semibold mb-0">General Report</h2>
        @if($isHolidayMode)
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#disableHolidayModeModal">
                <i class="fas fa-play me-2"></i>Disable Holiday Mode
            </button>
        @else
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#enableHolidayModeModal">
                <i class="fas fa-umbrella-beach me-2"></i>Enable Holiday Mode
            </button>
        @endif
  


     <div class="d-flex justify-content-between align-items-center mb-4">
        
        <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add New Listing
        </a>
    </div>
    </div>

    {{-- ───────── Summary Cards ───────── --}}
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
                    'value' => get_currency().number_format(wallet(),2),
                    'label' => 'Wallet Balance',
                    'icon'  => 'fas fa-wallet',
                    'class' => 'text-success',
                    'href'  => route('wallet.index')
                ],
                [
                    'value' => $total_offers . "<small class='text-success ms-1' title='Accepted'>(".$accepted_offers." ✓)</small> <small class='text-danger ms-1' title='Declined'>(".$declined_offers." ✗)</small>",
                    'label' => 'Offers Received',
                    'icon'  => 'fas fa-handshake',
                    'class' => 'text-primary',
                    'href'  => route('seller.offers.index')
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

    {{-- Flash success --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ───────── Latest Listings ───────── --}}
    <h4 class="h5 fw-semibold mb-3">Latest Listings</h4>

    @if($products->count())
        <div class="row g-4">
            @foreach($products as $product)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift position-relative">

                        {{-- Inactive & pay badge --}}
                           @php
          switch($product->is_active) {
            case 0: $label='Pending'; $class='warning'; break;
            case 1: $label='Active';  $class='success'; break;
            case 2: $label='Paused';  $class='secondary'; break;
            default:$label='Closed';  $class='dark'; break;
          }
        @endphp
        <span class="badge bg-{{ $class }} text-white position-absolute top-0 start-0 m-2">{{ $label }}</span>

                        {{-- Image --}}
                        @if($img = $product->media->first())
                            <img src="{{ asset('storage/'.$img->url) }}"
                                 class="card-img-top rounded-top-4"
                                 style="height:220px;object-fit:cover;" alt="{{ $product->name }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="height:220px;">
                                <span class="text-muted">No Image</span>
                            </div>
                        @endif

                        {{-- Body --}}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate">{{ $product->name }}</h5>
                            <p class="text-muted small mb-2">
                                {{ ucfirst($product->type) }}
                                @if(!is_null($product->stock)) • Stock {{ $product->stock }} @endif
                            </p>

                            {{-- Price --}}
                            <p class="fw-bold mb-3">
                                @if($product->discount_price)
                                    <span class="text-danger me-1">{{ get_currency() }} {{ number_format($product->discount_price) }}</span>
                                    <small class="text-muted text-decoration-line-through">
                                        {{ get_currency() }} {{ number_format($product->price) }}
                                    </small>
                                @else
                                    {{ get_currency() }} {{ number_format($product->price) }}
                                @endif
                            </p>

                            {{-- Buttons --}}
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('products.show',$product) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                    View
                                </a>
                                <a href="{{ route('products.edit',$product) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

       
    @else
        <div class="alert alert-info rounded-3 text-center py-4">
            You haven’t listed any products yet.
            <div class="mt-2">
                <a href="{{ route('products.create') }}" class="btn btn-sm brand-bg rounded-pill">
                    <i class="fas fa-plus-circle me-1"></i> Create Your First Product
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Enable Holiday Mode Modal --}}
<div class="modal fade" id="enableHolidayModeModal" tabindex="-1" aria-labelledby="enableHolidayModeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enableHolidayModeModalLabel">
                    <i class="fas fa-umbrella-beach text-warning me-2"></i>Enable Holiday Mode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">This action will pause all your active products, effectively putting your shop in holiday mode.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> All active products will be paused and won't be visible to buyers until you manually reactivate them.
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    You can reactivate individual products from your product listings page.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('seller.holiday-mode.enable') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-umbrella-beach me-2"></i>Enable Holiday Mode
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Disable Holiday Mode Modal --}}
<div class="modal fade" id="disableHolidayModeModal" tabindex="-1" aria-labelledby="disableHolidayModeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disableHolidayModeModalLabel">
                    <i class="fas fa-play text-success me-2"></i>Disable Holiday Mode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">This action will reactivate all your paused products, bringing your shop back online.</p>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Great!</strong> All paused products will be reactivated and visible to buyers again.
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Your shop will be fully operational once holiday mode is disabled.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('seller.holiday-mode.disable') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play me-2"></i>Disable Holiday Mode
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
