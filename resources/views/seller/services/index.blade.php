@extends('layouts.app')

@section('content')
<div class="content">

  {{-- Header --}}
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <h2 class="h3 mb-3 mb-md-0">Your Services</h2>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 w-100 w-sm-auto">
      <form action="{{ route('seller.services.index') }}" method="GET" class="d-flex flex-grow-1 flex-sm-grow-0">
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          placeholder="Search services..."
          class="form-control rounded-start"
        >
        <button class="btn btn-primary rounded-end ms-0">Search</button>
      </form>

      <a href="{{ route('seller.services.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Add New Service
      </a>
    </div>
  </div>

  {{-- Success Message --}}
  @if(session('success'))
    <div class="alert alert-success mb-4">
      {{ session('success') }}
    </div>
  @endif

  {{-- Products Grid --}}
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    @forelse($services as $service)
    <div class="col">
      <div class="card h-100 shadow-sm">
        {{-- Product Image --}}
        <div class="position-relative">
          @if($img = $service->media->first())
            <img
              src="{{ asset('storage/'.$img->url) }}"
              alt="{{ $service->name }}"
              class="card-img-top"
              style="height: 200px; object-fit: cover;"
            >
          @else
            <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
              <i class="fas fa-image fa-2x text-secondary"></i>
            </div>
          @endif
          {{-- Status Badge --}}
          @php
            $statusClasses = [
              'active'   => 'bg-success',
              'draft'    => 'bg-secondary',
              'pending'  => 'bg-warning text-dark',
              'archived' => 'bg-danger'
            ];
          @endphp
          <span class="position-absolute top-0 end-0 m-2 badge {{ $statusClasses[$service->status] ?? 'bg-secondary' }}">
            {{ ucfirst($service->status) }}
          </span>
        </div>

        {{-- Card Body --}}
        <div class="card-body">
          <h5 class="card-title text-truncate" title="{{ $service->name }}">
            {{ $service->name }}
          </h5>
          <p class="card-text text-muted small mb-2">
            {{ $service->category->name ?? 'Uncategorized' }}
          </p>
          
        </div>

        {{-- Card Footer --}}
        <div class="card-footer bg-transparent border-top-0">
          <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group">
              <a href="{{ route('seller.services.edit', $service->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i>
              </a>
              <a href="{{ route('products.show', $service) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eye"></i>
              </a>
            </div>
            <form
              action="{{ route('seller.services.destroy', $service->id) }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('Delete this service?');"
            >
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="col-12">
      <div class="text-center text-muted py-5">
        <i class="fas fa-box-open fa-3x mb-3"></i>
        <p class="h5">No services found.</p>
        <a href="{{ route('seller.services.create') }}" class="btn btn-primary mt-3">
          Add Your First Service
        </a>
      </div>
    </div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $services->withQueryString()->links() }}
  </div>

</div>
@endsection
