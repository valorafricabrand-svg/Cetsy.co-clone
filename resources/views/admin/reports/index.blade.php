{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4">{{ __('Reports') }}</h2>
@endsection

@section('content')
  <div class="content">
    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Inventory Health</h5>
            <p class="text-muted flex-grow-1">Find negative stock, untracked physical listings, and other inventory inconsistencies.</p>
            <a href="{{ route('admin.reports.inventory') }}" class="btn btn-primary mt-auto">Open Report</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Sales & Orders</h5>
            <p class="text-muted flex-grow-1">Summary of orders and revenue (placeholder).</p>
            <button class="btn btn-outline-secondary mt-auto" disabled>Coming soon</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
