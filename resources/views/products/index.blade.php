{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">My Listings</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add New Listing
        </a>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Status‐counts bar --}}
    <div class="mb-4">
        @php
            $labels = [
                0        => 'Pending',
                1        => 'Active',
                2        => 'Paused',
                3        => 'Suspended',
                'closed' => 'Closed',
            ];
            $classes = [
                0        => 'warning',
                1        => 'success',
                2        => 'secondary',
                3        => 'secondary',
                'closed' => 'dark',
            ];
        @endphp

        @foreach($labels as $key => $label)
            <a
                href="{{ route('products.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                class="btn btn-{{ $classes[$key] }} btn-sm me-2"
            >
                {{ $label }}
                <span class="badge bg-light text-dark ms-1">
                    {{ $statusCounts[$key] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    @if($products->count())
        @php
            $grouped = isset($groupedProducts) && $groupedProducts instanceof \Illuminate\Support\Collection
                ? $groupedProducts
                : $products->getCollection()->groupBy(function ($item) {
                    return $item->type ?? 'other';
                });

            $sectionMeta = [
                'physical' => ['title' => 'Products', 'icon' => 'fa-box-open'],
                'service'  => ['title' => 'Services', 'icon' => 'fa-briefcase'],
                'digital'  => ['title' => 'Digital Downloads', 'icon' => 'fa-cloud-arrow-down'],
            ];
        @endphp

        @foreach($sectionMeta as $type => $meta)
            @php $items = $grouped->get($type, collect()); @endphp
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas {{ $meta['icon'] }} text-muted"></i>
                        <h4 class="mb-0">{{ $meta['title'] }}</h4>
                    </div>
                    <span class="badge bg-light text-dark">{{ $items->count() }}</span>
                </div>

                @if($items->isEmpty())
                    <div class="alert alert-light border rounded-3 text-muted mb-0">
                        No {{ strtolower($meta['title']) }} on this page.
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($items as $product)
                            @include('products.partials.listing-card', ['product' => $product])
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        @foreach($grouped as $type => $items)
            @continue(array_key_exists($type, $sectionMeta) || $items->isEmpty())
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="mb-0 text-capitalize">{{ $type }} Listings</h4>
                    <span class="badge bg-light text-dark">{{ $items->count() }}</span>
                </div>

                <div class="row g-4">
                    @foreach($items as $product)
                        @include('products.partials.listing-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info rounded-3 text-center py-4">
            You haven’t listed any products yet.
            <div class="mt-2">
                <a href="{{ route('products.create') }}" class="btn btn-sm btn-success rounded-pill">
                    <i class="fas fa-plus-circle me-1"></i> Create Your First Product
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
