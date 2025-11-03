{{-- resources/views/account/orders_created.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Orders Created')

@section('main')
<section class="py-5" style="margin-top:100px;">
  <div class="container">
    @if(session('success'))
      <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h3 class="mb-4">Your Orders by Shop</h3>
    <p class="text-muted">We created separate orders for each shop in your cart. You can review and pay each order individually.</p>

    <div class="table-responsive d-none d-md-block">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Order #</th>
            <th>Shop</th>
            <th>Status</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Shipping</th>
            <th class="text-end">Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($orders as $order)
            <tr>
              <td>{{ $order->id }}</td>
              <td>
                @if($order->shop)
<a href="{{ route('shop.show', $order->shop->slug) }}">{{ $order->shop->name }}</a>
                @else
                  <span class="text-muted">Unknown shop</span>
                @endif
              </td>
              <td>{{ ucfirst($order->status) }}</td>
              <td class="text-end">{{ get_currency() }} {{ number_format((float)$order->subtotal,2) }}</td>
              <td class="text-end">{{ get_currency() }} {{ number_format((float)$order->shipping_cost,2) }}</td>
              <td class="text-end fw-semibold">{{ get_currency() }} {{ number_format((float)$order->total_amount,2) }}</td>
              <td>
                @if(method_exists($order,'isPaid') && $order->isPaid())
                  <a class="btn btn-outline-secondary btn-sm" href="{{ route('buyer.orders.show', $order->id) }}">View</a>
                @else
                  <a class="btn btn-primary btn-sm" href="{{ route('pay_now', $order->id) }}">Pay Now</a>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Mobile list (cards) --}}
    <div class="d-block d-md-none mb-3">
      <div class="list-group">
        @foreach($orders as $order)
          @php
            $currency = get_currency();
          @endphp
          <div class="list-group-item p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <div class="fw-semibold">#{{ $order->id }}</div>
              <div class="small">
                <span class="badge {{ method_exists($order,'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-secondary' }} text-capitalize">
                  {{ ucfirst($order->status) }}
                </span>
              </div>
            </div>
            <div class="mb-2 text-truncate">
              <span class="text-muted small">Shop:</span>
              @if($order->shop)
<a href="{{ route('shop.show', $order->shop->slug) }}" class="text-decoration-none">{{ $order->shop->name }}</a>
              @else
                <span class="text-muted">Unknown shop</span>
              @endif
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div class="small text-muted">Subtotal</div>
              <div>{{ $currency }} {{ number_format((float)$order->subtotal,2) }}</div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div class="small text-muted">Shipping</div>
              <div>{{ $currency }} {{ number_format((float)$order->shipping_cost,2) }}</div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Total</div>
              <div class="fw-semibold">{{ $currency }} {{ number_format((float)$order->total_amount,2) }}</div>
            </div>
            <div class="mt-2">
              @if(method_exists($order,'isPaid') && $order->isPaid())
                <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('buyer.orders.show', $order->id) }}">View</a>
              @else
                <a class="btn btn-primary btn-sm w-100" href="{{ route('pay_now', $order->id) }}">Pay Now</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="mt-3">
      <a class="btn btn-outline-secondary" href="{{ route('account.orders') }}">Go to My Orders</a>
    </div>
  </div>
  </section>
@endsection
