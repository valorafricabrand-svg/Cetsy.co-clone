@extends('layouts.app')

@section('content')
<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <h1 class="mb-4">My Orders</h1>

      @if($orders->isEmpty())
        <div class="text-center py-5">
          <p class="h5 text-muted mb-4">You have no orders yet.</p>
          <a href="{{ route('products.index') }}"
             class="btn btn-primary btn-lg">
            Shop Now
          </a>
        </div>
      @else
        <div class="list-group">
          @foreach($orders as $order)
            <a href="{{ route('orders.show', $order) }}"
               class="list-group-item list-group-item-action mb-3 shadow-sm rounded">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">Order #{{ $order->id }}</h5>
                <small class="text-muted">{{ $order->created_at->format('M j, Y') }}</small>
              </div>
              <p class="mb-1">
                <span class="me-3"><strong>Items:</strong> {{ $order->items->count() }}</span>
                <span><strong>Total:</strong> {{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span>
              </p>
              <small>
                <strong>Status:</strong>
                <span class="badge 
                  @switch($order->status)
                    @case('pending') bg-warning text-dark @break
                    @case('completed') bg-success @break
                    @case('cancelled') bg-danger @break
                    @default bg-secondary @break
                  @endswitch
                ">
                  {{ ucfirst($order->status) }}
                </span>
              </small>
            </a>
          @endforeach
        </div>

        <div class="mt-4">
          {{ $orders->links() }}
        </div>
      @endif

    </div>
  </div>
</div>
@endsection
