@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4 justify-center">
        <div class="md:col-span-12">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt"></i> Evidence Requests
                    </h4>
                </div>
                <div class="p-4 sm:p-5">
                    @if($evidenceRequests->count() > 0)
                        <div class="grid grid-cols-12 gap-4">
                            @foreach($evidenceRequests as $evidenceRequest)
                                <div class="md:col-span-6 mb-4">
                                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-100 border-{{ $evidenceRequest->getStatusBadgeClass() }}">
                                        <div class="border-b border-slate-200 px-4 py-3 flex justify-between items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-balance-scale"></i> 
                                                Appeal #{{ $evidenceRequest->appeal->id }}
                                            </h6>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $evidenceRequest->getStatusBadgeClass() }}">
                                                {{ ucfirst($evidenceRequest->status) }}
                                            </span>
                                        </div>
                                        <div class="p-4 sm:p-5">
                                            <div class="mb-3">
                                                <strong>Dispute:</strong> 
                                                <a href="{{ route('disputes.show', $evidenceRequest->appeal->dispute_id) }}" class="no-underline">
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
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 mr-1">{{ $evidenceType }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Deadline:</strong> 
                                                <span class="text-{{ $evidenceRequest->isDeadlineExpired() ? 'danger' : 'primary' }}">
                                                    {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                                </span>
                                                <br>
                                                <small class="text-slate-500">
                                                    @if($evidenceRequest->isDeadlineExpired())
                                                        <span class="text-rose-600">Deadline expired</span>
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
                                                    <small class="text-slate-500">
                                                        Submitted on {{ $evidenceRequest->submitted_at->format('M d, Y \a\t g:i A') }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="d-grid">
                                                @if($evidenceRequest->status === 'pending' && !$evidenceRequest->isDeadlineExpired())
                                                    <a href="{{ route('evidence-requests.show', $evidenceRequest->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                                        <i class="fas fa-upload"></i> Submit Evidence
                                                    </a>
                                                @elseif($evidenceRequest->status === 'submitted')
                                                    <a href="{{ route('evidence-requests.show', $evidenceRequest->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                        <i class="fas fa-eye"></i> View Submitted Evidence
                                                    </a>
                                                @elseif($evidenceRequest->status === 'overdue')
                                                    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 mb-0">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Deadline expired. Please contact support.
                                                    </div>
                                                @else
                                                    <a href="{{ route('evidence-requests.show', $evidenceRequest->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition btn-outline-info">
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
                        <div class="flex justify-center mt-4">
                            {{ $evidenceRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-slate-500 mb-3"></i>
                            <h4 class="text-slate-500">No Evidence Requests</h4>
                            <p class="text-slate-500">You don't have any evidence requests at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




