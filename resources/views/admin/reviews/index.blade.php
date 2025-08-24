@extends('layouts.app')

@section('title', 'All Reviews')

@section('styles')
<style>
    :root {
        --primary: #3b82f6;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-400: #9ca3af;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --border-radius: 8px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Arial', sans-serif;
        background-color: #f3f4f6;
        overflow-x: hidden;
    }

    .page-reviews {
        max-width: 1200px;
        margin: 0 auto;
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
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
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
        font-size: 0.875rem;
        line-height: 1;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .alert::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
    }

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .alert-success::before {
        background: var(--success);
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .alert-danger::before {
        background: var(--danger);
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
        opacity: 0.5;
        transition: var(--transition);
    }

    .btn-close:hover {
        opacity: 1;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .table thead th {
        background: var(--gray-50);
        padding: 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
        text-align: left;
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: top;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background: var(--gray-50);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn-danger {
        background: white;
        color: var(--danger);
        border: 1px solid var(--gray-200);
        font-size: 0.8125rem;
    }

    .btn-danger:hover {
        background: var(--danger);
        color: white;
        border-color: var(--danger);
        transform: translateY(-1px);
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
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .empty-text {
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .card-footer {
        background: var(--gray-50);
        padding: 1rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: between;
        align-items: center;
        gap: 1rem;
    }

    .pagination-info {
        font-size: 0.8125rem;
        color: var(--gray-600);
    }

    /* Pagination Styles */
    .pagination {
        margin: 0;
        padding: 0;
        display: flex;
        gap: 0.25rem;
    }

    .pagination .page-item {
        list-style: none;
    }

    .pagination .page-link {
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        background: var(--gray-100);
        color: var(--gray-700);
        text-decoration: none;
        cursor: pointer;
    }

    .pagination .page-link:hover {
        background: var(--gray-200);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .table thead th,
        .table tbody td {
            padding: 0.75rem 0.5rem;
        }

        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .btn-success,
        .btn-danger {
            width: 100%;
            justify-content: center;
        }

        .review-comment {
            max-width: 200px;
        }

        .customer-avatar {
            width: 2rem;
            height: 2rem;
        }

        .date-relative {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .page-reviews {
            padding: 1rem 0.5rem;
        }

        .table {
            font-size: 0.8125rem;
        }

        .shop-badge,
        .date-badge,
        .status-approved {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
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
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" id="successAlert">
                <div class="alert-progress"></div>
                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" onclick="closeAlert('successAlert')">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" id="errorAlert">
                <div class="alert-progress"></div>
                <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" onclick="closeAlert('errorAlert')">&times;</button>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Shop</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            {{ substr($review->user ? $review->user->name : 'U', 0, 1) }}
                                        </div>
                                        <div class="customer-name">
                                            {{ $review->user ? $review->user->name : 'Unknown User' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="shop-badge">
                                        {{ $review->shop ? $review->shop->name : 'Unknown Shop' }}
                                    </span>
                                </td>
                                <td>
                                    @if($review->rating)
                                        <div class="star-rating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star star {{ $i <= $review->rating ? '' : 'empty' }}"></i>
                                            @endfor
                                            <span class="rating-text">({{ $review->rating }}/5)</span>
                                        </div>
                                    @endif
                                    <div class="review-comment">
                                        {{ $review->comment ?? 'No comment provided' }}
                                    </div>
                                </td>
                                <td>
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
                                <td>
                                    <div class="action-buttons">
                                        @if(!$review->approved)
                                            <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check" style="margin-right: 0.375rem;"></i>
                                                    Approve
                                                </button>
                                            </form>
                                        @else
                                            <span class="status-approved">
                                                <i class="fas fa-check-circle"></i>
                                                Approved
                                            </span>
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
                        @empty
                            <tr>
                                <td colspan="5">
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
            </div>

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
</script>
@endsection
