@extends('layouts.app')
@section('title','Inventory Report')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Inventory Health Report</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
  </div>

  <div class="alert alert-info">This report highlights negative stock, non-physical items with stock set, physical listings without tracked stock, and active listings that are effectively out of stock.</div>

  {{-- Critical: Negative Product Stock --}}
  <div class="card mb-4">
    <div class="card-header bg-danger text-white">Negative product stock (critical)</div>
    <div class="card-body p-0">
      @if($productsNegative->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Name</th><th>Shop</th><th>Type</th><th>Stock</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($productsNegative as $p)
              <tr>
                <td>#{{ $p->id }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->shop->name ?? '—' }}</td>
                <td>{{ ucfirst($p->type ?? 'item') }}</td>
                <td class="text-danger fw-semibold">{{ $p->stock }}</td>
                <td>
                  @if(Route::has('admin.products.show'))
                    <a href="{{ route('admin.products.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Critical: Negative Variant Stock --}}
  <div class="card mb-4">
    <div class="card-header bg-danger text-white">Negative variant stock (critical)</div>
    <div class="card-body p-0">
      @if($variantsNegative->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Variant ID</th><th>Product</th><th>Shop</th><th>Stock</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($variantsNegative as $v)
              <tr>
                <td>#{{ $v->id }}</td>
                <td>{{ $v->product->name ?? '—' }}</td>
                <td>{{ optional($v->product?->shop)->name ?? '—' }}</td>
                <td class="text-danger fw-semibold">{{ $v->stock }}</td>
                <td>
                  @if(Route::has('admin.products.show') && $v->product)
                    <a href="{{ route('admin.products.show', $v->product->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Warning: Non-physical items with stock set --}}
  <div class="card mb-4">
    <div class="card-header bg-warning">Non-physical items with stock set (warning)</div>
    <div class="card-body p-0">
      @if($nonPhysicalWithStock->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Name</th><th>Shop</th><th>Type</th><th>Stock</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($nonPhysicalWithStock as $p)
              <tr>
                <td>#{{ $p->id }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->shop->name ?? '—' }}</td>
                <td>{{ ucfirst($p->type ?? '—') }}</td>
                <td>{{ $p->stock }}</td>
                <td>
                  @if(Route::has('admin.products.show'))
                    <a href="{{ route('admin.products.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Warning: Physical with no tracked stock (no variants & null stock) --}}
  <div class="card mb-4">
    <div class="card-header bg-warning">Physical listings without tracked stock (warning)</div>
    <div class="card-body p-0">
      @if($physicalUntracked->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Name</th><th>Shop</th><th>Stock</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($physicalUntracked as $p)
              <tr>
                <td>#{{ $p->id }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->shop->name ?? '—' }}</td>
                <td>—</td>
                <td>
                  @if(Route::has('admin.products.show'))
                    <a href="{{ route('admin.products.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Active but effectively out of stock --}}
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white">Active listings but out of stock (attention)</div>
    <div class="card-body p-0">
      @if($activeButOut->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Name</th><th>Shop</th><th>Type</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($activeButOut as $p)
              <tr>
                <td>#{{ $p->id }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->shop->name ?? '—' }}</td>
                <td>{{ ucfirst($p->type ?? '—') }}</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                  @if(Route::has('admin.products.show'))
                    <a href="{{ route('admin.products.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

</div>
@endsection

