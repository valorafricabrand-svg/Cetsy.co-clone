{{-- resources/views/listings/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

{{-- SEO-friendly page title --}}
@section('title', 'Marketplace – Products, Services & Digital Goods')

@section('main')
    {{-- ────────── Header / Hero ────────── --}}
    <section class="bg-success text-white py-5">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-2 text-white">All Listings</h1>
            <p class="lead mb-0">
                Browse our global marketplace for physical products, professional services, and instant digital downloads.
            </p>
        </div>
    </section>

    {{-- ────────── Product Grid ────────── --}}
    <section class="pb-5 bg-light">
        <div class="container">
            <div class="row g-4">
                @forelse ($products as $item)
                  <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
                @empty
                    {{-- Empty state --}}
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fas fa-box-open fa-2x mb-3"></i>
                        <p class="mb-0">No listings available right now. Please check back soon!</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($products->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>
@endsection
