{{-- resources/views/categories/show.blade.php --}}
@extends('theme.layouts.main')

@section('main')
  <!-- Category Banner -->
  <div class="relative bg-cover bg-center h-64"
       style="background-image: url('{{ $category->image
            ? asset('storage/' . $category->image)
            : asset('images/default-category.jpg') }}')">
    <div class="absolute inset-0 bg-green-700 bg-opacity-60 flex items-center justify-center">
      <div class="text-center text-white px-4">
        <h1 class="text-4xl font-bold">{{ $category->name }}</h1>
        <p class="mt-2 text-green-100">
          {{ $category->description  
             ?? 'Explore unique handmade treasures in this category.' }}
        </p>
      </div>
    </div>
  </div>

  <!-- Products Grid -->
  <section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-gray-800">
          Products in {{ $category->name }}
        </h2>
        <a href="{{ route('products.index') }}"
           class="text-green-600 hover:underline font-medium">
          Browse All Products
        </a>
      </div>

      @if($products->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
          @foreach($products as $product)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
              <a href="{{ route('products.show', $product) }}">
                @if($img = $product->media->first())
                  <img src="{{ asset('storage/' . $img->url) }}"
                       alt="{{ $product->name }}"
                       class="w-full h-48 object-cover">
                @else
                  <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400">No Image</span>
                  </div>
                @endif
              </a>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>
                <p class="mt-2 text-green-600 font-bold">
                  KES {{ number_format($product->price, 2) }}
                </p>
                <div class="mt-3 flex justify-between items-center">
                  <a href="{{ route('products.show', $product) }}"
                     class="text-sm text-gray-500 hover:underline">
                    View Details
                  </a>
                  <button
                    @click="addToCart({{ $product->id }}, 1)"
                    class="bg-green-600 text-white p-2 rounded-full hover:bg-green-700 transition"
                    aria-label="Add {{ $product->name }} to cart"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 
                               13L5.4 5M7 13l-2 5m5-5v5m4-5v5m1-10h2"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-gray-600">No products found in this category.</p>
      @endif
    </div>
  </section>
@endsection
