@extends('layouts.app')

@section('title', 'All Reviews')

@section('styles')
<style>
    :root {
        --primary: #3b82f6;
        --primary-dark: #1e40af;
        --primary-light: #dbeafe;
        --success: #10b981;
        --success-light: #d1fae5;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --surface: #ffffff;
        --surface-variant: #f8fafc;
        --surface-secondary: #f1f5f9;
        --border: #e2e8f0;
        --border-light: #f1f5f9;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #64748b;
        --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --radius-xl: 16px;
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        box-sizing: border-box;
    }

    .reviews-container {
        padding: 1.5rem;
        max-width: 100%;
        margin: 0 auto;
        background: var(--surface-variant);
        min-height: 100vh;
    }

    .page-header {
        background: var(--surface);
        border-radius: var(--radius-xl);
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), #8b5cf6, #06b6d4);
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-title i {
        color: var(--primary);
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .filters-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 2fr auto;
        gap: 1rem;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--surface);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: var(--transition);
        outline: none;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        text-decoration: none;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
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
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        border-left: 4px solid transparent;
        transition: var(--transition);
    }

    .alert-success {
        background: var(--success-light);
        color: #065f46;
        border-left-color: var(--success);
    }

    .alert-danger {
        background: var(--danger-light);
        color: #991b1b;
        border-left-color: var(--danger);
    }

    .btn-close {
        position: absolute;
        right: 1rem;
        background: none;
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

    .main-card {
        background: var(--surface);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .bulk-actions {
        padding: 1rem 1.5rem;
        background: var(--surface-secondary);
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .bulk-actions-text {
        font-size: 0.875rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .bulk-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 0 0 var(--radius-xl) var(--radius-xl);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: var(--surface);
        table-layout: fixed;
        min-width: 1200px;
    }

    .table thead {
        background: var(--surface-secondary);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table thead th {
        padding: 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-secondary);
        text-align: left;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }

    /* Optimized column widths */
    .table th:nth-child(1) { width: 50px; }     /* Checkbox */
    .table th:nth-child(2) { width: 180px; }   /* Customer */
    .table th:nth-child(3) { width: 140px; }   /* Shop */
    .table th:nth-child(4) { width: 80px; }    /* Rating */
    .table th:nth-child(5) { width: 300px; }   /* Review */
    .table th:nth-child(6) { width: 120px; }   /* Status */
    .table th:nth-child(7) { width: 140px; }   /* Date */
    .table th:nth-child(8) { width: 200px; }   /* Actions */

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-light);
        vertical-align: top;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background: var(--surface-variant);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .customer-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .customer-avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .customer-name {
        font-weight: 500;
        color: var(--text-primary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .shop-badge {
        background: var(--primary-light);
        color: var(--primary-dark);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius-lg);
        font-size: 0.8125rem;
        font-weight: 500;
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rating-display {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 2rem;
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 0.875rem;
    }

    .rating-1 { background: #fee2e2; color: #b91c1c; }
    .rating-2 { background: #fed7aa; color: #c2410c; }
    .rating-3 { background: #fef3c7; color: #a16207; }
    .rating-4 { background: #bbf7d0; color: #15803d; }
    .rating-5 { background: #86efac; color: #166534; }

    .no-rating {
        color: var(--text-muted);
        font-style: italic;
        font-size: 0.8125rem;
    }

    .review-content {
        max-width: 100%;
        padding: 0.75rem;
        background: var(--surface-variant);
        border-radius: var(--radius-md);
        border-left: 3px solid var(--primary);
        font-size: 0.875rem;
        line-height: 1.4;
        color: var(--text-secondary);
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    .no-comment {
        color: var(--text-muted);
        font-style: italic;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 0.875rem;
        border-radius: var(--radius-lg);
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-approved {
        background: var(--success-light);
        color: var(--success);
    }

    .status-pending {
        background: var(--warning-light);
        color: #a16207;
    }

    .status-rejected {
        background: var(--danger-light);
        color: var(--danger);
    }

    .rejection-reason {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        font-style: italic;
    }

    .date-cell {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .date-primary {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    .date-relative {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .actions-cell {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
    }

    .btn-xs {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        border-radius: var(--radius-sm);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }

    .empty-icon {
        font-size: 3rem;
        color: var(--border);
        margin-bottom: 1rem;
        opacity: 0.6;
    }

    .empty-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .empty-text {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    .pagination-footer {
        padding: 1.5rem;
        background: var(--surface-secondary);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background: var(--surface-secondary);
        border-bottom: 1px solid var(--border);
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        padding: 1.5rem;
    }

    .modal-title {
        font-weight: 700;
        color: var(--text-primary);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.5rem;
        background: var(--surface-variant);
        border-top: 1px solid var(--border);
        border-radius: 0 0 var(--radius-xl) var(--radius-xl);
        gap: 0.75rem;
    }

    /* Responsive Design */
    @media (max-width: 1400px) {
        .table { min-width: 1000px; }
        .table th:nth-child(5) { width: 250px; }
        .table th:nth-child(8) { width: 160px; }
    }

    @media (max-width: 1200px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .filters-grid > div:nth-child(3) {
            grid-column: 1 / -1;
        }
        
        .filters-grid > div:nth-child(4) {
            grid-column: 1 / -1;
            justify-self: center;
        }
    }

    @media (max-width: 768px) {
        .reviews-container {
            padding: 1rem;
        }
        
        .page-header {
            padding: 1rem 1.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .filters-grid {
            grid-template-columns: 1fr;
        }
        
        .bulk-actions {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .bulk-buttons {
            justify-content: center;
        }
        
        .table-wrapper {
            border-radius: 0;
        }
        
        .table thead {
            display: none;
        }
        
        .table,
        .table tbody,
        .table tr,
        .table td {
            display: block;
        }
        
        .table tr {
            background: var(--surface);
            margin-bottom: 1rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        
        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table td:last-child {
            border-bottom: none;
        }
        
        .table td::before {
            content: attr(data-label) ":";
            font-weight: 600;
            color: var(--text-secondary);
            min-width: 80px;
        }
        
        .actions-cell {
            flex-direction: column;
            width: 100%;
        }
        
        .btn-xs {
            width: 100%;
        }
    }

    /* Custom scrollbar */
    .table-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-wrapper::-webkit-scrollbar-track {
        background: var(--surface-variant);
        border-radius: 4px;
    }
    
    .table-wrapper::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 4px;
    }
    
    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    /* Loading states */
    .btn-loading {
        opacity: 0.7;
        cursor: not-allowed;
        position: relative;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        margin: auto;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Focus states for accessibility */
    .btn:focus,
    .form-control:focus,
    .form-select:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    input[type="checkbox"]:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
</style>
@endsection

@section('content')
<div class="content">
<div class="reviews-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-comments"></i>
            Reviews Management
        </h1>
    </div>

    <!-- Filters Card -->
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.reviews.index') }}">
            <div class="filters-grid">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @php($s = request('status'))
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $s === 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ $s === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $s === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select">
                        @php($r = (int) request('rating'))
                        <option value="">Any Rating</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ $r === $i ? 'selected' : '' }}>
                                {{ $i }} {{ str_repeat('★', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" 
                           placeholder="Search by comment, shop, or customer name...">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" id="successAlert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" onclick="closeAlert('successAlert')">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" onclick="closeAlert('errorAlert')">&times;</button>
        </div>
    @endif

    <!-- Main Content Card -->
    <div class="main-card">
        <form method="POST" id="bulkForm">
            @csrf
            <!-- Bulk Actions Bar -->
            <div class="bulk-actions">
                <div class="bulk-actions-text">
                    <i class="fas fa-info-circle"></i>
                    Select reviews to perform bulk actions
                </div>
                <div class="bulk-buttons">
                    <button type="submit" formaction="{{ route('admin.reviews.bulk-approve') }}" 
                            class="btn btn-success btn-sm" 
                            onclick="return confirm('Approve selected reviews?')">
                        <i class="fas fa-check-double"></i>
                        Bulk Approve
                    </button>
                    <button type="submit" formaction="{{ route('admin.reviews.bulk-delete') }}" 
                            class="btn btn-danger btn-sm" 
                            onclick="return confirm('Delete selected reviews? This action cannot be undone.')">
                        <i class="fas fa-trash-alt"></i>
                        Bulk Delete
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="checkAll" aria-label="Select all reviews">
                            </th>
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
                                <td data-label="Select">
                                    <input type="checkbox" name="ids[]" value="{{ $review->id }}" class="row-check" 
                                           aria-label="Select review">
                                </td>
                                
                                <td data-label="Customer">
                                    <div class="customer-cell">
                                        <div class="customer-avatar">
                                            {{ substr($review->user ? $review->user->name : 'U', 0, 1) }}
                                        </div>
                                        <div class="customer-name">
                                            {{ $review->user ? $review->user->name : 'Unknown User' }}
                                        </div>
                                    </div>
                                </td>
                                
                                <td data-label="Shop">
                                    <span class="shop-badge" title="{{ $review->shop ? $review->shop->name : 'Unknown Shop' }}">
                                        {{ $review->shop ? Str::limit($review->shop->name, 15) : 'Unknown Shop' }}
                                    </span>
                                </td>
                                
                                <td data-label="Rating">
                                    @if($review->rating)
                                        <div class="rating-display rating-{{ $review->rating }}">
                                            {{ $review->rating }}/5
                                        </div>
                                    @else
                                        <span class="no-rating">No rating</span>
                                    @endif
                                </td>
                                
                                <td data-label="Review">
                                    @if($review->comment)
                                        <div class="review-content" title="{{ $review->comment }}">
                                            {{ $review->comment }}
                                        </div>
                                    @else
                                        <div class="review-content no-comment">
                                            No comment provided
                                        </div>
                                    @endif
                                </td>
                                
                                <td data-label="Status">
                                    @if($review->approved)
                                        <span class="status-badge status-approved">
                                            <i class="fas fa-check-circle"></i>
                                            Approved
                                        </span>
                                    @elseif($review->rejected_at)
                                        <div>
                                            <span class="status-badge status-rejected">
                                                <i class="fas fa-times-circle"></i>
                                                Rejected
                                            </span>
                                            @if($review->rejection_reason)
                                                <div class="rejection-reason" title="{{ $review->rejection_reason }}">
                                                    {{ Str::limit($review->rejection_reason, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="status-badge status-pending">
                                            <i class="fas fa-clock"></i>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                
                                <td data-label="Date">
                                    <div class="date-cell">
                                        <div class="date-primary">
                                            {{ $review->created_at ? $review->created_at->format('M d, Y') : '-' }}
                                        </div>
                                        @if($review->created_at)
                                            <div class="date-relative">
                                                {{ $review->created_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                
                                <td data-label="Actions">
                                    <div class="actions-cell">
                                        @if(!$review->approved && !$review->rejected_at)
                                            <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-xs" 
                                                        onclick="return confirm('Approve this review?')">
                                                    <i class="fas fa-check"></i>
                                                    Approve
                                                </button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-warning btn-xs" 
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $review->id }}">
                                                <i class="fas fa-ban"></i>
                                                Reject
                                            </button>
                                        @endif

                                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this review? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal-{{ $review->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.reviews.reject', $review->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-ban me-2"></i>
                                                    Reject Review
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Customer:</strong> {{ $review->user ? $review->user->name : 'Unknown User' }}
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Review:</strong>
                                                    <div class="review-content mt-2">
                                                        {{ $review->comment ?? 'No comment provided' }}
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="reason-{{ $review->id }}">
                                                        <strong>Rejection Reason</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="reason" id="reason-{{ $review->id }}" class="form-control" rows="4" 
                                                              required placeholder="Please provide a clear reason for rejecting this review..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times"></i>
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-ban"></i>
                                                    Confirm Rejection
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="far fa-comments empty-icon"></i>
                                        <div class="empty-title">No Reviews Found</div>
                                        <div class="empty-text">
                                            @if(request()->hasAny(['status', 'rating', 'q']))
                                                No reviews match your current filters. Try adjusting your search criteria.
                                            @else
                                                Reviews will appear here once customers start leaving feedback for shops.
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if($reviews->count() > 0)
                <div class="pagination-footer">
                    <div class="pagination-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $reviews->firstItem() ?? 0 }} to {{ $reviews->lastItem() ?? 0 }} 
                        of {{ $reviews->total() }} reviews
                    </div>
                    <div>
                        {{ $reviews->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            closeAlert(alert.id);
        }, 5000);
    });

    // Bulk select functionality
    const checkAllBox = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-check');
    const bulkButtons = document.querySelectorAll('.bulk-buttons button');

    if (checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkButtonsState();
        });
    }

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCheckAllState();
            updateBulkButtonsState();
        });
    });

    function updateCheckAllState() {
        const totalCheckboxes = rowCheckboxes.length;
        const checkedCheckboxes = document.querySelectorAll('.row-check:checked').length;
        
        if (checkedCheckboxes === 0) {
            checkAllBox.indeterminate = false;
            checkAllBox.checked = false;
        } else if (checkedCheckboxes === totalCheckboxes) {
            checkAllBox.indeterminate = false;
            checkAllBox.checked = true;
        } else {
            checkAllBox.indeterminate = true;
            checkAllBox.checked = false;
        }
    }

    function updateBulkButtonsState() {
        const checkedCheckboxes = document.querySelectorAll('.row-check:checked').length;
        bulkButtons.forEach(button => {
            button.disabled = checkedCheckboxes === 0;
            if (checkedCheckboxes === 0) {
                button.classList.add('btn-loading');
            } else {
                button.classList.remove('btn-loading');
            }
        });
    }

    // Initial state
    updateBulkButtonsState();

    // Enhanced form submissions with loading states
    const forms = document.querySelectorAll('form[onsubmit]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.classList.add('btn-loading');
                submitButton.disabled = true;
            }
        });
    });

    // Smooth scroll for mobile tables
    const tableWrapper = document.querySelector('.table-wrapper');
    if (tableWrapper) {
        tableWrapper.addEventListener('scroll', function() {
            this.classList.add('scrolling');
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = setTimeout(() => {
                this.classList.remove('scrolling');
            }, 150);
        });
    }

    // Tooltip functionality for truncated content
    const truncatedElements = document.querySelectorAll('[title]');
    truncatedElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            if (this.scrollWidth > this.clientWidth || this.scrollHeight > this.clientHeight) {
                this.setAttribute('data-bs-toggle', 'tooltip');
            }
        });
    });

    // Search input enhancement
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Could add live search functionality here
                console.log('Search:', this.value);
            }, 500);
        });
    }

    // Filter change handler
    const filterSelects = document.querySelectorAll('.filters-card select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Auto-submit on filter change (optional)
            // this.closest('form').submit();
        });
    });

    // Modal enhancements
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const textarea = this.querySelector('textarea');
            if (textarea) {
                textarea.focus();
            }
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            if (checkAllBox) {
                checkAllBox.checked = !checkAllBox.checked;
                checkAllBox.dispatchEvent(new Event('change'));
            }
        }
    });
});

function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

// Progressive enhancement for rating display
function enhanceRatingDisplays() {
    const ratingDisplays = document.querySelectorAll('.rating-display');
    ratingDisplays.forEach(display => {
        const rating = parseInt(display.textContent);
        if (rating) {
            const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
            display.setAttribute('title', `${rating} out of 5 stars: ${stars}`);
        }
    });
}

// Initialize enhancements
enhanceRatingDisplays();

// Status badge animations
const statusBadges = document.querySelectorAll('.status-badge');
statusBadges.forEach(badge => {
    badge.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.05)';
    });
    
    badge.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});

// Add ripple effect to buttons
function addRippleEffect() {
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple-effect');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

addRippleEffect();

// Add CSS for ripple effect
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    .table-wrapper.scrolling {
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
    }
`;
document.head.appendChild(rippleStyle);
</script>
@endpush
@endsection