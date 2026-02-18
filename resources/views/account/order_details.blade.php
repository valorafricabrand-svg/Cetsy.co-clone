{{-- resources/views/orders/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Order Details')

@push('styles')
<style>
  /* Compact summary chips below header */
  .chip { display:inline-flex; align-items:center; gap:.45rem; padding:.35rem .6rem; border-radius:999px; background:#f1f3f5; font-size:.8125rem; }
  .chip i { opacity:.85; }
  .chip .label-muted { color:#6c757d; }

  /* Order item mobile cards */
  .order-item-card .label { color:#6c757d; font-size:.8125rem; }
  .order-item__thumb { width:72px; height:72px; object-fit:cover; border-radius:.5rem; flex:0 0 72px; }
  .order-item__thumb.placeholder { background:#f1f3f5; color:#adb5bd; display:flex; align-items:center; justify-content:center; }
  .order-item__row { display:flex; justify-content:space-between; align-items:center; margin-top:.5rem; }
  .order-item__total { font-weight:700; }
  .text-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient: vertical; overflow:hidden; }

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

@section('main')
<div class="content">
  <div class="container-xxl">

    {{-- ===== HEADER ===== --}}
    <div class="border-bottom pb-3 mb-4">

      @include('account.stepper')

      <div
        class="flex flex-col md:flex-row justify-content-md-between items-start md:items-center gap-3">

        {{-- Title --}}
  <h2 class="mb-0 text-emerald-600 font-semibold flex items-center gap-1">
    <i class="bi bi-receipt-cutoff"></i>
    Order&nbsp;#{{ $order->id }}&nbsp;&mdash;&nbsp;Details
  </h2>

        {{-- Action buttons --}}
        <div class="btn-toolbar flex-wrap gap-2">
          @php
            // Collect all downloadable files across digital items in this order
            $__digitalFiles = [];
            foreach (($order->items ?? []) as $__it) {
              $p = optional($__it->product);
              if ($p && ($p->type === 'digital')) {
                foreach (($p->digitalFiles ?? collect()) as $__df) {
                  $__digitalFiles[] = $__df;
                }
              }
            }
            $__canDownloadAll = in_array(
              $order->status,
              [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED]
            ) && count($__digitalFiles) > 0;
          @endphp

          @if($__canDownloadAll)
            @if(count($__digitalFiles) === 1)
              <a href="{{ route('digital-files.download', $__digitalFiles[0]) }}"
                 class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base flex gap-2"
                 data-bs-toggle="tooltip" data-bs-placement="bottom"
                 title="Download your digital file">
                <i class="bi bi-cloud-download fs-5"></i>
                <span>Download</span>
              </a>
            @else
              <button type="button"
                      class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base flex gap-2"
                      data-bs-toggle="modal" data-bs-target="#downloadAllModal"
                      title="Download your digital files">
                <i class="bi bi-cloud-download fs-5"></i>
                <span>Download Files</span>
              </button>
            @endif
          @endif

          @if($order->status === \App\Models\Order::STATUS_PENDING)
            <a href="{{ route('pay_now', $order->id) }}"
               class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base flex gap-2"
               data-bs-toggle="tooltip" data-bs-placement="bottom" title="Proceed to payment for this order">
              <i class="bi bi-credit-card fs-5"></i>
              <span>Pay&nbsp;Now</span>
            </a>
          @endif

          <a href="{{ route('orders.chat.show', $order->id) }}"
             class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition btn-outline-info px-5 py-2.5 text-base flex gap-2"
             data-bs-toggle="tooltip" data-bs-placement="bottom" title="Open conversation about this order">
            <i class="bi bi-chat-dots fs-5"></i>
            <span>Messages</span>
          </a>

          <button type="button"
                  class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-5 py-2.5 text-base flex gap-2 no-print"
                  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Print a clean invoice view"
                  onclick="window.print()">
            <i class="bi bi-printer fs-5"></i>
            <span>Print</span>
          </button>

          <a href="{{ route('account.orders') }}"
             class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-5 py-2.5 text-base flex gap-2"
             data-bs-toggle="tooltip" data-bs-placement="bottom" title="Return to your orders">
            <i class="bi bi-arrow-left-circle fs-5"></i>
            <span>Back</span>
          </a>

          @if(in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING]))
            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50 px-5 py-2.5 text-base flex gap-2"
                    data-bs-toggle="modal"
                    data-bs-target="#cancelModal-{{ $order->id }}"
                    title="Cancel this order">
              <i class="bi bi-x-circle fs-5"></i>
              <span>Cancel&nbsp;Order</span>
            </button>
          @endif

          @if($order->status === \App\Models\Order::STATUS_SHIPPED)
            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-5 py-2.5 text-base flex gap-2 has-tooltip"
                    data-bs-placement="bottom" title="Confirm you received the order"
                    data-bs-toggle="modal"
                    data-bs-target="#deliverModal-{{ $order->id }}">
              <i class="bi bi-check2-circle fs-5"></i>
              <span>Mark&nbsp;Delivered</span>
            </button>
            @include('seller.orders.modals.delivered')

            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50 px-5 py-2.5 text-base flex gap-2 has-tooltip"
                    data-bs-placement="bottom" title="Assess the shipped product or report a problem"
                    data-bs-toggle="modal"
                    data-bs-target="#assessModal-{{ $order->id }}">
              <i class="bi bi-clipboard-check fs-5"></i>
              <span>Asses&nbsp;Delivery</span>
            </button>
          @endif

        </div>

        @if($order->status === \App\Models\Order::STATUS_PENDING)
          <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mt-3 flex items-center justify-between" role="alert">
            <div class="flex items-center gap-2">
              <i class="bi bi-exclamation-triangle"></i>
              <span>Awaiting payment to start processing.</span>
            </div>
            <div class="flex items-center gap-2">
              <a href="{{ route('pay_now', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500">
                <i class="bi bi-credit-card"></i> Pay Now
              </a>
              <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 ml-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        @endif
  </div>

  @if(in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING]))
    <div class="modal" id="cancelModal-{{ $order->id }}" tabindex="-1" aria-labelledby="cancelModalLabel-{{ $order->id }}" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="{{ route('buyer.orders.cancel', $order) }}" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
          @csrf
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h5 class="text-base font-semibold text-slate-900" id="cancelModalLabel-{{ $order->id }}">Cancel Order #{{ $order->id }}</h5>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="px-4 py-4">
            <p class="mb-3">Are you sure you want to cancel this order? This action cannot be undone.</p>
            <div class="mb-3">
              <label for="cancel-reason-{{ $order->id }}" class="mb-1 block text-sm font-medium text-slate-700">Reason (optional)</label>
              <textarea name="cancel_reason" id="cancel-reason-{{ $order->id }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Tell the seller why youâ€™re cancelling"></textarea>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Keep Order</button>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-rose-600 text-white hover:bg-rose-500">Confirm Cancel</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  @if($order->status === \App\Models\Order::STATUS_SHIPPED)
    <div class="modal" id="assessModal-{{ $order->id }}" tabindex="-1" aria-labelledby="assessModalLabel-{{ $order->id }}" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h5 class="text-base font-semibold text-slate-900" id="assessModalLabel-{{ $order->id }}">Assess Delivered Item</h5>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="px-4 py-4">
            <div class="grid grid-cols-12 gap-4 gap-3">
              <div class="md:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full border-success">
                  <div class="p-4 sm:p-5 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                      <i class="bi bi-bag-check text-emerald-600"></i>
                      <strong>Received as described</strong>
                    </div>
                    <p class="text-slate-500 mb-3">No damage or issues. Mark order as delivered.</p>
                    <form method="POST" action="{{ route('buyer.orders.status', $order) }}" class="mt-auto">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="action" value="deliver">
                      <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 w-full">
                        <i class="bi bi-check2-circle"></i> Mark as Delivered
                      </button>
                    </form>
                  </div>
                </div>
              </div>
              <div class="md:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full border-danger">
                  <div class="p-4 sm:p-5 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                      <i class="bi bi-exclamation-triangle text-rose-600"></i>
                      <strong>There is a problem</strong>
                    </div>
                    <p class="text-slate-500 mb-3">Start a dispute if the item arrived damaged, not as described, or never arrived.</p>
                    <div class="d-grid gap-2 mt-auto">
                      <a class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50" href="{{ route('disputes.create', ['order_id' => $order->id, 'type' => \App\Models\Dispute::TYPE_QUALITY_ISSUES]) }}">
                        <i class="bi bi-tools"></i> Not as described / Damaged
                      </a>
                      <a class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50" href="{{ route('disputes.create', ['order_id' => $order->id, 'type' => \App\Models\Dispute::TYPE_SHIPPING_ISSUES]) }}">
                        <i class="bi bi-truck"></i> Never arrived / Not received
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endif

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
    $placedAt = $order->created_at instanceof \Carbon\Carbon ? $order->created_at : ($order->created_at ? \Carbon\Carbon::parse($order->created_at) : null);
    $shipStart = $placedAt && is_numeric($minDays) ? $placedAt->copy()->addDays($minDays) : null;
    $shipEnd   = $placedAt && is_numeric($maxDays) ? $placedAt->copy()->addDays($maxDays) : null;
    $shipStartLabel = $shipStart && $placedAt && $shipStart->isSameDay($placedAt) ? 'today' : ($shipStart? $shipStart->format('M j') : null);
    $shipEndLabel   = $shipEnd && $placedAt && $shipEnd->isSameDay($placedAt) ? 'today' : ($shipEnd? $shipEnd->format('M j') : null);

    $status = (string) $order->status;
    $paid = in_array($status, [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepPlaced     = true;
    // mark processing step active only after payment
    $stepProcessing = in_array($status, [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepShipped    = in_array($status, [\App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepDelivered  = in_array($status, [\App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
    $stepCompleted  = ($status === \App\Models\Order::STATUS_COMPLETED);

    $paymentsCollection = $order->relationLoaded('payments')
        ? $order->payments
        : $order->payments()->orderBy('created_at')->get();

    $firstPaidPayment = $paymentsCollection->firstWhere('status', '3')
        ?? $paymentsCollection->firstWhere('status', 3);

    $processingAt = optional($firstPaidPayment)->created_at;
    if (!$processingAt && $paid) {
        $processingAt = $order->created_at;
    }
    $shippedAt    = $order->shipped_at;
    $deliveredAt  = $order->delivered_at;
    $completedAt  = $order->completed_at ?: ($stepCompleted ? ($deliveredAt ?? $order->updated_at) : null);

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

        return $value->format('M j, Y \\a\\t g:i A');
    };
  @endphp

  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
    <div class="p-4 sm:p-5">
      <div class="grid grid-cols-12 gap-4 gap-3 items-center">
        <div class="col-span-12 md:col-span-6">
          <div class="font-semibold mb-1">Processing Timeline</div>
          <ul class="list-unstyled text-xs mb-0">
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-check-circle {{ $stepPlaced ? 'text-success' : 'text-muted' }}"></i>
                  <span>Order placed</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($placedAt) ?? 'â€”' }}</span>
              </div>
            </li>
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-gear-fill {{ $paid ? 'text-success' : 'text-warning' }}"></i>
                  <span>Processing</span>
                </div>
                <span class="text-slate-500">
                  @if($processingAt)
                    {{ $formatDateTime($processingAt) }}
                  @elseif($paid)
                    â€”
                  @else
                    Pending payment
                  @endif
                </span>
              </div>
              @unless($paid)
                <div class="text-xs text-slate-500 ml-4 mt-1">Processing will begin after your payment is completed.</div>
              @endunless
            </li>
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-truck {{ $stepShipped ? 'text-success' : 'text-muted' }}"></i>
                  <span>Shipped</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($shippedAt) ?? 'â€”' }}</span>
              </div>
            </li>
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-box-seam {{ $stepDelivered ? 'text-success' : 'text-muted' }}"></i>
                  <span>Delivered</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($deliveredAt) ?? 'â€”' }}</span>
              </div>
            </li>
            <li>
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-flag {{ $stepCompleted ? 'text-success' : 'text-muted' }}"></i>
                  <span>Completed</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($completedAt) ?? 'â€”' }}</span>
              </div>
            </li>
          </ul>
        </div>
        @if($paid)
          <div class="col-span-12 md:col-span-6">
            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-0">
              <i class="bi bi-info-circle mr-2"></i>
              @if($stepCompleted && $completedAt)
                Completed on <strong>{{ $formatDateTime($completedAt) }}</strong>
              @elseif($stepDelivered && $deliveredAt)
                Delivered on <strong>{{ $formatDateTime($deliveredAt) }}</strong>
              @elseif($stepShipped && $shippedAt)
                Shipped on <strong>{{ $formatDateTime($shippedAt) }}</strong>
              @elseif($shipStart && $shipEnd)
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
        @endif
      </div>
    </div>
  </div>
      {{-- Summary chips: status, placed, items, total --}}
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="chip">
          <i class="bi bi-activity"></i>
          <span class="label-muted">Status:</span>
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }} text-uppercase">{{ ucfirst($order->status) }}</span>
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
          <span class="font-semibold">{{ money((float)($order->total_amount ?? 0)) }}</span>
        </span>
      </div>

      @php
        $isPending = ($order->status === \App\Models\Order::STATUS_PENDING);
        $hasDigitalPending = $order->items->contains(function($it){ return optional($it->product)->type === 'digital'; });
      @endphp
      @if($hasDigitalPending && $isPending)
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mt-3 mb-0" role="alert">
          <i class="bi bi-lock mr-2"></i>
          This order includes digital items. Downloads unlock after payment is completed.
        </div>
      @endif

      @php
        $hasDigital = $order->items->contains(function($it){ return optional($it->product)->type === 'digital'; });
        $needsDownload = $order->items->contains(function($it){ return optional($it->product)->type === 'digital' && empty($it->downloaded_at); });
      @endphp
      @if($hasDigital && $needsDownload)
        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mt-3 mb-0" role="alert">
          <i class="bi bi-cloud-download mr-2"></i>
          Digital order: funds release and reviews unlock after your first download.
        </div>
      @endif

      @php
        $statusNow = $order->status;
        $canReviewPhysicalNow = in_array($statusNow, [\App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_COMPLETED]);
        $canReviewDigitalNow  = in_array($statusNow, [\App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_DELIVERED]);

        $pendingReviewItem = $order->items->first(function ($item) use ($canReviewPhysicalNow, $canReviewDigitalNow) {
          if ($item->review) {
            return false;
          }

          $product    = optional($item->product);
          $isDigital  = $product && $product->type === 'digital';
          $downloaded = !empty($item->downloaded_at);

          if ($isDigital) {
            return $canReviewDigitalNow && $downloaded;
          }

          return $canReviewPhysicalNow;
        });
      @endphp

      @if($pendingReviewItem)
        @php $pendingReviewModalId = 'reviewModal_'.$pendingReviewItem->id; @endphp
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 mt-3 flex items-center justify-between flex-wrap gap-3" role="alert">
          <div class="flex items-center gap-2">
            <i class="bi bi-star-fill fs-4"></i>
            <div>
              <strong>Your order has been delivered.</strong>
              <div class="text-xs">Share feedback with the seller by leaving a quick review.</div>
            </div>
          </div>
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400" data-bs-toggle="modal" data-bs-target="#{{ $pendingReviewModalId }}">
            <i class="bi bi-pencil-square"></i> Leave a Review
          </button>
        </div>
      @endif
    </div>

    {{-- ===== SHOP DETAILS ===== --}}
    @if($order->shop)
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
        <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
          <i class="bi bi-shop-window text-primary"></i> Shop&nbsp;Details
        </div>
        <div class="p-4 sm:p-5 text-xs">
          <div class="grid grid-cols-12 gap-4 gap-y-2">
            <div class="col-span-12 md:col-span-6"><span class="font-semibold">Name:</span> {{ $order->shop->name }}</div>
            @if($order->shop->description)
              <div class="col-span-12"><span class="font-semibold">Description:</span> {{ $order->shop->description }}</div>
            @endif
            @if($order->shop->address)
              <div class="col-span-12"><span class="font-semibold">Address:</span><br>{{ $order->shop->address }}</div>
            @endif
            <div class="col-span-12 md:col-span-6">
              <span class="font-semibold">Owner:</span> {{ optional($order->shop->user)->name ?? 'N/A' }}
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="grid grid-cols-12 gap-4">
      {{-- ===== ORDER SUMMARY ===== --}}
      <div class="md:col-span-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
          <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
            <i class="bi bi-clipboard-data text-primary"></i> Order&nbsp;Summary
          </div>
          <div class="p-4 sm:p-5 text-xs">
            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Tracking No:</span><span>{{ $order->tracking_no ?? 'N/A' }}</span>
              </li>
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Courier:</span><span>{{ $order->courier ?? 'N/A' }}</span>
              </li>
              @if(!empty($order->tracking_url))
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Tracking Link:</span>
                <span>
                  <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener" class="link-primary">Track package</a>
                </span>
              </li>
              @endif
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Quantity:</span><span>{{ $order->items->sum('quantity') }}</span>
              </li>
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Subtotal:</span>
                <span>{{ money((float)($order->subtotal ?? 0)) }}</span>
              </li>
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Shipping Fee:</span>
                <span>{{ money((float)($order->shipping_cost ?? 0)) }}</span>
              </li>
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Total Amount:</span>
                <span class="font-bold">{{ money((float)($order->total_amount ?? 0)) }}</span>
              </li>
              <li class="px-4 py-3 px-0 flex justify-between items-center">
                <span class="font-semibold">Status:</span>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }} px-3 py-2 text-uppercase">
                  {{ ucfirst($order->status) }}
                </span>
              </li>
              @if(in_array($order->status, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_REFUNDED]) && $order->cancel_reason)
                <li class="px-4 py-3 px-0 flex justify-between">
                  <span class="font-semibold text-rose-600">Cancellation Reason:</span>
                  <span class="text-rose-600">{{ $order->cancel_reason }}</span>
                </li>
              @endif
              <li class="px-4 py-3 px-0 flex justify-between">
                <span class="font-semibold">Created:</span>
                <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      {{-- ===== CUSTOMER INFO ===== --}}
      <div class="md:col-span-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
          <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
            <i class="bi bi-person-vcard text-primary"></i> Customer&nbsp;Info
          </div>
          <div class="p-4 sm:p-5 text-xs">
            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
              <li class="px-4 py-3 px-0">
                <span class="font-semibold">Name:</span> {{ $order->full_name }}
              </li>
              <li class="px-4 py-3 px-0">
                <span class="font-semibold">Email:</span> {{ $order->email }}
              </li>
              <li class="px-4 py-3 px-0">
                <span class="font-semibold">Phone:</span> {{ $order->phone }}
              </li>
              <li class="px-4 py-3 px-0">
                <span class="font-semibold">Shipping Address:</span>
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
              <li class="px-4 py-3 px-0">
                <span class="font-semibold">Shipping Method:</span> {{ ucfirst($order->shipping_method) }}
              </li>
              <li class="px-4 py-3 px-0">
                <span class="font-semibold">Payment Method:</span> {{ ucfirst($order->payment_method) }}
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
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
        <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
          <i class="bi bi-box-seam text-primary"></i> Order&nbsp;Items
        </div>

        <div class="p-4 sm:p-5 p-0">
          <div class="overflow-x-auto hidden md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm mb-0 align-middle">
              <thead class="bg-slate-50 text-nowrap">
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

                  <tr @if($reviewed && $item->review) id="review-{{ $item->review->id }}" @endif>
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
                        <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="no-underline">
                          {{ $product->name }}
                        </a>
                        @if($isDigital)
                          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 ml-1">Digital</span>
                        @endif
                      @else
                        <span class="text-slate-500">&mdash;</span>
                      @endif
                    </td>

                    {{-- Variation: saved summary --}}
                    <td>@if($item->variation_summary) {{ $item->variation_summary }} @else &mdash; @endif</td>

                    {{-- Qty --}}
                    <td>{{ $qty }}</td>

                    {{-- Unit Price --}}
                    <td>{{ money((float)$unit) }}</td>

                    {{-- Shipping profile (or hidden for digital) --}}
                    <td>{{ $label }}</td>

                    {{-- Shipping cost --}}
                    <td>{{ money((float)$shipCost) }}</td>

                    {{-- Subtotal (unit * qty, excludes shipping) --}}
                    <td class="font-semibold">{{ money((float)$lineSubtotal) }}</td>

                    {{-- Review (only after delivery) --}}
                    <td class="text-center">
                      @if($reviewed)
                        <div class="flex items-center justify-center gap-2">
                          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success gap-1">
                            <i class="bi bi-check-circle"></i>
                            {{ $item->review->rating }} &#9733;
                          </span>
                          @php $editModalId = 'editReview_'.$item->id; @endphp
                          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" data-bs-toggle="modal" data-bs-target="#{{ $editModalId }}">
                            <i class="bi bi-pencil"></i> Edit
                          </button>
                        </div>
                      @elseif($canReviewDelivered || ($product && $product->type === 'digital' && $canReviewDigitalIfCompleted))
                        @php $downloaded = !empty($item->downloaded_at); @endphp
                        @if($product && $product->type === 'digital' && ! $downloaded)
                          <span class="text-slate-500 text-xs">Download required to review</span>
                        @else
                          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50 px-3 py-1.5 text-xs" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                            <i class="bi bi-star"></i> Review
                          </button>
                        @endif
                      @else
                        <span class="text-slate-500 text-xs">Available after delivery</span>
                      @endif
                    </td>

                    {{-- Downloads (digital + allowed statuses) --}}
                    <td>
                      @if($canDownload && $product && $product->digitalFiles->count())
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-0">
                          <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
                            @foreach($product->digitalFiles as $file)
                              <li class="px-4 py-3 flex justify-between items-center">
                                <a href="{{ route('digital-files.download', $file) }}"
                                   target="_blank"
                                   class="inline-flex items-center">
                                  <i class="fas fa-file-download mr-2"></i> {{ $file->filename }}
                                </a>
                              </li>
                            @endforeach
                          </ul>
                        </div>
                      @else
                        <span class="text-slate-500">&mdash;</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Mobile/card layout -->
          <div class="block md:hidden p-3">
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
                $thumbUrl     = product_thumb_url($product);
                $canDownload  = in_array(
                                  $order->status,
                                  [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED]
                                ) && $product && $product->type === 'digital';
              @endphp

              <div class="rounded-2xl border border-slate-200 bg-white shadow-sm order-item-card mb-3" @if($reviewed && $item->review) id="review-{{ $item->review->id }}" @endif>
                <div class="p-4 sm:p-5">
                  <div class="flex items-start gap-3">
                    @if($thumbUrl)
                      <a href="{{ $product ? route('listing.show', $product->slug) : '#' }}" target="_blank">
                        <img src="{{ $thumbUrl }}" alt="{{ $product->name ?? 'Product image' }}" class="order-item__thumb">
                      </a>
                    @else
                      <div class="order-item__thumb placeholder rounded">
                        <i class="bi bi-image"></i>
                      </div>
                    @endif
                    <div class="flex-grow-1">
                      <div class="flex justify-between items-start">
                        <div style="min-width:0;">
                          <div class="font-semibold text-clamp-2">
                            @if($product)
                              <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="no-underline">{{ $product->name }}</a>
                            @else
                              <span class="text-slate-500">&mdash;</span>
                            @endif
                            @if($isDigital)
                              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 ml-1">Digital</span>
                            @endif
                          </div>
                          @if($item->variation_summary)
                            <div class="text-slate-500 text-xs">{{ $item->variation_summary }}</div>
                          @endif
                        </div>
                        <div class="text-right ml-2">
                          <div class="label">Unit</div>
                          <div>{{ money((float)$unit) }}</div>
                        </div>
                      </div>

                      <div class="order-item__row">
                        <div class="label">Quantity</div>
                        <div><strong>{{ $qty }}</strong></div>
                      </div>

                      @if(!$isDigital)
                        <div class="order-item__row">
                          <div class="label">Shipping</div>
                          <div>{{ $label }} <span class="text-slate-500">({{ money((float)$shipCost) }})</span></div>
                        </div>
                      @else
                        <div class="order-item__row">
                          <div class="label">Shipping</div>
                          <div class="text-slate-500">No shipping (digital)</div>
                        </div>
                      @endif

                      <hr class="my-2">

                      <div class="order-item__row">
                        <div class="label">Subtotal</div>
                        <div>{{ money((float)$lineSubtotal) }}</div>
                      </div>
                      <div class="order-item__row">
                        <div class="label">Total</div>
                        <div class="order-item__total">{{ money((float)$lineTotal) }}</div>
                      </div>

                      <div class="flex justify-between items-center mt-3">
                        <div>
                          @if($reviewed)
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success gap-1">
                              <i class="bi bi-check-circle"></i> Reviewed
                            </span>
                          @elseif(($order->status === \App\Models\Order::STATUS_DELIVERED) || ($isDigital && (($order->status === \App\Models\Order::STATUS_COMPLETED) || ($order->status === \App\Models\Order::STATUS_DELIVERED)) && !empty($item->downloaded_at)))
                            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                              <i class="bi bi-star"></i> Review
                            </button>
                          @else
                            <span class="text-slate-500 text-xs">Review after delivery</span>
                          @endif
                        </div>
                      </div>

                      @if($canDownload && $product && $product->digitalFiles->count())
                        <div class="mt-3">
                          <div class="label mb-1">Downloads</div>
                          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-0">
                            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
                              @foreach($product->digitalFiles as $file)
                                <li class="px-4 py-3 flex justify-between items-center">
                                  <a href="{{ route('digital-files.download', $file) }}" target="_blank" class="inline-flex items-center">
                                    <i class="fas fa-file-download mr-2"></i> {{ $file->filename }}
                                  </a>
                                </li>
                              @endforeach
                            </ul>
                          </div>
                        </div>
                      @endif

                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    {{-- ===== PAYMENTS ===== --}}
    @if($order->payments->count())
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
        <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
          <i class="bi bi-wallet2 text-primary"></i> Payments
        </div>
        <div class="p-4 sm:p-5 p-0">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm mb-0 align-middle">
              <thead class="bg-slate-50 text-nowrap">
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
                    <td>{{ money((float)($pay->total_amount ?? 0)) }}</td>
                    <td>
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $isCompleted ? 'bg-success' : 'bg-secondary' }}">
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
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
        <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold flex items-center gap-2">
          <i class="bi bi-info-circle text-primary"></i> Additional&nbsp;Information
        </div>
        <div class="p-4 sm:p-5 text-xs">
          @if($order->order_notes)
            <p class="mb-2"><span class="font-semibold">Order Notes:</span><br>{{ $order->order_notes }}</p>
          @endif
          @if($order->promo_code)
            <p class="mb-0"><span class="font-semibold">Promo Code:</span> {{ $order->promo_code }}</p>
          @endif
        </div>
      </div>
    @endif

</div>
</div>

@php
  // Prepare a modal listing when multiple digital files exist
  $__dlFiles = [];
  foreach (($order->items ?? []) as $__it) {
    $p = optional($__it->product);
    if ($p && ($p->type === 'digital')) {
      foreach (($p->digitalFiles ?? collect()) as $__df) {
        $__dlFiles[] = $__df;
      }
    }
  }
  $__showDownloadModal = in_array($order->status, [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED])
                        && count($__dlFiles) > 1;
@endphp
@if($__showDownloadModal)
  <div class="modal" id="downloadAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900">Your Downloads</h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="px-4 py-4 p-0">
          <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
            @foreach($__dlFiles as $__file)
              <li class="px-4 py-3 flex justify-between items-center">
                <div class="mr-3 text-truncate">
                  <i class="bi bi-file-earmark-arrow-down mr-2"></i>
                  <span class="text-truncate inline-block" style="max-width: 18rem;" title="{{ $__file->filename }}">{{ $__file->filename }}</span>
                </div>
                <a href="{{ route('digital-files.download', $__file) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500">
                  <i class="bi bi-download"></i> Download
                </a>
              </li>
            @endforeach
          </ul>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endif

{{-- ===== REVIEW MODALS (Delivered for physical; Completed/Delivered + download for digital) ===== --}}
@foreach($order->items as $item)
  @php
    $isDigital = optional($item->product)->type === 'digital';
    $allowReviewModal = $isDigital
      ? ($canReviewDigitalIfCompleted && !empty($item->downloaded_at))
      : $canReviewDelivered;
  @endphp
  @if($allowReviewModal && !$item->review)
    @php $modalId = 'reviewModal_'.$item->id; @endphp
    <div class="modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" enctype="multipart/form-data" action="{{ route('orders.items.reviews.store',[$item->order_id,$item->id]) }}" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
          @csrf
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h5 class="text-base font-semibold text-slate-900">Review &mdash; {{ optional($item->product)->name }}</h5>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal"></button>
          </div>

          <div class="px-4 py-4">
            <div class="mb-3">
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Rating</label>
              <select name="rating" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required>
<option value="" hidden>Choose&hellip;</option>
                @for($i=5;$i>=1;$i--)
                  <option value="{{ $i }}">{{ $i }} &#9733;</option>
                @endfor
              </select>
            </div>
            <div class="mb-3">
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Comment <span class="text-slate-500">&mdash;</span></label>
              <textarea name="comment" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Share details of your experience"></textarea>
            </div>
            <div class="mb-3">
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Add a photo (optional)</label>
              <input type="file" name="photo" accept="image/*" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
              <div class="mt-1 text-xs text-slate-500">JPEG, PNG, GIF, or WebP up to 5MB.</div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
              <i class="bi bi-send"></i> Submit Review
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
  @if($item->review)
    @php($editModalId = 'editReview_'.$item->id)
    <div class="modal" id="{{ $editModalId }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" enctype="multipart/form-data" action="{{ route('orders.items.reviews.update',[$item->order_id,$item->id,$item->review->id]) }}" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
          @csrf
          @method('PATCH')
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h5 class="text-base font-semibold text-slate-900">Edit Review â€” {{ optional($item->product)->name }}</h5>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal"></button>
          </div>
          <div class="px-4 py-4">
            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 text-xs">
              You can only increase your original rating. Lower ratings are disabled.
            </div>
            <div class="mb-3">
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Rating</label>
              <select name="rating" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required>
                @for($i=5;$i>=1;$i--)
                  <option value="{{ $i }}" {{ $i == $item->review->rating ? 'selected' : '' }} {{ $i < $item->review->rating ? 'disabled' : '' }}>
                    {{ $i }} &#9733; {{ $i < $item->review->rating ? '(locked)' : '' }}
                  </option>
                @endfor
              </select>
            </div>
            <div class="mb-3">
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Comment</label>
              <textarea name="comment" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Update your feedback if needed">{{ $item->review->comment }}</textarea>
            </div>
            <div class="mb-3">
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Update photo (optional)</label>
              <input type="file" name="photo" accept="image/*" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
              <div class="mt-1 text-xs text-slate-500">JPEG, PNG, GIF, or WebP up to 5MB. Uploading a new image replaces the existing one.</div>
              @if(!empty($item->review->image_path))
                <div class="mt-2">
                  <a href="{{ asset('storage/'.ltrim($item->review->image_path,'/')) }}" target="_blank">
                    <img src="{{ asset('storage/'.ltrim($item->review->image_path,'/')) }}" alt="Current review image" style="max-width: 120px; max-height: 120px; border-radius: 6px;"/>
                  </a>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" value="1" id="remove_photo_{{ $item->id }}" name="remove_photo">
                  <label class="form-check-label" for="remove_photo_{{ $item->id }}">
                    Remove current photo
                  </label>
                </div>
              @endif
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
              <i class="bi bi-save"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endforeach
@endsection




