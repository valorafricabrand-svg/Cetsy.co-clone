@extends('theme.'.theme().'.layouts.app')

@section('title', __('My Orders'))

@section('main')
@php
  $statusLabels = [
      \App\Models\Order::STATUS_PENDING => __('Pending'),
      \App\Models\Order::STATUS_PROCESSING => __('Processing'),
      \App\Models\Order::STATUS_SHIPPED => __('Shipped'),
      \App\Models\Order::STATUS_DELIVERED => __('Delivered'),
      \App\Models\Order::STATUS_COMPLETED => __('Completed'),
      \App\Models\Order::STATUS_CANCELLED => __('Cancelled'),
      \App\Models\Order::STATUS_REFUNDED => __('Refunded'),
  ];
  $statusCounts = [
      'all' => $summary['all'] ?? 0,
      \App\Models\Order::STATUS_PENDING => $summary['pending'] ?? 0,
      \App\Models\Order::STATUS_PROCESSING => $summary['processing'] ?? 0,
      \App\Models\Order::STATUS_SHIPPED => $summary['shipped'] ?? 0,
      \App\Models\Order::STATUS_DELIVERED => $summary['delivered'] ?? 0,
      \App\Models\Order::STATUS_COMPLETED => $summary['completed'] ?? 0,
      \App\Models\Order::STATUS_CANCELLED => $summary['cancelled'] ?? 0,
      \App\Models\Order::STATUS_REFUNDED => $summary['refunded'] ?? 0,
  ];
  $formatShortDate = static function ($value) {
      if (! $value) {
          return null;
      }

      if (! ($value instanceof \Carbon\Carbon)) {
          try {
              $value = \Carbon\Carbon::parse($value);
          } catch (\Throwable $e) {
              return null;
          }
      }

      return $value->translatedFormat('d M Y');
  };
  $formatDateTime = static function ($value) {
      if (! $value) {
          return null;
      }

      if (! ($value instanceof \Carbon\Carbon)) {
          try {
              $value = \Carbon\Carbon::parse($value);
          } catch (\Throwable $e) {
              return null;
          }
      }

      return $value->translatedFormat('M j, Y h:i A');
  };
  $progressMessageFor = static function ($order, $dispatchBy) use ($formatDateTime) {
      if ($order->status === \App\Models\Order::STATUS_COMPLETED) {
          $completedAt = $formatDateTime($order->completed_at ?: $order->delivered_at);

          return $completedAt
              ? __('Completed on :date', ['date' => $completedAt])
              : __('Completed');
      }

      if ($order->status === \App\Models\Order::STATUS_DELIVERED) {
          $deliveredAt = $formatDateTime($order->delivered_at);

          return $deliveredAt
              ? __('Delivered on :date', ['date' => $deliveredAt])
              : __('Delivered');
      }

      if ($order->status === \App\Models\Order::STATUS_SHIPPED) {
          $shippedAt = $formatDateTime($order->shipped_at);

          return $shippedAt
              ? __('Shipped on :date', ['date' => $shippedAt])
              : __('Shipped');
      }

      if ($dispatchBy) {
          return __('Dispatch by :date', ['date' => $dispatchBy]);
      }

      if (in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING], true)) {
          return __('Dispatch soon');
      }

      return null;
  };
@endphp
<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>
      <div class="col-span-12 lg:col-span-9">
    <div class="mb-3">
      <div class="mb-3">
        <h3 class="mb-1 text-2xl font-semibold text-slate-900">{{ __('My Orders') }}</h3>
        <p class="text-sm text-slate-500">{{ __('Track status, payment progress and shipping updates for all your orders.') }}</p>
      </div>
      <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" action="{{ url()->current() }}" class="w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="grid grid-cols-12 items-end gap-2">
            <div class="col-span-12 md:col-span-4">
              <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
              <input type="search" name="q" value="{{ request('q') }}" class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('Search orders by # or status') }}">
            </div>
            <div class="col-span-6 md:col-span-3">
              <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('From') }}</label>
              <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="col-span-6 md:col-span-3">
              <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('To') }}</label>
              <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="col-span-6 md:col-span-2">
              <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Sort') }}</label>
              <select name="sort" class="w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="newest" @selected(request('sort','newest')==='newest')>{{ __('Newest') }}</option>
                <option value="amount_desc" @selected(request('sort')==='amount_desc')>{{ __('Amount (high to low)') }}</option>
                <option value="amount_asc" @selected(request('sort')==='amount_asc')>{{ __('Amount (low to high)') }}</option>
              </select>
            </div>
            <div class="col-span-12 flex flex-col gap-2 md:col-span-12 sm:flex-row">
              <div class="inline-flex flex-wrap gap-2" role="group" aria-label="{{ __('Status filters') }}">
                @php $st = request('status'); @endphp
                <a class="inline-flex items-center justify-center rounded-xl border px-3 py-1.5 text-xs font-semibold transition {{ $st ? 'border-slate-300 text-slate-700 hover:bg-slate-50' : 'border-slate-900 bg-slate-900 text-white hover:bg-slate-700' }}" href="{{ url()->current() }}">{{ __('All') }}@if(($statusCounts['all'] ?? 0) > 0) ({{ $statusCounts['all'] }}) @endif</a>
                @foreach($statusLabels as $code => $label)
                  <a class="inline-flex items-center justify-center rounded-xl border px-3 py-1.5 text-xs font-semibold transition {{ $st === $code ? 'border-slate-900 bg-slate-900 text-white hover:bg-slate-700' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}" href="{{ url()->current().'?'.http_build_query(array_filter(array_merge(request()->except('page'),['status'=>$code]))) }}">
                    {{ $label }}@if(($statusCounts[$code] ?? 0) > 0) ({{ $statusCounts[$code] }}) @endif
                  </a>
                @endforeach
              </div>
              <button class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500 sm:ml-auto" type="submit">{{ __('Apply') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    @if($orders->isNotEmpty())
      <div class="mb-4 hidden overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
        <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
          <thead class="bg-slate-50">
            <tr>
              <th>{{ __('Order #') }}</th>
              <th>{{ __('Shop') }}</th>
              <th>{{ __('Date') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Total') }}</th>
              <th>{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
              @php
                $shopName = optional($order->shop)->localized_name ?? optional($order->shop)->name ?? __('N/A');
                $minDays = null;
                $maxDays = null;
                foreach (($order->items ?? []) as $it) {
                  $sp = $it->shippingProfile;
                  $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                  $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                  if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int) $pMin : min($minDays, (int) $pMin); }
                  if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int) $pMax : max($maxDays, (int) $pMax); }
                }
                $placedAt = $order->created_at instanceof \Carbon\Carbon ? $order->created_at : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);
                $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
                $shipEnd = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
                $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? __('today') : ($shipStart ? $shipStart->translatedFormat('M j') : null);
                $shipEndLabel = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? __('today') : ($shipEnd ? $shipEnd->translatedFormat('M j') : null);
                $dispatchBy = $shipEndLabel ?? $shipStartLabel;
                $progressMessage = $progressMessageFor($order, $dispatchBy);
              @endphp
              <tr class="js-order-row cursor-pointer hover:bg-slate-50" data-href="{{ route('buyer.orders.show', $order->id) }}" tabindex="0">
                <td>#{{ $order->id }}</td>
                <td>
                  @if($order->shop)
                    <a target="_blank" href="{{ localized_route('shop.show', $order->shop) }}" class="font-medium text-slate-800 hover:text-emerald-700">
                      {{ $shopName }}
                    </a>
                  @else
                    <span class="text-slate-500">{{ __('N/A') }}</span>
                  @endif
                </td>
                <td>{{ $formatShortDate($order->created_at) }}</td>
                <td>
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }}">
                    {{ $order->status_label }}
                  </span>
                  @if($order->status === \App\Models\Order::STATUS_PENDING)
                    <div class="mt-1 text-xs font-semibold text-amber-600">{{ __('Pending payment') }}</div>
                  @endif
                  @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED], true) && $order->cancel_reason)
                    <br><small class="text-rose-600">{{ Str::limit($order->cancel_reason, 50) }}</small>
                  @endif
                  @if(!empty($progressMessage))
                    <div class="mt-1 text-xs text-slate-500">{{ $progressMessage }}</div>
                  @endif
                </td>
                <td>{{ money((float) ($order->total_amount ?? 0)) }}</td>
                <td class="text-nowrap">
                  <a href="{{ route('buyer.orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">{{ __('View') }}</a>
                  <a href="{{ route('orders.chat.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">{{ __('Chat') }}</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mb-4 block md:hidden">
        <div class="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
          @foreach($orders as $order)
            @php
              $shopName = optional($order->shop)->localized_name ?? optional($order->shop)->name ?? __('N/A');
              $minDays = null;
              $maxDays = null;
              foreach (($order->items ?? []) as $it) {
                $sp = $it->shippingProfile;
                $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                if (is_numeric($pMin)) { $minDays = is_null($minDays) ? (int) $pMin : min($minDays, (int) $pMin); }
                if (is_numeric($pMax)) { $maxDays = is_null($maxDays) ? (int) $pMax : max($maxDays, (int) $pMax); }
              }
              $placedAt = $order->created_at instanceof \Carbon\Carbon ? $order->created_at : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);
              $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
              $shipEnd = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
              $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? __('today') : ($shipStart ? $shipStart->translatedFormat('M j') : null);
              $shipEndLabel = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? __('today') : ($shipEnd ? $shipEnd->translatedFormat('M j') : null);
              $dispatchBy = $shipEndLabel ?? $shipStartLabel;
              $progressMessage = $progressMessageFor($order, $dispatchBy);
            @endphp

            <a href="{{ route('buyer.orders.show', $order->id) }}" class="block px-4 py-3 transition hover:bg-slate-50">
              <div class="mb-1 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                <div class="font-semibold">#{{ $order->id }}</div>
                <div class="text-xs text-slate-500">{{ $formatShortDate($order->created_at) }}</div>
              </div>
              <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }}">{{ $order->status_label }}</span>
                @if($order->status === \App\Models\Order::STATUS_PENDING)
                  <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-slate-900">{{ __('Pending payment') }}</span>
                @endif
                @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED], true) && $order->cancel_reason)
                  <small class="text-rose-600">{{ Str::limit($order->cancel_reason, 50) }}</small>
                @endif
              </div>
              @if(!empty($progressMessage))
                <div class="mb-2 text-xs text-slate-500">{{ $progressMessage }}</div>
              @endif
              <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 truncate">
                  <span class="text-xs text-slate-500">{{ __('Shop') }}:</span>
                  <span class="text-xs">{{ $shopName }}</span>
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
              if (t.closest('a,button')) return;
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

      <div class="flex justify-center">
          {{ $orders->links('pagination::tailwind') }}
      </div>
    @else
      <div class="rounded-2xl border border-slate-200 bg-white py-8 text-center shadow-sm">
        <i class="bi bi-cart-x text-5xl text-slate-400"></i>
        <h4 class="mt-3 text-xl font-semibold text-slate-900">{{ __('No orders found') }}</h4>
        <p class="mb-4 text-slate-500">{{ __('You have no orders at the moment.') }}</p>
        <a href="{{ localized_route('listings') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white transition hover:bg-emerald-500">{{ __('Start Shopping') }}</a>
      </div>
    @endif
      </div>
    </div>
  </div>
</div>
@endsection
