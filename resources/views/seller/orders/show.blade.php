{{-- resources/views/seller/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Details')

@push('styles')
<style>
  .badge.text-capitalize { text-transform: capitalize; }
  .order-detail-icon { font-size: 1.25rem; }
</style>
@endpush

@section('content')
@php
  $symbol = get_currency();
@endphp

<div class="content">
  {{-- HEADER & ACTIONS --}}
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <h2 class="h4 text-success mb-0">
      <i class="fa-solid fa-receipt order-detail-icon me-2"></i>
      Order #{{ $order->id }} Details
    </h2>

    <div class="btn-toolbar gap-2 flex-wrap">
      <a href="{{ route('orders.chat.show', $order->id) }}"
         class="btn btn-outline-info btn-sm d-flex align-items-center gap-1">
        <i class="fa-solid fa-comments"></i> Messages
      </a>

      @if($order->status === \App\Models\Order::STATUS_PENDING)
        <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                data-bs-toggle="modal"
                data-bs-target="#processModal-{{ $order->id }}">
          <i class="fa-solid fa-gear"></i> Process
        </button>
        @include('seller.orders.modals.process')

        <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                data-bs-toggle="modal"
                data-bs-target="#cancelModal-{{ $order->id }}">
          <i class="fa-solid fa-times-circle"></i> Cancel
        </button>
        @include('seller.orders.modals.cancel')
      @elseif($order->status === \App\Models\Order::STATUS_PROCESSING)
        <button class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1"
                data-bs-toggle="modal"
                data-bs-target="#shipModal">
          <i class="fa-solid fa-truck"></i> Ship
        </button>

        <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                data-bs-toggle="modal"
                data-bs-target="#cancelModal-{{ $order->id }}">
          <i class="fa-solid fa-times-circle"></i> Cancel
        </button>
        @include('seller.orders.modals.cancel')
      @endif
    </div>
  </div>

  {{-- INITIATE DISPUTE BUTTON --}}
  <div class="d-flex justify-content-center mb-3">
    <a href="{{ route('disputes.create', ['order_id' => $order->id]) }}" 
       class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1">
      <i class="fa-solid fa-exclamation-triangle"></i> Initiate Dispute
    </a>
  </div>

  {{-- DISPUTE & APPEAL ACTIONS --}}
  @if($order->disputes && $order->disputes->isNotEmpty())
    @php
      $activeDispute = $order->disputes->where('status', '!=', 'final')->first();
      $resolvedDispute = $order->disputes->where('status', 'resolved')->first();
    @endphp
    
    @if($activeDispute)
      <div class="d-flex justify-content-center mb-3">
        <a href="{{ route('disputes.show', $activeDispute->id) }}" 
           class="btn btn-warning btn-sm d-flex align-items-center gap-1">
          <i class="fa-solid fa-exclamation-triangle"></i> View Dispute
        </a>
      </div>
    @endif

    @if($resolvedDispute && $resolvedDispute->canBeAppealed())
      <div class="d-flex justify-content-center mb-3">
        <a href="{{ route('disputes.appeal.create', $resolvedDispute->id) }}" 
           class="btn btn-danger btn-sm d-flex align-items-center gap-1">
          <i class="fa-solid fa-gavel"></i> Appeal Decision
          <span class="badge bg-light text-dark ms-1">{{ $resolvedDispute->getAppealDeadlineDaysLeft() }}d left</span>
        </a>
      </div>
    @endif
  @endif

  {{-- SUMMARY & CUSTOMER --}}
  <div class="row g-4 mb-4">
    {{-- Order Summary --}}
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
          <i class="fa-solid fa-list-check"></i> Order Summary
        </div>
        <div class="card-body">
          @foreach ([
            'Tracking No' => $order->tracking_no ?? '—',
            'Courier'     => $order->courier ?? '—',
            'Items'       => $order->items->sum('quantity'),
            'Subtotal'    => "{$symbol} ".number_format($order->subtotal,2),
          ] as $label => $value)
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-semibold">{{ $label }}:</span>
              <span>{{ $value }}</span>
            </div>
          @endforeach

          @if($order->shipping_cost)
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-semibold">Shipping Fee:</span>
              <span>{{ $symbol }} {{ number_format($order->shipping_cost,2) }}</span>
            </div>
          @endif

          <hr>

          <div class="d-flex justify-content-between mb-2 fw-bold">
            <span>Total Amount:</span>
            <span>{{ $symbol }} {{ number_format($order->total_amount,2) }}</span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="fw-semibold">Status:</span>
            <span>
              <span class="badge {{ $order->getStatusBadgeClass() }} text-capitalize">
                {{ $order->status }}
              </span>
            </span>
          </div>

          @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-semibold text-danger">Cancellation Reason:</span>
              <span class="text-danger">{{ $order->cancel_reason }}</span>
            </div>
          @endif

          <div class="d-flex justify-content-between">
            <span class="fw-semibold">Created:</span>
            <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Customer Info --}}
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
          <i class="fa-solid fa-user"></i> Customer Info
        </div>
        <div class="card-body">
          <p class="mb-1"><strong>Name:</strong> {{ $order->full_name }}</p>
          <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
          <p class="mb-3"><strong>Phone:</strong> {{ $order->phone ?? '—' }}</p>

          <p class="fw-semibold mb-1">Shipping Address</p>
          <address class="mb-3">
            {{ $order->shipping_address_1 }}<br>
            @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
            {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif<br>
            {{ $order->shipping_postal_code }}
          </address>

          <p class="mb-1"><strong>Shipping Method:</strong> {{ ucfirst($order->shipping_method) }}</p>
          <p class="mb-0"><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
        </div>
      </div>
    </div>
  </div>

  {{-- ITEMS (hide shipping details for digital products) --}}
  @if($order->items->isNotEmpty())
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
        <i class="fa-solid fa-boxes-stacked"></i> Order Items
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light text-nowrap">
            <tr>
              <th>#</th>
              <th>Image</th>
              <th>Product</th>
              <th>Variation</th>
              <th class="text-center">Qty</th>
              <th class="text-end">Price</th>
              <th>Shipping Profile</th>
              <th class="text-end">Shipping Cost</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($order->items as $item)
              @php
                $product   = optional($item->product);
                $isDigital = $product && $product->type === 'digital';

                $qty       = (int) ($item->quantity ?? 1);
                $unit      = (float) ($item->price ?? 0);

                // For digital items, hide shipping and force cost = 0
                if ($isDigital) {
                  $shipLabel = 'No shipping (digital)';
                  $shipCost  = 0.0;
                } else {
                  $sp        = optional($item->shippingProfile);
                  $shipLabel = $sp && $sp->dest_location_type === 'everywhere_else'
                                ? 'Everywhere'
                                : ($sp && $sp->destCountry ? ('Ship to '.$sp->destCountry->name) : ($sp->name ?? 'N/A'));
                  $shipCost  = (float) ($item->shipping_cost ?? 0);
                }

                $lineSub  = $unit * $qty; // product subtotal (no shipping)
                $thumbUrl = $product?->featured_image
                              ?: ($product?->media->first()?->url ? asset('storage/'.$product->media->first()->url) : asset('placeholder.jpg'));
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>

                <td>
                  @if($thumbUrl)
                    <a href="{{ $product?->slug ? route('listing.show', $product->slug) : 'javascript:void(0)' }}" target="_blank">
                      <img src="{{ $thumbUrl }}"
                           alt="{{ $product->name ?? 'Product' }}"
                           class="img-fluid rounded"
                           style="max-width: 80px; height:auto; object-fit: cover;">
                    </a>
                  @endif
                </td>

                <td>
                  @if($product?->slug)
                    <a href="{{ route('listing.show', $product->slug) }}" class="text-decoration-none" target="_blank">
                      {{ $product->name ?? 'N/A' }}
                    </a>
                  @else
                    {{ $product->name ?? 'N/A' }}
                  @endif
                  @if($isDigital)
                    <span class="badge bg-secondary ms-1">Digital</span>
                  @endif
                </td>

                {{-- Saved textual summary; no variations table required --}}
                <td>{{ $item->variation_summary ?? '—' }}</td>

                <td class="text-center">{{ $qty }}</td>
                <td class="text-end">{{ $symbol }} {{ number_format($unit,2) }}</td>

                {{-- Shipping profile / cost hidden (shown as em dash) for digital --}}
                <td>{{ $isDigital ? '—' : $shipLabel }}</td>
                <td class="text-end">{{ $symbol }} {{ number_format($isDigital ? 0 : $shipCost,2) }}</td>

                {{-- Product subtotal (without shipping) --}}
                <td class="text-end">{{ $symbol }} {{ number_format($lineSub,2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  {{-- PAYMENTS --}}
  @if($order->payments->isNotEmpty())
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
        <i class="fa-solid fa-wallet"></i> Payments
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light text-nowrap">
            <tr>
              <th>#</th>
              <th>Reference</th>
              <th>Method</th>
              <th class="text-end">Amount</th>
              <th>Status</th>
              <th>Paid On</th>
            </tr>
          </thead>
          <tbody>
            @foreach($order->payments as $payment)
              @php
                $raw = strtolower((string)$payment->status);
                $isCompleted = ($raw === 'success' || $raw === 'completed' || $raw === 'paid' || (string)$payment->status === '3');
                $statusText  = $isCompleted ? 'Completed' : (is_numeric($payment->status) ? $payment->status : ucfirst((string)$payment->status));
                $statusColor = $isCompleted ? 'success' : match($raw){
                  'pending' => 'secondary',
                  'failed'  => 'danger',
                  default   => 'dark',
                };
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $payment->local_transaction_id ?? 'N/A' }}</td>
                <td>{{ ucfirst($payment->payment_method) }}</td>
                <td class="text-end">{{ $symbol }} {{ number_format($payment->total_amount,2) }}</td>
                <td><span class="badge bg-{{ $statusColor }} text-capitalize">{{ $statusText }}</span></td>
                <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  {{-- ADDITIONAL INFO --}}
  @if($order->order_notes || $order->promo_code)
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
        <i class="fa-solid fa-info-circle"></i> Additional Information
      </div>
      <div class="card-body">
        @if($order->order_notes)
          <p><strong>Order Notes:</strong> {{ $order->order_notes }}</p>
        @endif
        @if($order->promo_code)
          <p><strong>Promo Code:</strong> {{ $order->promo_code }}</p>
        @endif
      </div>
    </div>
  @endif
</div>

{{-- DISPUTE INFORMATION --}}
@if($order->disputes && $order->disputes->isNotEmpty())
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
      <i class="fa-solid fa-exclamation-triangle text-warning"></i> Dispute Information
    </div>
    <div class="card-body">
      @foreach($order->disputes as $dispute)
        <div class="border-bottom pb-3 mb-3 @if(!$loop->last) @endif">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-1">
              {{ $dispute->getTypeLabel() }}
              <span class="badge {{ $dispute->getStatusBadgeClass() }} ms-2">
                {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
              </span>
            </h6>
            <small class="text-muted">{{ $dispute->created_at->format('d M Y, h:i A') }}</small>
          </div>
          
          <p class="mb-2 text-muted">{{ Str::limit($dispute->description, 150) }}</p>
          
          @if($dispute->isResolved())
            <div class="alert alert-info small mb-2">
              <strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}
              @if($dispute->refund_amount)
                <br><strong>Refund Amount:</strong> {{ $symbol }} {{ number_format($dispute->refund_amount, 2) }}
              @endif
            </div>
            
            @if($dispute->canBeAppealed())
              <div class="alert alert-warning small mb-2">
                @if($dispute->appeal_deadline)
                  <strong>Appeal Deadline:</strong> {{ $dispute->getAppealDeadlineDaysLeft() }} days remaining
                @else
                  <strong>Appeal Available:</strong> Submit immediately
                @endif
              </div>
            @endif
            
            @if($dispute->appeal)
              <div class="alert alert-warning small mb-2">
                <strong>Appeal Status:</strong> {{ ucfirst($dispute->appeal->status) }}
              </div>
            @endif
          @endif
          
          <div class="d-flex gap-2">
            <a href="{{ route('disputes.show', $dispute->id) }}" class="btn btn-outline-primary btn-sm">
              <i class="fa-solid fa-eye me-1"></i> View Details
            </a>
            
            @if($dispute->canBeAppealed())
              <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="btn btn-warning btn-sm">
                <i class="fa-solid fa-gavel me-1"></i> Appeal
              </a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif

{{-- NO DISPUTES SECTION --}}
@if(!$order->disputes || $order->disputes->isEmpty())
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-light fw-semibold d-flex align-items-center gap-2">
      <i class="fa-solid fa-check-circle text-success"></i> Dispute Status
    </div>
    <div class="card-body text-center">
      <p class="text-muted mb-2">No disputes have been filed for this order.</p>
      <p class="small text-muted mb-0">
        If you encounter any issues with this order, you can initiate a dispute using the button above.
      </p>
    </div>
  </div>
@endif

{{-- SHIPPING MODAL (PROCESSING → Ship) --}}
@if($order->status === \App\Models\Order::STATUS_PROCESSING)
  <div class="modal fade" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form action="{{ route('seller.orders.ship', $order) }}"
            method="POST"
            class="modal-content needs-validation" novalidate>
        @csrf
        <div class="modal-header bg-light">
          <h5 class="modal-title" id="shipModalLabel">
            <i class="fa-solid fa-truck-fast me-2"></i>
            Ship Order #{{ $order->id }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="px-4 pt-3">
          <div class="alert alert-info small mb-0">
            <strong>Customer:</strong> {{ $order->full_name }} &nbsp;|&nbsp;
            <strong>Total:</strong> {{ $symbol }} {{ number_format($order->total_amount,2) }}
          </div>
        </div>

        <div class="modal-body">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="form-floating">
                <select class="form-select" id="courierSelect" name="courier" required>
                  <option value="" disabled selected>Select courier…</option>
                  <option>Wells Fargo</option>
                  <option>DHL</option>
                  <option>Fargo Courier</option>
                  <option>G4S</option>
                  <option value="other">Other / Manual</option>
                </select>
                <label for="courierSelect">Courier *</label>
                <div class="invalid-feedback">Please select a courier.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="trackingInput" name="tracking_no" placeholder="ABC123" required>
                <label for="trackingInput">Tracking number *</label>
                <div class="invalid-feedback">Tracking number required.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="date" class="form-control" id="shipDateInput" name="shipping_date" value="{{ now()->toDateString() }}">
                <label for="shipDateInput">Shipping date</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <textarea class="form-control" id="shipNotes" name="ship_notes" style="height: 100px;"></textarea>
                <label for="shipNotes">Notes (optional)</label>
              </div>
            </div>
          </div>

          <h6 class="mb-3 fw-semibold">Items & Shipping Profiles</h6>
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th class="text-center">Qty</th>
                  <th>Shipping Profile</th>
                </tr>
              </thead>
              <tbody>
                @foreach($order->items as $item)
                  @php
                    $product  = optional($item->product);
                    $isDigital = $product && $product->type === 'digital';
                    $profiles = $product?->shippingProfiles ?? collect();
                    $selected = $item->shipping_profile_id;
                  @endphp
                  <tr>
                    <td>{{ $product->name ?? 'N/A' }} @if($isDigital)<span class="badge bg-secondary ms-1">Digital</span>@endif</td>
                    <td class="text-center">{{ (int)($item->quantity ?? 1) }}</td>
                    <td>
                      <input type="hidden" name="order_items[{{ $item->id }}][id]" value="{{ $item->id }}">
                      @if($isDigital)
                        <div class="form-control-plaintext text-muted">No shipping (digital)</div>
                      @else
                        <select name="order_items[{{ $item->id }}][shipping_profile_id]" class="form-select">
                          @foreach($profiles as $profile)
                            @php
                              $label = $profile->dest_location_type === 'everywhere_else'
                                      ? 'Everywhere'
                                      : ($profile->destCountry ? ('Ship to '.$profile->destCountry->name) : $profile->name);
                            @endphp
                            <option value="{{ $profile->id }}" @selected($selected == $profile->id)>
                              {{ $label }} ({{ $symbol }} {{ number_format((float)$profile->base_rate,2) }})
                            </option>
                          @endforeach
                        </select>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-truck me-1"></i> Mark as Shipped
          </button>
        </div>
      </form>
    </div>
  </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Auto-show ship modal if URL contains ?ship=1
  const params = new URLSearchParams(window.location.search);
  if (params.get('ship') === '1') {
    const modalEl = document.getElementById('shipModal');
    modalEl && new bootstrap.Modal(modalEl).show();
  }

  // Bootstrap validation
  document.querySelectorAll('.needs-validation').forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });
});
</script>
@endpush
