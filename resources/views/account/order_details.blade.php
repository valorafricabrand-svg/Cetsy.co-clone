{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="content">
  <div class="container-xxl">

    {{-- ===== HEADER ===== --}}
    <div class="border-bottom pb-3 mb-4">

      @include('account.stepper')

      <div
        class="d-flex flex-column flex-md-row justify-content-md-between
               align-items-start align-items-md-center gap-3">

        {{-- Title --}}
        <h2 class="mb-0 text-success fw-semibold d-flex align-items-center gap-1">
          <i class="bi bi-receipt-cutoff"></i>
          Order&nbsp;#{{ $order->id }}&nbsp;Details
        </h2>

        {{-- Action buttons --}}
        <div class="btn-toolbar flex-wrap gap-2">

          @if($order->status === \App\Models\Order::STATUS_PENDING)
            <a href="{{ route('pay_now', $order->id) }}"
               class="btn btn-primary btn-lg d-flex align-items-center gap-2 px-4 py-2">
              <i class="bi bi-credit-card fs-5"></i>
              <span>Pay&nbsp;Now</span>
            </a>
          @endif

          <a href="{{ route('orders.chat.show', $order->id) }}"
             class="btn btn-outline-info btn-lg d-flex align-items-center gap-2 px-4 py-2">
            <i class="bi bi-chat-dots fs-5"></i>
            <span>Messages</span>
          </a>

          <a href="{{ route('account.orders') }}"
             class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2 px-4 py-2">
            <i class="bi bi-arrow-left-circle fs-5"></i>
            <span>Back</span>
          </a>

          @if($order->status === \App\Models\Order::STATUS_SHIPPED)
            <button class="btn btn-outline-success btn-lg d-flex align-items-center gap-2 px-4 py-2"
                    data-bs-toggle="modal"
                    data-bs-target="#deliverModal-{{ $order->id }}">
              <i class="bi bi-check2-circle fs-5"></i>
              <span>Mark&nbsp;Delivered</span>
            </button>
            @include('seller.orders.modals.delivered')
          @endif

        </div>
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
            <div class="col-12 col-md-6">
              <span class="fw-semibold">Owner:</span> {{ optional($order->shop->user)->name ?? 'N/A' }}
            </div>
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
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Tracking No:</span><span>{{ $order->tracking_no ?? 'N/A' }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Courier:</span><span>{{ $order->courier ?? 'N/A' }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Quantity:</span><span>{{ $order->items->sum('quantity') }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Subtotal:</span>
                <span>{{ get_currency() }} {{ number_format($order->subtotal,2) }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Shipping Fee:</span>
                <span>{{ get_currency() }} {{ number_format($order->shipping_cost,2) }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Total Amount:</span>
                <span class="fw-bold">{{ get_currency() }} {{ number_format($order->total_amount,2) }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Status:</span>
                <span class="badge {{ $order->getStatusBadgeClass() }} px-3 py-2 text-uppercase">
                  {{ ucfirst($order->status) }}
                </span>
              </li>
              @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                <li class="list-group-item px-0 d-flex justify-content-between">
                  <span class="fw-semibold text-danger">Cancellation Reason:</span>
                  <span class="text-danger">{{ $order->cancel_reason }}</span>
                </li>
              @endif
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Created:</span>
                <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
              </li>
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
              <li class="list-group-item px-0">
                <span class="fw-semibold">Name:</span> {{ $order->full_name }}
              </li>
              <li class="list-group-item px-0">
                <span class="fw-semibold">Email:</span> {{ $order->email }}
              </li>
              <li class="list-group-item px-0">
                <span class="fw-semibold">Phone:</span> {{ $order->phone }}
              </li>
              <li class="list-group-item px-0">
                <span class="fw-semibold">Shipping Address:</span>
                <address class="mb-0">
                  {{ $order->shipping_address_1 }}<br>
                  @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                  {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif<br>
                  @if($order->shipping_postal_code){{ $order->shipping_postal_code }}<br>@endif
                  @if(method_exists($order, 'shippingCountry') && ($order->relationLoaded('shippingCountry') || optional($order->shippingCountry)->id))
                    {{ optional($order->shippingCountry)->name }}
                  @endif
                </address>
              </li>
              <li class="list-group-item px-0">
                <span class="fw-semibold">Shipping Method:</span> {{ ucfirst($order->shipping_method) }}
              </li>
              <li class="list-group-item px-0">
                <span class="fw-semibold">Payment Method:</span> {{ ucfirst($order->payment_method) }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== ORDER ITEMS (uses variation_summary) + DOWNLOADS ===== --}}
    @php
      $canReviewOrder = ($order->status === \App\Models\Order::STATUS_DELIVERED);
    @endphp

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
                  <th>Image</th>
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
                    $product   = optional($item->product);
                    $reviewed  = $item->review !== null;
                    $modalId   = 'reviewModal_'.$item->id;

                    // Shipping profile label – same rule as cart
                    $sp = optional($item->shippingProfile);
                    $label = $sp && $sp->dest_location_type === 'everywhere_else'
                              ? 'Everywhere'
                              : ($sp && $sp->destCountry ? 'Ship to '.$sp->destCountry->name : ($sp->name ?? 'N/A'));

                    $shipCost     = (float) ($item->shipping_cost ?? 0);
                    $qty          = (int)   ($item->quantity ?? 1);
                    $unit         = (float) ($item->price ?? 0);
                    $lineSubtotal = $unit * $qty;
                    $lineTotal    = $lineSubtotal + $shipCost;

                    // image
                    $thumbUrl = $product?->featured_image
                        ?: ($product?->media->first()?->url ? asset('storage/'.$product->media->first()->url) : null);

                    // Downloads still follow your previous rule (Processing/Completed)
                    $canDownload = in_array(
                                      $order->status,
                                      [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED]
                                   ) && $product && $product->type === 'digital';
                  @endphp

                  <tr>
                    <td>{{ $loop->iteration }}</td>

                    {{-- Image --}}
                    <td>
                      @if($thumbUrl)
                        <a href="{{ route('listing.show', $product->slug) }}" target="_blank">
                          <img
                            src="{{ $thumbUrl }}"
                            alt="{{ $product->name }}"
                            class="img-fluid rounded"
                            style="max-width:100px; height:auto; object-fit:cover;"
                          >
                        </a>
                      @endif
                    </td>

                    {{-- Product name --}}
                    <td>
                      @if($product)
                        <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="text-decoration-none">
                          {{ $product->name }}
                        </a>
                      @else
                        <span class="text-muted">N/A</span>
                      @endif
                    </td>

                    {{-- Variation: saved summary string --}}
                    <td>{{ $item->variation_summary ?? '—' }}</td>

                    {{-- Qty --}}
                    <td>{{ $qty }}</td>

                    {{-- Unit Price --}}
                    <td>{{ get_currency() }} {{ number_format($unit, 2) }}</td>

                    {{-- Shipping profile --}}
                    <td>{{ $label }}</td>

                    {{-- Shipping cost --}}
                    <td>{{ get_currency() }} {{ number_format($shipCost, 2) }}</td>

                    {{-- Line total --}}
                    <td class="fw-semibold">{{ get_currency() }} {{ number_format($lineTotal, 2) }}</td>

                    {{-- Review --}}
                    <td class="text-center">
                      @if($reviewed)
                        <span class="badge bg-success d-inline-flex align-items-center gap-1">
                          <i class="bi bi-check-circle"></i>
                          {{ $item->review->rating }} ⭐
                        </span>
                      @else
                        @if($canReviewOrder)
                          <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                            <i class="bi bi-star"></i> Review
                          </button>
                        @else
                          <span class="text-muted small">Available after delivery</span>
                        @endif
                      @endif
                    </td>

                    {{-- Downloads (digital products only + allowed statuses) --}}
                    <td>
                      @if($canDownload && $product && $product->digitalFiles->count())
                        <div class="card mb-0 shadow-sm">
                          <ul class="list-group list-group-flush">
                            @foreach($product->digitalFiles as $file)
                              <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('digital-files.download', $file) }}"
                                   target="_blank"
                                   class="d-inline-flex align-items-center">
                                  <i class="fas fa-file-download me-2"></i> {{ $file->filename }}
                                </a>
                              </li>
                            @endforeach
                          </ul>
                        </div>
                      @else
                        <span class="text-muted">—</span>
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
                <tr>
                  <th>#</th>
                  <th>Reference</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Paid On</th>
                </tr>
              </thead>
              <tbody>
                @foreach($order->payments as $pay)
                  @php
                    $statusStr   = strtolower((string)$pay->status);
                    $isCompleted = (string)$pay->status === '3' || $statusStr === 'success' || $statusStr === 'completed';
                    $statusLabel = $isCompleted ? 'Completed' : (is_numeric($pay->status) ? $pay->status : ucfirst((string)$pay->status));
                  @endphp
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pay->local_transaction_id }}</td>
                    <td>{{ ucfirst($pay->payment_method) }}</td>
                    <td>{{ get_currency() }} {{ number_format($pay->total_amount,2) }}</td>
                    <td>
                      <span class="badge {{ $isCompleted ? 'bg-success' : 'bg-secondary' }}">
                        {{ $statusLabel }}
                      </span>
                    </td>
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

{{-- ===== REVIEW MODALS (only when Delivered and not already reviewed) ===== --}}
@php $canReviewOrder = ($order->status === \App\Models\Order::STATUS_DELIVERED); @endphp
@foreach($order->items as $item)
  @if($canReviewOrder && !$item->review)
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
                @for($i=5;$i>=1;$i--)
                  <option value="{{ $i }}">{{ $i }} ⭐</option>
                @endfor
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Comment <span class="text-muted">(optional)</span></label>
              <textarea name="comment" rows="4" class="form-control" placeholder="Share details of your experience"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary">
              <i class="bi bi-send"></i> Submit Review
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endforeach
@endsection
