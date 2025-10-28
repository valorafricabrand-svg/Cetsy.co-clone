@extends('layouts.app')
@section('title','Inventory Report')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Inventory Report</h2>
    <a href="{{ route('seller.analytics.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-line me-1"></i> Analytics</a>
  </div>

  @if(!$shopId)
    <div class="alert alert-warning">You don’t have a shop set up yet. Create your shop to see inventory insights.</div>
  @else

  <div class="alert alert-info">This report shows stock issues for your listings only.</div>

  {{-- Negative product stock --}}
  <div class="card mb-4">
    <div class="card-header bg-danger text-white">Negative product stock (critical)</div>
    <div class="card-body p-0">
      @if($productsNegative->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($productsNegative as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td class="text-danger fw-semibold">{{ $p->stock }}</td>
              <td><a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Negative variant stock --}}
  <div class="card mb-4">
    <div class="card-header bg-danger text-white">Negative variant stock (critical)</div>
    <div class="card-body p-0">
      @if($variantsNegative->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead class="table-light"><tr><th>Variant ID</th><th>Product</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($variantsNegative as $v)
            <tr>
              <td>#{{ $v->id }}</td>
              <td>{{ $v->product->name ?? '—' }}</td>
              <td class="text-danger fw-semibold">{{ $v->stock }}</td>
              <td>
                @if($v->product)
                  <a href="{{ route('products.variations', $v->product) }}" class="btn btn-sm btn-outline-primary">Manage Variations</a>
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

  {{-- Non-physical with stock --}}
  <div class="card mb-4">
    <div class="card-header bg-warning">Non-physical items with stock set (warning)</div>
    <div class="card-body p-0">
      @if($nonPhysicalWithStock->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Type</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($nonPhysicalWithStock as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td>{{ ucfirst($p->type ?? '—') }}</td>
              <td>{{ $p->stock }}</td>
              <td><a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Physical without tracked stock --}}
  <div class="card mb-4">
    <div class="card-header bg-warning">Physical listings without tracked stock (warning)</div>
    <div class="card-body p-0">
      @if($physicalUntracked->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($physicalUntracked as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td>—</td>
              <td><a href="{{ route('products.settings', $p) }}" class="btn btn-sm btn-outline-primary">Settings</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Active but out of stock --}}
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white">Active listings but out of stock (attention)</div>
    <div class="card-body p-0">
      @if($activeButOut->isEmpty())
        <div class="p-3 text-muted">No issues found.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($activeButOut as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td><span class="badge bg-success">Active</span></td>
              <td>
                <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-primary">Open</a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  @endif
</div>
@endsection

