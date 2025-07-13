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
</style>

<div class="content">

    {{-- ───────── Header ───────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-semibold mb-0">General Report</h2>
      
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
                    'value' => 0,
                    'label' => 'Active / Inactive',
                    'icon'  => 'fas fa-toggle-on',
                    'class' => 'text-primary',
                    'href'  => route('products.index')
                ],
            ];
        @endphp

        @foreach($cards as $c)
            <div class="col-6 col-md-3">
                <a href="{{ $c['href'] }}" class="text-decoration-none text-dark">
                    <div class="card hover-lift shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="{{ $c['icon'] }} fa-xl {{ $c['class'] }}"></i>
                            </div>
                            <div class="fs-4 fw-semibold">{{ $c['value'] }}</div>
                            <div class="text-muted small">{{ $c['label'] }}</div>
                        </div>
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
                        @if(! $product->is_active)
                            <span class="status-badge">Inactive</span>
                            @php $fee = $product->category->listing_fee ?? 0; @endphp
                            <form method="POST" action="{{ route('products.pay-fee',$product) }}"
                                  class="position-absolute end-0 bottom-0 m-2">
                                @csrf
                                <button class="btn btn-sm brand-bg shadow-sm">
                                    Pay {{ get_currency() }} {{ number_format($fee,2) }}
                                </button>
                            </form>
                        @endif

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
@endsection
