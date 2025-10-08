{{-- resources/views/admin/wallets/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Wallet Transactions')

@section('content')
<div class="content">
  <div class="card shadow-sm">
    <div class="card-body">

      {{-- Header --}}
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h2 class="h5 mb-0">Wallet Transactions</h2>
        @if(auth()->user()->isAdmin())
          <div class="ms-md-auto">
            <a href="{{ route('admin.wallets.create') }}" class="btn btn-sm btn-primary">
              + Top Up Seller Wallet
            </a>
          </div>
        @endif

        {{-- Filters --}}
        <form method="get" class="d-flex flex-wrap gap-2">
          <input type="text" name="q" value="{{ $search ?? request('q') }}" placeholder="Search ref/desc/user…" class="form-control form-control-sm" style="width:220px">

          <input type="number" name="user_id" value="{{ $userId ?? request('user_id') }}" placeholder="User ID" class="form-control form-control-sm" style="width:110px">

          <select name="type" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
            <option value="">All Types</option>
            @foreach(($types ?? ['deposit','payment','fee','subscription']) as $t)
              <option value="{{ $t }}" @selected(($type ?? request('type')) === $t)>{{ ucfirst($t) }}</option>
            @endforeach
          </select>

          <select name="per_page" class="form-select form-select-sm" style="width:100px" onchange="this.form.submit()">
            @foreach([10,25,50,100] as $n)
              <option value="{{ $n }}" @selected(($perPage ?? request('per_page',10)) == $n)>{{ $n }}</option>
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
        $creditSum  = $totals['credit']  ?? ($wallets->sum('credit'));
        $debitSum   = $totals['debit']   ?? ($wallets->sum('debit'));
        $balanceSum = $totals['balance'] ?? ($wallets->sum('balance'));
      @endphp
      <div class="row g-3 mb-3 small">
        <div class="col-md-4">
          <div class="p-3 bg-light rounded border">
            <strong>Total Credit:</strong> {{ number_format($creditSum, 2) }}
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 bg-light rounded border">
            <strong>Total Debit:</strong> {{ number_format($debitSum, 2) }}
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 bg-light rounded border">
            <strong>Net Balance (sum of rows):</strong> {{ number_format($balanceSum, 2) }}
          </div>
        </div>
      </div>

      {{-- Bulk + Table --}}
      <form id="bulkForm" method="POST" action="{{ route('admin.wallets.bulk') ?? '#' }}">
        @csrf
        @method('DELETE')

        <div class="table-responsive mb-3">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:32px"><input type="checkbox" id="checkAll"></th>
                @php $dirToggler = fn($field) => request('dir','desc')==='asc' ? 'desc' : 'asc'; @endphp
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'id','dir'=>$dirToggler('id')]) }}" class="text-decoration-none">#</a>
                </th>
                <th>User</th>
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'credit','dir'=>$dirToggler('credit')]) }}" class="text-decoration-none">Credit</a>
                </th>
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'debit','dir'=>$dirToggler('debit')]) }}" class="text-decoration-none">Debit</a>
                </th>
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'balance','dir'=>$dirToggler('balance')]) }}" class="text-decoration-none">Balance</a>
                </th>
                <th>Type</th>
                <th>Reference</th>
                <th class="d-none d-lg-table-cell">Description</th>
                <th class="d-none d-md-table-cell">
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'created_at','dir'=>$dirToggler('created_at')]) }}" class="text-decoration-none">Date</a>
                </th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($wallets as $w)
                <tr>
                  <td><input type="checkbox" name="ids[]" value="{{ $w->id }}" class="row-check"></td>
                  <td>#{{ $w->id }}</td>
                  <td>
                    @if(isset($w->user))
                      <a href="{{ route('admin.users.show', $w->user_id) ?? '#' }}" class="text-decoration-none">
                        {{ $w->user->name ?? 'User '.$w->user_id }}
                      </a>
                    @else
                      User {{ $w->user_id }}
                    @endif
                  </td>
                  <td class="text-success">{{ number_format($w->credit, 2) }}</td>
                  <td class="text-danger">{{ number_format($w->debit, 2) }}</td>
                  <td class="fw-semibold">{{ number_format($w->balance, 2) }}</td>
                  <td>{{ $w->type ?? '—' }}</td>
                  <td><span class="small">{{ $w->reference }}</span></td>
                  <td class="d-none d-lg-table-cell small">{{ $w->description }}</td>
                  <td class="d-none d-md-table-cell">{{ $w->created_at?->format('M d, Y H:i') }}</td>
                  <td class="text-end">
                    @if(auth()->user()->isAdmin())
                      <a href="{{ route('admin.wallets.show',$w) ?? '#' }}" class="btn btn-sm btn-outline-primary me-1">View</a>
                    
                     
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center text-muted py-3">No wallet records found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination & Bulk btns --}}
        <div class="d-flex justify-content-between align-items-center">
          <div>
            @if(auth()->user()->isAdmin())
              <div class="btn-group">
                <button type="button" id="exportCsvBtn" class="btn btn-outline-info btn-sm">Export CSV</button>
                <button type="submit" class="btn btn-outline-danger btn-sm"
                        onclick="this.form.action='{{ route('admin.wallets.bulk') ?? '#' }}'; return confirm('Delete selected?');">
                  Bulk Delete
                </button>
              </div>
            @endif
          </div>

          <div>


            {{ $wallets->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const checkAll  = document.getElementById('checkAll');
  const rowChecks = document.querySelectorAll('.row-check');
  if (checkAll) {
    checkAll.addEventListener('change', () => rowChecks.forEach(cb => cb.checked = checkAll.checked));
  }

  // Export CSV (client-side quick hack)
  document.getElementById('exportCsvBtn')?.addEventListener('click', function(){
    const rows = [];
    const headers = ["ID","User ID","Credit","Debit","Balance","Type","Reference","Description","Created At"];
    rows.push(headers.join(','));

    @foreach($wallets as $w)
      rows.push([
        "{{ $w->id }}",
        "{{ $w->user_id }}",
        "{{ $w->credit }}",
        "{{ $w->debit }}",
        "{{ $w->balance }}",
        "{{ $w->type }}",
        "{{ $w->reference }}",
        "{{ str_replace(["\r","\n",","],[' ',' ',' '], $w->description) }}",
        "{{ $w->created_at }}"
      ].join(','));
    @endforeach

    const blob = new Blob([rows.join('\n')], {type: 'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'wallets.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });
})();
</script>
@endpush
