@extends('layouts.app')

@section('title', 'All Reviews')

@section('styles')
<style>
    :root {
        --primary-color: #4361ee;
        --success-color: #2ecc71;
        --danger-color: #e74c3c;
        --light-gray: #f8f9fa;
        --border-color: #e9ecef;
        --text-muted: #6c757d;
        --radius-sm: 0.25rem;
        --radius-md: 0.375rem;
        --shadow: rgba(0, 0, 0, 0.05) 0px 1px 3px;
    }
    
    .page-reviews {
        padding-bottom: 2rem;
    }
    
    .page-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 0;
    }
    
    .reviews-card {
        border: none;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    
    .avatar-circle {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background-color: var(--light-gray);
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        margin-right: 0.75rem;
        font-size: 0.875rem;
    }
    
    .review-comment {
        max-width: 320px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.4;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        color: #495057;
    }
    
    .review-row:hover .review-comment {
        -webkit-line-clamp: 5;
        max-height: 120px;
    }
    .date-badge .badge {
    font-weight: 500;
    font-size: 0.8125rem;
    background-color: rgba(248, 249, 250, 0.8) !important;
}

.date-badge .badge i {
    font-size: 0.75rem;
    opacity: 0.8;
}

@media (max-width: 767.98px) {
    .date-badge .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem !important;
    }
}
    .star-rating {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    .star-rating i {
        color: #ffc107;
        font-size: 0.875rem;
        margin-right: 1px;
    }
    
    table.review-table {
        margin-bottom: 0;
    }
    
    .review-table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        border-top: none;
        padding: 1rem;
    }
    
    .review-table td {
        padding: 1rem;
        vertical-align: middle;
        border-color: var(--border-color);
    }
    
    .review-row {
        transition: background-color 0.15s ease-in-out;
    }
    
    .review-row:hover {
        background-color: rgba(248, 249, 250, 0.7);
    }
    
    .shop-badge {
        background-color: var(--light-gray);
        border: 1px solid #dee2e6;
        color: #495057;
        font-weight: 500;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
    }
    
    .btn-action {
        border-radius: var(--radius-sm);
        font-size: 0.8125rem;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        box-shadow: none;
        transition: all 0.2s;
    }
    
    .btn-approve {
        background-color: var(--success-color);
        border-color: var(--success-color);
        color: white;
    }
    
    .btn-approve:hover {
        background-color: #27ae60;
        border-color: #27ae60;
        color: white;
    }
    
    .btn-delete {
        background-color: white;
        border-color: #dee2e6;
        color: var(--danger-color);
    }
    
    .btn-delete:hover {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
    }
    
    .btn-back {
        background-color: white;
        border-color: #dee2e6;
        color: #495057;
        font-size: 0.8125rem;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
    }
    
    .btn-back:hover {
        background-color: #f8f9fa;
        color: #212529;
    }
    
    .status-badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
    }
    
    .status-approved {
        background-color: rgba(46, 204, 113, 0.15);
        color: #27ae60;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    
    .empty-state {
        padding: 3rem 0;
        text-align: center;
    }
    
    .empty-icon {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .card-footer {
        background-color: white;
        border-color: var(--border-color);
        padding: 0.75rem 1rem;
    }
    
    .pagination-info {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }
    
    @media (max-width: 767.98px) {
        .review-comment {
            max-width: 200px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="content">
    <div class="page-reviews">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Reviews</h1>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" id="successAlert">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<script>
    // Auto-dismiss the alert after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alertElement = document.getElementById('successAlert');
        if (alertElement) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }, 5000); // 5000 milliseconds = 5 seconds
        }
    });
</script>
        @endif
        
        <div class="card reviews-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table review-table">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Shop</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr class="review-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-medium">{{ $review->user ? $review->user->name : '-' }}</div>
                                        </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class=>
                                            {{ $review->shop ? $review->shop->name : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <p class="review-comment mb-0">{{ $review->comment ?? '-' }}</p>
                                    </td>
                                    <td>
                                    <div class="d-flex align-items-center">
                                                    <div class="date-badge me-2">
                                                        <span class="badge bg-light text-dark border border-secondary-subtle rounded-1 p-2">
                                                            <i class="far fa-calendar-alt me-1 text-primary"></i>
                                                            {{ $review->created_at ? $review->created_at->format('Y-m-d') : '-' }}
                                                        </span>
                                                    </div>
                                                    @if($review->created_at)
                                                        <small class="text-muted d-none d-md-inline">
                                                            {{ $review->created_at->diffForHumans() }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if(!$review->approved)
                                                <form action="/admin/reviews/{{ $review->id }}/approve" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-action btn-approve">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                </form>
                                            @else
                                                <span class="status-badge status-approved">
                                                    <i class="fas fa-check-circle me-1"></i> Approved
                                                </span>
                                            @endif
                                            
                                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action btn-delete">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="far fa-comment-dots empty-icon"></i>
                                            <h5 class="fw-normal text-muted mb-1">No Reviews Found</h5>
                                            <p class="text-muted small mb-0">Reviews will appear here once customers start leaving feedback.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reviews->count() > 0)
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="pagination-info">
                            Showing {{ $reviews->firstItem() ?? 0 }} to {{ $reviews->lastItem() ?? 0 }} of {{ $reviews->total() }} reviews
                        </div>
                        <div>
                            {{ $reviews->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection