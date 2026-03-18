{{-- resources/views/orders/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Order Details')

@push('styles')
<style>
  /* Compact summary chips below header */
  .chip { display:inline-flex; align-items:center; gap:.45rem; padding:.35rem .6rem; border-radius:999px; background:#f1f3f5; font-size:.8125rem; }
  .chip i { opacity:.85; }
  .chip .label-muted { color:#64748b; }

  /* Order item mobile cards */
  .order-item-card .label { color:#6c757d; font-size:.8125rem; }
  .order-item__thumb-wrap {
    width:72px;
    height:72px;
    min-width:72px;
    min-height:72px;
    max-width:72px;
    max-height:72px;
    flex:0 0 72px;
    border-radius:.5rem;
    overflow:hidden;
    display:block;
  }
  .order-item__thumb { width:100%; height:100%; object-fit:cover; object-position:center; display:block; }
  .order-item__thumb.placeholder { background:#f1f3f5; color:#adb5bd; display:flex; align-items:center; justify-content:center; }
  .order-item__row { display:flex; justify-content:space-between; align-items:center; margin-top:.5rem; }
  .order-item__total { font-weight:700; }
  .text-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient: vertical; overflow:hidden; }
  .text-uppercase { text-transform: uppercase; }
  .no-underline { text-decoration: none; }
  .list-group-flush { list-style: none; margin: 0; padding: 0; border: 0; border-radius: .75rem; }
  .img-fluid { max-width: 100%; height: auto; }
  .text-primary { color: #0284c7; }
  .text-success { color: #059669; }
  .text-warning { color: #d97706; }
  .text-muted { color: #94a3b8; }
  .bg-success { background: #d1fae5; color: #065f46; }
  .bg-secondary { background: #e2e8f0; color: #334155; }
  .border-success { border-color: #86efac !important; }
  .border-danger { border-color: #fda4af !important; }
  .d-grid { display: grid; }
  .btn-toolbar { display: flex; }
  .form-check { display: flex; align-items: center; gap: .5rem; }
  .form-check-input { width: 1rem; height: 1rem; border-radius: .25rem; border: 1px solid #cbd5e1; }
  .form-check-label { font-size: .875rem; color: #334155; }
  .order-actions { width: 100%; }
  .order-actions > * { max-width: 100%; }
  @media (max-width: 640px) {
    .order-actions {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .5rem;
    }
    .order-actions > * {
      width: 100%;
      min-width: 0;
      justify-content: center;
    }
    .order-actions > * span {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }

  /* Bootstrap-free modal behavior */
  .modal {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.55);
    padding: 1rem;
  }
  .modal.is-open { display: flex; }
  .modal-dialog { width: 100%; max-width: 32rem; }
  .modal-dialog.modal-lg { max-width: 56rem; }
  .modal-dialog.modal-dialog-centered { margin: 0 auto; }
  .modal-content {
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    box-shadow: 0 20px 48px rgba(15, 23, 42, 0.25);
  }
  .modal-header, .modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .9rem 1rem;
  }
  .modal-header { border-bottom: 1px solid #e2e8f0; }
  .modal-footer { border-top: 1px solid #e2e8f0; justify-content: flex-end; }
  .modal-body { padding: 1rem; }
  .modal-title { font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0; }
  .btn-close {
    width: 2rem;
    height: 2rem;
    border-radius: .5rem;
    border: 1px solid transparent;
    background: transparent;
    color: #64748b;
    cursor: pointer;
  }
  .btn-close::before { content: '\00d7'; font-size: 1.1rem; line-height: 1; display: inline-block; }
  .btn-close:hover { background: #f1f5f9; color: #1e293b; }
  .form-floating { display: flex; flex-direction: column; gap: .35rem; }
  .form-floating > label { font-size: .8125rem; color: #64748b; }

  /* Print stylesheet: clean invoice look */
  @media print {
    @page { margin: 12mm; }
    html, body { background: #fff !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 12px; }

    /* Hide nav/footer/UI chrome and actions */
    nav, footer, [role="alert"], .tw-modal, .chip, .stepper, .no-print { display: none !important; }

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
    const body = document.body;
    const openModal = (selector) => {
      const modal = document.querySelector(selector);
      if (!modal) return null;
      modal.classList.add('is-open');
      body.classList.add('overflow-hidden');
      return modal;
    };
    const closeModal = (modal) => {
      if (!modal) return;
      modal.classList.remove('is-open');
      if (!document.querySelector('.modal.is-open')) {
        body.classList.remove('overflow-hidden');
      }
    };

    document.querySelectorAll('[data-ui-toggle="modal"][data-ui-target]').forEach((trigger) => {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        openModal(this.getAttribute('data-ui-target'));
      });
    });

    document.querySelectorAll('[data-ui-dismiss="modal"]').forEach((button) => {
      button.addEventListener('click', function () {
        closeModal(this.closest('.modal'));
      });
    });

    document.querySelectorAll('.modal').forEach((modal) => {
      modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal(modal);
      });
    });

    document.querySelectorAll('[data-ui-dismiss="alert"]').forEach((button) => {
      button.addEventListener('click', function () {
        const alertEl = this.closest('[role="alert"]');
        if (alertEl) alertEl.remove();
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        const topModal = document.querySelector('.modal.is-open');
        if (topModal) closeModal(topModal);
      }
    });

    const params = new URLSearchParams(window.location.search);
    const reviewItemId = params.get('review_item');
    if (reviewItemId && /^\d+$/.test(reviewItemId)) {
      const modal = openModal(`#reviewModal_${reviewItemId}`);
      if (modal && window.history.replaceState) {
        params.delete('review_item');
        const nextQuery = params.toString();
        const nextUrl = `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}${window.location.hash}`;
        window.history.replaceState({}, document.title, nextUrl);
      }
    }
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
<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-12 lg:col-span-3">
        @include('buyer.partials.sidebar')
      </div>
      <div class="col-span-12 lg:col-span-9">

    {{-- ===== HEADER ===== --}}
    <div class="border-bottom pb-3 mb-4">

      @include('account.stepper')

      <div
        class="flex flex-col items-start gap-3 md:flex-row md:items-center md:justify-between">

        {{-- Title --}}
  <h2 class="mb-0 text-emerald-600 font-semibold flex items-center gap-1">
    <i class="bi bi-receipt-cutoff"></i>
    Order&nbsp;#{{ $order->id }}&nbsp;&mdash;&nbsp;Details
  </h2>

        {{-- Action buttons --}}
        <div class="order-actions flex flex-wrap gap-2">
          @php
            $downloadableStatuses = [
              \App\Models\Order::STATUS_PROCESSING,
              \App\Models\Order::STATUS_COMPLETED,
              \App\Models\Order::STATUS_DELIVERED,
            ];
            $orderItemsCollection = collect($order->items ?? []);
            $__digitalItems = $orderItemsCollection->filter(function ($__it) {
              return strtolower((string) (optional($__it->product)->type ?? '')) === 'digital';
            });

            // Collect all downloadable files across digital items in this order
            $__digitalFiles = [];
            foreach ($orderItemsCollection as $__it) {
              $p = optional($__it->product);
              if ($p && strtolower((string) ($p->type ?? '')) === 'digital') {
                foreach (($p->digitalFiles ?? collect()) as $__df) {
                  $__digitalFiles[] = $__df;
                }
              }
            }
            $__canDownloadAll = in_array(
              $order->status,
              $downloadableStatuses,
              true
            ) && count($__digitalFiles) > 0;
            $__showNoFilesDownloadHint = in_array($order->status, $downloadableStatuses, true)
              && $__digitalItems->isNotEmpty()
              && count($__digitalFiles) === 0;
          @endphp

          @if($__canDownloadAll)
            @if(count($__digitalFiles) === 1)
              <a href="{{ route('digital-files.download', $__digitalFiles[0]) }}"
                 target="_blank" rel="noopener"
                 class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white transition hover:bg-emerald-500"
                 title="Download your digital file">
                <i class="bi bi-cloud-download text-lg"></i>
                <span>Download</span>
              </a>
            @else
              <button type="button"
                      class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white transition hover:bg-emerald-500"
                      data-ui-toggle="modal" data-ui-target="#downloadAllModal"
                      title="Download your digital files">
                <i class="bi bi-cloud-download text-lg"></i>
                <span>Download Files</span>
              </button>
            @endif
          @elseif($__showNoFilesDownloadHint)
            <span class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-800">
              <i class="bi bi-exclamation-triangle"></i>
              <span>Digital file not uploaded yet</span>
            </span>
          @endif

          @if($order->status === \App\Models\Order::STATUS_PENDING)
            <a href="{{ route('pay_now', $order->id) }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white transition hover:bg-emerald-500"
               title="Proceed to payment for this order">
              <i class="bi bi-credit-card text-lg"></i>
              <span>Pay Now</span>
            </a>
          @endif

          <a href="{{ route('orders.chat.show', $order->id) }}"
             class="inline-flex items-center justify-center gap-2 rounded-xl border border-sky-600 px-5 py-2.5 text-base font-semibold text-sky-700 transition hover:bg-sky-50"
             title="Open conversation about this order">
            <i class="bi bi-chat-dots text-lg"></i>
            <span>Messages</span>
          </a>

          <button type="button"
                  class="no-print inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-5 py-2.5 text-base font-semibold text-slate-700 transition hover:bg-slate-50"
                  title="Print a clean invoice view"
                  onclick="window.print()">
            <i class="bi bi-printer text-lg"></i>
            <span>Print</span>
          </button>

          <a href="{{ route('account.orders') }}"
             class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-5 py-2.5 text-base font-semibold text-slate-700 transition hover:bg-slate-50"
             title="Return to your orders">
            <i class="bi bi-arrow-left-circle text-lg"></i>
            <span>Back</span>
          </a>

          @if(in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_PROCESSING]))
            <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-600 px-5 py-2.5 text-base font-semibold text-rose-700 transition hover:bg-rose-50"
                    data-ui-toggle="modal"
                    data-ui-target="#cancelModal-{{ $order->id }}"
                    title="Cancel this order">
              <i class="bi bi-x-circle text-lg"></i>
              <span>Cancel Order</span>
            </button>
          @endif

          @if($order->status === \App\Models\Order::STATUS_SHIPPED)
            <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-600 px-5 py-2.5 text-base font-semibold text-emerald-700 transition hover:bg-emerald-50 has-tooltip"
                    title="Confirm you received the order"
                    data-ui-toggle="modal"
                    data-ui-target="#deliverModal-{{ $order->id }}">
              <i class="bi bi-check2-circle text-lg"></i>
              <span>Mark Delivered</span>
            </button>
            @include('seller.orders.modals.delivered')

            <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-500 px-5 py-2.5 text-base font-semibold text-amber-700 transition hover:bg-amber-50 has-tooltip"
                    title="Assess the shipped product or report a problem"
                    data-ui-toggle="modal"
                    data-ui-target="#assessModal-{{ $order->id }}">
              <i class="bi bi-clipboard-check text-lg"></i>
              <span>Assess Delivery</span>
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
              <a href="{{ route('pay_now', $order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                <i class="bi bi-credit-card"></i> Pay Now
              </a>
              <button type="button" class="ml-2 inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
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
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
          </div>
          <div class="px-4 py-4">
            <p class="mb-3">Are you sure you want to cancel this order? This action cannot be undone.</p>
            <div class="mb-3">
              <label for="cancel-reason-{{ $order->id }}" class="mb-1 block text-sm font-medium text-slate-700">Reason (optional)</label>
              <textarea name="cancel_reason" id="cancel-reason-{{ $order->id }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Tell the seller why you're cancelling"></textarea>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Keep Order</button>
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
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
          </div>
          <div class="px-4 py-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
              <div class="col-span-12 md:col-span-6">
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
              <div class="col-span-12 md:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full border-danger">
                  <div class="p-4 sm:p-5 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                      <i class="bi bi-exclamation-triangle text-rose-600"></i>
                      <strong>There is a problem</strong>
                    </div>
                    <p class="text-slate-500 mb-3">Start a dispute if the item arrived damaged, not as described, or never arrived.</p>
                    <div class="grid gap-2 mt-auto">
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
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Processing timeline & ship-by notice --}}
  @php
    $timelineItems = collect($order->items ?? []);
    $timelineDigitalItems = $timelineItems->filter(function ($it) {
      return strtolower((string) (optional($it->product)->type ?? '')) === 'digital';
    });
    $isDigitalOnlyOrder = $timelineDigitalItems->isNotEmpty() && $timelineDigitalItems->count() === $timelineItems->count();
    $timelineDigitalFilesCount = $timelineDigitalItems->sum(function ($it) {
      return (int) collect(optional($it->product)->digitalFiles ?? [])->count();
    });

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
      <div class="grid grid-cols-12 gap-3 items-center">
        <div class="col-span-12 md:col-span-6">
          <div class="font-semibold mb-1">Processing Timeline</div>
          <ul class="text-xs">
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-check-circle {{ $stepPlaced ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                  <span>Order placed</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($placedAt) ?? '-' }}</span>
              </div>
            </li>
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-gear-fill {{ $paid ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                  <span>Processing</span>
                </div>
                <span class="text-slate-500">
                  @if($processingAt)
                    {{ $formatDateTime($processingAt) }}
                  @elseif($paid)
                    -
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
                  <i class="bi bi-truck {{ $stepShipped ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                  <span>Shipped</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($shippedAt) ?? '-' }}</span>
              </div>
            </li>
            <li class="mb-2">
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-box-seam {{ $stepDelivered ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                  <span>Delivered</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($deliveredAt) ?? '-' }}</span>
              </div>
            </li>
            <li>
              <div class="flex justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <i class="bi bi-flag {{ $stepCompleted ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                  <span>Completed</span>
                </div>
                <span class="text-slate-500">{{ $formatDateTime($completedAt) ?? '-' }}</span>
              </div>
            </li>
          </ul>
        </div>
        @if($paid)
          <div class="col-span-12 md:col-span-6">
            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-0">
              <i class="bi bi-info-circle mr-2"></i>
              @if($isDigitalOnlyOrder)
                @if($status === \App\Models\Order::STATUS_PENDING)
                  Complete payment to unlock your digital downloads.
                @elseif($timelineDigitalFilesCount > 0)
                  Your digital files are ready to download below.
                @else
                  Digital order confirmed. The seller has not added a download file or access link yet.
                @endif
              @elseif($stepCompleted && $completedAt)
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
        $hasDigitalPending = $order->items->contains(function ($it) {
          return strtolower((string) (optional($it->product)->type ?? '')) === 'digital';
        });
      @endphp
      @if($hasDigitalPending && $isPending)
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mt-3 mb-0" role="alert">
          <i class="bi bi-lock mr-2"></i>
          This order includes digital items. Downloads unlock after payment is completed.
        </div>
      @endif

      @php
        $hasDigital = $order->items->contains(function ($it) {
          return strtolower((string) (optional($it->product)->type ?? '')) === 'digital';
        });
        $needsDownload = $order->items->contains(function ($it) {
          $isDigitalItem = strtolower((string) (optional($it->product)->type ?? '')) === 'digital';
          return $isDigitalItem && empty($it->downloaded_at);
        });
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
          $isDigital  = $product && strtolower((string) ($product->type ?? '')) === 'digital';
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
            <i class="bi bi-star-fill text-xl"></i>
            <div>
              <strong>Your order has been delivered.</strong>
              <div class="text-xs">Share feedback with the seller by leaving a quick review.</div>
            </div>
          </div>
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400" data-ui-toggle="modal" data-ui-target="#{{ $pendingReviewModalId }}">
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

    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
      {{-- ===== ORDER SUMMARY ===== --}}
      <div class="col-span-12 md:col-span-6">
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
                  <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener" class="font-medium text-sky-700 underline hover:text-sky-600">Track package</a>
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
      <div class="col-span-12 md:col-span-6">
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
                <span class="font-semibold">Payment Method:</span> {{ payment_method_label($order->payments->last()?->payment_method ?? $order->payment_method) }}
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
                    $isDigital  = $product && strtolower((string) ($product->type ?? '')) === 'digital';

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
                                      [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_DELIVERED],
                                      true
                                   ) && $isDigital;
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
                            class="h-auto max-w-full rounded"
                            style="max-width:100px; height:auto; object-fit:cover;">
                        </a>
                      @endif
                    </td>

                    {{-- Product --}}
                    <td>
                      @if($product)
                        <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="text-slate-900 hover:text-emerald-700">
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
                          <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                            <i class="bi bi-check-circle"></i>
                            {{ $item->review->rating }} &#9733;
                          </span>
                          @php $editModalId = 'editReview_'.$item->id; @endphp
                          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" data-ui-toggle="modal" data-ui-target="#{{ $editModalId }}">
                            <i class="bi bi-pencil"></i> Edit
                          </button>
                        </div>
                      @elseif($canReviewDelivered || ($isDigital && $canReviewDigitalIfCompleted))
                        @php $downloaded = !empty($item->downloaded_at); @endphp
                        @if($isDigital && ! $downloaded)
                          <span class="text-slate-500 text-xs">Download required to review</span>
                        @else
                          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50 px-3 py-1.5 text-xs" data-ui-toggle="modal" data-ui-target="#{{ $modalId }}">
                            <i class="bi bi-star"></i> Review
                          </button>
                        @endif
                      @else
                        <span class="text-slate-500 text-xs">Available after delivery</span>
                      @endif
                    </td>

                    {{-- Downloads (digital + allowed statuses) --}}
                    <td>
                      @if($isDigital && $canDownload)
                        @if($product && $product->digitalFiles->count())
                          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-0">
                            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
                              @foreach($product->digitalFiles as $file)
                                <li class="px-4 py-3 flex justify-between items-center">
                                  <a href="{{ route('digital-files.download', $file) }}"
                                     target="_blank" rel="noopener"
                                     class="inline-flex items-center">
                                    <i class="fas {{ $file->isExternalUrl() ? 'fa-link' : 'fa-file-download' }} mr-2"></i> {{ $file->filename }}
                                  </a>
                                </li>
                              @endforeach
                            </ul>
                          </div>
                        @else
                          <span class="text-amber-700 text-xs">No digital delivery asset added yet</span>
                        @endif
                      @elseif($isDigital && $order->status === \App\Models\Order::STATUS_PENDING)
                        <span class="text-slate-500 text-xs">Unlocks after payment</span>
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
                $isDigital  = $product && strtolower((string) ($product->type ?? '')) === 'digital';

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
                                  [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_DELIVERED],
                                  true
                                ) && $isDigital;
              @endphp

              <div class="rounded-2xl border border-slate-200 bg-white shadow-sm order-item-card mb-3" @if($reviewed && $item->review) id="review-{{ $item->review->id }}" @endif>
                <div class="p-4 sm:p-5">
                  <div class="flex items-start gap-3">
                    @if($thumbUrl)
                      <a href="{{ $product ? route('listing.show', $product->slug) : '#' }}" target="_blank" class="order-item__thumb-wrap">
                        <img src="{{ $thumbUrl }}" alt="{{ $product->name ?? 'Product image' }}" class="order-item__thumb">
                      </a>
                    @else
                      <div class="order-item__thumb-wrap">
                        <div class="order-item__thumb placeholder">
                          <i class="bi bi-image"></i>
                        </div>
                      </div>
                    @endif
                    <div class="grow">
                      <div class="flex justify-between items-start">
                        <div style="min-width:0;">
                          <div class="font-semibold text-clamp-2">
                            @if($product)
                              <a href="{{ route('listing.show', $product->slug) }}" target="_blank" class="text-slate-900 hover:text-emerald-700">{{ $product->name }}</a>
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
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                              <i class="bi bi-check-circle"></i> Reviewed
                            </span>
                          @elseif(($order->status === \App\Models\Order::STATUS_DELIVERED) || ($isDigital && (($order->status === \App\Models\Order::STATUS_COMPLETED) || ($order->status === \App\Models\Order::STATUS_DELIVERED)) && !empty($item->downloaded_at)))
                            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs" data-ui-toggle="modal" data-ui-target="#{{ $modalId }}">
                              <i class="bi bi-star"></i> Review
                            </button>
                          @else
                            <span class="text-slate-500 text-xs">Review after delivery</span>
                          @endif
                        </div>
                      </div>

                      @if($isDigital)
                        <div class="mt-3">
                          <div class="label mb-1">Downloads</div>
                          @if($canDownload && $product && $product->digitalFiles->count())
                            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-0">
                              <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
                                @foreach($product->digitalFiles as $file)
                                  <li class="px-4 py-3 flex justify-between items-center">
                                    <a href="{{ route('digital-files.download', $file) }}" target="_blank" rel="noopener" class="inline-flex items-center">
                                      <i class="fas {{ $file->isExternalUrl() ? 'fa-link' : 'fa-file-download' }} mr-2"></i> {{ $file->filename }}
                                    </a>
                                  </li>
                                @endforeach
                              </ul>
                            </div>
                          @elseif($order->status === \App\Models\Order::STATUS_PENDING)
                            <div class="text-xs text-slate-500">Unlocks after payment</div>
                          @elseif($canDownload)
                            <div class="text-xs text-amber-700">No digital delivery asset added yet</div>
                          @else
                            <div class="text-xs text-slate-500">Downloads become available after payment processing.</div>
                          @endif
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
          {{-- Mobile: stacked cards --}}
          <div class="block p-2 md:hidden">
            <div class="space-y-2">
              @foreach($order->payments as $pay)
                @php
                  $statusStr   = strtolower((string)$pay->status);
                  $isCompleted = (string)$pay->status === '3' || $statusStr === 'success' || $statusStr === 'completed';
                  $statusLabel = $isCompleted ? 'Completed' : (is_numeric($pay->status) ? $pay->status : ucfirst((string)$pay->status));
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                  <div class="flex items-center justify-between gap-2">
                    <div class="text-xs text-slate-500">Payment #{{ $loop->iteration }}</div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $isCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                      {{ $statusLabel }}
                    </span>
                  </div>
                  <div class="mt-2 space-y-1 text-sm">
                    <div class="flex items-start justify-between gap-3">
                      <span class="text-slate-500">Reference</span>
                      <span class="min-w-0 break-all text-right font-medium text-slate-900">{{ $pay->local_transaction_id ?: '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                      <span class="text-slate-500">Method</span>
                      <span class="text-right text-slate-900">{{ payment_method_label($pay->payment_method) }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                      <span class="text-slate-500">Amount</span>
                      <span class="font-semibold text-slate-900">{{ money((float)($pay->total_amount ?? 0)) }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                      <span class="text-slate-500">Paid On</span>
                      <span class="text-right text-slate-900">{{ optional($pay->created_at)->format('d M Y, h:i A') ?: '-' }}</span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Desktop/Tablet: table --}}
          <div class="hidden overflow-x-auto md:block">
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
                    <td class="max-w-[260px] break-all">{{ $pay->local_transaction_id }}</td>
                    <td>{{ payment_method_label($pay->payment_method) }}</td>
                    <td>{{ money((float)($pay->total_amount ?? 0)) }}</td>
                    <td>
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $isCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
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
</div>
</div>

@php
  // Prepare a modal listing when multiple digital files exist
  $__dlFiles = [];
  foreach (($order->items ?? []) as $__it) {
    $p = optional($__it->product);
    if ($p && strtolower((string) ($p->type ?? '')) === 'digital') {
      foreach (($p->digitalFiles ?? collect()) as $__df) {
        $__dlFiles[] = $__df;
      }
    }
  }
  $__showDownloadModal = in_array($order->status, [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_DELIVERED], true)
                        && count($__dlFiles) > 1;
@endphp
@if($__showDownloadModal)
  <div class="modal" id="downloadAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900">Your Downloads</h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="px-4 py-4 p-0">
          <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
            @foreach($__dlFiles as $__file)
              <li class="px-4 py-3 flex justify-between items-center">
                <div class="mr-3 truncate">
                  <i class="bi bi-file-earmark-arrow-down mr-2"></i>
                  <span class="inline-block max-w-72 truncate" title="{{ $__file->filename }}">{{ $__file->filename }}</span>
                </div>
                <a href="{{ route('digital-files.download', $__file) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500">
                  <i class="bi {{ $__file->isExternalUrl() ? 'bi-link-45deg' : 'bi-download' }}"></i> {{ $__file->isExternalUrl() ? 'Open link' : 'Download' }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endif

{{-- ===== REVIEW MODALS (Delivered for physical; Completed/Delivered + download for digital) ===== --}}
@foreach($order->items as $item)
  @php
    $isDigital = strtolower((string) (optional($item->product)->type ?? '')) === 'digital';
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
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
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
            <h5 class="text-base font-semibold text-slate-900">Edit Review - {{ optional($item->product)->name }}</h5>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
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





