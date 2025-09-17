@extends('layouts.app')

@section('title', 'Dispute Management')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Dispute Management</h1>
                <div>
                    <a href="{{ route('admin.admin-disputes.statistics') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.admin-disputes.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            @php
                                $statusOptions = [
                                    'pending' => 'Pending',
                                    'under_review' => 'Under Review',
                                    'closed' => 'Closed',
                                    'mutually_resolved' => 'Mutually Resolved',
                                ];
                                if (config('disputes.enable_appeals')) {
                                    $statusOptions['appealed'] = 'Appealed';
                                    $statusOptions['final'] = 'Final';
                                }
                            @endphp
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
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

            <!-- Status Summary (clickable filters) -->
            @php
                $baseQuery = request()->except(['status','page']);
                $active = fn($s) => request('status') === $s || ($s === null && !request()->filled('status'));
                $cardClasses = function($isActive) {
                    return 'card text-center ' . ($isActive ? 'border-primary shadow-sm' : '');
                };
            @endphp
            <div class="row mb-4 g-3">
                <div class="col-md-2">
                    <a class="text-decoration-none d-block" href="{{ route('admin.admin-disputes.index', array_merge($baseQuery, ['status' => 'pending'])) }}">
                        <div class="{{ $cardClasses($active('pending')) }}">
                            <div class="card-body">
                                <h5 class="card-title text-primary">{{ $statusCounts['pending'] ?? 0 }}</h5>
                                <p class="card-text small mb-0">Pending</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a class="text-decoration-none d-block" href="{{ route('admin.admin-disputes.index', array_merge($baseQuery, ['status' => 'under_review'])) }}">
                        <div class="{{ $cardClasses($active('under_review')) }}">
                            <div class="card-body">
                                <h5 class="card-title text-info">{{ $statusCounts['under_review'] ?? 0 }}</h5>
                                <p class="card-text small mb-0">Under Review</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a class="text-decoration-none d-block" href="{{ route('admin.admin-disputes.index', array_merge($baseQuery, ['status' => 'closed'])) }}">
                        <div class="{{ $cardClasses($active('closed')) }}">
                            <div class="card-body">
                                <h5 class="card-title text-secondary">{{ $statusCounts['closed'] ?? 0 }}</h5>
                                <p class="card-text small mb-0">Closed</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a class="text-decoration-none d-block" href="{{ route('admin.admin-disputes.index', array_merge($baseQuery, ['status' => 'mutually_resolved'])) }}">
                        <div class="{{ $cardClasses($active('mutually_resolved')) }}">
                            <div class="card-body">
                                <h5 class="card-title text-success">{{ $statusCounts['mutually_resolved'] ?? 0 }}</h5>
                                <p class="card-text small mb-0">Mutually Resolved</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a class="text-decoration-none d-block" href="{{ route('admin.admin-disputes.index', $baseQuery) }}">
                        <div class="{{ $cardClasses($active(null)) }}">
                            <div class="card-body">
                                <h5 class="card-title text-dark">{{ array_sum($statusCounts) }}</h5>
                                <p class="card-text small mb-0">Total</p>
                            </div>
                        </div>
                    </a>
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
                                                    <a href="{{ route('admin.admin-disputes.show', $dispute->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    
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
