{{-- resources/views/products/show.blade.php --}}
@extends('layouts.frontapp')

@section('content')
<div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

    {{-- Image gallery --}}
    <div x-data="{ 
          mainImage: '{{ $product->media->first() ? asset('storage/'.$product->media->first()->url) : '' }}' 
        }"
         class="space-y-4">
      <template x-if="mainImage">
        <img 
          :src="mainImage" 
          alt="{{ $product->name }}" 
          class="object-cover w-full h-96 rounded-lg"
        />
      </template>
      @if($product->media->count())
        <div class="grid grid-cols-4 gap-2">
          @foreach($product->media as $media)
            <img 
              @click="mainImage='{{ asset('storage/'.$media->url) }}'" 
              src="{{ asset('storage/'.$media->url) }}"
              alt=""
              class="object-cover w-full h-20 rounded-lg cursor-pointer hover:opacity-75 transition"
            />
          @endforeach
        </div>
      @endif
    </div>

    {{-- Details --}}
    <div>
      <h1 class="text-3xl font-extrabold text-neutral-900">{{ $product->name }}</h1>
      <p class="mt-2 text-2xl text-primary font-semibold">
        KES {{ number_format($product->price, 2) }}
      </p>

      <div class="mt-6 space-y-4 text-gray-700">
        <p>{{ $product->description }}</p>

        <div>
          <span class="font-medium">Category:</span>
          @if($product->category)
            <a 
              href="{{ route('categories.show', $product->category) }}" 
              class="text-primary hover:underline"
            >
              {{ $product->category->name }}
            </a>
          @else
            <span class="italic">Uncategorized</span>
          @endif
        </div>

        <div>
          <span class="font-medium">Shop:</span>
          <a 
            href="{{ route('shops.show', $product->shop) }}" 
            class="text-primary hover:underline"
          >
            {{ $product->shop->name }}
          </a>
        </div>
      </div>

      {{-- Add to Cart (placeholder) --}}
      <form 
        action="{{ route('cart.index') }}" 
        method="GET" 
        class="mt-8 flex items-center space-x-4"
      >
        <label for="quantity" class="sr-only">Quantity</label>
        <input 
          type="number" 
          name="quantity" 
          id="quantity" 
          value="1" 
          min="1"
          class="w-20 border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary"
        />
        <button 
          type="submit"
          class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition"
        >
          Add to Cart
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
