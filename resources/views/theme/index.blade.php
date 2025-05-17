@extends('layouts.frontapp')

@section('content')
    <!-- Hero -->
    <div class="relative bg-cover bg-center h-96" style="background-image:url('{{ asset('images/hero.jpg') }}')">
        <div class="absolute inset-0 bg-green-600 bg-opacity-60 flex items-center justify-center">
            <div class="text-center px-4">
                <h1 class="text-5xl font-extrabold text-white sm:text-6xl">Discover Handmade Treasures</h1>
                <p class="mt-4 text-lg text-green-100">Unique finds from small shops all around the world.</p>
                <a href="{{ auth()->check() ? route('shops.create') : route('register') }}"
                   class="mt-8 inline-block bg-white text-green-600 font-semibold px-8 py-3 rounded-full shadow-lg hover:bg-green-50 transition">
                    {{ auth()->check() ? 'Open Your Shop' : 'Join Cetsy' }}
                </a>
            </div>
        </div>
    </div>

   <!-- Trending Categories -->
  <section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-gray-800 mb-8">Trending Categories</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-6">
        @foreach($categories as $cat)
          <a href="{{ route('categories.show', $cat) }}" class="group block">
            <div class="h-32 bg-gray-100 rounded-lg overflow-hidden">
              @if($cat->image)
                <img src="{{ asset('storage/'.$cat->image) }}"
                     alt="{{ $cat->name }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              @else
                <div class="flex items-center justify-center h-full">
                  <span class="text-gray-700 font-medium">{{ $cat->name }}</span>
                </div>
              @endif
            </div>
            <p class="mt-2 text-center text-sm font-medium text-gray-800 group-hover:text-green-600">{{ $cat->name }}</p>
          </a>
        @endforeach
      </div>
    </div>
  </section>

<!-- Featured Products -->
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Featured for You</h2>
            <a href="{{ route('products.index') }}" class="text-green-600 hover:underline font-medium">
                See All Products
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach($featuredProducts as $product)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                    <a href="{{ route('products.show', $product) }}">
                        @if($img = $product->media->first())
                            <img src="{{ asset('storage/'.$img->url) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200"></div>
                        @endif
                    </a>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>
                        <p class="mt-2 text-green-600 font-bold">KES {{ number_format($product->price,2) }}</p>
                        <div class="mt-3 flex justify-between items-center">
                            <a href="{{ route('listing.show', $product->slug) }}"
                               class="text-sm text-gray-500 hover:underline">
                                View Details
                            </a>
                            
                            <button class="bg-green-600 text-white p-2 rounded-full hover:bg-green-700 transition">
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
    </div>
</section>

@endsection
