@extends('theme.'.theme().'.layouts.app')

@section('title', 'Product Details')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 mt-4">`r`n<div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="-span-6">
            <h2 class="mb-3">{{ $product->name }}</h2>
            <div class="mb-3">
                @php
                    $images = $product->productimages;
                @endphp
                @if($images->count())
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $images->first()->path) }}" alt="{{ $product->name }}" class="h-auto max-w-full rounded" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($images as $img)
                            <img src="{{ asset('storage/' . $img->storage_path) }}" alt="Thumbnail" class="rounded" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #27b105;">
                        @endforeach
                    </div>
                @else
                    <img src="{{ asset('images/default.jpg') }}" alt="No Image" class="h-auto max-w-full rounded" style="max-width: 100%; max-height: 350px; object-fit: cover;">
                @endif
            </div>
        </div>
        <div class="-span-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-4">
                    <h4 class="text-base font-bold text-slate-900 mb-3">Product Information</h4>
                    <p><strong>Price:</strong> <span class="text-primary">{{ $product->currency_code ?? '$' }} {{ number_format($product->price, 2) }}</span></p>
                    <p><strong>Stock:</strong> {{ $product->stock }}</p>
                    <p><strong>Status:</strong> 
                        @if($product->status)
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200">Inactive</span>
                        @endif
                    </p>
                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    <p><strong>Category:</strong> {{ $product->category->label ?? '-' }}</p>
                    <p><strong>Created At:</strong> {{ $product->created_at->format('d M Y') }}</p>
                    <p><strong>Description:</strong><br>{{ $product->description }}</p>
                    <div class="mt-4">
                        <a href="{{ route('products.details', $product->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Edit Product</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
      </div>
    </div>
  </div>
</section>
@endsection 





