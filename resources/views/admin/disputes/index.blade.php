@extends('layouts.admin')

@section('title', 'Dispute Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Dispute Management</h1>
                <div>
                    <a href="{{ route('admin.disputes.statistics') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.disputes.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="appealed" {{ request('status') == 'appealed' ? 'selected' : '' }}>Appealed</option>
                                <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="customs_fees" {{ request('type') == 'customs_fees' ? 'selected' : '' }}>Customs Fees</option>
                                <option value="item_misrepresentation" {{ request('type') == 'item_misrepresentation' ? 'selected' : '' }}>Item Misrepresentation</option>
                                <option value="shipping_issues" {{ request('type') == 'shipping_issues' ? 'selected' : '' }}>Shipping Issues</option>
                                <option value="quality_issues" {{ request('type') == 'quality_issues' ? 'selected' : '' }}>Quality Issues</option>
                                <option value="payment_issues" {{ request('type') == 'payment_issues' ? 'selected' : '' }}>Payment Issues</option>
                                <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="">Default</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status Summary -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-primary">{{ $statusCounts['pending'] ?? 0 }}</h5>
                            <p class="card-text small">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-info">{{ $statusCounts['under_review'] ?? 0 }}</h5>
                            <p class="card-text small">Under Review</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-success">{{ $statusCounts['resolved'] ?? 0 }}</h5>
                            <p class="card-text small">Resolved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-warning">{{ $statusCounts['appealed'] ?? 0 }}</h5>
                            <p class="card-text small">Appealed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-secondary">{{ $statusCounts['final'] ?? 0 }}</h5>
                            <p class="card-text small">Final</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-dark">{{ array_sum($statusCounts) }}</h5>
                            <p class="card-text small">Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disputes Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Disputes</h5>
                </div>
                <div class="card-body">
                    @if($disputes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Order</th>
                                        <th>Type</th>
                                        <th>Buyer</th>
                                        <th>Seller</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($disputes as $dispute)
                                        <tr>
                                            <td>
                                                <strong>#{{ $dispute->id }}</strong>
                                                @if($dispute->appeal)
                                                    <span class="badge bg-warning ms-1">Appealed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('orders.show', $dispute->order->id) }}" class="text-decoration-none">
                                                    #{{ $dispute->order->order_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $dispute->getTypeLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $dispute->buyer->name }}</td>
                                            <td>{{ $dispute->seller->name }}</td>
                                            <td>
                                                <span class="badge {{ $dispute->getStatusBadgeClass() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $dispute->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.disputes.show', $dispute->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($dispute->isPending() || $dispute->isUnderReview())
                                                        <a href="{{ route('admin.disputes.resolve.create', $dispute->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    @endif
                                                    @if($dispute->isAppealed())
                                                        <a href="{{ route('admin.disputes.finalize.store', $dispute->id) }}" 
                                                           class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-gavel"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $disputes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No disputes found</h4>
                            <p class="text-muted">There are no disputes matching your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterForm = document.querySelector('form');
    const filterInputs = filterForm.querySelectorAll('select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});
</script>
@endpush
@endsection
