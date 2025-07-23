{{-- resources/views/admin/wallets/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Wallet Txn #'.$wallet->id)

@section('content')
<div class="content">
  <div class="card shadow-sm">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Wallet Transaction #{{ $wallet->id }}</h2>
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-sm btn-outline-secondary">
          ← Back to list
        </a>
      </div>

      {{-- Flash --}}
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle mb-0">
          <tbody>
            <tr>
              <th style="width:220px">ID</th>
              <td>#{{ $wallet->id }}</td>
            </tr>
            <tr>
              <th>User</th>
              <td>
                @if($wallet->user)
                  <a href="{{ route('admin.users.show', $wallet->user_id) ?? '#' }}">
                    {{ $wallet->user->name }} ({{ $wallet->user->email }})
                  </a>
                @else
                  User {{ $wallet->user_id }}
                @endif
              </td>
            </tr>
            <tr>
              <th>Credit</th>
              <td class="text-success fw-semibold">{{ number_format($wallet->credit, 2) }}</td>
            </tr>
            <tr>
              <th>Debit</th>
              <td class="text-danger fw-semibold">{{ number_format($wallet->debit, 2) }}</td>
            </tr>
            <tr>
              <th>Balance (row)</th>
              <td class="fw-bold">{{ number_format($wallet->balance, 2) }}</td>
            </tr>
            <tr>
              <th>Type</th>
              <td>{{ $wallet->type ?? '—' }}</td>
            </tr>
            <tr>
              <th>Reference</th>
              <td><code>{{ $wallet->reference }}</code></td>
            </tr>
            <tr>
              <th>Description</th>
              <td>{{ $wallet->description ?? '—' }}</td>
            </tr>
            <tr>
              <th>Created At</th>
              <td>{{ $wallet->created_at?->format('M d, Y H:i:s') }}</td>
            </tr>
            <tr>
              <th>Updated At</th>
              <td>{{ $wallet->updated_at?->format('M d, Y H:i:s') }}</td>
            </tr>
          </tbody>
        </table>
      </div>

 

    </div>
  </div>
</div>
@endsection
