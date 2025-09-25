{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Details')

@push('styles')
<style>
  /* Compact summary chips below header */
  .chip { display:inline-flex; align-items:center; gap:.45rem; padding:.35rem .6rem; border-radius:999px; background:#f1f3f5; font-size:.8125rem; }
  .chip i { opacity:.85; }
  .chip .label-muted { color:#6c757d; }

  /* Print stylesheet: clean invoice look */
  @media print {
    @page { margin: 12mm; }
    html, body { background: #fff !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 12px; }

    /* Hide nav/footer/UI chrome and actions */
    nav, .navbar, footer, .footer, .btn-toolbar, .btn, .alert, .modal, .chip, .stepper, .no-print { display: none !important; }

    /* Expand content */
    .container, .container-xxl, .content { max-width: 100% !important; padding: 0 !important; }

    /* Headings */
    h1, h2, h3, h4, h5 { color: #000 !important; }

    /* Cards and tables with clear borders */
    .card { box-shadow: none !important; border: 1px solid #000 !important; page-break-inside: avoid; }
    .card-header { border-bottom: 1px solid #000 !important; }
    .card-body { padding: 12px !important; }
    .table { border-collapse: collapse !important; }
    .table th, .table td { border: 1px solid #000 !important; }
    .table thead { border-bottom: 2px solid #000 !important; }
    .table-striped > tbody > tr:nth-of-type(odd) { --bs-table-accent-bg: transparent !important; }

    /* Badges render as plain framed text */
    .badge { background: transparent !important; color: #000 !important; border: 1px solid #000 !important; }

    /* Links without URLs appended */
    a[href]::after { content: '' !important; }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], .has-tooltip'));
    tooltipTriggerList.forEach(function (el) { try { new bootstrap.Tooltip(el); } catch(e) {} });
  });
  // Allow quick Ctrl/Cmd+P to show clean print
  window.addEventListener('keydown', function(e){
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
      document.body.classList.add('printing');
      setTimeout(function(){ document.body.classList.remove('printing'); }, 2000);
    }
  });
</script>
@endpush

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
    Order&nbsp;#{{ $order->id }}&nbsp;&mdash;&nbsp;Details
  </h2>

        {{-- Action buttons --}}
        <div class="btn-toolbar flex-wrap gap-2">

          @if($order->status === \App\Models\Order::STATUS_PENDING)
            <a href="{{ route('pay_now', $order->id) }}"
               class="btn btn-primary btn-lg d-flex align-items-center gap-2 px-4 py-2"
               data-bs-toggle="tooltip" data-bs-placement="bottom" title="Proceed to payment for this order">
              <i class="bi bi-credit-card fs-5"></i>
              <span>Pay&nbsp;Now</span>
            </a>
          @endif

          <a href="{{ route('orders.chat.show', $order->id) }}"
             class="btn btn-outline-info btn-lg d-flex align-items-center gap-2 px-4 py-2"
             data-bs-toggle="tooltip" data-bs-placement="bottom" title="Open conversation about this order">
            <i class="bi bi-chat-dots fs-5"></i>
            <span>Messages</span>
          </a>

          <button type="button"
                  class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2 px-4 py-2 no-print"
                  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Print a clean invoice view"
                  onclick="window.print()">
            <i class="bi bi-printer fs-5"></i>
            <span>Print</span>
          </button>

          <a href="{{ route('account.orders') }}"
             class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2 px-4 py-2"
             data-bs-toggle="tooltip" data-bs-placement="bottom" title="Return to your orders">
            <i class="bi bi-arrow-left-circle fs-5"></i>
            <span>Back</span>
          </a>

          @if($order->status === \App\Models\Order::STATUS_SHIPPED)
            <button class="btn btn-outline-success btn-lg d-flex align-items-center gap-2 px-4 py-2 has-tooltip"
                    data-bs-placement="bottom" title="Confirm you received the order"
                    data-bs-toggle="modal"
                    data-bs-target="#deliverModal-{{ $order->id }}">
              <i class="bi bi-check2-circle fs-5"></i>
              <span>Mark&nbsp;Delivered</span>
            </button>
            @include('seller.orders.modals.delivered')
          @endif

        </div>
  </div>

  {{-- Processing timeline & ship-by notice --}}
  @php
    $minDays = null; $maxDays = null;
    foreach (($order->items ?? []) as $it) {
      $sp = $it->shippingProfile; // may be null (digital)
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

    $status = (string) $order->status;
    $stepPlaced     = true;
    $stepProcessing = in_array($status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepShipped    = in_array($status, [\App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepDelivered  = in_array($status, [\App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepCompleted  = ($status === \App\Models\Order::STATUS_COMPLETED);
  @endphp

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <div class="col-12 col-md-6">
          <div class="fw-semibold mb-1">Processing Timeline</div>
          <ul class="list-unstyled small mb-0">
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-check-circle {{ $stepPlaced ? 'text-success' : 'text-muted' }}"></i>
              <span>Order placed {{ $placedAt? $placedAt->format('M j, Y') : '' }}</span>
            </li>
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-gear-fill {{ $stepProcessing ? 'text-success' : 'text-muted' }}"></i>
              <span>
                @if($shipStart && $shipEnd)
                  Ships within {{ (int)$minDays }}&ndash;{{ (int)$maxDays }} days ({{ $shipStartLabel }} &ndash; {{ $shipEndLabel }})
                @elseif(!is_null($minDays))
                  Ships within {{ (int)$minDays }} days (by {{ $shipStartLabel }})
                @elseif(!is_null($maxDays))
                  Ships by {{ (int)$maxDays }} days (by {{ $shipEndLabel }})
                @else
                  Processing
                @endif
              </span>
            </li>
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-truck {{ $stepShipped ? 'text-success' : 'text-muted' }}"></i>
              <span>Shipped</span>
            </li>
            <li class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-box-seam {{ $stepDelivered ? 'text-success' : 'text-muted' }}"></i>
              <span>Delivered</span>
            </li>
            <li class="d-flex align-items-center gap-2">
              <i class="bi bi-flag {{ $stepCompleted ? 'text-success' : 'text-muted' }}"></i>
              <span>Completed</span>
            </li>
          </ul>
        </div>
        <div class="col-12 col-md-6">
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            @if($shipStart && $shipEnd)
              Ship-by window: <strong>{{ $shipStartLabel }} &ndash; {{ $shipEndLabel }}</strong>
            @elseif($shipStart)
              Ship by: <strong>{{ $shipStartLabel }}</strong>
            @elseif($shipEnd)
              Ship by: <strong>{{ $shipEndLabel }}</strong>
            @else
              Seller will ship your order soon.
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
      {{-- Summary chips: status, placed, items, total --}}
      <div class="mt-3 d-flex flex-wrap gap-2">
        <span class="chip">
          <i class="bi bi-activity"></i>
          <span class="label-muted">Status:</span>
          <span class="badge {{ $order->getStatusBadgeClass() }} text-uppercase">{{ ucfirst($order->status) }}</span>
        </span>
        <span class="chip">
          <i class="bi bi-calendar-event"></i>
          <span class="label-muted">Placed:</span>
          <span>{{ $order->created_at->format('d M Y') }}</span>
        </span>
        <span class="chip">
          <i class="bi bi-bag"></i>
          <span class="label-muted">Items:</span>
          <span>{{ $order->items->sum('quantity') }}</span>
        </span>
        <span class="chip">
          <i class="bi bi-cash-coin"></i>
          <span class="label-muted">Total:</span>
          <span class="fw-semibold">{{ money() }}</span>
        </span>
      </div>

      @php
        $hasDigital = $order->items->contains(function($it){ return optional($it->product)->type === 'digital'; });
        $needsDownload = $order->items->contains(function($it){ return optional($it->product)->type === 'digital' && empty($it->downloaded_at); });
      @endphp
      @if($hasDigital && $needsDownload)
        <div class="alert alert-info mt-3 mb-0" role="alert">
          <i class="bi bi-cloud-download me-2"></i>
          Digital order: funds release and reviews unlock after your first download.
        </div>
      @endif
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
                <span>{{ money() }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Shipping Fee:</span>
                <span>{{ money() }}</span>
              </li>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <span class="fw-semibold">Total Amount:</span>
                <span class="fw-bold">{{ money() }}</span>
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

    {{-- ===== ORDER ITEMS (uses variation_summary, hides shipping for digital) + DOWNLOADS ===== --}}
    @php
      $canReviewDelivered = ($order->status === \App\Models\Order::STATUS_DELIVERED);
      $canReviewDigitalIfCompleted = ($order->status === \App\Models\Order::STATUS_COMPLETED) || $canReviewDelivered;
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
                    $product    = optional($item->product);
                    $reviewed   = $item->review !== null;
                    $modalId    = 'reviewModal_'.$item->id;
                    $isDigital  = $product && $product->type === 'digital';

                    // Shipping label + cost (hidden/zero for digital)
                    if ($isDigital) {
                        $label    = 'No shipping (digital)';
                        $shipCost = 0.0;
                    } else {
                        $sp    = optional($item->shippingProfile);
                        $label = $sp && $sp->dest_location_type === 'everywhere_else'
                                 ? 'Everywhere'
                                 : ($sp && $sp->destCountry ? 'Ship to '.$sp->destCountry->name : ($sp->name ?? 'N/A'));
                        $shipCost = (float) ($item->shipping_cost ?? 0);
                    }

                    $qty          = (int)   ($item->quantity ?? 1);
                    $unit         = (float) ($item->price ?? 0);
                    $lineSubtotal = $unit * $qty;
                    $lineTotal    = $lineSubtotal + $shipCost;

                    // image
                    $thumbUrl = product_thumb_url($product);

                    // Downloads: allow on Processing/Completed for digital products
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
                            style="max-width:100px; height:auto; object-fit:cover;">
                        </a>
                      @endif
                    </td>

                    {{-- Product --}}
                    <td>
                      @if($product)
                        <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="text-decoration-none">
                          {{ $product->name }}
                        </a>
                        @if($isDigital)
                          <span class="badge bg-secondary ms-1">Digital</span>
                        @endif
                      @else
                        <span class="text-muted">&mdash;</span>
                      @endif
                    </td>

                    {{-- Variation: saved summary --}}
                    <td>@if($item->variation_summary) {{ $item->variation_summary }} @else &mdash; @endif</td>

                    {{-- Qty --}}
                    <td>{{ $qty }}</td>

                    {{-- Unit Price --}}
                    <td>{{ money() }}</td>

                    {{-- Shipping profile (or hidden for digital) --}}
                    <td>{{ $label }}</td>

                    {{-- Shipping cost --}}
                    <td>{{ money() }}</td>

                    {{-- Line total --}}
                    <td class="fw-semibold">{{ money() }}</td>

                    {{-- Review (only after delivery) --}}
                    <td class="text-center">
                      @if($reviewed)
                        <span class="badge bg-success d-inline-flex align-items-center gap-1">
                          <i class="bi bi-check-circle"></i>
                          {{ $item->review->rating }} &#9733;
                        </span>
                      @elseif($canReviewDelivered || ($product && $product->type === 'digital' && $canReviewDigitalIfCompleted))
                        @php $downloaded = !empty($item->downloaded_at); @endphp
                        @if($product && $product->type === 'digital' && ! $downloaded)
                          <span class="text-muted small">Download required to review</span>
                        @else
                          <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                            <i class="bi bi-star"></i> Review
                          </button>
                        @endif
                      @else
                        <span class="text-muted small">Available after delivery</span>
                      @endif
                    </td>

                    {{-- Downloads (digital + allowed statuses) --}}
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
                        <span class="text-muted">&mdash;</span>
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
                    <td>{{ money() }}</td>
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

{{-- ===== REVIEW MODALS (Delivered for physical; Completed/Delivered + download for digital) ===== --}}
@foreach($order->items as $item)
  @php
    $isDigital = optional($item->product)->type === 'digital';
    $allowReviewModal = $isDigital
      ? ($canReviewDigitalIfCompleted && !empty($item->downloaded_at))
      : $canReviewDelivered;
  @endphp
  @if($allowReviewModal && !$item->review)
    @php($modalId = 'reviewModal_'.$item->id)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('orders.items.reviews.store',[$item->order_id,$item->id]) }}" class="modal-content">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Review &mdash; {{ optional($item->product)->name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Rating</label>
              <select name="rating" class="form-select" required>
<option value="" hidden>Choose&hellip;</option>
                @for($i=5;$i>=1;$i--)
                  <option value="{{ $i }}">{{ $i }} &#9733;</option>
                @endfor
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Comment <span class="text-muted">&mdash;</span></label>
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







