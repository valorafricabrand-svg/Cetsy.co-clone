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

    <div class="table-responsive">
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
                  <a href="{{ route('shop.show', $order->shop->id) }}">{{ $order->shop->name }}</a>
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

    <div class="mt-3">
      <a class="btn btn-outline-secondary" href="{{ route('account.orders') }}">Go to My Orders</a>
    </div>
  </div>
  </section>
@endsection

