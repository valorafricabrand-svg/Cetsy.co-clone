@extends('layouts.app')

@section('title', 'Product Reports')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Product Reports</h4>
                            <p class="text-muted mb-0">Manage and review product violation reports</p>
                        </div>
                        
                        {{-- Status Filter --}}
                        <div class="d-flex align-items-center gap-3">
                            <form method="GET" action="{{ route('admin.product-reports.index') }}" class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                        Pending ({{ $statusCounts['pending'] ?? 0 }})
                                    </option>
                                    <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>
                                        Reviewed ({{ $statusCounts['reviewed'] ?? 0 }})
                                    </option>
                                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>
                                        Resolved ({{ $statusCounts['resolved'] ?? 0 }})
                                    </option>
                                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>
                                        Dismissed ({{ $statusCounts['dismissed'] ?? 0 }})
                                    </option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-filter me-1"></i>Filter
                                </button>
                                @if(request('status'))
                                    <a href="{{ route('admin.product-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-times me-1"></i>Clear
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($reports->count() > 0)
                        @if(request('status'))
                            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                                <i class="fa-solid fa-filter me-1"></i>
                                Showing reports with status: <strong>{{ ucfirst(request('status')) }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Reporter</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Reported</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>#{{ $report->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $productThumb = function_exists('product_thumb_url')
                                                            ? product_thumb_url($report->product)
                                                            : (!empty($report->product->featured_image)
                                                                ? asset('storage/' . ltrim($report->product->featured_image, '/'))
                                                                : asset('storage/placeholder.jpg'));
                                                    @endphp
                                                    <div class="position-relative me-2 flex-shrink-0" style="width: 40px; height: 40px;">
                                                        <img src="{{ $productThumb }}"
                                                             alt="{{ $report->product->name }}"
                                                             class="rounded"
                                                             style="width: 40px; height: 40px; object-fit: cover;"
                                                             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none'); this.nextElementSibling.style.display='flex';">
                                                        <div class="bg-light rounded d-none align-items-center justify-content-center position-absolute top-0 start-0"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fa-solid fa-image text-muted"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $report->product->name }}</div>
                                                        <small class="text-muted">by {{ $report->product->shop->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $report->user->name }}</div>
                                                    <small class="text-muted">{{ $report->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $report->reason === 'inappropriate' ? 'danger' : ($report->reason === 'counterfeit' ? 'warning' : ($report->reason === 'spam' ? 'info' : ($report->reason === 'misleading' ? 'primary' : 'secondary'))) }}">
                                                    {{ ucfirst($report->reason) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'reviewed' => 'info',
                                                        'resolved' => 'success',
                                                        'dismissed' => 'secondary'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $report->created_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $report->created_at->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#reportModal{{ $report->id }}">
                                                        <i class="fa-solid fa-eye me-1"></i>View
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateModal{{ $report->id }}">
                                                        <i class="fa-solid fa-edit me-1"></i>Update
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $reports->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-flag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No reports found</h5>
                            <p class="text-muted">There are no product reports to review at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Report Detail Modals --}}
@foreach($reports as $report)
    <div class="modal fade" id="reportModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Details #{{ $report->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Product Information</h6>
                            <div class="mb-3">
                                <strong>Name:</strong> {{ $report->product->name }}<br>
                                <strong>Shop:</strong> {{ $report->product->shop->name }}<br>
                                <strong>Price:</strong> {{ get_currency() }} {{ number_format($report->product->price, 2) }}<br>
                                <strong>Type:</strong> {{ ucfirst($report->product->type) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Reporter Information</h6>
                            <div class="mb-3">
                                <strong>Name:</strong> {{ $report->user->name }}<br>
                                <strong>Email:</strong> {{ $report->user->email }}<br>
                                <strong>Reported:</strong> {{ $report->created_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-semibold">Report Details</h6>
                        <div class="mb-2">
                            <strong>Reason:</strong> 
                            <span class="badge bg-{{ $report->reason === 'inappropriate' ? 'danger' : ($report->reason === 'counterfeit' ? 'warning' : ($report->reason === 'spam' ? 'info' : ($report->reason === 'misleading' ? 'primary' : 'secondary'))) }}">
                                {{ ucfirst($report->reason) }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Description:</strong><br>
                            <div class="bg-light p-3 rounded">
                                {{ $report->description }}
                            </div>
                        </div>
                    </div>

                    @if($report->admin_notes)
                        <div class="mb-3">
                            <h6 class="fw-semibold">Admin Notes</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $report->admin_notes }}
                            </div>
                        </div>
                    @endif

                    @if($report->action_at && $report->action_at instanceof \Carbon\Carbon)
                        <div class="mb-3">
                            <h6 class="fw-semibold">Action Taken</h6>
                            <div>
                                <strong>Status:</strong> {{ ucfirst($report->status) }}<br>
                                <strong>Action Date:</strong> {{ $report->action_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Status Modal --}}
    <div class="modal fade" id="updateModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('admin.product-reports.update', $report->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Report Status #{{ $report->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status{{ $report->id }}" class="form-label">Status</label>
                        <select name="status" id="status{{ $report->id }}" class="form-select" required>
                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_notes{{ $report->id }}" class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes{{ $report->id }}" rows="3" class="form-control" placeholder="Add notes about this report...">{{ $report->admin_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection 
