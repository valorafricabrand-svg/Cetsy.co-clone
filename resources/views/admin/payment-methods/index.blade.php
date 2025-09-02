@extends('layouts.app')
@section('title','Seller Payment Methods')

@section('content')
<div class="content py-4">
  <div class="container-xxl">
    <h2 class="mb-4">Seller Payment Methods</h2>

    <form class="mb-3" method="GET">
      <div class="input-group" style="max-width:520px;">
        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Search by seller, email, or type">
        <button class="btn btn-outline-secondary">Search</button>
      </div>
    </form>

    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Seller</th>
              <th>Shop</th>
              <th>Type</th>
              <th>Account Name</th>
              <th>Account Number</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            @forelse($methods as $m)
              @php
                $acc = (string)($m->account_number ?? '');
                $masked = strlen($acc) > 4 ? str_repeat('•', max(0, strlen($acc) - 4)) . substr($acc, -4) : $acc;
              @endphp
              <tr>
                <td>{{ $m->id }}</td>
                <td>{{ optional(optional($m->shop)->user)->name ?? '—' }}<br><span class="text-muted small">{{ optional(optional($m->shop)->user)->email }}</span></td>
                <td>{{ optional($m->shop)->name ?? '—' }}</td>
                <td>{{ optional($m->paymentType)->name ?? '—' }}</td>
                <td>{{ $m->account_name }}</td>
                <td><span class="text-monospace">{{ $masked }}</span></td>
                <td>{{ $m->created_at?->format('d M Y') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">No payment methods found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">{{ $methods->links() }}</div>
    </div>
  </div>
</div>
@endsection

