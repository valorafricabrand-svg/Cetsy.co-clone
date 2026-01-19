{{-- resources/views/admin/payments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="content">
  <div class="container-xxl">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <h2 class="mb-0">Payments</h2>

      {{-- Filters --}}
      <form method="GET" class="d-flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="width:220px" placeholder="Search ref/desc/name/email/phone">

  
   

        {{-- method --}}
        <select name="payment_method" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
          <option value="">All methods</option>
          @foreach($methods ?? ['mpesa','paypal','stripe','paystack','card','wallet'] as $m)
            <option value="{{ $m }}" @selected(request('payment_method')===$m)>{{ strtoupper($m) }}</option>
          @endforeach
        </select>

   

        {{-- Date range --}}
        <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" style="width:150px">
        <input type="date" name="to"   value="{{ request('to')   }}" class="form-control form-control-sm" style="width:150px">

        {{-- per page --}}
        <select name="per_page" class="form-select form-select-sm" style="width:90px" onchange="this.form.submit()">
          @foreach([15,25,50,100] as $n)
            <option value="{{ $n }}" @selected(request('per_page',15)==$n)>{{ $n }}</option>
          @endforeach
        </select>

        <button class="btn btn-sm btn-primary">Filter</button>
      </form>
    </div>

    {{-- Flash --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Totals --}}
    @php
      $sumAmount = $totalAmount ?? $payments->sum('total_amount');
    @endphp
    <div class="row g-3 mb-3 small">
      <div class="col-md-4">
        <div class="p-3 bg-light rounded border">
          <strong>Total Amount (filtered):</strong> {{ get_currency() }} {{ number_format($sumAmount,2) }}
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              @php $dir = request('dir','desc')==='asc'?'asc':'desc';
                   $toggle = fn($f)=> request('dir','desc')==='asc' ? 'desc' : 'asc';
              @endphp
              <th>
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'id','dir'=>$toggle('id')]) }}" class="text-decoration-none">#</a>
              </th>
              <th>Payer</th>
              <th>
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'total_amount','dir'=>$toggle('total_amount')]) }}" class="text-decoration-none">Amount</a>
              </th>
              <th>Method</th>
                 <th>Payment</th>
         
              <th>
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'created_at','dir'=>$toggle('created_at')]) }}" class="text-decoration-none">Date</a>
              </th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $payment)
              <tr>
                <td>{{ $payment->id }}</td>
                <td>
                  {{ $payment->customer_name
                      ?? $payment->customer->name
                      ?? 'User '.$payment->user_id }}
                  <br>
                  <small class="text-muted">
                    {{ $payment->customer_email ?? $payment->customer->email ?? '' }}
                    {{ $payment->customer_phone ? ' • '.$payment->customer_phone : '' }}
                  </small>
                </td>
                <td>{{ get_currency() }} {{ number_format($payment->total_amount,2) }}</td>
                <td class="text-uppercase small">{{ $payment->payment_method }}</td>
              <td class="text-uppercase small">{{ $payment->payment_name }}</td>
                <td>{{ $payment->created_at?->format('d M Y H:i') }}</td>
                <td class="text-end">
                  <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-outline-secondary btn-sm">
                    View
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  No payments found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <button id="exportCsvBtn" class="btn btn-sm btn-outline-info">Export CSV</button>
        {{ $payments->appends(request()->query())->links('pagination::bootstrap-5') }}
      </div>
    </div>

  </div>
</div>
@endsection

