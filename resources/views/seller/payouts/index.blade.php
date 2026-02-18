@extends('theme.'.theme().'.layouts.app')
@section('title','Payout Requests')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content py-4">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
      @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
      @endif

      <div class="flex items-center justify-between mb-4">
        <h3 class="mb-0">Payout Requests</h3>
        <div>
          <a href="{{ route('wallet.index', ['open_payout' => 1]) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
            Request Payout
          </a>
        </div>
      </div>

      @if(!empty($otpPending))
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex items-center justify-between">
          <div>
            <i class="fa-solid fa-shield mr-2"></i>
            You have a payout request (ID #{{ $otpPending->id }}) awaiting verification.
          </div>
          <div class="flex items-center gap-2">
            <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/verify')) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">Continue Verification</a>
            <form action="{{ route('seller.payouts.otp.cancel', $otpPending) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this payout request?');">
              @csrf
              <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-rose-600 text-rose-700 hover:bg-rose-50">Cancel</button>
            </form>
          </div>
        </div>
      @endif

      <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-4">
          Available balance: {{ get_currency() }} {{ number_format($balance,2) }}
      </div>

      @if(!empty($otpPending))
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mb-4">
          <div class="border-b border-slate-200 px-4 py-3 bg-white font-semibold">
            Verify Payout Request #{{ $otpPending->id }}
          </div>
          <div class="p-4">
            @if(session('success'))
              <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.submit') ? route('seller.payouts.otp.submit', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/verify')) }}" class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
              @csrf
              <div class="col-span-12 md:col-span-6">
                <label class="form-label">Verification Code</label>
                <input type="text" name="code" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('code') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" placeholder="6-digit code" required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-span-12 md:col-span-6 flex items-end gap-2">
                <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700" type="submit">Verify &amp; Submit</button>
                <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/verify')) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">Open full verify page</a>
              </div>
            </form>
            <div class="mt-2 flex gap-3">
              <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.resend') ? route('seller.payouts.otp.resend', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/resend-otp')) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition text-emerald-700 hover:underline p-0">Resend code</button>
              </form>
              <form method="POST" action="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.cancel') ? route('seller.payouts.otp.cancel', $otpPending) : url('/seller/payouts/'.$otpPending->id.'/cancel')) }}" class="inline" onsubmit="return confirm('Cancel this payout request?');">
                @csrf
                <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition text-emerald-700 hover:underline text-rose-600 p-0">Cancel</button>
              </form>
            </div>
          </div>
        </div>
      @endif

      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
          <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-sm mb-0 align-middle">
                  <thead class="bg-slate-50 text-slate-600">
                      <tr>
                          <th>#</th>
                          <th>Amount</th>
                          <th>Status</th>
                          <th>Requested&nbsp;On</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($requests as $req)
                          <tr>
                              <td>{{ $req->id }}</td>
                              <td>{{ get_currency() }} {{ number_format($req->amount,2) }}</td>
                              <td>
                                @php
                                  $st = strtolower((string) $req->status);
                                  $map = [
                                    'otp_pending' => ['warning','Awaiting verification'],
                                    'pending'     => ['secondary','Pending'],
                                    'approved'    => ['primary','Approved'],
                                    'sent'        => ['info','Sent'],
                                    'paid'        => ['success','Paid'],
                                    'rejected'    => ['danger','Rejected'],
                                    'cancelled'   => ['secondary','Cancelled'],
                                  ];
                                  [$cls, $label] = $map[$st] ?? ['dark', ucfirst($st)];
                                @endphp
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-{{ $cls }}">{{ $label }}</span>
                              </td>
                              <td>{{ $req->created_at->format('d M Y') }}</td>
                              <td class="whitespace-nowrap">
                                @if($req->status === 'otp_pending')
                                  <a href="{{ (\Illuminate\Support\Facades\Route::has('seller.payouts.otp.verify') ? route('seller.payouts.otp.verify', $req) : url('/seller/payouts/'.$req->id.'/verify')) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">Verify</a>
                                  <form action="{{ route('seller.payouts.otp.cancel', $req) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this payout request?');">
                                    @csrf
                                    <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-rose-600 text-rose-700 hover:bg-rose-50">Cancel</button>
                                  </form>
                                @else
                                  <span class="text-slate-500">-</span>
                                @endif
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="4" class="text-center py-4">
                                  No payout requests yet.
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
          <div class="border-t border-slate-200 px-4 py-3">
              {{ $requests->links() }}
          </div>
      </div>

  </div>
</div>

      </div>
    </div>
  </div>
</section>
@endsection

 

 








