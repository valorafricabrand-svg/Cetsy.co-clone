{{-- resources/views/seller/orders/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Order Details')

@push('styles')
<style>
 .capitalize { text-transform: capitalize; }
 .order-detail-icon { font-size: 1.25rem; }
 .tw-dropdown-menu {
 position: absolute;
 right: 0;
 z-index: 50;
 margin-top: .5rem;
 display: none;
 min-width: 15rem;
 border-radius: .75rem;
 border: 1px solid #e2e8f0;
 background: #fff;
 padding: .35rem;
 box-shadow: 0 16px 30px rgba(15, 23, 42, .15);
 }
 .tw-dropdown-menu.show { display: block; }
 .tw-dropdown-item {
 display: flex;
 align-items: center;
 gap: .5rem;
 border-radius: .5rem;
 padding: .45rem .55rem;
 font-size: .85rem;
 color: #1e293b;
 text-decoration: none;
 }
 .tw-dropdown-item:hover { background: #f1f5f9; }
 .tw-dropdown-divider { margin: .35rem 0; border-color: #e2e8f0; }
 .tw-modal {
 position: fixed;
 inset: 0;
 z-index: 80;
 display: none;
 align-items: center;
 justify-content: center;
 background: rgba(15, 23, 42, .55);
 padding: 1rem;
 }
 .tw-modal.is-open { display: flex; }
 .tw-modal-dialog { width: 100%; max-width: 32rem; }
 .tw-modal-dialog.tw-modal-lg { max-width: 56rem; }
 .tw-modal-content {
 border-radius: 1rem;
 border: 1px solid #e2e8f0;
 background: #fff;
 box-shadow: 0 20px 48px rgba(15, 23, 42, 0.25);
 }
 .tw-modal-header, .tw-modal-footer {
 display: flex;
 align-items: center;
 gap: .75rem;
 padding: .9rem 1rem;
 }
 .tw-modal-header { justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
 .tw-modal-footer { justify-content: flex-end; border-top: 1px solid #e2e8f0; }
 .tw-modal-body { padding: 1rem; }
 .tw-modal-title { margin: 0; font-size: 1rem; font-weight: 600; color: #0f172a; }
 @media (max-width: 640px) {
 .tw-modal-footer { flex-direction: column-reverse; align-items: stretch; }
 .tw-modal-footer > * { width: 100%; justify-content: center; }
 }
 .form-floating { display: flex; flex-direction: column; gap: .35rem; }
 .form-floating > label { font-size: .8125rem; color: #64748b; }
 .form-text { font-size: .75rem; color: #64748b; margin-top: .35rem; }
 .invalid-feedback { display: none; font-size: .75rem; color: #b91c1c; margin-top: .35rem; }
 .was-validated :invalid ~ .invalid-feedback { display: block; }
</style>
@endpush

@section('main')
@php
 $symbol = shop_currency($order->shop ?? null);
 $disputes = $order->disputes ?? collect();
 $activeDispute = null; $resolvedDispute = null;
 if ($disputes->isNotEmpty()) {
 $activeDispute = $disputes->where('status', '!=', 'final')->first();
 $resolvedDispute = $disputes->where('status', 'resolved')->first();
 }

 // Detect if the active dispute contains a current buyer request for return/exchange
 $buyerResolutionRequest = null;
 $exchangeRequested = false;
 if ($activeDispute && method_exists($activeDispute, 'getBuyerResolutionRequest')) {
 try {
 $buyerResolutionRequest = $activeDispute->getBuyerResolutionRequest();
 $exchangeRequested = (($buyerResolutionRequest['type'] ?? null) === 'return_exchange');
 } catch (\Throwable $e) { $exchangeRequested = false; }
 }
 if (!$exchangeRequested && $activeDispute && method_exists($activeDispute, 'messages')) {
 try {
 $msgs = $activeDispute->messages ?? collect();
 $exchangeRequested = $msgs->contains(function($m){
 $typeOk = ($m->type ?? null) === \App\Models\DisputeMessage::TYPE_SYSTEM_MESSAGE;
 $text = strtolower((string)($m->message ?? ''));
 return $typeOk && (str_contains($text, 'return/exchange') || str_contains($text, 'exchange'));
 });
 } catch (\Throwable $e) { $exchangeRequested = false; }
 }

 // Compute safe totals in case DB totals are null/missing
 $computedSubtotal = ($order->items ?? collect())->sum(function($it){
 return (float)($it->price ?? 0) * (int)($it->quantity ?? 1);
 });
 $computedShipping = ($order->items ?? collect())->sum(function($it){
 return (float)($it->shipping_cost ?? 0);
 });
 $subtotalVal = isset($order->subtotal) ? (float)$order->subtotal : (float)$computedSubtotal;
 $shippingVal = isset($order->shipping_cost) ? (float)$order->shipping_cost : (float)$computedShipping;
 $totalVal = isset($order->total_amount) ? (float)$order->total_amount : (float)($subtotalVal + $shippingVal);
 // Determine if this order contains only digital items
 $digitalOnly = ($order->items ?? collect())->every(function($it){
 return optional($it->product)->type === 'digital';
 });
@endphp

<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-4">
<div class="content">
 {{-- HEADER & ACTIONS --}}
 <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-3">
 <h2 class="text-2xl font-semibold text-emerald-600">
 <i class="fa-solid fa-receipt order-detail-icon mr-2"></i>
 Order #{{ $order->id }} Details
 </h2>

 <div class="flex flex-wrap items-center gap-2">
 @if($activeDispute && $exchangeRequested)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 border-amber-200 text-slate-900 flex gap-2" title="Buyer requested a return/exchange via dispute">
 <i class="fa-solid fa-triangle-exclamation"></i>
 The buyer requested a return or exchange and this order was restored to processing so you can ship a replacement.
 </span>
 @endif
 <a href="{{ route('orders.chat.show', $order->id) }}"
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-sky-600 text-sky-700 hover:bg-sky-50 px-2.5 py-1.5 text-xs rounded-lg flex gap-1">
 <i class="fa-solid fa-comments"></i> Messages
 </a>

 @if($order->status === \App\Models\Order::STATUS_PENDING)
 @php $paid = method_exists($order,'isPaid') ? $order->isPaid() : false; @endphp
 @if($paid)
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg flex gap-1"
 data-ui-toggle="modal"
 data-target="#processModal-{{ $order->id }}">
 <i class="fa-solid fa-gear"></i> Process
 </button>
 @include('seller.orders.modals.process')
 @else
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg flex gap-1" disabled
 title="Awaiting buyer payment">
 <i class="fa-solid fa-hourglass-half"></i> Pending Payment
 </button>
 @endif

 {{-- Cancel moved into kebab menu --}}
 @elseif($order->status === \App\Models\Order::STATUS_PROCESSING)
 @if($digitalOnly)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200 flex gap-2" title="Digital order - no shipping required">
 <i class="fa-solid fa-cloud-arrow-down"></i> Digital order
 </span>
 @else
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50 px-2.5 py-1.5 text-xs rounded-lg flex gap-1"
 data-ui-toggle="modal"
 data-target="#shipModal">
 <i class="fa-solid fa-truck"></i> Ship
 </button>
 @endif

 {{-- Cancel moved into kebab menu --}}
 @elseif($order->status === \App\Models\Order::STATUS_SHIPPED)
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg flex gap-1"
 data-ui-toggle="modal"
 data-target="#editTrackingModal">
 <i class="fa-solid fa-pen"></i> Edit Tracking
 </button>
 @endif

 {{-- More (kebab) menu with dispute/appeal actions --}}
 <div class="relative inline-block">
 <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg flex"
 id="moreActions"
 aria-expanded="false" aria-haspopup="true"
 title="More actions">
 <i class="fa fa-ellipsis-v"></i>
 </button>
 <ul class="tw-dropdown-menu tw-dropdown-menu-end" aria-labelledby="moreActions">
 @if($activeDispute)
 <li>
 <a class="tw-dropdown-item flex items-center gap-2" href="{{ route('disputes.show', $activeDispute->id) }}">
 <i class="fa-solid fa-exclamation-triangle text-amber-600"></i>
 <span>View Dispute</span>
 </a>
 </li>
 @else
 {{-- Sellers do not initiate disputes; buyers open disputes and sellers respond. --}}
 <li>
 <span class="tw-dropdown-item text-slate-500 text-xs flex items-center gap-2" title="Sellers do not initiate disputes">
 <i class="fa-solid fa-circle-info"></i>
 <span>Disputes are initiated by buyers</span>
 </span>
 </li>
 @endif
 @if(in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING]))
 <li><hr class="tw-dropdown-divider"></li>
 <li>
 <a class="tw-dropdown-item flex items-center gap-2"
 href="#" data-ui-toggle="modal" data-target="#cancelModal-{{ $order->id }}">
 <i class="fa-solid fa-times-circle text-rose-600"></i>
 <span>Cancel Order</span>
 </a>
 </li>
 @endif
 @if($resolvedDispute && method_exists($resolvedDispute,'canBeAppealed') && $resolvedDispute->canBeAppealed())
 <li><hr class="tw-dropdown-divider"></li>
 <li>
 <a class="tw-dropdown-item flex items-center gap-2" href="{{ route('disputes.appeal.create', $resolvedDispute->id) }}">
 <i class="fa-solid fa-gavel text-rose-600"></i>
 <span>Appeal Decision</span>
 </a>
 </li>
 @endif
 </ul>
 </div>
 </div>
 </div>
 {{-- Include cancel modal once for dropdown trigger --}}
 @include('seller.orders.modals.cancel')

 {{-- SUMMARY & CUSTOMER --}}
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-4">
 {{-- Order Summary --}}
 <div class="col-span-12 md:col-span-6">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-list-check"></i> Order Summary
 </div>
 <div class="p-4">
 @foreach ([
 'Tracking No' => $order->tracking_no ?? '-',
 'Courier' => $order->courier ?? '-',
 'Items' => $order->items->sum('quantity'),
 'Subtotal' => "{$symbol} ".number_format($subtotalVal,2),
 ] as $label => $value)
 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between mb-2">
 <span class="font-semibold">{{ $label }}:</span>
 <span>{{ $value }}</span>
 </div>
 @endforeach

 @if(!empty($order->tracking_url))
 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between mb-2">
 <span class="font-semibold">Tracking Link:</span>
 <span>
 <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener" class="font-medium text-sky-700 underline hover:text-sky-600">Open tracking</a>
 </span>
 </div>
 @endif

 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between mb-2">
 <span class="font-semibold">Shipping Fee:</span>
 <span>{{ $symbol }} {{ number_format($shippingVal,2) }}</span>
 </div>

 <hr>

 <div class="flex flex-col gap-1 font-bold sm:flex-row sm:justify-between mb-2">
 <span>Total Amount:</span>
 <span>{{ $symbol }} {{ number_format($totalVal,2) }}</span>
 </div>

 @php
 $minDays = null; $maxDays = null;
 foreach (($order->items ?? []) as $it) {
 $sp = $it->shippingProfile; // may be null for digital
 $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
 $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
 if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin); }
 if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax); }
 }
 $placedAt = $order->created_at instanceof \Carbon\Carbon ? $order->created_at : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);
 $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
 $shipEnd = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
 $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? 'today' : ($shipStart? $shipStart->format('M j') : null);
 $shipEndLabel = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? 'today' : ($shipEnd? $shipEnd->format('M j') : null);
 @endphp
 @if(!is_null($minDays) || !is_null($maxDays))
 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between mb-2">
 <span class="font-semibold">Ship by:</span>
 <span>
 @if($shipStart && $shipEnd)
 Ships within {{ (int)$minDays }}&ndash;{{ (int)$maxDays }} days
 ({{ $shipStartLabel }} &ndash; {{ $shipEndLabel }})
 @elseif(!is_null($minDays))
 Ships within {{ (int)$minDays }} days
 (by {{ $shipStartLabel }})
 @elseif(!is_null($maxDays))
 Ships by {{ (int)$maxDays }} days
 (by {{ $shipEndLabel }})
 @endif
 </span>
 </div>
 @endif

 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between mb-2">
 <span class="font-semibold">Status:</span>
 <span>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $order->getStatusBadgeClass() }} capitalize">
 {{ $order->getSellerStatusLabel() }}
 </span>
 </span>
 </div>

 @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between mb-2">
 <span class="font-semibold text-rose-600">Cancellation Reason:</span>
 <span class="text-rose-600">{{ $order->cancel_reason }}</span>
 </div>
 @endif

 <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
 <span class="font-semibold">Created:</span>
 <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
 </div>
 </div>
 </div>
 </div>

 {{-- Customer Info --}}
 <div class="col-span-12 md:col-span-6">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-user"></i> Customer Info
 </div>
 <div class="p-4">
 <p class="mb-1"><strong>Name:</strong> {{ $order->full_name }}</p>
 <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
 <p class="mb-3"><strong>Phone:</strong> {{ $order->phone ?? '-' }}</p>

 <p class="font-semibold mb-1">Shipping Address</p>
 <address class="mb-3">
 {{ $order->shipping_address_1 }}<br>
 @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
 {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif<br>
 {{ $order->shipping_postal_code }}
 </address>

 <p class="mb-1"><strong>Shipping Method:</strong> {{ ucfirst($order->shipping_method) }}</p>
 <p class="mb-0"><strong>Payment Method:</strong> {{ payment_method_label($order->payments->last()?->payment_method ?? $order->payment_method) }}</p>
 </div>
 </div>
 </div>
 </div>

 {{-- ITEMS (hide shipping details for digital products) --}}
 @if($order->items->isNotEmpty())
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-boxes-stacked"></i> Order Items
 </div>
 {{-- Mobile: stacked cards --}}
 <div class="block p-2 md:hidden">
 <div class="space-y-2">
 @foreach($order->items as $item)
 @php
 $product = optional($item->product);
 $isDigital = $product && $product->type === 'digital';

 $qty = (int) ($item->quantity ?? 1);
 $unit = (float) ($item->price ?? 0);

 if ($isDigital) {
 $shipLabel = 'No shipping (digital)';
 $shipCost = 0.0;
 } else {
 $sp = optional($item->shippingProfile);
 $shipLabel = $sp && $sp->dest_location_type === 'everywhere_else'
 ? 'Everywhere'
 : ($sp && $sp->destCountry ? ('Ship to '.$sp->destCountry->name) : ($sp->name ?? 'N/A'));
 $shipCost = (float) ($item->shipping_cost ?? 0);
 }

 $lineSub = $unit * $qty;
 $thumbUrl = product_thumb_url($product);
 @endphp
 <div class="rounded-xl border border-slate-200 bg-white p-3">
 <div class="flex gap-2">
 @if($thumbUrl)
 <img src="{{ $thumbUrl }}" alt="{{ $product->name ?? 'Product' }}" class="rounded" style="width:64px;height:64px;object-fit:cover;">
 @endif
 <div class="grow">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
 <div class="font-semibold truncate">
 @if($product?->slug)
 <a href="{{ route('listing.show', $product->slug) }}" class="text-slate-900 hover:text-emerald-700" target="_blank">{{ $product->name ?? 'N/A' }}</a>
 @else
 {{ $product->name ?? 'N/A' }}
 @endif
 @if($isDigital)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200 ml-1">Digital</span>
 @endif
 </div>
 <div class="ml-2 whitespace-nowrap">{{ $symbol }} {{ number_format($unit,2) }}</div>
 </div>
 <div class="text-xs text-slate-500 truncate">Listing: {{ $product->id ?? $item->product_id ?? 'N/A' }}</div>
 @if($item->variation_summary)
 <div class="text-xs text-slate-500">{{ $item->variation_summary }}</div>
 @endif
 <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between mt-1">
 <div class="text-xs"><span class="text-slate-500">Qty:</span> {{ $qty }}</div>
 <div class="text-xs text-slate-500">Shipping: {{ $isDigital ? '-' : ($shipLabel ?: '-') }}</div>
 <div class="font-semibold">{{ $symbol }} {{ number_format($lineSub,2) }}</div>
 </div>
 </div>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 {{-- Desktop/Tablet: table --}}
 <div class="hidden overflow-x-auto p-0 md:block">
 <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
 <thead class="bg-slate-50 text-slate-600 whitespace-nowrap">
 <tr>
 <th>#</th>
 <th>Image</th>
 <th>Product</th>
 <th>Listing ID</th>
 <th>Variation</th>
 <th class="text-center">Qty</th>
 <th class="text-right">Price</th>
 <th>Shipping Profile</th>
 <th class="text-right">Shipping Cost</th>
 <th class="text-right">Subtotal</th>
 </tr>
 </thead>
 <tbody>
 @foreach($order->items as $item)
 @php
 $product = optional($item->product);
 $isDigital = $product && $product->type === 'digital';

 $qty = (int) ($item->quantity ?? 1);
 $unit = (float) ($item->price ?? 0);

 if ($isDigital) {
 $shipLabel = 'No shipping (digital)';
 $shipCost = 0.0;
 } else {
 $sp = optional($item->shippingProfile);
 $shipLabel = $sp && $sp->dest_location_type === 'everywhere_else'
 ? 'Everywhere'
 : ($sp && $sp->destCountry ? ('Ship to '.$sp->destCountry->name) : ($sp->name ?? 'N/A'));
 $shipCost = (float) ($item->shipping_cost ?? 0);
 }

 $lineSub = $unit * $qty;
 $thumbUrl = product_thumb_url($product);
 @endphp
 <tr>
 <td>{{ $loop->iteration }}</td>
 <td>
 @if($thumbUrl)
 <a href="{{ $product?->slug ? route('listing.show', $product->slug) : 'javascript:void(0)' }}" target="_blank">
 <img src="{{ $thumbUrl }}" alt="{{ $product->name ?? 'Product' }}" class="h-auto max-w-full rounded" style="max-width: 80px; height:auto; object-fit: cover;">
 </a>
 @endif
 </td>
 <td>
 @if($product?->slug)
 <a href="{{ route('listing.show', $product->slug) }}" class="text-slate-900 hover:text-emerald-700" target="_blank">{{ $product->name ?? 'N/A' }}</a>
 @else
 {{ $product->name ?? 'N/A' }}
 @endif
 @if($isDigital)
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200 ml-1">Digital</span>
 @endif
 </td>
 <td>{{ $product->id ?? $item->product_id ?? 'N/A' }}</td>
 <td>{{ $item->variation_summary ?? '-' }}</td>
 <td class="text-center">{{ $qty }}</td>
 <td class="text-right">{{ $symbol }} {{ number_format($unit,2) }}</td>
 <td>{{ $isDigital ? '-' : $shipLabel }}</td>
 <td class="text-right">{{ $symbol }} {{ number_format($isDigital ? 0 : $shipCost,2) }}</td>
 <td class="text-right">{{ $symbol }} {{ number_format($lineSub,2) }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 @endif

 {{-- PAYMENTS --}}
 @if($order->payments->isNotEmpty())
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-wallet"></i> Payments
 </div>
 {{-- Mobile: stacked cards --}}
 <div class="block p-2 md:hidden">
 <div class="space-y-2">
 @foreach($order->payments as $payment)
 @php
 $raw = strtolower((string)$payment->status);
 $isCompleted = ($raw === 'success' || $raw === 'completed' || $raw === 'paid' || (string)$payment->status === '3');
 $statusText = $isCompleted ? 'Completed' : (is_numeric($payment->status) ? $payment->status : ucfirst((string)$payment->status));
 $statusColor = $isCompleted ? 'success' : match($raw){
 'pending' => 'secondary',
 'failed' => 'danger',
 default => 'dark',
 };
 $statusClass = match($statusColor){
 'success' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
 'secondary' => 'bg-slate-100 text-slate-700 border-slate-200',
 'danger' => 'bg-rose-100 text-rose-700 border-rose-200',
 default => 'bg-slate-800 text-white border-slate-800',
 };
 @endphp
 <div class="rounded-xl border border-slate-200 bg-white p-3">
 <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between mb-1">
 <div class="font-semibold break-all">{{ $payment->local_transaction_id ?? 'N/A' }}</div>
 <div class="text-xs text-slate-500">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
 </div>
 <div class="flex flex-wrap items-center justify-between gap-2">
 <div class="text-xs text-slate-500">{{ payment_method_label($payment->payment_method) }}</div>
 <div><span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }} capitalize">{{ $statusText }}</span></div>
 <div class="font-semibold">{{ $symbol }} {{ number_format($payment->total_amount,2) }}</div>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 {{-- Desktop/Tablet: table --}}
 <div class="hidden overflow-x-auto p-0 md:block">
 <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
 <thead class="bg-slate-50 text-slate-600 whitespace-nowrap">
 <tr>
 <th>#</th>
 <th>Reference</th>
 <th>Method</th>
 <th class="text-right">Amount</th>
 <th>Status</th>
 <th>Paid On</th>
 </tr>
 </thead>
 <tbody>
 @foreach($order->payments as $payment)
 @php
 $raw = strtolower((string)$payment->status);
 $isCompleted = ($raw === 'success' || $raw === 'completed' || $raw === 'paid' || (string)$payment->status === '3');
 $statusText = $isCompleted ? 'Completed' : (is_numeric($payment->status) ? $payment->status : ucfirst((string)$payment->status));
 $statusColor = $isCompleted ? 'success' : match($raw){
 'pending' => 'secondary',
 'failed' => 'danger',
 default => 'dark',
 };
 $statusClass = match($statusColor){
 'success' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
 'secondary' => 'bg-slate-100 text-slate-700 border-slate-200',
 'danger' => 'bg-rose-100 text-rose-700 border-rose-200',
 default => 'bg-slate-800 text-white border-slate-800',
 };
 @endphp
 <tr>
 <td>{{ $loop->iteration }}</td>
 <td>{{ $payment->local_transaction_id ?? 'N/A' }}</td>
 <td>{{ payment_method_label($payment->payment_method) }}</td>
 <td class="text-right">{{ $symbol }} {{ number_format($payment->total_amount,2) }}</td>
 <td><span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }} capitalize">{{ $statusText }}</span></td>
 <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 @endif

 {{-- REVIEWS ON THIS ORDER (visible on Delivered/Completed) --}}
 @php
 $isFinished = in_array($order->status, [\App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
 $reviewsOnOrder = $order->items->map(fn($it)=>$it->review)->filter();
 @endphp
 @if($isFinished)
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-star text-amber-600"></i> Reviews on this Order
 </div>
 <div class="p-4">
 @if($reviewsOnOrder->isEmpty())
 <div class="text-slate-500 text-xs">No reviews left yet for this order.</div>
 @else
 <ul class="space-y-2">
 @foreach($order->items as $item)
 @php $rev = $item->review; @endphp
 @if($rev)
 <li class="rounded-xl border border-slate-200 bg-white p-3">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
 <div>
 <div class="font-semibold">{{ optional($item->product)->name ?? 'Product' }}</div>
 <div class="text-xs text-slate-500">Rating: {{ $rev->rating }} / 5</div>
 @if($rev->comment)
 <div class="text-xs mt-1">{{ $rev->comment }}</div>
 @endif
 </div>
 <div class="ml-3">
 <a href="{{ route('orders.chat.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Message Buyer</a>
 </div>
 </div>
 </li>
 @endif
 @endforeach
 </ul>
 @endif
 </div>
 </div>
 @endif
</div>

{{-- DISPUTE INFORMATION --}}
@if($order->disputes && $order->disputes->isNotEmpty())
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-exclamation-triangle text-amber-600"></i> Dispute Information
 </div>
 <div class="p-4">
 @foreach($order->disputes as $dispute)
 <div class="border-b border-slate-200 pb-3 mb-3 @if(!$loop->last) @endif">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-2">
 <h6 class="mb-1">
 {{ $dispute->getTypeLabel() }}
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $dispute->getStatusBadgeClass() }} ml-2">
 {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
 </span>
 </h6>
 <span class="text-slate-500 text-xs">{{ $dispute->created_at->format('d M Y, h:i A') }}</span>
 </div>
 
 <p class="mb-2 text-slate-500">{{ Str::limit($dispute->description, 150) }}</p>
 
 @if($dispute->isResolved())
 <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-xs mb-2">
 <strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}
 @if($dispute->refund_amount)
 <br><strong>Refund Amount:</strong> {{ $symbol }} {{ number_format($dispute->refund_amount, 2) }}
 @endif
 </div>
 
 @if($dispute->canBeAppealed())
 <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 text-xs mb-2">
 @if($dispute->appeal_deadline)
 <strong>Appeal Deadline:</strong> {{ $dispute->getAppealDeadlineDaysLeft() }} days remaining
 @else
 <strong>Appeal Available:</strong> Submit immediately
 @endif
 </div>
 @endif
 
 @if($dispute->appeal)
 <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 text-xs mb-2">
 <strong>Appeal Status:</strong> {{ ucfirst($dispute->appeal->status) }}
 </div>
 @endif
 @endif
 
 <div class="flex gap-2">
 <a href="{{ route('disputes.show', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg">
 <i class="fa-solid fa-eye mr-1"></i> View Details
 </a>
 
 @if($dispute->canBeAppealed())
 <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400 px-2.5 py-1.5 text-xs rounded-lg">
 <i class="fa-solid fa-gavel mr-1"></i> Appeal
 </a>
 @endif
 </div>
 </div>
 @endforeach
 </div>
 </div>
@endif

{{-- EDIT TRACKING MODAL (SHIPPED + Edit) --}}
@if($order->status === \App\Models\Order::STATUS_SHIPPED)
 <div class="tw-modal" id="editTrackingModal" tabindex="-1" aria-labelledby="editTrackingLabel" aria-hidden="true">
 <div class="tw-modal-dialog tw-modal-dialog-centered tw-modal-lg">
 <form action="{{ route('seller.orders.tracking', $order) }}"
 method="POST"
 class="tw-modal-content needs-validation" novalidate>
 @csrf
 @method('PATCH')
 <div class="tw-modal-header bg-slate-50">
 <h5 class="tw-modal-title" id="editTrackingLabel">
 <i class="fa-solid fa-pen mr-2"></i>
 Edit Tracking - Order #{{ $order->id }}
 </h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>

 <div class="tw-modal-body">
 @php
 $couriers = (array) (couriers_list() ?? []);
 $courierValue = old('courier', $order->courier ?? null);
 $standardList = array_merge($couriers, ['Courier','Postal service','Express','Manual','Other']);
 $isCustomCourier = $courierValue && !in_array($courierValue, $standardList, true);
 $showOther = $isCustomCourier || in_array(strtolower((string)$courierValue), ['manual','other'], true);
 @endphp
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3 mb-3">
 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="editCourierSelect" name="courier" required>
 <option value="" disabled {{ $courierValue ? '' : 'selected' }}>Select courier...</option>
 @foreach($couriers as $c)
 <option value="{{ $c }}" {{ (string)$courierValue === (string)$c ? 'selected' : '' }}>{{ $c }}</option>
 @endforeach
 <option value="manual" {{ strtolower((string)$courierValue)==='manual' ? 'selected' : '' }}>Manual</option>
 <option value="other" {{ $showOther && strtolower((string)$courierValue)!=='manual' ? 'selected' : '' }}>Other</option>
 </select>
 <label for="editCourierSelect">Courier *</label>
 <div class="invalid-feedback">Please select a courier.</div>
 </div>
 <div class="form-floating mt-2" id="editCourierOtherWrap" style="display: {{ $showOther ? 'block' : 'none' }};">
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="editCourierOtherInput" name="courier_other" placeholder="Courier name" value="{{ old('courier_other', $isCustomCourier ? (string)$courierValue : '') }}">
 <label for="editCourierOtherInput">Courier name (if Manual/Other)</label>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="editTrackingInput" name="tracking_no" placeholder="ABC123" value="{{ old('tracking_no', $order->tracking_no) }}" required>
 <label for="editTrackingInput">Tracking number *</label>
 <div class="invalid-feedback">Tracking number required.</div>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <input type="url" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="editTrackingUrlInput" name="tracking_url" value="{{ old('tracking_url', $order->tracking_url) }}" placeholder="https://carrier.example/track/ABC123">
 <label for="editTrackingUrlInput">Tracking URL (optional)</label>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <input type="date" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="editShipDateInput" name="shipped_at" value="{{ old('shipped_at', optional($order->shipped_at)->toDateString() ?? now()->toDateString()) }}">
 <label for="editShipDateInput">Shipping date</label>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="editShipNotes" name="ship_notes" style="height: 100px;">{{ old('ship_notes', $order->ship_notes) }}</textarea>
 <label for="editShipNotes">Notes (optional)</label>
 </div>
 </div>
 </div>
 </div>

 <div class="tw-modal-footer bg-slate-50">
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100" data-ui-dismiss="modal">Cancel</button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-solid fa-floppy-disk mr-1"></i> Save Changes
 </button>
 </div>
 </form>
 </div>
 </div>
@endif
{{-- NO DISPUTES SECTION --}}
@if(!$order->disputes || $order->disputes->isEmpty())
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 font-semibold flex items-center gap-2">
 <i class="fa-solid fa-check-circle text-emerald-600"></i> Dispute Status
 </div>
 <div class="p-4 text-center">
 <p class="text-slate-500 mb-2">No disputes have been filed for this order.</p>
 <p class="text-xs text-slate-500 mb-0">
 Sellers do not initiate disputes. If a buyer opens a dispute, it will appear here and you’ll be notified to respond.
 </p>
 </div>
 </div>
@endif

{{-- SHIPPING MODAL (PROCESSING + Ship) --}}
@if($order->status === \App\Models\Order::STATUS_PROCESSING)
 <div class="tw-modal" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
 <div class="tw-modal-dialog tw-modal-dialog-centered tw-modal-lg">
 <form action="{{ route('seller.orders.ship', $order) }}"
 method="POST"
 class="tw-modal-content needs-validation" novalidate>
 @csrf
 <div class="tw-modal-header bg-slate-50">
 <h5 class="tw-modal-title" id="shipModalLabel">
 <i class="fa-solid fa-truck-fast mr-2"></i>
 Ship Order #{{ $order->id }}
 </h5>
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
 </div>

 <div class="px-4 pt-3">
 <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-xs mb-0">
 <strong>Customer:</strong> {{ $order->full_name }} &nbsp;|&nbsp;
 <strong>Total:</strong> {{ $symbol }} {{ number_format($order->total_amount,2) }}
 </div>
 </div>

 <div class="tw-modal-body">
 @php
 // SAFELY PREDEFINE ALL COURIER VARS ONCE
 $couriers = (array) (couriers_list() ?? []);
 $courierValue = old('courier', $order->courier ?? null);
 $standardList = array_merge($couriers, ['Courier','Postal service','Express','Manual','Other']);
 // in_array with strict to avoid truthy/loose surprises
 $isCustomCourier = $courierValue && !in_array($courierValue, $standardList, true);
 $showOther = $isCustomCourier || in_array(strtolower((string)$courierValue), ['manual','other'], true);
 @endphp

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3 mb-4">
 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="courierSelect" name="courier" data-target="#courierOtherWrap" required>
 <option value="" disabled {{ $courierValue ? '' : 'selected' }}>Select courier...</option>
 @foreach($couriers as $c)
 <option value="{{ $c }}" {{ (string)$courierValue === (string)$c ? 'selected' : '' }}>{{ $c }}</option>
 @endforeach
 <option value="manual" {{ strtolower((string)$courierValue)==='manual' ? 'selected' : '' }}>Manual</option>
 <option value="other" {{ $showOther && strtolower((string)$courierValue)!=='manual' ? 'selected' : '' }}>Other</option>
 </select>
 <label for="courierSelect">Courier *</label>
 <div class="invalid-feedback">Please select a courier.</div>
 </div>

 <div class="form-floating mt-2" id="courierOtherWrap" style="display: {{ $showOther ? 'block' : 'none' }};">
 <input type="text"
 class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
 id="courierOtherInput"
 name="courier_other"
 placeholder="Courier name"
 value="{{ old('courier_other', $isCustomCourier ? (string)$courierValue : '') }}">
 <label for="courierOtherInput">Courier name (if Manual/Other)</label>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="trackingInput" name="tracking_no" placeholder="ABC123" required>
 <label for="trackingInput">Tracking number *</label>
 <div class="invalid-feedback">Tracking number required.</div>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <input type="url" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="trackingUrlInput" name="tracking_url" placeholder="https://carrier.example/track/ABC123" required>
 <label for="trackingUrlInput">Tracking URL *</label>
 <div class="invalid-feedback">Tracking URL is required.</div>
 <div class="form-text">Paste a direct tracking link from the courier.</div>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <input type="date" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="shipDateInput" name="shipped_at" value="{{ old('shipped_at', now()->toDateString()) }}">
 <label for="shipDateInput">Shipping date</label>
 </div>
 </div>

 <div class="col-span-12 md:col-span-6">
 <div class="form-floating">
 <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="shipNotes" name="ship_notes" style="height: 100px;"></textarea>
 <label for="shipNotes">Notes (optional)</label>
 </div>
 </div>
 </div>

 {{-- Items & Shipping Profiles section removed per request --}}
 </div>

 <div class="tw-modal-footer bg-slate-50">
 <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100" data-ui-dismiss="modal">Cancel</button>
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fa-solid fa-truck mr-1"></i> Mark as Shipped
 </button>
 </div>
 </form>
 </div>
 </div>
@endif
</div>
 </div>
 </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
 const body = document.body;
 const openModal = (selector) => {
 const modal = document.querySelector(selector);
 if (!modal) return;
 modal.classList.add('is-open');
 body.classList.add('overflow-hidden');
 };
 const closeModal = (modal) => {
 if (!modal) return;
 modal.classList.remove('is-open');
 if (!document.querySelector('.tw-modal.is-open')) {
 body.classList.remove('overflow-hidden');
 }
 };

 const dropdownToggle = document.getElementById('moreActions');
 const dropdownMenu = dropdownToggle ? dropdownToggle.nextElementSibling : null;
 const closeDropdown = () => {
 if (!dropdownToggle || !dropdownMenu) return;
 dropdownMenu.classList.remove('show');
 dropdownToggle.setAttribute('aria-expanded', 'false');
 };

 document.querySelectorAll('[data-ui-toggle="modal"][data-target]').forEach((trigger) => {
 trigger.addEventListener('click', function (event) {
 event.preventDefault();
 closeDropdown();
 openModal(this.getAttribute('data-target'));
 });
 });

 document.querySelectorAll('[data-ui-dismiss="modal"]').forEach((button) => {
 button.addEventListener('click', function () {
 closeModal(this.closest('.tw-modal'));
 });
 });

 document.querySelectorAll('.tw-modal').forEach((modal) => {
 modal.addEventListener('click', function (event) {
 if (event.target === modal) closeModal(modal);
 });
 });

 if (dropdownToggle && dropdownMenu) {
 dropdownToggle.addEventListener('click', function (event) {
 event.preventDefault();
 event.stopPropagation();
 const open = dropdownMenu.classList.toggle('show');
 dropdownToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
 });

 document.addEventListener('click', function (event) {
 if (!dropdownMenu.contains(event.target) && !dropdownToggle.contains(event.target)) {
 closeDropdown();
 }
 });
 }

 document.addEventListener('keydown', function (event) {
 if (event.key === 'Escape') {
 closeDropdown();
 const topModal = document.querySelector('.tw-modal.is-open');
 if (topModal) closeModal(topModal);
 }
 });

 const params = new URLSearchParams(window.location.search);
 const shouldShowByQuery = params.get('ship') === '1';
 const shouldShowByErrors = Boolean(@json(optional($errors->getBag('ship'))->any()));
 const shouldShowByFlag = Boolean(@json(session('show_ship_modal', false)));
 if (shouldShowByQuery || shouldShowByErrors || shouldShowByFlag) {
 openModal('#shipModal');
 }

 document.querySelectorAll('.needs-validation').forEach(form => {
 form.addEventListener('submit', e => {
 if (!form.checkValidity()) {
 e.preventDefault();
 e.stopPropagation();
 }
 form.classList.add('was-validated');
 });
 });

 const courierSelect = document.getElementById('courierSelect');
 const courierWrap = document.getElementById('courierOtherWrap');
 const toggleCourier = () => {
 if(!courierSelect || !courierWrap) return;
 const v = (courierSelect.value || '').toLowerCase();
 courierWrap.style.display = (v === 'other' || v === 'manual') ? 'block' : 'none';
 };
 courierSelect && courierSelect.addEventListener('change', toggleCourier);
 toggleCourier();

 const editSel = document.getElementById('editCourierSelect');
 const editWrap= document.getElementById('editCourierOtherWrap');
 function toggleEditCourier(){
 if(!editSel || !editWrap) return;
 const v = (editSel.value || '').toLowerCase();
 editWrap.style.display = (v === 'other' || v === 'manual') ? 'block' : 'none';
 }
 editSel && editSel.addEventListener('change', toggleEditCourier);
 toggleEditCourier();
});
</script>
@endpush

