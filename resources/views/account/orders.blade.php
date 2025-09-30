@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="content">
  <div class="container">
    {{-- Header with title and search/filter --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
      <h3 class="text-dark mb-3 mb-md-0">My Orders</h3>
      <form class="d-flex w-100 w-md-auto" method="GET" action="{{ url()->current() }}">
        <input
          type="search"
          name="q"
          value="{{ request('q') }}"
          class="form-control form-control-sm me-2"
          placeholder="Search orders..."
          aria-label="Search orders"
        >
        <button class="btn btn-sm btn-primary" type="submit">Search</button>
      </form>
    </div>

    @if($orders->isNotEmpty())
      {{-- Orders List Table --}}
      <div class="table-responsive mb-4">
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
                    $placedAt = optional($order->created_at);
                    $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
                    $shipEnd   = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
                    $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? 'today' : ($shipStart? $shipStart->format('M j') : null);
                    $shipEndLabel   = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? 'today' : ($shipEnd? $shipEnd->format('M j') : null);
                  @endphp
                  @php
                    // Choose a single dispatch-by date: prefer end if present, else start
                    $dispatchBy = $shipEndLabel ?? $shipStartLabel;
                  @endphp
                  <div class="small text-muted mt-1">
                    @if($dispatchBy)
                      Dispatch by {{ $dispatchBy }}
                    @else
                      Dispatch soon
                    @endif
                  </div>
                </td>
                <td>{{ money((float) ($order->total_amount ?? 0)) }}</td>
                <td>
                  <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
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


