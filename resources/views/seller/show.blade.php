@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="container mt-4">
    <style>
        .text-primary, .btn-outline-primary, .bg-success, .btn-primary, .badge.bg-success {
            color: #fff !important;
            background-color: #27b105 !important;
            border-color: #27b105 !important;
        }
        .btn-outline-primary {
            color: #27b105 !important;
            background-color: #fff !important;
            border-color: #27b105 !important;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: #27b105 !important;
            color: #fff !important;
        }
        .badge.bg-success {
            background-color: #27b105 !important;
        }
    </style>
    <div class="row">
        <div class="col-md-6">
            <h2 class="mb-3">{{ $product->name }}</h2>
            <div class="mb-3">
                @php
                    $images = $product->productimages;
                @endphp
                @if($images->count())
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $images->first()->path) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($images as $img)
                            <img src="{{ asset('storage/' . $img->storage_path) }}" alt="Thumbnail" class="rounded" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #27b105;">
                        @endforeach
                    </div>
                @else
                    <img src="{{ asset('images/default.jpg') }}" alt="No Image" class="img-fluid rounded" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Product Information</h4>
                    <p><strong>Price:</strong> <span class="text-primary">{{ $product->currency_code ?? '$' }} {{ number_format($product->price, 2) }}</span></p>
                    <p><strong>Stock:</strong> {{ $product->stock }}</p>
                    <p><strong>Status:</strong> 
                        @if($product->status)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </p>
                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    <p><strong>Category:</strong> {{ $product->category->label ?? '-' }}</p>
                    <p><strong>Created At:</strong> {{ $product->created_at->format('d M Y') }}</p>
                    <p><strong>Description:</strong><br>{{ $product->description }}</p>
                    <div class="mt-4">
                        <a href="{{ route('products.details', $product->id) }}" class="btn btn-outline-primary">Edit Product</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
