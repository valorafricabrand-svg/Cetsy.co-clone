@extends('theme.'.theme().'.layouts.app')

@section('main')

<!-- Shop Reviews Header -->
<section class="py-5 bg-white border-bottom">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4 items-center">
      <div class="md:col-span-8">
        <nav class="text-xs text-slate-500" aria-label="Breadcrumb">
          <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('shop.show', $shop) }}" class="no-underline hover:text-slate-700">{{ $shop->name }}</a></li>
            <li>/</li>
            <li class="font-semibold text-slate-700" aria-current="page">Reviews</li>
          </ol>
        </nav>
        <h1 class="mb-1 mt-2 text-xl font-bold text-slate-900">Reviews for {{ $shop->name }}</h1>
        
        <!-- Rating Summary -->
        <div class="flex items-center gap-3 mt-3">
          <div class="flex items-center">
            @for($i = 1; $i <= 5; $i++)
              @if($i <= $averageRating)
                <i class="fas fa-star" style="font-size: 18px; color:#e5780b;"></i>
              @elseif($i - $averageRating < 1 && $i - $averageRating > 0)
                <i class="fas fa-star-half-alt" style="font-size: 18px; color:#e5780b;"></i>
              @else
                <i class="far fa-star" style="font-size: 18px; color:#000;"></i>
              @endif
            @endfor
          </div>
          <div>
            <span class="text-xl font-bold text-slate-900">{{ number_format($averageRating, 1) }}</span>
            <span class="text-slate-500">out of 5</span>
          </div>
          <div class="text-slate-500">
            {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}
          </div>
        </div>
      </div>
      
      <div class="md:col-span-4 md:text-right">
        <a href="{{ route('shop.show', $shop) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
          <i class="fas fa-arrow-left mr-1"></i> Back to Shop
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Reviews List -->
<section class="py-5 bg-slate-100">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    @if($reviews->isEmpty())
      <div class="text-center py-5">
        <i class="fas fa-star text-slate-500" style="font-size: 48px;"></i>
        <h3 class="mt-3">No Reviews Yet</h3>
        <p class="text-slate-500">This shop hasn't received any reviews yet. Be the first to leave a review!</p>
      </div>
    @else
      <div class="grid grid-cols-12 gap-4">
        <div class="lg:col-span-8">
          <!-- Reviews -->
          @foreach($reviews as $review)
            <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div class="p-4 sm:p-5">
                <div class="flex justify-between items-start mb-3">
                  <div class="flex items-center gap-3">
                    <div class="flex items-center">
                      @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->rating)
                          <i class="fas fa-star" style="font-size: 14px; color:#e5780b;"></i>
                        @else
                          <i class="far fa-star" style="font-size: 14px; color:#000;"></i>
                        @endif
                      @endfor
                    </div>
                    <div>
                      <strong>{{ $review->user ? $review->user->name : 'Anonymous User' }}</strong>
                      <div class="text-slate-500 text-xs">{{ $review->created_at->diffForHumans() }}</div>
                    </div>
                  </div>
                  
                  @if($review->orderItem && $review->orderItem->product)
                    @php
                      $product = $review->orderItem->product;
                      $thumbUrl = null;
                      if (!empty($product->featured_image)) {
                        $thumbUrl = str_starts_with($product->featured_image, 'http')
                          ? $product->featured_image
                          : asset('storage/' . ltrim($product->featured_image, '/'));
                      } elseif (method_exists($product, 'media')) {
                        try {
                          $firstImage = $product->media()->where('type','image')->first();
                          if ($firstImage) {
                            $thumbUrl = asset('storage/' . ltrim($firstImage->url,'/'));
                          }
                        } catch (\Throwable $e) {}
                      }
                    @endphp
                    <div class="flex flex-col items-end text-right">
                      <small class="text-slate-500">Reviewed item</small>
                      <div class="flex items-center mt-1">
                        @if($thumbUrl)
                          <a href="{{ route('listing.show', $product->slug ?? $product->id) }}" class="mr-2">
                            <img src="{{ $thumbUrl }}" alt="{{ $product->name }} thumbnail" style="width:56px;height:56px;object-fit:cover;border-radius:6px;">
                          </a>
                        @endif
                        <a href="{{ route('listing.show', $product->slug ?? $product->id) }}" 
                           class="no-underline text-emerald-600 text-xs">
                          {{ $product->name }}
                        </a>
                      </div>
                    </div>
                  @endif
                </div>
                
                @if($review->comment)
                  <p class="mb-2">{{ $review->comment }}</p>
                @else
                  <p class="text-slate-500 mb-2"><em>No comment provided</em></p>
                @endif

                @if(!empty($review->image_path))
                  <a href="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" target="_blank">
                    <img src="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" alt="Review image" style="max-width: 160px;max-height:160px;border-radius:8px;"/>
                  </a>
                @endif
              </div>
            </div>
          @endforeach
          
          <!-- Pagination -->
          @if($reviews->hasPages())
            <div class="flex justify-center">
              {{ $reviews->links('pagination::tailwind') }}
            </div>
          @endif
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-4">
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-white px-4 py-3 font-semibold text-slate-900">
              Shop Information
            </div>
            <div class="p-4 sm:p-5">
              <div class="flex items-center gap-3 mb-3">
                @if($shop->logo_url)
                  <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }} logo"
                       class="rounded-full"
                       style="width: 50px; height: 50px; object-fit: cover;">
                @endif
                <div>
                  <h6 class="mb-0">{{ $shop->name }}</h6>
                  <small class="text-slate-500">Owned by {{ $shop->user->name }}</small>
                </div>
              </div>
              
              @if($shop->bio)
                <p class="text-xs text-slate-500 mb-3">{{ Str::limit($shop->bio, 100) }}</p>
              @endif
              
              <a href="{{ route('shop.show', $shop) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 w-full">
                Visit Shop
              </a>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
</section>

@endsection 


