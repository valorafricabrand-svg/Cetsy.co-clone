@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-balance-scale"></i> Appeals Management
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Appeals
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_appeals'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_appeals'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Evidence Requested
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['evidence_requested'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Resolved Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['resolved_today'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.appeals.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="evidence_requested" {{ request('status') == 'evidence_requested' ? 'selected' : '' }}>Evidence Requested</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dispute_type" class="form-label">Dispute Type</label>
                    <select name="dispute_type" id="dispute_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="customs_fees" {{ request('dispute_type') == 'customs_fees' ? 'selected' : '' }}>Customs Fees</option>
                        <option value="item_misrepresentation" {{ request('dispute_type') == 'item_misrepresentation' ? 'selected' : '' }}>Item Misrepresentation</option>
                        <option value="shipping_issues" {{ request('dispute_type') == 'shipping_issues' ? 'selected' : '' }}>Shipping Issues</option>
                        <option value="quality_issues" {{ request('dispute_type') == 'quality_issues' ? 'selected' : '' }}>Quality Issues</option>
                        <option value="payment_issues" {{ request('dispute_type') == 'payment_issues' ? 'selected' : '' }}>Payment Issues</option>
                        <option value="other" {{ request('dispute_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_range" class="form-label">Date Range</label>
                    <select name="date_range" id="date_range" class="form-select">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
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

    <!-- Appeals Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Appeals</h6>
        </div>
        <div class="card-body">
            @if($appeals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="appealsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Dispute</th>
                                <th>Appealed By</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Evidence Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appeals as $appeal)
                                <tr>
                                    <td>
                                        <strong>#{{ $appeal->id }}</strong>
                                        @if($appeal->status === 'pending')
                                            <span class="badge bg-warning ms-1">New</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.admin-disputes.show', $appeal->dispute->id) }}" class="text-decoration-none">
                                            Dispute #{{ $appeal->dispute->id }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $appeal->dispute->getTypeLabel() }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $appeal->appealedBy->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ ucfirst($appeal->appealedBy->user_type) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $appeal->dispute->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $appeal->getStatusBadgeClass() }}">
                                            {{ ucfirst(str_replace('_', ' ', $appeal->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($appeal->status === 'evidence_requested')
                                            @php
                                                $buyerEvidence = $appeal->buyerEvidenceRequest;
                                                $sellerEvidence = $appeal->sellerEvidenceRequest;
                                            @endphp
                                            <div class="small">
                                                <div class="mb-1">
                                                    <strong>Buyer:</strong>
                                                    @if($buyerEvidence)
                                                        <span class="badge {{ $buyerEvidence->getStatusBadgeClass() }}">
                                                            {{ ucfirst($buyerEvidence->status) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Pending</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong>Seller:</strong>
                                                    @if($sellerEvidence)
                                                        <span class="badge {{ $sellerEvidence->getStatusBadgeClass() }}">
                                                            {{ ucfirst($sellerEvidence->status) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Pending</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $appeal->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.appeals.show', $appeal->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if($appeal->status === 'pending')
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#requestEvidenceModal-{{ $appeal->id }}">
                                                    <i class="fas fa-file-alt"></i> Request Evidence
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $appeals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No appeals found</h4>
                    <p class="text-muted">There are no appeals matching your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Evidence Request Modals -->
@foreach($appeals as $appeal)
    @if($appeal->status === 'pending')
        <div class="modal fade" id="requestEvidenceModal-{{ $appeal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-alt"></i> Request Evidence from Both Parties
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.appeals.request-evidence', $appeal->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Evidence Request Process:</strong> This will send evidence requests to both the buyer and seller with a deadline. Both parties will be able to see the appeal progress and submit their evidence.
                            </div>
                            
                            <div class="mb-3">
                                <label for="message-{{ $appeal->id }}" class="form-label">Request Message *</label>
                                <textarea name="message" id="message-{{ $appeal->id }}" rows="4" class="form-control" 
                                    placeholder="Please explain what evidence you need from both parties..." required></textarea>
                                <div class="form-text">
                                    This message will be sent to both the buyer and seller explaining what evidence is needed.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="evidence_types-{{ $appeal->id }}" class="form-label">Required Evidence Types *</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="screenshots" id="screenshots-{{ $appeal->id }}">
                                            <label class="form-check-label" for="screenshots-{{ $appeal->id }}">
                                                Screenshots
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="documents" id="documents-{{ $appeal->id }}">
                                            <label class="form-check-label" for="documents-{{ $appeal->id }}">
                                                Documents
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="photos" id="photos-{{ $appeal->id }}">
                                            <label class="form-check-label" for="photos-{{ $appeal->id }}">
                                                Photos
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="receipts" id="receipts-{{ $appeal->id }}">
                                            <label class="form-check-label" for="receipts-{{ $appeal->id }}">
                                                Receipts
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="communication_logs" id="communication_logs-{{ $appeal->id }}">
                                            <label class="form-check-label" for="communication_logs-{{ $appeal->id }}">
                                                Communication Logs
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="bank_statements" id="bank_statements-{{ $appeal->id }}">
                                            <label class="form-check-label" for="bank_statements-{{ $appeal->id }}">
                                                Bank Statements
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="tracking_info" id="tracking_info-{{ $appeal->id }}">
                                            <label class="form-check-label" for="tracking_info-{{ $appeal->id }}">
                                                Tracking Information
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="evidence_types[]" value="other" id="other-{{ $appeal->id }}">
                                            <label class="form-check-label" for="other-{{ $appeal->id }}">
                                                Other Evidence
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deadline_days-{{ $appeal->id }}" class="form-label">Deadline (Days) *</label>
                                <select name="deadline_days" id="deadline_days-{{ $appeal->id }}" class="form-select" required>
                                    <option value="">Select deadline</option>
                                    <option value="3">3 days</option>
                                    <option value="5">5 days</option>
                                    <option value="7">7 days</option>
                                    <option value="10">10 days</option>
                                    <option value="14">14 days</option>
                                    <option value="21">21 days</option>
                                    <option value="30">30 days</option>
                                </select>
                                <div class="form-text">
                                    Both parties will have this amount of time to submit their evidence.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-file-alt"></i> Request Evidence
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
$(document).ready(function() {
    $('#appealsTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "language": {
            "search": "Search appeals:",
            "lengthMenu": "Show _MENU_ appeals per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ appeals"
        }
    });
});
</script>
@endpush
@endsection
