@extends('theme.'.theme().'.layouts.app')

@section('title', 'My Orders')

@section('main')
<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>
      <div class="col-span-12 lg:col-span-9">
    {{-- Header with title and filters --}}
    <div class="mb-3">
      <div class="mb-3">
        <h3 class="mb-1 text-2xl font-semibold text-slate-900">My Orders</h3>
        <p class="text-sm text-slate-500">Track status, payment progress and shipping updates for all your orders.</p>
      </div>
      <div class="flex flex-col lg:flex-row gap-3 lg:items-end justify-between">
        <form method="GET" action="{{ url()->current() }}" class="w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="grid grid-cols-12 gap-2 items-end">
            <div class="col-span-12 md:col-span-4">
              <label class="mb-1 block text-xs font-medium text-slate-500">Search</label>
              <input type="search" name="q" value="{{ request('q') }}" class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Search orders by # or status">
            </div>
            <div class="col-span-6 md:col-span-3">
              <label class="mb-1 block text-xs font-medium text-slate-500">From</label>
              <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="col-span-6 md:col-span-3">
              <label class="mb-1 block text-xs font-medium text-slate-500">To</label>
              <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="col-span-6 md:col-span-2">
              <label class="mb-1 block text-xs font-medium text-slate-500">Sort</label>
              <select name="sort" class="w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="newest" @selected(request('sort','newest')==='newest')>Newest</option>
                <option value="amount_desc" @selected(request('sort')==='amount_desc')>Amount (high to low)</option>
                <option value="amount_asc" @selected(request('sort')==='amount_asc')>Amount (low to high)</option>
              </select>
            </div>
            <div class="col-span-12 md:col-span-12 flex flex-col gap-2 sm:flex-row">
              <div class="inline-flex flex-wrap gap-2" role="group" aria-label="Status filters">
                @php $st = request('status'); @endphp
                <a class="inline-flex items-center justify-center rounded-xl border px-3 py-1.5 text-xs font-semibold transition {{ $st ? 'border-slate-300 text-slate-700 hover:bg-slate-50' : 'border-slate-900 bg-slate-900 text-white hover:bg-slate-700' }}" href="{{ url()->current() }}">All{{ isset($summary['all'])? ' ('.$summary['all'].')':'' }}</a>
                @foreach([
                  \App\Models\Order::STATUS_PENDING=>'Pending',
                  \App\Models\Order::STATUS_PROCESSING=>'Processing',
                  \App\Models\Order::STATUS_SHIPPED=>'Shipped',
                  \App\Models\Order::STATUS_DELIVERED=>'Delivered',
                  \App\Models\Order::STATUS_COMPLETED=>'Completed',
                  \App\Models\Order::STATUS_CANCELLED=>'Cancelled',
                  \App\Models\Order::STATUS_REFUNDED=>'Refunded',
                ] as $code=>$label)
                  <a class="inline-flex items-center justify-center rounded-xl border px-3 py-1.5 text-xs font-semibold transition {{ $st === $code ? 'border-slate-900 bg-slate-900 text-white hover:bg-slate-700' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}" href="{{ url()->current().'?'.http_build_query(array_filter(array_merge(request()->except('page'),['status'=>$code]))) }}">
                    {{ $label }}@if(!empty($summary[strtolower($label)])) ({{ $summary[strtolower($label)] }}) @endif
                  </a>
                @endforeach
              </div>
              <button class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500 sm:ml-auto" type="submit">Apply</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    @if($orders->isNotEmpty())
      {{-- Orders List (Desktop/Tablet) --}}
      <div class="overflow-x-auto mb-4 hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
        <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
          <thead class="bg-slate-50">
            <tr>
              <th>Order #</th>
              <th>Shop</th>
              <th>Date</th>
              <th>Status</th>
              <th>Total</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
              <tr class="js-order-row cursor-pointer hover:bg-slate-50" data-href="{{ route('buyer.orders.show', $order->id) }}" tabindex="0">
                <td>#{{ $order->id }}</td>
                <td>
                  @if($order->shop)
                    <a target="_blank" href="{{ route('shop.show', $order->shop) }}" class="font-medium text-slate-800 hover:text-emerald-700">
                      {{ optional($order->shop)->name ?? 'N/A' }}
                    </a>
                  @else
                    <span class="text-slate-500">N/A</span>
                  @endif
                </td>
                <td>{{ $order->created_at->format('d M Y') }}</td>
                <td>
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }}">
                    {{ ucfirst($order->status) }}
                  </span>
                  @if($order->status === \App\Models\Order::STATUS_PENDING)
                    <div class="text-xs text-amber-600 font-semibold mt-1">Pending payment</div>
                  @endif
                  @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                    <br><small class="text-rose-600">{{ Str::limit($order->cancel_reason, 50) }}</small>
                  @endif
                  @php
                    $minDays = null; $maxDays = null;
                    foreach (($order->items ?? []) as $it) {
                      $sp = $it->shippingProfile;
                      $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                      $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                      if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin); }
                      if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax); }
                    }
                    $placedAt = $order->created_at instanceof \Carbon\Carbon ? $order->created_at : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);
                    $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
                    $shipEnd   = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
                    $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? 'today' : ($shipStart? $shipStart->format('M j') : null);
                    $shipEndLabel   = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? 'today' : ($shipEnd? $shipEnd->format('M j') : null);
                  @endphp
                  @php
                    // Choose a single dispatch-by date: prefer end if present, else start
                    $dispatchBy = $shipEndLabel ?? $shipStartLabel;
                  @endphp
                  @php
                    $formatDateTime = static function ($value) {
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
                    $progressMessage = null;
                    if ($order->status === \App\Models\Order::STATUS_COMPLETED) {
                      $progressMessage = $formatDateTime($order->completed_at ?: $order->delivered_at) 
                        ? 'Completed on '.$formatDateTime($order->completed_at ?: $order->delivered_at)
                        : 'Completed';
                    } elseif ($order->status === \App\Models\Order::STATUS_DELIVERED) {
                      $progressMessage = $formatDateTime($order->delivered_at)
                        ? 'Delivered on '.$formatDateTime($order->delivered_at)
                        : 'Delivered';
                    } elseif ($order->status === \App\Models\Order::STATUS_SHIPPED) {
                      $progressMessage = $formatDateTime($order->shipped_at)
                        ? 'Shipped on '.$formatDateTime($order->shipped_at)
                        : 'Shipped';
                    } elseif ($dispatchBy) {
                      $progressMessage = 'Dispatch by '.$dispatchBy;
                    } elseif (in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING])) {
                      $progressMessage = 'Dispatch soon';
                    } else {
                      $progressMessage = null; // no progress text for cancelled/refunded and others
                    }
                  @endphp
                  @if(!empty($progressMessage))
                    <div class="text-xs text-slate-500 mt-1">{{ $progressMessage }}</div>
                  @endif
                </td>
                <td>{{ money((float) ($order->total_amount ?? 0)) }}</td>
                <td class="text-nowrap">
                  <a href="{{ route('buyer.orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View</a>
                  <a href="{{ route('orders.chat.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">Chat</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Orders List (Mobile Cards) --}}
      <div class="mb-4 block md:hidden">
        <div class="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
          @foreach($orders as $order)
            @php
              $minDays = null; $maxDays = null;
              foreach (($order->items ?? []) as $it) {
                $sp = $it->shippingProfile;
                $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin); }
                if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax); }
              }
              $placedAt = $order->created_at instanceof \Carbon\Carbon ? $order->created_at : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);
              $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
              $shipEnd   = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
              $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? 'today' : ($shipStart? $shipStart->format('M j') : null);
              $shipEndLabel   = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? 'today' : ($shipEnd? $shipEnd->format('M j') : null);
              $dispatchBy = $shipEndLabel ?? $shipStartLabel;
              $formatDateTime = static function ($value) {
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
                $progressMessage = $formatDateTime($order->completed_at ?: $order->delivered_at)
                  ? 'Completed on '.$formatDateTime($order->completed_at ?: $order->delivered_at)
                  : 'Completed';
              } elseif ($order->status === \App\Models\Order::STATUS_DELIVERED) {
                $progressMessage = $formatDateTime($order->delivered_at)
                  ? 'Delivered on '.$formatDateTime($order->delivered_at)
                  : 'Delivered';
              } elseif ($order->status === \App\Models\Order::STATUS_SHIPPED) {
                $progressMessage = $formatDateTime($order->shipped_at)
                  ? 'Shipped on '.$formatDateTime($order->shipped_at)
                  : 'Shipped';
              } elseif ($dispatchBy) {
                $progressMessage = 'Dispatch by '.$dispatchBy;
              } elseif (in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING])) {
                $progressMessage = 'Dispatch soon';
              } else {
                $progressMessage = null; // no progress text for cancelled/refunded and others
              }
            @endphp

            <a href="{{ route('buyer.orders.show', $order->id) }}" class="block px-4 py-3 transition hover:bg-slate-50">
              <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between mb-1">
                <div class="font-semibold">#{{ $order->id }}</div>
                <div class="text-slate-500 text-xs">{{ $order->created_at->format('d M Y') }}</div>
              </div>
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }}">{{ ucfirst($order->status) }}</span>
                @if($order->status === \App\Models\Order::STATUS_PENDING)
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-slate-900">Pending payment</span>
                @endif
                @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                  <small class="text-rose-600">{{ Str::limit($order->cancel_reason, 50) }}</small>
                @endif
              </div>
              @if(!empty($progressMessage))
                <div class="text-xs text-slate-500 mb-2">{{ $progressMessage }}</div>
              @endif
              <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 truncate">
                  <span class="text-slate-500 text-xs">Shop:</span>
                  <span class="text-xs">{{ optional($order->shop)->name ?? 'N/A' }}</span>
                </div>
                <div class="font-semibold">{{ money((float) ($order->total_amount ?? 0)) }}</div>
              </div>
            </a>
          @endforeach
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function(){
          document.querySelectorAll('.js-order-row').forEach(function(row){
            row.addEventListener('click', function(e){
              const t = e.target;
              if (t.closest('a,button')) return; // let inner links/buttons work normally
              const href = row.getAttribute('data-href');
              if (href) window.location = href;
            });
            row.addEventListener('keydown', function(e){
              if (e.key === 'Enter' || e.key === ' ') {
                const href = row.getAttribute('data-href');
                if (href) window.location = href;
              }
            });
          });
        });
      </script>

      {{-- Pagination --}}
      <div class="flex justify-center">
          {{ $orders->links('pagination::tailwind') }}
      </div>
    @else
      {{-- Empty state --}}
      <div class="rounded-2xl border border-slate-200 bg-white py-8 text-center shadow-sm">
        <i class="bi bi-cart-x text-5xl text-slate-400"></i>
        <h4 class="mt-3 text-xl font-semibold text-slate-900">No orders found</h4>
        <p class="mb-4 text-slate-500">You have no orders at the moment.</p>
        <a href="{{ route('listings') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white transition hover:bg-emerald-500">Start Shopping</a>
      </div>
    @endif
      </div>
    </div>
  </div>
</div>
@endsection




