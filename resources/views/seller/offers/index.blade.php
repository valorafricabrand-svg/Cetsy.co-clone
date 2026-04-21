@extends('theme.'.theme().'.layouts.app')
@section('title', 'My Offers')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
 <div class="min-w-0">
 <h1 class="text-xl font-semibold mb-1">My Offers Received</h1>
 <p class="text-slate-500 mb-0">Manage offers from potential buyers for your products</p>
 </div>
 <div class="flex flex-wrap gap-2">
 @if($stats['pending'] > 0)
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400 px-2.5 py-1.5 text-xs rounded-lg" data-ui-toggle="modal" data-target="#bulkActionModal">
 <i class="fa-solid fa-check-double mr-1"></i>Bulk Actions
 </button>
 @endif
 
 </div>
 </div>

 @if(session('success'))
 <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
 @endif
 @if(session('warning'))
 <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">{{ session('warning') }}</div>
 @endif
 @if(session('error'))
 <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700">{{ session('error') }}</div>
 @endif

 {{-- Statistics Cards --}}
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-4">
 <div class="col-span-12 md:col-span-6 lg:col-span-4 mb-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
 <div class="p-4">
 <div class="flex items-center">
 <div class="shrink-0">
 <div class="bg-emerald-600 text-white border-emerald-600 bg-opacity-10 rounded p-3">
 <i class="fa-solid fa-hand-holding-dollar text-emerald-600 text-xl"></i>
 </div>
 </div>
 <div class="flex-1 ml-3">
 <h6 class="text-base font-bold text-slate-900 mb-1">Total Offers</h6>
 <h4 class="mb-0">{{ $stats['total'] }}</h4>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-4 mb-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
 <div class="p-4">
 <div class="flex items-center">
 <div class="shrink-0">
 <div class="bg-amber-100 text-amber-800 border-amber-200 bg-opacity-10 rounded p-3">
 <i class="fa-regular fa-clock text-amber-600 text-xl"></i>
 </div>
 </div>
 <div class="flex-1 ml-3">
 <h6 class="text-base font-bold text-slate-900 mb-1">Pending</h6>
 <h4 class="mb-0">{{ $stats['pending'] }}</h4>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-4 mb-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
 <div class="p-4">
 <div class="flex items-center">
 <div class="shrink-0">
 <div class="bg-emerald-100 text-emerald-800 border-emerald-200 bg-opacity-10 rounded p-3">
 <i class="fa-regular fa-circle-check text-emerald-600 text-xl"></i>
 </div>
 </div>
 <div class="flex-1 ml-3">
 <h6 class="text-base font-bold text-slate-900 mb-1">Accepted</h6>
 <h4 class="mb-0">{{ $stats['accepted'] }}</h4>
 </div>
 </div>
 </div>
 </div>
 </div>
 <!-- <div class="col-span-12 md:col-span-6 lg:col-span-3 mb-3">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
 <div class="p-4">
 <div class="flex items-center">
 <div class="shrink-0">
 <div class="bg-sky-100 text-sky-800 border-sky-200 bg-opacity-10 rounded p-3">
 <i class="fa-solid fa-dollar-sign text-sky-600 text-xl"></i>
 </div>
 </div>
 <div class="flex-1 ml-3">
 <h6 class="text-base font-bold text-slate-900 mb-1">Avg Value</h6>
 <h4 class="mb-0">{{ shop_currency() }} {{ number_format($stats['avg_value'] ?? 0, 2) }}</h4>
 </div>
 </div>
 </div>
 </div>
 </div> -->
 </div>

 {{-- Filters --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
 <div class="p-4">
 <form method="GET" action="{{ route('seller.offers.index') }}" id="filterForm">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
 <div class="col-span-12 md:col-span-6 xl:col-span-2">
 <label class="form-label text-xs">Status</label>
 <select name="status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 <option value="">All Status</option>
 <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
 <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
 <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
 <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
 </select>
 </div>
 <div class="col-span-12 md:col-span-6 xl:col-span-2">
 <label class="form-label text-xs">Type</label>
 <select name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 <option value="">All Types</option>
 <option value="original" {{ request('type') === 'original' ? 'selected' : '' }}>Original Offers</option>
 <option value="counter" {{ request('type') === 'counter' ? 'selected' : '' }}>Counter Offers</option>
 </select>
 </div>
 <div class="col-span-12 md:col-span-6 xl:col-span-2">
 <label class="form-label text-xs">Product</label>
 <select name="product" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 <option value="">All Products</option>
 @foreach($products as $product)
 <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
 {{ \Illuminate\Support\Str::limit($product->name, 25) }}
 </option>
 @endforeach
 </select>
 </div>
 <div class="col-span-12 md:col-span-6 xl:col-span-2">
 <label class="form-label text-xs">Price Min</label>
 <input type="number" name="price_min" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" value="{{ request('price_min') }}" placeholder="Min">
 </div>
 <div class="col-span-12 md:col-span-6 xl:col-span-2">
 <label class="form-label text-xs">Price Max</label>
 <input type="number" name="price_max" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" value="{{ request('price_max') }}" placeholder="Max">
 </div>
 <div class="col-span-12 md:col-span-6 xl:col-span-2">
 <label class="form-label text-xs">&nbsp;</label>
 <div class="flex flex-wrap gap-2">
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 px-2.5 py-1.5 text-xs rounded-lg">
 <i class="fa-solid fa-filter mr-1"></i>Filter
 </button>
 <a href="{{ route('seller.offers.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg">
 <i class="fa-solid fa-xmark-circle mr-1"></i>Clear
 </a>
 </div>
 </div>
 </div>
 </form>
 </div>
 </div>

 {{-- Offers Table --}}
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <div class="flex flex-wrap items-center justify-between gap-2">
 <h6 class="mb-0">Offers ({{ $offers->total() }})</h6>
 <div class="form-check">
 <input class="form-check-input" type="checkbox" id="selectAllHeader">
 <label class="form-check-label text-xs" for="selectAllHeader">Select All</label>
 </div>
 </div>
 </div>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
 <thead class="bg-slate-50 text-slate-600">
 <tr>
 <th width="30">
 <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
 </th>
 <th>Product</th>
 <th>Buyer</th>
 <th>Offer</th>
 <th>Status</th>
 <th>Date</th>
 <th class="text-right">Actions</th>
 </tr>
 </thead>
 <tbody>
 @forelse($offers as $offer)
 <tr>
 <td>
 @if($offer->status === 'pending')
 <input type="checkbox" class="form-check-input offer-checkbox" value="{{ $offer->id }}">
 @endif
 </td>
 <td style="min-width:200px;">
 <a href="{{ route('seller.offers.show', $offer->id) }}" class="group flex items-center gap-2 rounded-lg -m-1 p-1 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-200">
 @php
 $thumb = product_thumb_url($offer->product);
 @endphp
 <div class="relative h-10 w-10 shrink-0">
 <img src="{{ $thumb }}" class="h-10 w-10 rounded object-cover" alt="{{ $offer->product->name }}" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
 <div class="absolute inset-0 hidden items-center justify-center rounded border border-slate-200 bg-slate-100 text-slate-400">
 <i class="fa-solid fa-image"></i>
 </div>
 </div>
 <div class="flex-1">
 <span class="font-semibold text-slate-900 block group-hover:text-emerald-700" title="{{ $offer->product->name ?? '-' }}">
 {{ \Illuminate\Support\Str::limit($offer->product->name ?? '-', 25) }}
 </span>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-500 text-xs">#{{ $offer->product_id }}</span>
 @if($offer->is_counter_offer)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200 text-xs ml-1">Counter</span>
 @endif
 <span class="mt-1 block text-[11px] font-medium text-emerald-700 md:hidden">Tap to open</span>
 </div>
 </a>
 </td>
 <td style="min-width:150px;">
 <a href="{{ route('seller.offers.show', $offer->id) }}" class="flex flex-col rounded-lg -m-1 p-1 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-200">
 <span class="font-semibold text-xs text-slate-900">{{ $offer->buyer->name ?? '-' }}</span>
 <span class="text-slate-500 text-xs" title="{{ $offer->buyer->email ?? '' }}">
 <i class="fa-regular fa-envelope mr-1"></i>{{ \Illuminate\Support\Str::limit($offer->buyer->email ?? '', 20) }}
 </span>
 </a>
 </td>
 <td>
 <a href="{{ route('seller.offers.show', $offer->id) }}" class="flex flex-col rounded-lg -m-1 p-1 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-200">
 <span class="font-bold text-slate-900">{{ $offer->formatted_price }}</span>
 @if($offer->is_counter_offer)
 @php
 $diff = $offer->getPriceDifference();
 $diffPercent = $offer->getPriceDifferencePercentage();
 @endphp
 @if($diff != 0)
 <span class="text-xs {{ $diff > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
 <i class="fa-solid {{ $diff > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
 {{ $diff > 0 ? '+' : '' }}{{ shop_currency() }} {{ number_format(abs($diff), 2) }}
 ({{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%)
 </span>
 @endif
 @endif
 </a>
 </td>
 <td>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $offer->status_badge_class }}">{{ $offer->status_label }}</span>
 </td>
 <td>
 <div class="flex flex-col">
 <span class="text-xs text-slate-900">{{ $offer->created_at->format('d M Y') }}</span>
 <span class="text-slate-500 text-xs">{{ $offer->time_ago }}</span>
 </div>
 </td>
 <td class="text-right">
 <a href="{{ route('seller.offers.show', $offer->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg">
 <i class="fa-regular fa-eye mr-1"></i>View
 </a>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="text-center py-4 text-slate-500">
 <i class="fa-solid fa-inbox mr-2"></i> No offers found for your products.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 @if($offers->hasPages())
 <div class="border-t border-slate-200 px-4 py-3">
 {{ $offers->links() }}
 </div>
 @endif
 </div>
</div>

{{-- Bulk Action Modal --}}
<div class="tw-modal" id="bulkActionModal" tabindex="-1">
 <div class="tw-modal-dialog">
 <div class="tw-modal-content">
 <div class="tw-modal-header">
 <h5 class="tw-modal-title">Bulk Actions</h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>
 <form method="POST" action="{{ route('seller.offers.bulk-action') }}" id="bulkActionForm">
 @csrf
 <div class="tw-modal-body">
 <div class="mb-3">
 <label class="form-label">Action</label>
 <select name="action" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
 <option value="">Select Action</option>
 <option value="accept">Accept Selected</option>
 <option value="decline">Decline Selected</option>
 <option value="expire">Mark as Expired</option>
 </select>
 </div>
 <div class="mb-3">
 <label class="form-label">Reason (for decline)</label>
 <textarea name="reason" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" rows="3" placeholder="Optional reason for declining..."></textarea>
 </div>
 <div id="selectedOffersInfo" class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 hidden">
 <i class="fa-regular fa-circle-info mr-2"></i>
 <span id="selectedCount">0</span> offers selected
 </div>
 </div>
 <div class="tw-modal-footer">
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-700 bg-slate-700 text-white hover:bg-slate-800" data-ui-dismiss="modal">Cancel</button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">Apply Action</button>
 </div>
 </form>
 </div>
 </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
 // Select all functionality (supports header and table toggles)
 const selectAllHeader = document.getElementById('selectAllHeader');
 const selectAllCheckbox = document.getElementById('selectAllCheckbox');
 const offerCheckboxes = Array.from(document.querySelectorAll('.offer-checkbox'));
 // If no selectable offers on this page, disable master toggles for clarity
 if (offerCheckboxes.length === 0) {
 if (selectAllHeader) selectAllHeader.disabled = true;
 if (selectAllCheckbox) selectAllCheckbox.disabled = true;
 }

 function setAllChecked(checked) {
 // Only touch checkboxes that are enabled/visible
 offerCheckboxes.forEach(cb => { if (!cb.disabled) cb.checked = checked; });
 if (selectAllHeader) {
 selectAllHeader.checked = checked;
 selectAllHeader.indeterminate = false;
 }
 if (selectAllCheckbox) {
 selectAllCheckbox.checked = checked;
 selectAllCheckbox.indeterminate = false;
 }
 updateSelectedCount();
 }

 if (selectAllHeader) {
 selectAllHeader.addEventListener('change', function() {
 setAllChecked(this.checked);
 });
 }
 if (selectAllCheckbox) {
 selectAllCheckbox.addEventListener('change', function() {
 setAllChecked(this.checked);
 });
 }

 offerCheckboxes.forEach(checkbox => {
 checkbox.addEventListener('change', function() {
 updateSelectedCount();
 updateSelectAllState();
 });
 });

 function updateSelectedCount() {
 const form = document.getElementById('bulkActionForm');
 const selected = document.querySelectorAll('.offer-checkbox:checked');
 const count = selected.length;
 const info = document.getElementById('selectedOffersInfo');
 const countSpan = document.getElementById('selectedCount');

 if (info && countSpan) {
 if (count > 0) {
 info.classList.remove('hidden');
 countSpan.textContent = count;
 } else {
 info.classList.add('hidden');
 }
 }

 if (form) {
 // Clear existing hidden inputs scoped to the bulk form
 form.querySelectorAll('input[name="offer_ids[]"]').forEach(input => input.remove());

 // Create new hidden inputs for each selected offer
 selected.forEach(checkbox => {
 const input = document.createElement('input');
 input.type = 'hidden';
 input.name = 'offer_ids[]';
 input.value = checkbox.value;
 form.appendChild(input);
 });
 }

 // Debug logging
 // console.log('Selected offers:', Array.from(selected).map(cb => cb.value));
 }

 function updateSelectAllState() {
 const total = offerCheckboxes.length;
 const checked = document.querySelectorAll('.offer-checkbox:checked').length;
 const allChecked = checked === total && total > 0;
 const someChecked = checked > 0 && checked < total;

 if (selectAllCheckbox) {
 selectAllCheckbox.checked = allChecked;
 selectAllCheckbox.indeterminate = someChecked;
 }
 if (selectAllHeader) {
 selectAllHeader.checked = allChecked;
 selectAllHeader.indeterminate = someChecked;
 }
 }

 // Modal handlers
 const bulkActionModal = document.getElementById('bulkActionModal');

 // Bulk action form submission handler
 if (bulkActionModal) {
 bulkActionModal.addEventListener('modal:open', function() {
 const form = document.getElementById('bulkActionForm');
 if (!form) return;
 // Always refresh the selection snapshot and visible count when opening
 updateSelectedCount();
 updateSelectAllState();
 // Rebind submit once (avoid duplicates)
 form.removeEventListener('submit', handleBulkActionSubmit);
 form.addEventListener('submit', handleBulkActionSubmit);
 });
 }

 function handleBulkActionSubmit(e) {
 const form = e.target;
 const selectedOffers = form.querySelectorAll('input[name="offer_ids[]"]');
 if (selectedOffers.length === 0) {
 e.preventDefault();
 alert('Please select at least one offer to perform bulk action.');
 return false;
 }

 const actionSelect = form.querySelector('select[name="action"]');
 if (!actionSelect || !actionSelect.value) {
 e.preventDefault();
 alert('Please select an action to perform.');
 return false;
 }
 }
});
</script>
@endpush

@push('styles')
<style>
 table th, table td {
 vertical-align: middle;
}
.badge {
 font-size: 0.75rem;
}
.tw-dropdown-item {
 cursor: pointer;
}
.form-check-input:checked {
 background-color: #10b981;
 border-color: #10b981;
}
</style>
@endpush
 </div>
 </div>
 </div>
</section>
@endsection 






