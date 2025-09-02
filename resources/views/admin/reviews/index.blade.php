@extends('layouts.app')

@section('title', 'All Reviews')

@section('styles')
<style>
    :root {
        --primary: #3b82f6;
        --primary-light: #eff6ff;
        --success: #10b981;
        --success-light: #ecfdf5;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --warning: #f59e0b;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --border-radius: 8px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .page-reviews {
        margin: 2rem auto;
        padding: 2rem;
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-800);
        margin: 0;
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        border-left: 4px solid transparent;
    }

    .alert-success {
        background-color: var(--success-light);
        color: #065f46;
        border-left-color: var(--success);
    }

    .alert-danger {
        background-color: var(--danger-light);
        color: #991b1b;
        border-left-color: var(--danger);
    }

    .alert-dismissible {
        padding-right: 3rem;
    }

    .btn-close {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        opacity: 0.6;
        transition: var(--transition);
        color: inherit;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .table-container {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .table thead {
        background: var(--gray-50);
    }

    .table thead th {
        padding: 1rem 1.25rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--gray-700);
        text-align: left;
        border-bottom: 1px solid var(--gray-200);
    }

    .table tbody td {
        padding: 1.25rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: top;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background: var(--gray-50);
    }

    .customer-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .customer-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .customer-name {
        font-weight: 500;
        color: var(--gray-800);
    }

    .shop-badge {
        background: var(--gray-100);
        color: var(--gray-700);
        padding: 0.375rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .rating-column {
        min-width: 90px;
    }

    .rating-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.5rem;
        height: 2.25rem;
        border-radius: 1.5rem;
        font-weight: 600;
        font-size: 0.9375rem;
    }

    .rating-1 { background-color: #fee2e2; color: #b91c1c; }
    .rating-2 { background-color: #fed7aa; color: #c2410c; }
    .rating-3 { background-color: #fde68a; color: #a16207; }
    .rating-4 { background-color: #bbf7d0; color: #15803d; }
    .rating-5 { background-color: #86efac; color: #166534; }

    .no-rating {
        color: var(--gray-500);
        font-style: italic;
        font-size: 0.875rem;
    }

    .review-column {
        min-width: 250px;
    }

    .review-comment {
        color: var(--gray-700);
        font-size: 0.9375rem;
        line-height: 1.5;
        margin: 0;
        padding: 0.75rem;
        background: var(--gray-50);
        border-radius: var(--border-radius);
        border-left: 3px solid var(--primary);
    }

    .no-comment {
        color: var(--gray-500);
        font-style: italic;
    }

    .date-wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .date-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        background: var(--gray-100);
        color: var(--gray-700);
        padding: 0.375rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .date-relative {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.8125rem;
        padding: 0.5rem 1rem;
        line-height: 1;
        white-space: nowrap;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .btn-danger {
        background: white;
        color: var(--danger);
        border: 1px solid var(--gray-200);
    }

    .btn-danger:hover {
        background: var(--danger);
        color: white;
        border-color: var(--danger);
    }

    .status-approved {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        background: var(--success-light);
        color: var(--success);
        padding: 0.5rem 0.875rem;
        border-radius: var(--border-radius);
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-icon {
        font-size: 3rem;
        color: var(--gray-300);
        margin-bottom: 1rem;
    }

    .empty-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .empty-text {
        color: var(--gray-500);
        font-size: 0.875rem;
        max-width: 400px;
        margin: 0 auto;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem;
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
    }

    .pagination-info {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .pagination .page-item {
        display: inline-block;
    }

    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 2.25rem;
        height: 2.25rem;
        padding: 0 0.5rem;
        border-radius: var(--border-radius);
        background: white;
        color: var(--gray-700);
        text-decoration: none;
        border: 1px solid var(--gray-300);
        font-size: 0.875rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .pagination .page-link:hover {
        background: var(--gray-100);
        border-color: var(--gray-400);
    }

    .pagination .active .page-link {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .pagination .disabled .page-link {
        background: var(--gray-100);
        color: var(--gray-400);
        cursor: not-allowed;
    }

    /* Responsive design */
    @media (max-width: 1024px) {
        .page-reviews {
            margin: 1rem;
            padding: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .table thead {
            display: none;
        }

        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 0;
        }

        .table tbody td {
            display: block;
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
            text-align: right;
        }

        .table tbody td:last-child {
            border-bottom: none;
        }

        .table tbody td::before {
            content: attr(data-label);
            float: left;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.8125rem;
        }

        .customer-info {
            justify-content: flex-end;
        }

        .action-buttons {
            justify-content: center;
        }

        .review-comment {
            max-width: none;
        }

        .date-relative {
            display: block;
        }

        .card-footer {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 640px) {
        .page-reviews {
            padding: 1rem;
            margin: 0.5rem;
        }

        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="content">
    <div class="page-reviews">
        <div class="page-header">
            <h1 class="page-title">All Reviews</h1>
        </div>

        <form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @php($s = request('status'))
                    <option value="">All</option>
                    <option value="pending" {{ $s==='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $s==='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $s==='rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="form-label">Rating</label>
                <select name="rating" class="form-select">
                    @php($r = (int) request('rating'))
                    <option value="">Any</option>
                    @for($i=5;$i>=1;$i--)
                      <option value="{{ $i }}" {{ $r===$i ? 'selected' : '' }}>{{ $i }} ★</option>
                    @endfor
                </select>
            </div>
            <div class="flex-grow-1">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Comment, shop, or user">
            </div>
            <div>
                <button class="btn btn-outline-secondary">Filter</button>
            </div>
        </form>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" id="successAlert">
                <i class="fas fa-check-circle" style="margin-right: 0.75rem;"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" onclick="closeAlert('successAlert')">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" id="errorAlert">
                <i class="fas fa-exclamation-circle" style="margin-right: 0.75rem;"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" onclick="closeAlert('errorAlert')">&times;</button>
            </div>
        @endif

        <form method="POST" id="bulkForm">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="small text-muted">Select rows to bulk approve/delete</div>
              <div class="d-flex gap-2">
                <button type="submit" formaction="{{ route('admin.reviews.bulk-approve') }}" class="btn btn-success btn-sm" onclick="return confirm('Approve selected reviews?')">Bulk Approve</button>
                <button type="submit" formaction="{{ route('admin.reviews.bulk-delete') }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected reviews?')">Bulk Delete</button>
              </div>
            </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:28px"><input type="checkbox" id="checkAll"></th>
                        <th>Customer</th>
                        <th>Shop</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td data-label="Select"><input type="checkbox" name="ids[]" value="{{ $review->id }}" class="row-check"></td>
                            <td data-label="Customer">
                                <div class="customer-info">
                                    <div class="customer-avatar">
                                        {{ substr($review->user ? $review->user->name : 'U', 0, 1) }}
                                    </div>
                                    <div class="customer-name">
                                        {{ $review->user ? $review->user->name : 'Unknown User' }}
                                    </div>
                                </div>
                            </td>
                            <td data-label="Shop">
                                <span class="shop-badge">
                                    {{ $review->shop ? $review->shop->name : 'Unknown Shop' }}
                                </span>
                            </td>
                            <td data-label="Rating" class="rating-column">
                                @if($review->rating)
                                    <div class="rating-badge rating-{{ $review->rating }}">
                                        {{ $review->rating }}/5
                                    </div>
                                @else
                                    <span class="no-rating">No rating</span>
                                @endif
                            </td>
                            <td data-label="Review" class="review-column">
                                <div class="review-comment">
                                    {{ $review->comment ?? '<span class="no-comment">No comment provided</span>' }}
                                </div>
                            </td>
                            <td data-label="Status">
                                @if($review->approved)
                                    <span class="badge bg-success">Approved</span>
                                @elseif($review->rejected_at)
                                    <span class="badge bg-danger">Rejected</span>
                                    @if($review->rejection_reason)
                                        <div class="text-muted small">{{ Str::limit($review->rejection_reason, 80) }}</div>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td data-label="Date">
                                <div class="date-wrapper">
                                    <div class="date-badge">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ $review->created_at ? $review->created_at->format('M d, Y') : '-' }}
                                    </div>
                                    @if($review->created_at)
                                        <span class="date-relative">
                                            {{ $review->created_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    @if(!$review->approved && !$review->rejected_at)
                                        <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check" style="margin-right: 0.375rem;"></i>
                                                Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $review->id }}">
                                            <i class="fas fa-ban" style="margin-right: 0.375rem;"></i>
                                            Reject
                                        </button>
                                    @elseif($review->approved)
                                        <span class="status-approved">
                                            <i class="fas fa-check-circle"></i>
                                            Approved
                                        </span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif

                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash" style="margin-right: 0.375rem;"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        {{-- Reject Modal --}}
                        <div class="modal fade" id="rejectModal-{{ $review->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <form method="POST" action="{{ route('admin.reviews.reject', $review->id) }}" class="modal-content">
                              @csrf
                              @method('PATCH')
                              <div class="modal-header">
                                <h5 class="modal-title">Reject Review</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <div class="modal-body">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this review is rejected"></textarea>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-warning">Confirm Reject</button>
                              </div>
                            </form>
                          </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="far fa-comment-dots empty-icon"></i>
                                    <div class="empty-title">No Reviews Found</div>
                                    <div class="empty-text">Reviews will appear here once customers start leaving feedback.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($reviews->count() > 0)
                <div class="card-footer">
                    <div class="pagination-info">
                        Showing {{ $reviews->firstItem() ?? 0 }} to {{ $reviews->lastItem() ?? 0 }} of {{ $reviews->total() }} reviews
                    </div>
                    <div>
                        {{ $reviews->links() }}
                    </div>
                </div>
            @endif
        </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            closeAlert(alert.id);
        }, 5000);
    });
});

function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            alert.remove();
        }, 200);
    }
}

// Bulk select
document.getElementById('checkAll')?.addEventListener('change', function(){
  document.querySelectorAll('.row-check').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
