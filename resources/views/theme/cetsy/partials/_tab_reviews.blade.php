{{-- resources/views/theme/{{ theme() }}/partials/_tab_reviews.blade.php --}}
<div class="listing-tab-pane hidden" id="reviews-pane" role="tabpanel">
  @forelse($reviews as $review)
    <article class="border-b border-slate-200 py-4 last:border-0">
      <div class="mb-1 flex items-center gap-1">
        @for($i=1; $i<=5; $i++)
          <i class="fa-star{{ $i <= $review->rating ? ' fa-solid text-amber-500' : ' fa-regular text-slate-300' }}"></i>
        @endfor
        <small class="ml-auto text-xs text-slate-500">{{ $review->created_at->diffForHumans() }}</small>
      </div>

      @if($review->orderItem && $review->orderItem->product)
        @php
          $p = $review->orderItem->product;
          $thumb = product_thumb_url($p);
        @endphp
        <div class="mb-2 flex items-center gap-2">
          <a href="{{ route('listing.show', $p->slug ?? $p->id) }}">
            <img src="{{ $thumb }}" alt="{{ $p->localized_name ?? $p->name }} thumbnail" class="h-12 w-12 rounded-lg border border-slate-200 object-cover">
          </a>
          <div class="text-sm">
            <div class="max-w-[220px] truncate font-semibold text-slate-900">
              <a href="{{ route('listing.show', $p->slug ?? $p->id) }}" class="hover:text-emerald-700">
                {{ $p->localized_name ?? $p->name }}
              </a>
            </div>
            <div class="text-xs text-slate-500">Reviewed item</div>
          </div>
        </div>
      @endif

      <p class="text-sm text-slate-700">{{ $review->comment }}</p>
      <div class="mt-1 text-xs text-slate-500">{{ $review->user->name }}</div>

      @if(!empty($review->image_path))
        <div class="mt-2">
          <a href="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" target="_blank" rel="noopener">
            <img
              src="{{ asset('storage/'.ltrim($review->image_path,'/')) }}"
              alt="Review photo"
              class="max-h-40 max-w-40 rounded-lg border border-slate-200 object-cover"
            >
          </a>
        </div>
      @endif
    </article>
  @empty
    <p class="text-sm text-slate-500">No reviews yet.</p>
  @endforelse
</div>
