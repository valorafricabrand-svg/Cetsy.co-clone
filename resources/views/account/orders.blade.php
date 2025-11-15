@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="content">
  <div class="container">
    {{-- Header with title and filters --}}
    <div class="mb-3">
      <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-end justify-content-between">
        <h3 class="text-dark mb-0">My Orders</h3>
        <form method="GET" action="{{ url()->current() }}" class="w-100">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
              <label class="form-label small text-muted">Search</label>
              <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search orders by # or status">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label small text-muted">From</label>
              <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label small text-muted">To</label>
              <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label small text-muted">Sort</label>
              <select name="sort" class="form-select form-select-sm">
                <option value="newest" @selected(request('sort','newest')==='newest')>Newest</option>
                <option value="amount_desc" @selected(request('sort')==='amount_desc')>Amount (high to low)</option>
                <option value="amount_asc" @selected(request('sort')==='amount_asc')>Amount (low to high)</option>
              </select>
            </div>
            <div class="col-12 col-md-12 d-flex gap-2">
              <div class="btn-group" role="group" aria-label="Status filters">
                @php $st = request('status'); @endphp
                <a class="btn btn-sm {{ $st? 'btn-outline-secondary':'btn-secondary' }}" href="{{ url()->current() }}">All{{ isset($summary['all'])? ' ('.$summary['all'].')':'' }}</a>
                @foreach([
                  \App\Models\Order::STATUS_PENDING=>'Pending',
                  \App\Models\Order::STATUS_PROCESSING=>'Processing',
                  \App\Models\Order::STATUS_SHIPPED=>'Shipped',
                  \App\Models\Order::STATUS_DELIVERED=>'Delivered',
                  \App\Models\Order::STATUS_COMPLETED=>'Completed',
                  \App\Models\Order::STATUS_CANCELLED=>'Cancelled',
                  \App\Models\Order::STATUS_REFUNDED=>'Refunded',
                ] as $code=>$label)
                  <a class="btn btn-sm {{ $st===$code? 'btn-secondary':'btn-outline-secondary' }}" href="{{ url()->current().'?'.http_build_query(array_filter(array_merge(request()->except('page'),['status'=>$code]))) }}">
                    {{ $label }}@if(!empty($summary[strtolower($label)])) ({{ $summary[strtolower($label)] }}) @endif
                  </a>
                @endforeach
              </div>
              <button class="btn btn-sm btn-primary ms-auto" type="submit">Apply</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    @if($orders->isNotEmpty())
      {{-- Orders List (Desktop/Tablet) --}}
      <div class="table-responsive mb-4 d-none d-md-block">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
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
              <tr class="js-order-row" data-href="{{ route('buyer.orders.show', $order->id) }}" style="cursor:pointer;" tabindex="0">
                <td>#{{ $order->id }}</td>
                <td><a target="_blank" href="{{ route('shop.show', $order->shop) }}"> {{ optional($order->shop)->name ?? 'N/A' }}</a></td>
                <td>{{ $order->created_at->format('d M Y') }}</td>
                <td>
                  <span class="badge {{ $order->getStatusBadgeClass() }}">
                    {{ ucfirst($order->status) }}
                  </span>
                  @if($order->status === \App\Models\Order::STATUS_PENDING)
                    <div class="small text-warning fw-semibold mt-1">Pending payment</div>
                  @endif
                  @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                    <br><small class="text-danger">{{ Str::limit($order->cancel_reason, 50) }}</small>
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
                    <div class="small text-muted mt-1">{{ $progressMessage }}</div>
                  @endif
                </td>
                <td>{{ money((float) ($order->total_amount ?? 0)) }}</td>
                <td class="text-nowrap">
                  <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                  <a href="{{ route('orders.chat.show', $order->id) }}" class="btn btn-sm btn-outline-primary">Chat</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Orders List (Mobile Cards) --}}
      <div class="d-block d-md-none mb-4">
        <div class="list-group">
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

            <a href="{{ route('buyer.orders.show', $order->id) }}" class="list-group-item list-group-item-action p-3">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="fw-semibold">#{{ $order->id }}</div>
                <div class="text-muted small">{{ $order->created_at->format('d M Y') }}</div>
              </div>
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge {{ $order->getStatusBadgeClass() }}">{{ ucfirst($order->status) }}</span>
                @if($order->status === \App\Models\Order::STATUS_PENDING)
                  <span class="badge bg-warning text-dark">Pending payment</span>
                @endif
                @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                  <small class="text-danger">{{ Str::limit($order->cancel_reason, 50) }}</small>
                @endif
              </div>
              @if(!empty($progressMessage))
                <div class="small text-muted mb-2">{{ $progressMessage }}</div>
              @endif
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-truncate">
                  <span class="text-muted small">Shop:</span>
                  <span class="small">{{ optional($order->shop)->name ?? 'N/A' }}</span>
                </div>
                <div class="fw-semibold">{{ money((float) ($order->total_amount ?? 0)) }}</div>
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
      <div class="d-flex justify-content-center">
          {{ $orders->links('pagination::bootstrap-5') }}
      </div>
    @else
      {{-- Empty state --}}
      <div class="text-center py-5">
        <i class="bi bi-cart-x" style="font-size: 3rem; color: #6c757d;"></i>
        <h4 class="mt-3">No orders found</h4>
        <p class="mb-4 text-muted">You have no orders at the moment.</p>
        <a href="{{ route('listings') }}" class="btn btn-lg btn-primary">Start Shopping</a>
      </div>
    @endif
  </div>
</div>
@endsection

