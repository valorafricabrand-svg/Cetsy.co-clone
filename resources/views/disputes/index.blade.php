@extends('layouts.app')

@section('title', 'My Disputes')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">My Disputes</h1>
                <a href="{{ route('disputes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Dispute
                </a>
            </div>

            <!-- Status Filter Tabs -->
            <div class="card mb-4">
                <div class="card-body">
                    @php
                        // Show only these statuses in the UI
                        $visibleStatuses = ['pending','under_review','closed'];
                        $allVisibleCount = collect($visibleStatuses)->sum(function($s) use ($statusCounts) {
                            return $statusCounts[$s] ?? 0;
                        });
                    @endphp
                    <ul class="nav nav-tabs" id="disputeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                All <span class="badge bg-secondary">{{ $allVisibleCount }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                Pending <span class="badge bg-warning">{{ $statusCounts['pending'] ?? 0 }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="under-review-tab" data-bs-toggle="tab" data-bs-target="#under_review" type="button" role="tab">
                                Under Review <span class="badge bg-info">{{ $statusCounts['under_review'] ?? 0 }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="closed-tab" data-bs-toggle="tab" data-bs-target="#closed" type="button" role="tab">
                                Closed <span class="badge bg-secondary">{{ $statusCounts['closed'] ?? 0 }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Disputes List -->
            <div class="tab-content" id="disputeTabsContent">
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    @php
                        // Ensure cards render newest ID first even within the current page
                        $orderedDisputes = $disputes->sortByDesc('id');
                    @endphp
                    @if($orderedDisputes->count() > 0)
                        <div class="row">
                            @foreach($orderedDisputes as $dispute)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    @include('disputes.partials.dispute-card', ['dispute' => $dispute])
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $disputes->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No disputes found</h4>
                            <p class="text-muted">You haven't created any disputes yet.</p>
                            <a href="{{ route('disputes.create') }}" class="btn btn-primary">Create Your First Dispute</a>
                        </div>
                    @endif
                </div>

                <!-- Individual status tabs -->
                @foreach(['pending', 'under_review', 'closed'] as $status)
                    <div class="tab-pane fade" id="{{ $status }}" role="tabpanel">
                        @php
                            // Keep per-status tabs ordered by latest updates
                            $filteredDisputes = $orderedDisputes
                                ->where('status', $status)
                                ->sortByDesc('id');
                        @endphp
                        
                        @if($filteredDisputes->count() > 0)
                            <div class="row">
                                @foreach($filteredDisputes as $dispute)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        @include('disputes.partials.dispute-card', ['dispute' => $dispute])
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No {{ str_replace('_', ' ', $status) }} disputes</h4>
                                <p class="text-muted">You don't have any disputes in this status.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab navigation with URL hash
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) {
            const tabInstance = new bootstrap.Tab(tab);
            tabInstance.show();
        }
    }

    // Update URL hash when tabs are clicked
    const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            window.location.hash = target;
        });
    });
});
</script>
@endpush
@endsection
