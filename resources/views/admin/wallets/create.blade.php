{{-- resources/views/admin/wallets/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Top Up Seller Wallet')

@section('content')
<div class="content">
  <div class="card shadow-sm">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Top Up Seller Wallet</h2>
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-sm btn-outline-secondary">Back to Wallets</a>
      </div>

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.wallets.store') }}" class="row g-3" autocomplete="off">
        @csrf

        <div class="col-12 col-md-6">
          <label class="form-label">Seller (ID or Email)</label>
          <input type="text" name="seller" value="{{ old('seller', $prefill['user_id'] ?? $prefill['email'] ?? $prefill['user'] ?? '') }}" class="form-control" placeholder="e.g. 123 or seller@example.com" required>
          <div class="form-text">Only seller accounts are allowed.</div>
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label">Amount (USD)</label>
          <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $prefill['amount'] ?? '') }}" class="form-control" required>
        </div>

        <div class="col-12">
          <label class="form-label">Description (optional)</label>
          <input type="text" name="description" value="{{ old('description', $prefill['description'] ?? 'Admin top-up') }}" class="form-control" maxlength="1000">
        </div>

        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Top Up Wallet</button>
          <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
  
  <div class="small text-muted mt-3">
    Tip: You can prefill the form via query string, e.g. <code>?user_id=123&amount=50</code>.
  </div>
</div>
@endsection

