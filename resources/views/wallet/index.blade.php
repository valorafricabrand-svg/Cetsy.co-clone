@extends('theme.'.theme().'.layouts.app')

@section('title', 'Wallet Transactions')
@push('styles')
<style>
  .wallet-modal {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.55);
    padding: 1rem;
  }
  .wallet-modal.is-open { display: flex; }
  .wallet-modal-dialog { width: 100%; max-width: 34rem; }
  .wallet-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: .25rem .6rem;
    font-size: .75rem;
    font-weight: 600;
    color: #334155;
  }
</style>
@endpush
@section('main')
@php
    $showBuyerSidebar = auth()->check() && auth()->user()->isBuyer();
    $showSellerSidebar = auth()->check() && auth()->user()->isSeller();
    $hasSidebar = $showBuyerSidebar || $showSellerSidebar;
@endphp
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            @if($hasSidebar)
                <div class="col-span-12 lg:col-span-3">
                    @if($showBuyerSidebar)
                        @include('buyer.partials.sidebar')
                    @elseif($showSellerSidebar)
                        @include('seller.partials.sidebar')
                    @endif
                </div>
            @endif
            <div class="{{ $hasSidebar ? 'col-span-12 lg:col-span-9' : 'col-span-12' }}">

            {{-- Header --}}
<div class="flex items-center justify-between mb-2">
    <h2 class="text-xl font-semibold text-slate-900">Wallet Overview</h2>
    <div class="flex flex-wrap items-center gap-2">
        {{-- View Payouts --}}
        @if(auth()->user()->isSeller())
        <a href="{{ route('seller.payouts.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
            <i class="fas fa-receipt mr-1"></i> View Payouts
        </a>
        @endif
        <a href="{{ route('wallet.deposit.form') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
            <i class="fas fa-plus mr-1"></i> Deposit Funds
        </a>

        @if(auth()->user()->isSeller())
          <button class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500"
                  data-ui-toggle="modal"
                  data-ui-target="#payoutModal"
                  @disabled($balance < $minAmount || ($paymentMethods?->count() ?? 0) === 0)>
              Request&nbsp;Payout
          </button>
        @endif

        
        <a href="{{ route('wallet.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
            <i class="fas fa-sync-alt mr-1"></i> Refresh
        </a>
    </div>
</div>


            {{-- Summary Card --}}
            <div class="grid grid-cols-12 gap-4 mb-4">
                <div class="col-span-12 md:col-span-4">
                    <div class="h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="p-4 sm:p-5 flex items-center">
                            <i class="fas fa-wallet text-3xl text-emerald-600 mr-3"></i>
                            <div>
                                <div class="text-slate-500 text-xs">Available Balance</div>
                                <div class="text-3xl font-bold text-slate-900">
                                    USD {{ number_format($balance, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-4">
                    <div class="h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="p-4 sm:p-5 flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-pause-circle text-3xl text-amber-600 mr-3"></i>
                                <div>
                                    <div class="text-slate-500 text-xs">On Hold</div>
                                    <div class="text-3xl font-bold text-slate-900">
                                        USD {{ number_format($onHold, 2) }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('wallet.index', array_merge(request()->query(), ['status' => 'on_hold'])) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" title="View on-hold transactions">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>






@if(($paymentMethods?->count() ?? 0) === 0)
  <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
      Add a payout method to request payouts.
      <a href="{{ route('seller.payment-methods.index') }}" class="font-semibold underline">Manage methods</a>.
    </div>
  </div>
@endif

{{-- Payout modal --}}
<div class="wallet-modal" id="payoutModal" tabindex="-1" aria-hidden="true">
    <div class="wallet-modal-dialog">
        <form method="POST"
      action="{{ route('seller.payouts.store') }}"
      class="rounded-2xl border border-slate-200 bg-white shadow-xl needs-validation"
      novalidate>
    @csrf
    <input type="hidden" name="require_otp" value="1">


    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h5 class="text-base font-semibold text-slate-900">Request Payout</h5>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
    </div>

    <div class="px-4 py-4">
        {{-- amount --}}
        <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Amount</label>
            <div class="flex w-full items-stretch">
              <input type="number"
                     name="amount"
                     class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('amount') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                     step="0.01"
                     min="{{ number_format($minAmount, 2, '.', '') }}"
                     max="{{ number_format($maxPayout, 2, '.', '') }}"
                     value="{{ old('amount') }}"
                     required>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50"
                      type="button"
                      id="payoutMaxBtn"
                      tabindex="-1"
                      aria-label="Use available balance"
                      
                      
                      title="Use available balance" @disabled($maxPayout <= 0)>Max</button>
            </div>
            <div class="mt-1 text-xs text-rose-600">@error('amount') {{ $message }} @else Required @enderror</div>
            <small class="text-slate-500 block">Available: {{ get_currency() }} {{ number_format($balance,2) }}</small>
            <small class="text-slate-500 block">Max request (before fee deducted): {{ get_currency() }} {{ number_format($maxPayout,2) }}</small>
            <small class="text-slate-500 block">
              Fee rate: {{ number_format($feeRate * 100, 2) }}% &middot; Minimum: {{ get_currency() }} {{ number_format($minAmount,2) }}
            </small>
            <small class="text-slate-500 block">
              Estimated fee: <span id="payoutFee">0.00</span> &middot; You receive: <span id="payoutNet">0.00</span>
            </small>
            @if($maxPayout <= 0)
              <small class="text-rose-600 block">Insufficient balance to request a payout. Increase your available balance or wait for on-hold funds to be released.</small>
            @endif
        </div>

        {{-- method --}}
        <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Method</label>
            <select name="method"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('method') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    required>
                <option hidden value="">Choose&hellip;</option>
                @forelse($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod->id }}" {{ old('method') == $paymentMethod->id ? 'selected' : '' }}>
                        {{ $paymentMethod->paymentType->name }} - {{ $paymentMethod->account_name }}
                    </option>
                @empty
                    <option value="">No payment methods found</option>
                @endforelse
            </select>
            <div class="mt-1 text-xs text-rose-600">@error('method') {{ $message }} @else Required @enderror</div>
            @if(($paymentMethods?->count() ?? 0) === 0)
              <div class="mt-1 text-xs text-slate-500">
                No payout methods yet. <a href="{{ route('seller.payment-methods.index') }}" target="_blank">Add one</a> to continue or use the button below.
              </div>
            @endif
        </div>

        {{-- Add new payout method (separate modal trigger) --}}
        <div class="mb-3">
          <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" data-ui-toggle="modal" data-ui-target="#addPayoutMethodModal">
            <i class="bi bi-plus-lg"></i> Add Payout Method
          </button>
          <small class="text-slate-500 ml-2">After saving, this page refreshes and you can submit the payout.</small>
        </div>

        
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3 justify-between">
        <span class="wallet-chip" id="payoutNetBadge">You receive: {{ get_currency() }} 0.00</span>
        <button id="payoutSubmitBtn" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" type="submit">
            Submit&nbsp;Request
        </button>
    </div>
</form>

</div>

@if(!empty($otpPendingPayout))
  <div id="payout-verify-inline" class="mt-3 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold">Verify Payout</div>
    <div class="p-4 sm:p-5">
      <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.submit') ? route('seller.payouts.otp.submit', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/verify')) }}" class="grid grid-cols-12 gap-4 gap-3 items-end">
        @csrf
        <div class="col-span-12 md:col-span-6">
          <label class="mb-1 block text-sm font-medium text-slate-700">Verification Code</label>
          <input type="text" name="code" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" placeholder="6-digit code" required>
          @error('code') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
        </div>
        <div class="col-span-12 md:col-span-6 flex gap-2">
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" type="submit">Verify &amp; Submit</button>
          <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/verify')) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Open full verify page</a>
        </div>
      </form>
      <div class="mt-2 flex gap-3">
        <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.resend') ? route('seller.payouts.otp.resend', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/resend-otp')) }}" class="inline">
          @csrf
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition text-emerald-700 hover:text-emerald-600 underline-offset-2 hover:underline p-0">Resend code</button>
        </form>
        <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.cancel') ? route('seller.payouts.otp.cancel', $otpPendingPayout) : url('/seller/payouts/'.$otpPendingPayout->id.'/cancel')) }}" class="inline" onsubmit="return confirm('Cancel this payout request?');">
          @csrf
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition text-emerald-700 hover:text-emerald-600 underline-offset-2 hover:underline text-rose-600 p-0">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endif
</div>

{{-- Add Payout Method Modal (separate to avoid nested forms) --}}
<div class="wallet-modal" id="addPayoutMethodModal" tabindex="-1" aria-hidden="true">
  <div class="wallet-modal-dialog">
    <form action="{{ route('seller.payment-methods.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white shadow-xl needs-validation" novalidate>
      @csrf
      <input type="hidden" name="redirect_to" value="{{ route('wallet.index') }}">
      <input type="hidden" name="open_payout" value="1">
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
        <h5 class="text-base font-semibold text-slate-900">Add Payout Method</h5>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="px-4 py-4">
        <div class="grid grid-cols-12 gap-4 gap-3">
          <div class="col-span-12 md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
            <select name="payment_type_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required>
              <option hidden value="">Choose&hellip;</option>
              @foreach($paymentTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-12 md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Account Name</label>
            <input type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" name="account_name" required>
          </div>
          <div class="col-span-12">
            <label class="mb-1 block text-sm font-medium text-slate-700">Account Number</label>
            <input type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" name="account_number" required>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
        <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Save Method</button>
      </div>
    </form>
  </div>
  </div>


            {{-- Filters --}}
            <form method="GET" action="{{ route('wallet.index') }}" class="grid grid-cols-12 gap-4 gap-3 mb-4">
                <div class="col-span-12 md:col-span-3">
                    <select name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
            </select>
        </div>
                <div class="col-span-12 md:col-span-3">
                    <select name="status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">All Statuses</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="From date">
                </div>
                <div class="col-span-12 md:col-span-3">
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="To date">
                </div>
                <div class="col-span-12 md:col-span-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </form>

            {{-- Transactions Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
                    <thead class="bg-slate-50">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Hold Info</th>
                            <th class="text-right">Credit (USD)</th>
                            <th class="text-right">Debit (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    {{ $transaction->description ?? 'Transaction' }}
                                    @php
                                        $meta = is_array($transaction->meta) ? $transaction->meta : (json_decode($transaction->meta ?? '', true) ?: []);
                                        $orderId = $meta['order_id'] ?? ($transaction->order_id ?? null);
                                    @endphp
                                    @if($orderId && (auth()->user()->isSeller() ?? false))
                                        <div class="text-xs text-slate-500">
                                            Order #{{ $orderId }}
                                            @if(\Illuminate\Support\Facades\Route::has('seller.orders.show'))
                                                <a href="{{ route('seller.orders.show', $orderId) }}">view</a>
                                            @else
                                                <a href="{{ url('/seller/orders/'.$orderId) }}">view</a>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>{{ ucfirst(str_replace('_',' ', $transaction->status ?? 'completed')) }}</td>
                                <td>
                                    @if(($transaction->status ?? null) === 'on_hold')
                                        @php
                                            $oid = $orderId ?? null;
                                            $o = $oid ? ($ordersById[$oid] ?? null) : null;
                                        @endphp
                                        @if($o)
                                            @php
                                                $status = strtolower((string)($o->status ?? ''));
                                                $eta = null;
                                                if ($status === 'shipped') {
                                                    $base = $o->shipped_at ?? $o->updated_at;
                                                    if ($base) {
                                                        $eta = $base->copy()->addDays($autoReleaseDays);
                                                    }
                                                }
                                            @endphp
                                            <div class="text-xs">
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900">Order {{ $status }}</span>
                                                @php
                                                    $message = null;
                                                    if ($status === 'pending') {
                                                        $message = 'Awaiting buyer payment';
                                                    } elseif ($status === 'processing') {
                                                        $message = 'Paid – waiting for shipment';
                                                    } elseif ($status === 'shipped') {
                                                        if ($eta) {
                                                            $message = 'Shipped – auto-release '.$autoReleaseDays.' days after shipment';
                                                        } else {
                                                            $message = 'Shipped – awaiting buyer confirmation';
                                                        }
                                                    } elseif ($status === 'delivered') {
                                                        $message = 'Delivered – releasing shortly';
                                                    } elseif ($status === 'completed') {
                                                        $message = 'Funds released for this order';
                                                    } elseif ($status === 'refunded') {
                                                        $message = 'Order refunded – payout reversed';
                                                    } elseif ($status === 'cancelled') {
                                                        $message = 'Order cancelled – no payout due';
                                                    } elseif ($status === 'returned') {
                                                        $message = 'Order returned – resolving payout';
                                                    }
                                                @endphp
                                                @if($eta && $status === 'shipped')
                                                    <div class="text-slate-500">
                                                        ETA: {{ $eta->format('d M Y') }} (auto-release {{ $autoReleaseDays }} days after shipped)
                                                    </div>
                                                @endif
                                                @if($message)
                                                    <div class="text-slate-500">{{ $message }}</div>
                                                @endif
                                            </div>
                                        @else
                                            @php
                                                $method = strtolower((string)($transaction->method ?? $transaction->type ?? ''));
                                            @endphp
                                            @if($method === 'mpesa_stk')
                                                <div class="text-xs text-slate-500">Awaiting M-Pesa confirmation</div>
                                            @else
                                                <div class="text-xs text-slate-500">On hold (pending processing)</div>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="text-right text-emerald-600">
                                    {{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}
                                </td>
                                <td class="text-right text-rose-600">
                                    {{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-500">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
      
                  {{ $transactions->links('pagination::tailwind') }}
            </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const body = document.body;
    const openModal = (selector) => {
      const modal = document.querySelector(selector);
      if (!modal) return;
      modal.classList.add('is-open');
      body.classList.add('overflow-hidden');
    };
    const closeModal = (modal) => {
      if (!modal) return;
      modal.classList.remove('is-open');
      if (!document.querySelector('.wallet-modal.is-open')) {
        body.classList.remove('overflow-hidden');
      }
    };
    window.openWalletModal = openModal;
    document.querySelectorAll('[data-ui-toggle="modal"][data-ui-target]').forEach((trigger) => {
      trigger.addEventListener('click', function(event){
        event.preventDefault();
        openModal(this.getAttribute('data-ui-target'));
      });
    });
    document.querySelectorAll('[data-ui-dismiss="modal"]').forEach((button) => {
      button.addEventListener('click', function(){
        closeModal(this.closest('.wallet-modal'));
      });
    });
    document.querySelectorAll('.wallet-modal').forEach((modal) => {
      modal.addEventListener('click', function(event){
        if (event.target === modal) closeModal(modal);
      });
    });
    document.addEventListener('keydown', function(event){
      if (event.key === 'Escape') {
        const topModal = document.querySelector('.wallet-modal.is-open');
        if (topModal) closeModal(topModal);
      }
    });
  })();
  (function(){
    // Support inline payout form (preferred) or fallback to old modal selectors
    let input = document.querySelector('#payout-inline input[name="amount"]');
    let methodSel = document.querySelector('#payout-inline select[name="method"]');
    if(!input){
      input = document.querySelector('#payoutModal input[name="amount"]');
      methodSel = document.querySelector('#payoutModal select[name="method"]');
    }
    if(!input) return;
    const feeRate = {{ json_encode($feeRate) }};
    const feeEl = document.getElementById('payoutFee');
    const netEl = document.getElementById('payoutNet');
    // methodSel defined above
    const submitBtn = document.getElementById('payoutSubmitBtn');
    const maxBtn = document.getElementById('payoutMaxBtn');
    const fmt = (n) => (isFinite(n) ? Number(n).toFixed(2) : '0.00');
    const currency = {{ json_encode(get_currency()) }};
    const netBadge = document.getElementById('payoutNetBadge');
    function recalc(){
      const amt = parseFloat(input.value || '0');
      const fee = Math.round((amt * feeRate) * 100) / 100;
      const net = Math.max(0, amt - fee);
      if (feeEl) feeEl.textContent = fmt(fee);
      if (netEl) netEl.textContent = fmt(net);
      if (netBadge) netBadge.textContent = 'You receive: ' + currency + ' ' + fmt(net);
    }
    function updateSubmitDisabled(){
      let disabled = false;
      const v = parseFloat(input.value || '');
      const min = parseFloat(input.min || '0');
      const max = parseFloat(input.max || '0');
      if (!isFinite(v) || v < min || v > max) disabled = true;
      if (methodSel && (!methodSel.value || methodSel.value === '')) disabled = true;
      if (submitBtn) submitBtn.disabled = disabled;
    }
    input.addEventListener('input', recalc);
    input.addEventListener('input', updateSubmitDisabled);
    if (methodSel) methodSel.addEventListener('change', updateSubmitDisabled);
    if (maxBtn) maxBtn.addEventListener('click', function(){
      if (!input) return;
      const maxStr = input.max || '0';
      const maxVal = parseFloat(maxStr);
      const minVal = parseFloat(input.min || '0');
      if (!isFinite(maxVal)) return;
      // Confirm if max is effectively zero or below minimum
      if (maxVal <= 0.01 || maxVal < minVal) {
        const msg = 'Your maximum payout is very low or below the minimum threshold. You likely need more available balance. Continue to fill Max?';
        if (!window.confirm(msg)) return;
      }
      input.value = maxStr;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    // Disable Max button dynamically based on max value
    (function syncMaxBtnDisabled(){
      const maxVal = parseFloat(input.max || '0');
      if (maxBtn) maxBtn.disabled = !(isFinite(maxVal) && maxVal > 0);
    })();
    recalc();
    updateSubmitDisabled();
  })();
  // Auto-open payout modal if requested (after adding method)
  @if(session('open_payout_modal') || request()->boolean('open_payout') || $errors->has('amount') || $errors->has('method'))
    document.addEventListener('DOMContentLoaded', function(){
      window.openWalletModal && window.openWalletModal('#payoutModal');
    });
  @endif
</script>
@endpush






