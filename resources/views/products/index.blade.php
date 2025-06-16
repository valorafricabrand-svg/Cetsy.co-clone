@extends('layouts.app')

@section('content')
<div class="content">

  {{-- Header --}}
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <h2 class="h3 mb-3 mb-md-0">Your Products</h2>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 w-100 w-sm-auto">
      <form action="{{ route('products.index') }}" method="GET" class="d-flex flex-grow-1 flex-sm-grow-0">
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          placeholder="Search products..."
          class="form-control rounded-start"
        >
        <button class="btn btn-primary rounded-end ms-0">Search</button>
      </form>

      <a href="{{ route('products.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Add New Product
      </a>
    </div>
  </div>

  {{-- Success Message --}}
  @if(session('success'))
    <div class="alert alert-success mb-4">
      {{ session('success') }}
    </div>
  @endif

  {{-- Products Table Card --}}
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th class="text-end">Price</th>
            <th class="text-end">Stock</th>
            <th class="text-center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
          <tr>
            <td class="align-middle">
              @if($product->media->first())
                <img
                  src="{{ asset('storage/' . $product->media->first()->url) }}"
                  alt="{{ $product->name }}"
                  class="rounded me-2"
                  style="width:48px;height:48px;object-fit:cover;"
                >
              @else
                <div class="bg-secondary bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                  &mdash;
                </div>
              @endif
            </td>
            <td class="align-middle">{{ $product->name }}</td>
            <td class="align-middle">{{ $product->category->name ?? '—' }}</td>
            <td class="align-middle text-end">KES {{ number_format($product->price,2) }}</td>
            <td class="align-middle text-end">{{ $product->stock }}</td>
            <td class="align-middle text-center">
              @php
                $statusClasses = [
                  'active'   => 'badge bg-success',
                  'draft'    => 'badge bg-secondary',
                  'pending'  => 'badge bg-warning text-dark',
                  'archived' => 'badge bg-danger'
                ];
              @endphp
              <span class="{{ $statusClasses[$product->status] ?? 'badge bg-secondary' }}">
                {{ ucfirst($product->status) }}
              </span>
            </td>
            <td class="align-middle text-end">
              <a href="{{ route('products.edit', $product) }}" class="text-primary me-2">
                <i class="fas fa-edit"></i>
              </a>
              <form
                action="{{ route('products.destroy', $product) }}"
                method="POST"
                class="d-inline"
                onsubmit="return confirm('Delete this product?');"
              >
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link p-0 m-0 text-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-3">
              No products found.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $products->withQueryString()->links() }}
  </div>

</div>
@endsection
