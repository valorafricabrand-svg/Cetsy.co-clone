{{-- resources/views/theme/{{ theme() }}/partials/_tab_reviews.blade.php --}}
<div class="tab-pane fade" id="reviews-pane" role="tabpanel">
  @forelse($reviews as $review)
    <div class="border-bottom py-3">
      <div class="d-flex align-items-center mb-1">
        @for($i=1; $i<=5; $i++)
          <i class="fa-star{{ $i <= $review->rating ? ' fa-solid text-warning' : ' fa-regular text-muted' }} me-1"></i>
        @endfor
        <small class="text-muted ms-auto">{{ $review->created_at->diffForHumans() }}</small>
      </div>
      @if($review->orderItem && $review->orderItem->product)
        @php
          $p = $review->orderItem->product;
          $thumb = null;
          if (!empty($p->featured_image)) {
            $thumb = str_starts_with($p->featured_image, 'http')
              ? $p->featured_image
              : asset('storage/' . ltrim($p->featured_image, '/'));
          }
        @endphp
        <div class="d-flex align-items-center mb-2">
          @if($thumb)
            <a href="{{ route('listing.show', $p->slug ?? $p->id) }}" class="me-2">
              <img src="{{ $thumb }}" alt="{{ $p->name }} thumbnail"
                   style="width:48px;height:48px;border-radius:6px;object-fit:cover;">
            </a>
          @endif
          <div class="small">
            <div class="fw-semibold text-truncate" style="max-width:220px;">
              <a href="{{ route('listing.show', $p->slug ?? $p->id) }}" class="text-decoration-none text-dark">
                {{ $p->name }}
              </a>
            </div>
            <div class="text-muted">Reviewed item</div>
          </div>
        </div>
      @endif
      <p class="small mb-0">{{ $review->comment }}</p>
      <div class="small text-muted mt-1">{{ $review->user->name }}</div>
      @if(!empty($review->image_path))
        <div class="mt-2">
          <a href="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" target="_blank">
            <img src="{{ asset('storage/'.ltrim($review->image_path,'/')) }}"
                 alt="Review photo"
                 style="max-width:160px;max-height:160px;border-radius:8px;">
          </a>
        </div>
      @endif
    </div>
  @empty
    <p class="text-muted small mb-0">No reviews yet.</p>
  @endforelse
</div>
