{{-- resources/views/theme/{{ theme() }}/partials/_reviews.blade.php --}}

@if(isset($reviews) && $reviews->isNotEmpty())
  <section class="product-reviews mt-12">
    <h2 class="text-2xl font-semibold mb-6">Customer Reviews</h2>
    <div class="space-y-8">
      @foreach($reviews as $review)
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <div class="text-gray-800 font-medium">{{ $review->user->name }}</div>
            <div class="text-gray-500 text-sm">{{ $review->created_at->format('F j, Y') }}</div>
          </div>
          <div class="mb-4">
            @for($i = 1; $i <= 5; $i++)
              <span class="text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">&#9733;</span>
            @endfor
          </div>
          <p class="text-gray-700 leading-relaxed">{{ $review->comment }}</p>
        </div>
      @endforeach
    </div>
  </section>
@endif
