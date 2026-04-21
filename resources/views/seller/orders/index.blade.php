@extends('theme.'.theme().'.layouts.app')

@section('title', 'Shop Orders')

@section('main')
@php
 $disputeCount = $statusCounts['disputes'] ?? 0;
 $appealCount = $statusCounts['appeals'] ?? 0;
 $totalOrders = $statusCounts['all'] ?? 0;

 $statuses = [
 'all' => 'All',
 'pending' => 'Pending',
 'processing' => 'Processing',
 'shipped' => 'Shipped',
 'completed' => 'Completed',
 'cancelled' => 'Cancelled',
 ];

 $statusTone = static function (string $status): string {
 return match ($status) {
 'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
 'processing' => 'bg-sky-100 text-sky-800 border-sky-200',
 'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
 'delivered', 'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
 'cancelled', 'refunded' => 'bg-rose-100 text-rose-800 border-rose-200',
 default => 'bg-slate-100 text-slate-700 border-slate-200',
 };
 };

 $disputeTone = static function (?string $status): string {
 return match ((string) $status) {
 'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
 'under_review' => 'bg-sky-100 text-sky-800 border-sky-200',
 'resolved', 'mutually_resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
 'rejected', 'cancelled' => 'bg-slate-100 text-slate-700 border-slate-200',
 default => 'bg-slate-100 text-slate-700 border-slate-200',
 };
 };

 $progressMessage = static function ($order): string {
 $items = collect($order->items ?? []);
 $digitalItems = $items->filter(function ($item) {
 return strtolower((string) (optional($item->product)->type ?? '')) === 'digital';
 });
 $isDigitalOnly = $items->isNotEmpty() && $digitalItems->count() === $items->count();

 if ($isDigitalOnly) {
 $allDownloaded = $digitalItems->every(function ($item) {
 return ! empty($item->downloaded_at);
 });

 return $allDownloaded ? 'Downloaded' : 'Digital delivery';
 }

 $minDays = null;
 $maxDays = null;

 foreach ($items as $item) {
 $sp = $item->shippingProfile;
 $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
 $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;

 if (is_numeric($pMin)) {
 $minDays = is_null($minDays) ? (int) $pMin : min($minDays, (int) $pMin);
 }
 if (is_numeric($pMax)) {
 $maxDays = is_null($maxDays) ? (int) $pMax : max($maxDays, (int) $pMax);
 }
 }

 $placedAt = $order->created_at instanceof \Carbon\Carbon
 ? $order->created_at
 : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);

 $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
 $shipEnd = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;

 $dispatchBy = $shipEnd?->format('M j') ?? $shipStart?->format('M j');

 $formatDateTime = static function ($value): ?string {
 if (! $value) {
 return null;
 }
 if (! $value instanceof \Carbon\Carbon) {
 try {
 $value = \Carbon\Carbon::parse($value);
 } catch (\Throwable $e) {
 return null;
 }
 }
 return $value->format('M j, Y \a\t g:i A');
 };

 if ($order->status === \App\Models\Order::STATUS_COMPLETED) {
 $time = $formatDateTime($order->completed_at ?: $order->delivered_at);
 return $time ? 'Completed on '.$time : 'Completed';
 }

 if ($order->status === \App\Models\Order::STATUS_DELIVERED) {
 $time = $formatDateTime($order->delivered_at);
 return $time ? 'Delivered on '.$time : 'Delivered';
 }

 if ($order->status === \App\Models\Order::STATUS_SHIPPED) {
 $time = $formatDateTime($order->shipped_at);
 return $time ? 'Shipped on '.$time : 'Shipped';
 }

 return $dispatchBy ? 'Dispatch by '.$dispatchBy : 'Dispatch soon';
 };
@endphp

<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')

 <div class="space-y-6">
 <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
 <div>
 <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Orders for {{ $shop->name ?? $user->name }}</h1>
 <p class="mt-1 text-sm text-slate-500">Track fulfillment, disputes, and appeals from one place.</p>
 </div>
 @if($disputeCount > 0 || $appealCount > 0)
 <div class="flex flex-wrap gap-2">
 @if($disputeCount > 0)
 <a href="{{ route('seller.orders.index', ['status' => 'disputes']) }}" class="inline-flex items-center rounded-xl border border-amber-300 bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-800">
 <i class="fa-solid fa-triangle-exclamation mr-2"></i>
 Disputes ({{ $disputeCount }})
 </a>
 @endif
 @if($appealCount > 0)
 <a href="{{ route('seller.orders.index', ['status' => 'appeals']) }}" class="inline-flex items-center rounded-xl border border-rose-300 bg-rose-100 px-3 py-2 text-sm font-semibold text-rose-800">
 <i class="fa-solid fa-gavel mr-2"></i>
 Appeals ({{ $appealCount }})
 </a>
 @endif
 </div>
 @endif
 </div>
 </div>

 @if($disputeCount > 0 || $appealCount > 0)
 <div class="grid gap-4 sm:grid-cols-3">
 <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
 <p class="text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">Active Disputes</p>
 <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $disputeCount }}</p>
 </div>
 <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm">
 <p class="text-xs font-semibold uppercase tracking-[0.14em] text-rose-700">Pending Appeals</p>
 <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $appealCount }}</p>
 </div>
 <div class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm">
 <p class="text-xs font-semibold uppercase tracking-[0.14em] text-sky-700">Total Orders</p>
 <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $totalOrders }}</p>
 </div>
 </div>
 @endif

 <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
 <form method="GET" action="{{ route('seller.orders.index') }}" class="space-y-3">
 <div class="flex flex-wrap gap-2">
 @foreach($statuses as $key => $label)
 @php
 $count = $statusCounts[$key] ?? 0;
 $active = $currentStatus === $key;
 @endphp
 <a href="{{ route('seller.orders.index', ['status' => $key, 'search' => $searchId]) }}"
 class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100' }}">
 {{ $label }} ({{ $count }})
 </a>
 @endforeach

 <a href="{{ route('seller.orders.index', ['status' => 'disputes', 'search' => $searchId]) }}"
 class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $currentStatus === 'disputes' ? 'border-amber-500 bg-amber-500 text-white' : 'border-amber-300 bg-amber-100 text-amber-800' }}">
 Disputes ({{ $disputeCount }})
 </a>
 <a href="{{ route('seller.orders.index', ['status' => 'appeals', 'search' => $searchId]) }}"
 class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $currentStatus === 'appeals' ? 'border-rose-600 bg-rose-600 text-white' : 'border-rose-300 bg-rose-100 text-rose-800' }}">
 Appeals ({{ $appealCount }})
 </a>
 </div>

 <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
 <input type="hidden" name="status" value="{{ $currentStatus }}">
 <div class="relative w-full sm:max-w-sm">
 <i class="fa-solid fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
 <input type="search" name="search" value="{{ $searchId }}" placeholder="Search by order ID"
 class="w-full rounded-xl border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
 </div>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
 Apply
 </button>
 @if($searchId)
 <a href="{{ route('seller.orders.index', ['status' => $currentStatus]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
 Clear
 </a>
 @endif
 </div>
 </form>
 </div>

 @if($orders->isNotEmpty())
 <div class="space-y-3 md:hidden">
 @foreach($orders as $order)
 @php
 $qtyTotal = $order->items->sum('quantity');
 $symbol = shop_currency($order->shop ?? null);
 $dispute = $order->disputes()->latest()->first();
 $statusClass = $statusTone((string) $order->status);
 $primaryItem = $order->items->first();
 $primaryProduct = optional($primaryItem)->product;
 $thumbUrl = $primaryItem ? product_thumb_url($primaryProduct) : null;
 $extraItems = max($order->items->count() - 1, 0);
 @endphp
 <a href="{{ route('seller.orders.show', $order) }}" class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
 <div class="flex gap-3">
 <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
 @if($thumbUrl)
 <img src="{{ $thumbUrl }}" alt="{{ $primaryProduct->name ?? 'Order item' }}" class="h-full w-full object-cover" loading="lazy">
 @else
 <div class="flex h-full w-full items-center justify-center text-slate-400">
 <i class="fa-solid fa-box-open"></i>
 </div>
 @endif
 @if($extraItems > 0)
 <span class="absolute bottom-1 right-1 inline-flex items-center rounded-full bg-slate-900 px-1.5 py-0.5 text-[10px] font-semibold text-white">+{{ $extraItems }}</span>
 @endif
 </div>

 <div class="min-w-0 flex-1">
 <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-2">
 <p class="text-sm font-bold text-slate-900">Order #{{ $order->id }}</p>
 <p class="text-xs text-slate-500">{{ optional($order->created_at)->format('d M Y') }}</p>
 </div>

 <p class="mt-2 text-sm text-slate-700">{{ $order->full_name }}</p>
 @if($primaryProduct)
 <p class="mt-1 truncate text-xs text-slate-500">{{ $primaryProduct->name }}</p>
 @endif

 <div class="mt-2 flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
 <p class="text-slate-500">Qty: {{ $qtyTotal }}</p>
 <p class="font-bold text-slate-900">{{ $symbol }} {{ number_format((float)$order->total_amount, 2) }}</p>
 </div>

 <div class="mt-2 flex flex-wrap items-center gap-2">
 <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }}">{{ $order->getSellerStatusLabel() }}</span>
 @if($order->status === \App\Models\Order::STATUS_PENDING)
 <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Pending payment</span>
 @endif
 </div>

 <p class="mt-2 text-xs text-slate-500">{{ $progressMessage($order) }}</p>

 @if($dispute)
 <div class="mt-2 flex flex-wrap gap-2">
 <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $disputeTone((string) $dispute->status) }}">
 Dispute: {{ ucfirst(str_replace('_', ' ', (string) $dispute->status)) }}
 </span>
 @if($dispute->appeal)
 <span class="inline-flex rounded-full border border-rose-200 bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800">
 Appeal: {{ ucfirst((string) $dispute->appeal->status) }}
 </span>
 @endif
 </div>
 @endif

 <p class="mt-2 text-xs text-slate-500">Tracking: {{ $order->tracking_no ?: '-' }}</p>
 </div>
 </div>
 </a>
 @endforeach

 @if($orders->hasPages())
 <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
 {{ $orders->appends(request()->only('status','search'))->links('pagination::tailwind') }}
 </div>
 @endif
 </div>

 <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">
 <tr>
 <th class="px-4 py-3">#</th>
 <th class="px-4 py-3">Buyer</th>
 <th class="px-4 py-3 text-center">Qty</th>
 <th class="px-4 py-3 text-right">Amount</th>
 <th class="px-4 py-3">Status</th>
 <th class="px-4 py-3">Dispute/Appeal</th>
 <th class="px-4 py-3">Tracking</th>
 <th class="px-4 py-3 text-center">Action</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-200">
 @foreach($orders as $order)
 @php
 $row = $orders->firstItem() + $loop->index;
 $qtyTotal = $order->items->sum('quantity');
 $dispute = $order->disputes()->latest()->first();
 $statusClass = $statusTone((string) $order->status);
 @endphp
 <tr class="order-row cursor-pointer hover:bg-slate-50" data-href="{{ route('seller.orders.show', $order) }}" tabindex="0" aria-label="View order #{{ $order->id }} details">
 <td class="px-4 py-3 font-semibold text-slate-900">{{ $row }}</td>
 <td class="px-4 py-3">
 <p class="font-semibold text-slate-900">{{ $order->full_name }}</p>
 <p class="text-xs text-slate-500">{{ $order->phone ?: '-' }}</p>
 </td>
 <td class="px-4 py-3 text-center text-slate-700">{{ $qtyTotal }}</td>
 <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ shop_currency($order->shop ?? null) }} {{ number_format((float)$order->total_amount,2) }}</td>
 <td class="px-4 py-3">
 <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }}">{{ $order->getSellerStatusLabel() }}</span>
 @if($order->status === \App\Models\Order::STATUS_PENDING)
 <p class="mt-1 text-[11px] font-semibold text-amber-700">Pending payment</p>
 @endif
 @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
 <p class="mt-1 text-[11px] text-rose-600">{{ \Illuminate\Support\Str::limit($order->cancel_reason, 50) }}</p>
 @endif
 <p class="mt-1 text-[11px] text-slate-500">{{ $progressMessage($order) }}</p>
 </td>
 <td class="px-4 py-3">
 @if($dispute)
 <div class="flex flex-wrap gap-1">
 <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $disputeTone((string) $dispute->status) }}">
 {{ ucfirst(str_replace('_', ' ', (string) $dispute->status)) }}
 </span>
 @if($dispute->appeal)
 <span class="inline-flex rounded-full border border-rose-200 bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800">
 Appeal: {{ ucfirst((string) $dispute->appeal->status) }}
 </span>
 @endif
 </div>
 @else
 <span class="text-xs text-slate-400">-</span>
 @endif
 </td>
 <td class="px-4 py-3 text-slate-700">{{ $order->tracking_no ?: '-' }}</td>
 <td class="px-4 py-3 text-center">
 <a href="{{ route('seller.orders.show', $order) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
 <i class="fa-regular fa-eye mr-1"></i>
 View
 </a>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>

 @if($orders->hasPages())
 <div class="border-t border-slate-200 bg-white px-4 py-3">
 {{ $orders->appends(request()->only('status','search'))->links('pagination::tailwind') }}
 </div>
 @endif
 </div>
 @else
 <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-800 shadow-sm">
 No orders found for your shop.
 </div>
 @endif
 </div>
 </div>
 </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
 function isInteractive(el) {
 if (!el) return false;
 return el.closest('a,button,input,select,textarea,[data-ui-toggle]') !== null;
 }

 document.querySelectorAll('tr.order-row').forEach(function (row) {
 row.addEventListener('click', function (e) {
 if (isInteractive(e.target)) return;
 const href = row.getAttribute('data-href');
 if (href) window.location.href = href;
 });

 row.addEventListener('keydown', function (e) {
 if (e.key === 'Enter' || e.key === ' ') {
 e.preventDefault();
 const href = row.getAttribute('data-href');
 if (href) window.location.href = href;
 }
 });
 });
});
</script>
@endpush
