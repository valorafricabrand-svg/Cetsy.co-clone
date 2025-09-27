@extends('layouts.app')

@section('title', 'Product Activities')

@section('content')
<div class="content">
  <div class="row g-3">
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h5 fw-semibold mb-0">Product Activities</h2>
      </div>

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <form class="row g-2" method="GET">
            <div class="col-md-3">
              <label class="form-label small">Product</label>
              <select name="product_id" class="form-select">
                <option value="">All</option>
                @foreach($productOptions as $p)
                  <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->id }} – {{ Str::limit($p->name, 40) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small">User</label>
              <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach($userOptions as $u)
                  <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->id }} – {{ $u->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Section</label>
              <select name="section" class="form-select">
                <option value="">All</option>
                @foreach(['about','details','pricing','variations','shipping','settings','media','full_update'] as $sec)
                  <option value="{{ $sec }}" {{ request('section') === $sec ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $sec)) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button class="btn btn-primary w-100">Filter</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>When</th>
                <th>User</th>
                <th>Product</th>
                <th>Section</th>
                <th>Description</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($activities as $a)
                @php $props = (array) ($a->properties ?? []); @endphp
                <tr>
                  <td>{{ $a->id }}</td>
                  <td class="text-nowrap">{{ $a->created_at->format('Y-m-d H:i') }}</td>
                  <td>{{ optional($a->user)->name ?? ('User #'.$a->user_id) }}</td>
                  <td class="text-nowrap">#{{ $a->related_id }} <small class="text-muted">{{ $a->related_type }}</small></td>
                  <td>{{ $props['section'] ?? '-' }}</td>
                  <td>{{ Str::limit($a->description, 60) }}</td>
                  <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.product-activities.show', $a) }}">View</a></td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No activities found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer bg-white">
          {{ $activities->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
