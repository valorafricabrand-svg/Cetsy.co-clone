@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt"></i> Evidence Requests
                    </h4>
                </div>
                <div class="card-body">
                    @if($evidenceRequests->count() > 0)
                        <div class="row">
                            @foreach($evidenceRequests as $evidenceRequest)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-{{ $evidenceRequest->getStatusBadgeClass() }}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-balance-scale"></i> 
                                                Appeal #{{ $evidenceRequest->appeal->id }}
                                            </h6>
                                            <span class="badge {{ $evidenceRequest->getStatusBadgeClass() }}">
                                                {{ ucfirst($evidenceRequest->status) }}
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Dispute:</strong> 
                                                <a href="{{ route('disputes.show', $evidenceRequest->appeal->dispute_id) }}" class="text-decoration-none">
                                                    Dispute #{{ $evidenceRequest->appeal->dispute_id }}
                                                </a>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Request Message:</strong>
                                                <p class="mb-0 mt-1">{{ $evidenceRequest->request_message }}</p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Required Evidence:</strong>
                                                <div class="mt-1">
                                                    @foreach($evidenceRequest->getRequiredEvidenceTypesList() as $evidenceType)
                                                        <span class="badge bg-light text-dark me-1">{{ $evidenceType }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Deadline:</strong> 
                                                <span class="text-{{ $evidenceRequest->isDeadlineExpired() ? 'danger' : 'primary' }}">
                                                    {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    @if($evidenceRequest->isDeadlineExpired())
                                                        <span class="text-danger">Deadline expired</span>
                                                    @else
                                                        <span class="text-{{ $evidenceRequest->getDaysUntilDeadline() <= 3 ? 'warning' : 'success' }}">
                                                            {{ $evidenceRequest->getDaysUntilDeadline() }} days remaining
                                                        </span>
                                                    @endif
                                                </small>
                                            </div>
                                            
                                            @if($evidenceRequest->status === 'submitted')
                                                <div class="mb-3">
                                                    <strong>Submitted Evidence:</strong>
                                                    <p class="mb-1 mt-1">{{ $evidenceRequest->submitted_evidence['description'] ?? 'N/A' }}</p>
                                                    <small class="text-muted">
                                                        Submitted on {{ $evidenceRequest->submitted_at->format('M d, Y \a\t g:i A') }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="d-grid">
                                                @if($evidenceRequest->status === 'pending' && !$evidenceRequest->isDeadlineExpired())
                                                    <a href="{{ route('evidence-requests.show', $evidenceRequest->id) }}" class="btn btn-primary">
                                                        <i class="fas fa-upload"></i> Submit Evidence
                                                    </a>
                                                @elseif($evidenceRequest->status === 'submitted')
                                                    <a href="{{ route('evidence-requests.show', $evidenceRequest->id) }}" class="btn btn-outline-success">
                                                        <i class="fas fa-eye"></i> View Submitted Evidence
                                                    </a>
                                                @elseif($evidenceRequest->status === 'overdue')
                                                    <div class="alert alert-danger mb-0">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Deadline expired. Please contact support.
                                                    </div>
                                                @else
                                                    <a href="{{ route('evidence-requests.show', $evidenceRequest->id) }}" class="btn btn-outline-info">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $evidenceRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Evidence Requests</h4>
                            <p class="text-muted">You don't have any evidence requests at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
