@extends('layouts.app')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">One‑Off Deals</h1>
    <a href="{{ route('seller.deals.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i>New Deal
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Deal Name</th>
              <th>Discount</th>
              <th>Applies To</th>
              <th>Status</th>
              <th>Period</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($deals as $deal)
              <tr>
                <td>
                  <strong>{{ $deal->name }}</strong>
                </td>
                <td>
                  <span class="badge bg-success fs-6">{{ $deal->discount_percent }}% OFF</span>
                </td>
                <td>
                  @if($deal->applies_to_all)
                    <span class="text-muted">All Products</span>
                  @else
                    <span class="text-muted">{{ $deal->products->count() }} Selected</span>
                  @endif
                </td>
                <td>
                  @if($deal->isActive())
                    <span class="badge bg-success">Active</span>
                  @elseif($deal->starts_at->isFuture())
                    <span class="badge bg-warning">Scheduled</span>
                  @else
                    <span class="badge bg-secondary">Expired</span>
                  @endif
                </td>
                <td>
                  <small class="text-muted">
                    {{ $deal->starts_at->format('M d, Y H:i') }}<br>
                    → {{ $deal->ends_at->format('M d, Y H:i') }}
                  </small>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="{{ route('seller.deals.edit', $deal) }}" 
                       class="btn btn-outline-primary btn-sm" 
                       title="Edit Deal">
                      <i class="fas fa-edit"></i>
                    </a>
                    
                    @if($deal->isActive())
                      <form action="{{ route('seller.deals.stop', $deal) }}" 
                            method="POST" 
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to stop this deal?')">
                        @csrf
                        <button type="submit" 
                                class="btn btn-outline-warning btn-sm" 
                                title="Stop Deal">
                          <i class="fas fa-stop"></i>
                        </button>
                      </form>
                    @endif
                    
                    <form action="{{ route('seller.deals.destroy', $deal) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this deal? This action cannot be undone.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" 
                              class="btn btn-outline-danger btn-sm" 
                              title="Delete Deal">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4">
                  <div class="text-muted">
                    <i class="fas fa-percent fa-3x mb-3"></i>
                    <p class="mb-0">No deals created yet</p>
                    <small>Create your first deal to start offering discounts to customers</small>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($deals->hasPages())
    <div class="mt-4">
      {{ $deals->links() }}
    </div>
  @endif
</div>
@endsection
