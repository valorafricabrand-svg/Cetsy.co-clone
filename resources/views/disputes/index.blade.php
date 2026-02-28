@extends('theme.'.theme().'.layouts.app')

@section('title', 'My Disputes')

@section('main')
<div class="content">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-12">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-0">My Disputes</h1>
                <a href="{{ route('disputes.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                    <i class="fas fa-plus"></i> Create New Dispute
                </a>
            </div>

            <!-- Status Filter Tabs -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                <div class="p-4 sm:p-5">
                    @php
                        // Show only these statuses in the UI
                        $visibleStatuses = ['pending','under_review','closed'];
                        $allVisibleCount = collect($visibleStatuses)->sum(function($s) use ($statusCounts) {
                            return $statusCounts[$s] ?? 0;
                        });
                    @endphp
                    <ul class="nav flex flex-wrap gap-2 border-b border-slate-200" id="disputeTabs" role="tablist">
                        <li class="" role="presentation">
                            <button class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 active" id="all-tab" data-ui-toggle="tab" data-ui-target="#all" type="button" role="tab">
                                All <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200">{{ $allVisibleCount }}</span>
                            </button>
                        </li>
                        <li class="" role="presentation">
                            <button class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900" id="pending-tab" data-ui-toggle="tab" data-ui-target="#pending" type="button" role="tab">
                                Pending <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100">{{ $statusCounts['pending'] ?? 0 }}</span>
                            </button>
                        </li>
                        <li class="" role="presentation">
                            <button class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900" id="under-review-tab" data-ui-toggle="tab" data-ui-target="#under_review" type="button" role="tab">
                                Under Review <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sky-100">{{ $statusCounts['under_review'] ?? 0 }}</span>
                            </button>
                        </li>
                        <li class="" role="presentation">
                            <button class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900" id="closed-tab" data-ui-toggle="tab" data-ui-target="#closed" type="button" role="tab">
                                Closed <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200">{{ $statusCounts['closed'] ?? 0 }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Disputes List -->
            <div class="space-y-4" id="disputeTabsContent">
                <div class="active" id="all" role="tabpanel">
                    @php
                        // Ensure cards render newest ID first even within the current page
                        $orderedDisputes = $disputes->sortByDesc('id');
                    @endphp
                    @if($orderedDisputes->count() > 0)
                        <div class="grid grid-cols-12 gap-4">
                            @foreach($orderedDisputes as $dispute)
                                <div class="col-span-12 md:col-span-6 lg:col-span-4 mb-4">
                                    @include('disputes.partials.dispute-card', ['dispute' => $dispute])
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="flex justify-center">
                            {{ $disputes->links('pagination::tailwind') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-slate-500 mb-3"></i>
                            <h4 class="text-slate-500">No disputes found</h4>
                            <p class="text-slate-500">You haven't created any disputes yet.</p>
                            <a href="{{ route('disputes.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Create Your First Dispute</a>
                        </div>
                    @endif
                </div>

                <!-- Individual status tabs -->
                @foreach(['pending', 'under_review', 'closed'] as $status)
                    <div class="" id="{{ $status }}" role="tabpanel">
                        @php
                            // Keep per-status tabs ordered by latest updates
                            $filteredDisputes = $orderedDisputes
                                ->where('status', $status)
                                ->sortByDesc('id');
                        @endphp
                        
                        @if($filteredDisputes->count() > 0)
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($filteredDisputes as $dispute)
                                    <div class="col-span-12 md:col-span-6 lg:col-span-4 mb-4">
                                        @include('disputes.partials.dispute-card', ['dispute' => $dispute])
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-slate-500 mb-3"></i>
                                <h4 class="text-slate-500">No {{ str_replace('_', ' ', $status) }} disputes</h4>
                                <p class="text-slate-500">You don't have any disputes in this status.</p>
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
    const tabs = document.querySelectorAll('[data-ui-toggle="tab"]');
    const panels = document.querySelectorAll('#disputeTabsContent > [role="tabpanel"]');

    const activateTab = (targetSelector, updateHash = true) => {
        if (!targetSelector) return;
        const targetPanel = document.querySelector(targetSelector);
        if (!targetPanel) return;

        tabs.forEach(tab => {
            const isActive = tab.getAttribute('data-ui-target') === targetSelector;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(panel => {
            const isActive = `#${panel.id}` === targetSelector;
            panel.classList.toggle('active', isActive);
            panel.classList.toggle('hidden', !isActive);
        });

        if (updateHash) {
            window.location.hash = targetSelector;
        }
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            activateTab(this.getAttribute('data-ui-target'));
        });
    });

    const hash = window.location.hash;
    if (hash && document.querySelector(`[data-ui-target="${hash}"]`)) {
        activateTab(hash, false);
    } else {
        activateTab('#all', false);
    }
});
</script>
@endpush
@endsection





