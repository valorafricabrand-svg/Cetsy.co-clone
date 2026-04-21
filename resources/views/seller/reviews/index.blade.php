@extends('theme.'.theme().'.layouts.app')

@section('title', 'Shop Reviews')

@push('styles')
<style>
 /* Star colors */
 .rating-stars .star-filled { color: #e5780b !important; }
 .rating-stars .star-empty { color: #000000 !important; }
 .stat-card { border: 0; }
 .stat-card .value { font-size: 2rem; font-weight: 700; }
 .stat-card .label { text-transform: uppercase; font-size: .75rem; letter-spacing: .08em; }
 .review-comment { white-space: pre-line; }
 .seller-response { white-space: pre-line; }
 .review-photo-cell { width: 120px; }
 .review-comment-cell { min-width: 280px; }
 .review-thumb-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 88px;
  height: 88px;
  padding: 0;
  overflow: hidden;
  border: 1px solid #cbd5e1;
  border-radius: 16px;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
 }
 .review-thumb-button:hover {
  transform: translateY(-1px);
  border-color: #94a3b8;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
 }
 .review-thumb-button:focus-visible {
  outline: 3px solid rgba(16, 185, 129, 0.2);
  outline-offset: 2px;
 }
 .review-thumb-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
 }
 .review-thumb-empty {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 88px;
  height: 88px;
  padding: .75rem;
  border: 1px dashed #cbd5e1;
  border-radius: 16px;
  background: #f8fafc;
  color: #94a3b8;
  font-size: .7rem;
  font-weight: 600;
  text-align: center;
  line-height: 1.3;
 }
 .review-thumb-label {
  margin-top: .45rem;
  font-size: .7rem;
  font-weight: 500;
  color: #64748b;
 }
 .review-preview-dialog {
  width: min(90vw, 880px);
  max-width: 90vw;
  padding: 0;
  border: 0;
  border-radius: 24px;
  background: transparent;
 }
 .review-preview-dialog::backdrop {
  background: rgba(15, 23, 42, 0.72);
  backdrop-filter: blur(4px);
 }
 .review-preview-panel {
  position: relative;
  overflow: hidden;
  text-align: center;
  border-radius: 24px;
  background: #0f172a;
  box-shadow: 0 32px 80px rgba(15, 23, 42, 0.4);
 }
 .review-preview-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border: 0;
  border-radius: 9999px;
  background: rgba(15, 23, 42, 0.72);
  color: #fff;
  font-size: 1.1rem;
 }
 .review-preview-image {
  display: block;
  width: auto;
  max-width: 100%;
  height: auto;
  max-height: 80vh;
  margin: 0 auto;
  background: #020617;
 }
 .review-preview-caption {
  padding: 1rem 1.25rem;
  color: #e2e8f0;
  font-size: .9rem;
 }
 @media (max-width: 1024px) {
  .review-photo-cell { width: 100px; }
  .review-comment-cell { min-width: 240px; }
  .review-thumb-button,
  .review-thumb-empty {
   width: 72px;
   height: 72px;
   border-radius: 14px;
  }
 }
</style>
@endpush

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
@php
 $summary = array_merge([
 'count' => 0,
 'average' => 0,
 'five_star' => 0,
 'four_star' => 0,
 'three_star' => 0,
 'two_star' => 0,
 'one_star' => 0,
 ], $summary ?? []);

 $ratingFilter = $ratingFilter ?? null;
 $respondReviewId = $respondReviewId ?? null;
@endphp

<div class="content">
 <div class="flex flex-wrap justify-between items-end mb-4 gap-3">
 <div>
 <h2 class="mb-1">Shop Reviews</h2>
 <p class="text-slate-500 mb-0">Only reviews from finished or delivered orders are shown here.</p>
 </div>
 <div class="text-right">
 <div class="value text-amber-600">{{ number_format($summary['average'], 1) }}</div>
 <div class="text-slate-500 text-xs">Average rating across {{ $summary['count'] }} review{{ $summary['count'] === 1 ? '' : 's' }}</div>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3 mb-4">
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm stat-card h-full">
 <div class="p-4 text-center">
 <div class="label text-slate-500 mb-1">Total Reviews</div>
 <div class="value">{{ $summary['count'] }}</div>
 </div>
 </div>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm stat-card h-full">
 <div class="p-4 text-center">
 <div class="label text-slate-500 mb-1">Five-Star</div>
 @php $fivePercent = $summary['count'] ? round(($summary['five_star'] / $summary['count']) * 100) : 0; @endphp
 <div class="value text-amber-600">{{ $summary['five_star'] }}</div>
 <div class="text-xs text-slate-500">{{ $fivePercent }}% of reviews</div>
 </div>
 </div>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm stat-card h-full">
 <div class="p-4 text-center">
 <div class="label text-slate-500 mb-1">Four-Star</div>
 @php $fourPercent = $summary['count'] ? round(($summary['four_star'] / $summary['count']) * 100) : 0; @endphp
 <div class="value text-amber-600">{{ $summary['four_star'] }}</div>
 <div class="text-xs text-slate-500">{{ $fourPercent }}% of reviews</div>
 </div>
 </div>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm stat-card h-full">
 <div class="p-4 text-center">
 <div class="label text-slate-500 mb-1">Three-Star & Below</div>
 @php $lowCount = $summary['three_star'] + $summary['two_star'] + $summary['one_star'];
 $lowPercent = $summary['count'] ? round(($lowCount / $summary['count']) * 100) : 0;
 @endphp
 <div class="value text-amber-600">{{ $lowCount }}</div>
 <div class="text-xs text-slate-500">{{ $lowPercent }}% of reviews</div>
 </div>
 </div>
 </div>
 </div>

 <form action="{{ route('seller.reviews.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="p-4 grid grid-cols-1 gap-4 md:grid-cols-12 gap-3 items-end">
 <div class="col-span-12 md:col-span-6 lg:col-span-5">
 <label class="form-label">Search</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" name="q" value="{{ $search }}" placeholder="Search by listing, buyer, order, or comment">
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <label class="form-label">Rating</label>
 <select name="rating" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 <option value="">All ratings</option>
 @for($i = 5; $i >= 1; $i--)
 <option value="{{ $i }}" @selected($ratingFilter === $i)>{{ $i }} star{{ $i === 1 ? '' : 's' }}</option>
 @endfor
 </select>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-2">
 <label class="form-label">Per Page</label>
 <select name="per_page" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 @foreach([10, 15, 25, 50, 100] as $option)
 <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-2 flex gap-2">
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 flex-1"><i class="fas fa-filter mr-1"></i> Filter</button>
 @if($search !== '' || ! is_null($ratingFilter) || $perPage !== 15)
 <a href="{{ route('seller.reviews.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100" title="Reset filters">
 <i class="fas fa-rotate-left"></i>
 </a>
 @endif
 </div>
 </div>
 </form>

 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
 <thead class="bg-slate-50 text-slate-600">
 <tr>
 <th scope="col">Order</th>
 <th scope="col">Listing</th>
 <th scope="col">Buyer</th>
 <th scope="col">Rating</th>
 <th scope="col" class="review-photo-cell">Photo</th>
 <th scope="col" class="review-comment-cell">Comment</th>
 <th scope="col">Left On</th>
 </tr>
 </thead>
 <tbody>
 @forelse($reviews as $review)
 @php
 $order = $review->order;
 $orderNumber = $order?->order_number ?: ('#' . str_pad((string) ($order?->id ?? ''), 6, '0', STR_PAD_LEFT));
 $product = optional($review->orderItem)->product;
 $buyer = $review->user;
 $status = $order?->status;
 $statusBadge = method_exists($order, 'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-slate-200 text-slate-700 border-slate-300';
 $isRespondTarget = $respondReviewId === (int) $review->id;
 $reviewImageUrl = !empty($review->image_path) ? asset('storage/'.ltrim($review->image_path, '/')) : null;
 $reviewImageCaption = $product?->name
  ? $product->name.' by '.($buyer->name ?? 'Unknown Buyer')
  : 'Review photo';
  @endphp
<tr id="review-{{ $review->id }}" @class(['bg-emerald-50' => $isRespondTarget])>
<td>
 <div class="font-semibold">{{ $orderNumber }}</div>
 @if($status)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusBadge }} capitalize">{{ $order->getSellerStatusLabel() }}</span>
 @endif
 </td>
 <td>
 @if($product)
 <div class="font-semibold">{{ $product->name }}</div>
 <div class="text-slate-500 text-xs capitalize">{{ $product->type }}</div>
 @else
 <span class="text-slate-500">Listing removed</span>
 @endif
 </td>
 <td>
 <div class="font-semibold">{{ $buyer->name ?? 'Unknown Buyer' }}</div>
 </td>
 <td>
 <div class="rating-stars mb-1">
 @for($i = 1; $i <= 5; $i++)
 @if($i <= (int) $review->rating)
 <i class="fas fa-star star-filled"></i>
 @else
 <i class="far fa-star star-empty"></i>
 @endif
 @endfor
 </div>
 <div class="text-xs text-slate-500">{{ $review->rating }} / 5</div>
</td>
 <td class="review-photo-cell">
 @if($reviewImageUrl)
 <button
  type="button"
  class="review-thumb-button"
  aria-haspopup="dialog"
  data-review-preview
  data-full-src="{{ $reviewImageUrl }}"
  data-alt="{{ $reviewImageCaption }}"
  data-caption="{{ $reviewImageCaption }}"
 >
  <img
   src="{{ $reviewImageUrl }}"
   alt="{{ $reviewImageCaption }}"
   class="review-thumb-image"
   loading="lazy"
   decoding="async"
  />
 </button>
 <div class="review-thumb-label">Click to enlarge</div>
 @else
 <div class="review-thumb-empty">No photo</div>
 @endif
 </td>
 <td class="review-comment-cell">
 @if($review->comment)
 <div class="review-comment text-xs">{{ \Illuminate\Support\Str::limit($review->comment, 200) }}</div>
 @else
 <span class="text-slate-500 text-xs">No comment left</span>
 @endif

 {{-- Seller response display / form --}}
 <div class="mt-2">
 @if(!empty($review->seller_response))
 <div class="border rounded p-2 bg-slate-50">
 <div class="font-semibold mb-1">Your response</div>
 <div class="seller-response text-xs">{{ $review->seller_response }}</div>
 <div class="flex gap-2 mt-2">
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" type="button" data-ui-toggle="collapse" data-target="#respondForm-{{ $review->id }}" aria-expanded="{{ $isRespondTarget ? 'true' : 'false' }}" aria-controls="respondForm-{{ $review->id }}">
 Edit response
 </button>
 </div>
 </div>
 @else
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50" type="button" data-ui-toggle="collapse" data-target="#respondForm-{{ $review->id }}" aria-expanded="{{ $isRespondTarget ? 'true' : 'false' }}" aria-controls="respondForm-{{ $review->id }}">
 Respond
 </button>
 @endif
 </div>

 <div @class(['mt-2', 'hidden' => ! $isRespondTarget]) id="respondForm-{{ $review->id }}">
 <form action="{{ route('seller.reviews.respond', $review) }}" method="POST" class="border rounded p-2">
 @csrf
 <label for="seller_response_{{ $review->id }}" class="form-label mb-1">Your response</label>
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="seller_response_{{ $review->id }}" name="seller_response" rows="3" maxlength="2000" placeholder="Write a helpful, professional reply visible to the buyer." @if($isRespondTarget) autofocus @endif>{{ old('seller_response', $review->seller_response) }}</textarea>
 <div class="flex justify-end gap-2 mt-2">
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">Save</button>
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" data-ui-toggle="collapse" data-target="#respondForm-{{ $review->id }}">Cancel</button>
 </div>
 </form>
 </div>
 </td>
 <td>
 <div class="font-semibold">{{ optional($review->created_at)->format('M d, Y') }}</div>
 @if($order?->delivered_at)
 <div class="text-slate-500 text-xs">Delivered: {{ optional($order->delivered_at)->format('M d, Y') }}</div>
 @elseif($order?->completed_at)
 <div class="text-slate-500 text-xs">Completed: {{ optional($order->completed_at)->format('M d, Y') }}</div>
 @endif
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="text-center py-5 text-slate-500">
 <i class="fas fa-star-half-alt fa-2x mb-2"></i>
 <div>No reviews yet. Encourage buyers to leave feedback once orders are delivered.</div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($reviews->hasPages())
 <div class="border-t border-slate-200 px-4 py-3 bg-white">
 {{ $reviews->links('pagination::tailwind') }}
 </div>
 @endif
 </div>
</div>
 </div>
 </div>
 </div>
</section>

<dialog id="reviewImagePreviewDialog" class="review-preview-dialog">
 <div class="review-preview-panel" data-review-preview-panel>
  <button type="button" class="review-preview-close" data-review-preview-close aria-label="Close review photo preview">
   <i class="fas fa-times"></i>
  </button>
  <img src="" alt="" class="review-preview-image" data-review-preview-image>
  <div class="review-preview-caption" data-review-preview-caption></div>
 </div>
</dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
 var dialog = document.getElementById('reviewImagePreviewDialog');
 if (!dialog) {
  return;
 }

 var image = dialog.querySelector('[data-review-preview-image]');
 var caption = dialog.querySelector('[data-review-preview-caption]');
 var lastTrigger = null;

 function closeDialog() {
  if (dialog.open) {
   dialog.close();
  }
 }

 document.querySelectorAll('[data-review-preview]').forEach(function (trigger) {
  trigger.addEventListener('click', function () {
   var src = trigger.getAttribute('data-full-src');

   if (!src) {
    return;
   }

   if (typeof dialog.showModal !== 'function') {
    window.open(src, '_blank', 'noopener');
    return;
   }

   image.src = src;
   image.alt = trigger.getAttribute('data-alt') || 'Review photo preview';
   caption.textContent = trigger.getAttribute('data-caption') || 'Review photo';
   lastTrigger = trigger;
   dialog.showModal();
  });
 });

 dialog.querySelectorAll('[data-review-preview-close]').forEach(function (trigger) {
  trigger.addEventListener('click', closeDialog);
 });

 dialog.addEventListener('click', function (event) {
  if (event.target === dialog) {
   closeDialog();
  }
 });

 dialog.addEventListener('close', function () {
  image.removeAttribute('src');
  image.alt = '';
  caption.textContent = '';

  if (lastTrigger) {
   lastTrigger.focus();
   lastTrigger = null;
  }
 });
});
</script>
@endpush

