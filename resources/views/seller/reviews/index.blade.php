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
 <div class="col-span-5">
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
 <div class="col-span-2 flex gap-2">
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
 <th scope="col">Comment</th>
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
 @endphp
 <tr id="review-{{ $review->id }}">
 <td>
 <div class="font-semibold">{{ $orderNumber }}</div>
 @if($status)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusBadge }} capitalize">{{ str_replace('_', ' ', $status) }}</span>
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
 <td>
 @if($review->comment)
 <div class="review-comment text-xs">{{ \Illuminate\Support\Str::limit($review->comment, 200) }}</div>
 @else
 <span class="text-slate-500 text-xs">No comment left</span>
 @endif

 @if(!empty($review->image_path))
 <div class="mt-2">
 <a href="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" target="_blank">
 <img src="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" alt="Review image" style="max-width: 120px; max-height: 120px; border-radius: 6px;"/>
 </a>
 </div>
 @endif

 {{-- Seller response display / form --}}
 <div class="mt-2">
 @if(!empty($review->seller_response))
 <div class="border rounded p-2 bg-slate-50">
 <div class="font-semibold mb-1">Your response</div>
 <div class="seller-response text-xs">{{ $review->seller_response }}</div>
 <div class="flex gap-2 mt-2">
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" type="button" data-ui-toggle="collapse" data-target="#respondForm-{{ $review->id }}" aria-expanded="false" aria-controls="respondForm-{{ $review->id }}">
 Edit response
 </button>
 </div>
 </div>
 @else
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50" type="button" data-ui-toggle="collapse" data-target="#respondForm-{{ $review->id }}" aria-expanded="false" aria-controls="respondForm-{{ $review->id }}">
 Respond
 </button>
 @endif
 </div>

 <div class="hidden mt-2" id="respondForm-{{ $review->id }}">
 <form action="{{ route('seller.reviews.respond', $review) }}" method="POST" class="border rounded p-2">
 @csrf
 <label for="seller_response_{{ $review->id }}" class="form-label mb-1">Your response</label>
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="seller_response_{{ $review->id }}" name="seller_response" rows="3" maxlength="2000" placeholder="Write a helpful, professional reply visible to the buyer.">{{ old('seller_response', $review->seller_response) }}</textarea>
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
 <td colspan="6" class="text-center py-5 text-slate-500">
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
@endsection






