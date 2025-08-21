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
      <p class="small mb-0">{{ $review->comment }}</p>
      <div class="small text-muted mt-1">{{ $review->user->name }}</div>
    </div>
  @empty
    <p class="text-muted small mb-0">No reviews yet.</p>
  @endforelse
</div>