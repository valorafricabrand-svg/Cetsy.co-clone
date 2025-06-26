{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="content">
  <div class="container-xxl">

    {{-- ===== HEADER ===== --}}
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center gap-3 mb-4">
      <h2 class="mb-0 text-success fw-semibold">
        <i class="bi bi-receipt-cutoff me-1"></i>
        Order&nbsp;#{{ $order->id }}&nbsp;Details
      </h2>

      <div class="btn-toolbar gap-2">
        <a href="{{ route('orders.chat.show', $order->id) }}"
           class="btn btn-outline-info btn-sm d-flex align-items-center gap-1">
          <i class="bi bi-chat-dots"></i> Messages
        </a>

        <a href="{{ route('account.orders') }}"
           class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
          <i class="bi bi-arrow-left-circle"></i> Back
        </a>

        @if($order->status === \App\Models\Order::STATUS_PENDING)
          <a href="{{ route('pay_now', $order->id) }}"
             class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-credit-card"></i> Pay&nbsp;Now
          </a>
        @endif

        @if($order->status === \App\Models\Order::STATUS_SHIPPED)
          <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1"
                  data-bs-toggle="modal"
                  data-bs-target="#deliverModal-{{ $order->id }}">
            <i class="bi bi-check2-circle"></i> Mark Delivered
          </button>
          @include('seller.orders.modals.delivered')
        @endif
      </div>
    </div>

    {{-- ===== SHOP DETAILS ===== --}}
    @if($order->shop)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
          <i class="bi bi-shop-window text-primary"></i> Shop&nbsp;Details
        </div>
        <div class="card-body small">
          <div class="row gy-2">
            <div class="col-12 col-md-6"><span class="fw-semibold">Name:</span> {{ $order->shop->name }}</div>
            @if($order->shop->description)
              <div class="col-12"><span class="fw-semibold">Description:</span> {{ $order->shop->description }}</div>
            @endif
            @if($order->shop->address)
              <div class="col-12"><span class="fw-semibold">Address:</span><br>{{ $order->shop->address }}</div>
            @endif
            <div class="col-12 col-md-6"><span class="fw-semibold">Owner:</span> {{ optional($order->shop->user)->name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
    @endif

    <div class="row g-4">
      {{-- ===== ORDER SUMMARY ===== --}}
      <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-clipboard-data text-primary"></i> Order&nbsp;Summary
          </div>
          <div class="card-body small">
            <ul class="list-group list-group-flush">
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Tracking No:</span><span>{{ $order->tracking_no ?? 'N/A' }}</span></li>
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Courier:</span><span>{{ $order->courier ?? 'N/A' }}</span></li>
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Quantity:</span><span>{{ $order->items->sum('quantity') }}</span></li>
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Subtotal:</span><span>{{ get_currency() }} {{ number_format($order->subtotal,2) }}</span></li>
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Shipping Fee:</span><span>{{ get_currency() }} {{ number_format($order->shipping_cost,2) }}</span></li>
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Total Amount:</span><span class="fw-bold">{{ get_currency() }} {{ number_format($order->total_amount,2) }}</span></li>
              <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Status:</span>
                <span class="badge {{ $order->getStatusBadgeClass() }} px-3 py-2 text-uppercase">{{ ucfirst($order->status) }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between"><span class="fw-semibold">Created:</span><span>{{ $order->created_at->format('d M Y, h:i A') }}</span></li>
            </ul>
          </div>
        </div>
      </div>

      {{-- ===== CUSTOMER INFO ===== --}}
      <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-person-vcard text-primary"></i> Customer&nbsp;Info
          </div>
          <div class="card-body small">
            <ul class="list-group list-group-flush">
              <li class="list-group-item px-0"><span class="fw-semibold">Name:</span> {{ $order->full_name }}</li>
              <li class="list-group-item px-0"><span class="fw-semibold">Email:</span> {{ $order->email }}</li>
              <li class="list-group-item px-0"><span class="fw-semibold">Phone:</span> {{ $order->phone }}</li>
              <li class="list-group-item px-0">
                <span class="fw-semibold">Shipping Address:</span>
                <address class="mb-0">{{ $order->shipping_address_1 }}<br>@if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif{{ $order->shipping_city }}, {{ $order->shipping_state }}<br>{{ $order->shipping_postal_code }}</address>
              </li>
              <li class="list-group-item px-0"><span class="fw-semibold">Shipping Method:</span> {{ ucfirst($order->shipping_method) }}</li>
              <li class="list-group-item px-0"><span class="fw-semibold">Payment Method:</span> {{ ucfirst($order->payment_method) }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== ORDER ITEMS w/ DOWNLOADS ===== --}}
    @if($order->items->count())
      <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
          <i class="bi bi-box-seam text-primary"></i> Order&nbsp;Items
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
              <thead class="table-light text-nowrap">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Variation</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Shipping Profile</th>
                  <th>Shipping Cost</th>
                  <th>Subtotal</th>
                  <th class="text-center">Review</th>
                  <th style="min-width:160px">Downloads</th>
                </tr>
              </thead>

              <tbody>
                @foreach($order->items as $item)
                  @php
                    $reviewed  = $item->review !== null;
                    $modalId   = 'reviewModal_'.$item->id;
                    $profile   = optional($item->shippingProfile)->name ?? 'N/A';
                    $shipCost  = $item->shipping_cost ?? 0;
                    $canDownload = in_array(
                      $order->status,
                      [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED]
                    ) && optional($item->product)->type === 'digital';
                  @endphp

                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                    <td>{{ $item->variation_details ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ get_currency() }} {{ number_format($item->price,2) }}</td>
                    <td>{{ $profile }}</td>
                    <td>{{ get_currency() }} {{ number_format($shipCost,2) }}</td>
                    <td class="fw-semibold">{{ get_currency() }} {{ number_format($item->price*$item->quantity + $shipCost,2) }}</td>

                    {{-- review column --}}
                    <td class="text-center">
                      @if($reviewed)
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> {{ $item->review->rating }} ⭐</span>
                      @else
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                          <i class="bi bi-star"></i> Review
                        </button>
                      @endif
                    </td>

                    {{-- downloads column --}}
                    <td>
                        @if($item->product->digitalFiles->count())
      <div class="card mb-4 shadow-sm">
        
        <ul class="list-group list-group-flush">
          @foreach($item->product->digitalFiles as $file)
            <li class="list-group-item d-flex justify-content-between align-items-center">

              <a href="{{ route('digital-files.download', $file) }}" target="_blank" class="d-inline-flex align-items-center">
  <i class="fas fa-file-download me-2"></i>{{ $file->filename }}
</a>


            
           
            </li>
          @endforeach
        </ul>
      </div>
    @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    {{-- ===== PAYMENTS ===== --}}
    @if($order->payments->count())
      <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
          <i class="bi bi-wallet2 text-primary"></i> Payments
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
              <thead class="table-light text-nowrap">
                <tr><th>#</th><th>Reference</th><th>Method</th><th>Amount</th><th>Status</th><th>Paid On</th></tr>
              </thead>
              <tbody>
                @foreach($order->payments as $pay)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pay->local_transaction_id }}</td>
                    <td>{{ ucfirst($pay->payment_method) }}</td>
                    <td>{{ get_currency() }} {{ number_format($pay->total_amount,2) }}</td>
                    <td><span class="badge {{ $pay->status === 'success' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($pay->status) }}</span></td>
                    <td>{{ $pay->created_at->format('d M Y, h:i A') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    {{-- ===== ADDITIONAL INFO ===== --}}
    @if($order->order_notes || $order->promo_code)
      <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
          <i class="bi bi-info-circle text-primary"></i> Additional&nbsp;Information
        </div>
        <div class="card-body small">
          @if($order->order_notes)
            <p class="mb-2"><span class="fw-semibold">Order Notes:</span><br>{{ $order->order_notes }}</p>
          @endif
          @if($order->promo_code)
            <p class="mb-0"><span class="fw-semibold">Promo Code:</span> {{ $order->promo_code }}</p>
          @endif
        </div>
      </div>
    @endif

  </div>
</div>

{{-- ===== REVIEW MODALS ===== --}}
@foreach($order->items as $item)
  @if(!$item->review)
    @php($modalId = 'reviewModal_'.$item->id)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('orders.items.reviews.store',[$item->order_id,$item->id]) }}" class="modal-content">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Review – {{ optional($item->product)->name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Rating</label>
              <select name="rating" class="form-select" required>
                <option value="" hidden>Choose…</option>
                @for($i=5;$i>=1;$i--)<option value="{{ $i }}">{{ $i }} ⭐</option>@endfor
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Comment <span class="text-muted">(optional)</span></label>
              <textarea name="comment" rows="4" class="form-control" placeholder="Share details of your experience"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary"><i class="bi bi-send"></i> Submit Review</button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endforeach
@endsection
