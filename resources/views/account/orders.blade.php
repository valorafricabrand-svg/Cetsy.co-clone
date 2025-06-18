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
              <tr>
                <td>#{{ $order->id }}</td>
                <td><a target="_blank" href="{{ route('shop.show', $order->shop) }}"> {{ optional($order->shop)->name ?? 'N/A' }}</a></td>
                <td>{{ $order->created_at->format('d M Y') }}</td>
                <td>
                  <span class="badge {{ $order->getStatusBadgeClass() }}">
                    {{ ucfirst($order->status) }}
                  </span>
                </td>
                <td>{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</td>
                <td>
                  <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary me-1">View</a>
                  @if($order->status === \App\Models\Order::STATUS_PENDING)
                    <a href="{{ route('pay_now', $order->id) }}" class="btn btn-sm btn-primary">Pay Now</a>
                  @endif

                   <a href="{{ route('orders.chat.show', $order->id) }}"
           class="btn btn-sm btn-outline-info me-1">
          Messages
        </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-center">
        {{ $orders->withQueryString()->links() }}
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
