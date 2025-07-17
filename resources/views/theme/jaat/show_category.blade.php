{{-- resources/views/categories/show.blade.php --}}
@extends('layouts.frontapp')

{{-- Optional SEO title --}}
@section('title', $category->name . ' – Marketplace Category')

@section('main')
{{-- ────────── Category Banner ────────── --}}
<div class="position-relative bg-cover bg-center"
style="background-image:url('{{ $category->image ? asset('storage/' . $category->image) : asset('assets/img/default-category.jpg') }}'); height:300px;">
<div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-75 d-flex align-items-center justify-content-center">
    <div class="text-center text-white px-3">
        <h1 class="display-5 fw-bold text-white">{{ $category->name }}</h1>
        <p class="lead mb-0">
          {{ $category->description 
          ?? 'Explore a wide range of ' . $category->name .' '.$category->listing_type }}
      </p>

  </div>
</div>
</div>

{{-- ────────── Products Grid ────────── --}}
<section class="py-5 bg-light">
    <div class="container">

        {{-- Header with “Browse all” link --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold mb-0">Listings in {{ $category->name }}</h2>
            <a href="{{ route('products.index') }}" class="text-success text-decoration-none">
                Browse All Listings
            </a>
        </div>

        @if ($products->count())
        <div class="row g-4">
            @foreach ($products as $item)
            
   <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>
@endforeach
</div>

{{-- Pagination --}}
@if ($products->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $products->links('pagination::bootstrap-5') }}
</div>
@endif
@else
{{-- Empty State --}}
<div class="alert alert-info text-center">
    No listings found in this category.
</div>
@endif
</div>
</section>
@endsection
