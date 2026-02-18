@php
    $statusClass = match($dispute->status) {
        \App\Models\Dispute::STATUS_PENDING => 'bg-warning text-dark',
        \App\Models\Dispute::STATUS_UNDER_REVIEW => 'bg-info text-dark',
        \App\Models\Dispute::STATUS_RESOLVED,
        \App\Models\Dispute::STATUS_MUTUALLY_RESOLVED,
        \App\Models\Dispute::STATUS_APPEAL_APPROVED => 'bg-success text-white',
        \App\Models\Dispute::STATUS_APPEALED,
        \App\Models\Dispute::STATUS_APPEAL_UNDER_REVIEW => 'bg-primary text-white',
        \App\Models\Dispute::STATUS_APPEAL_REJECTED => 'bg-danger text-white',
        \App\Models\Dispute::STATUS_FINAL,
        \App\Models\Dispute::STATUS_CLOSED => 'bg-secondary text-white',
        default => 'bg-light text-dark'
    };
    $statusLabel = ucfirst(str_replace('_', ' ', $dispute->status));
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full dispute-card">
    <div class="border-b border-slate-200 px-4 py-3 flex justify-between items-center">
        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium px-3 py-2 {{ $statusClass }}">
            {{ $statusLabel }}
        </span>
        <small class="text-slate-500">{{ $dispute->created_at->diffForHumans() }}</small>
    </div>
    
    <div class="p-4 sm:p-5">
        <div class="mb-3">
            <h6 class="text-lg font-semibold text-slate-900 mb-1">
                <strong>{{ $dispute->getTypeLabel() }}</strong>
            </h6>
            <p class="card-text text-slate-500 text-xs mb-0">
                Order #{{ $dispute->order->id }}
            </p>
        </div>

        <div class="mb-3">
            <p class="card-text">
                {!! Str::limit($dispute->description, 100) !!}
            </p>
        </div>

        <div class="grid grid-cols-12 gap-4 text-center mb-3">
            <div class="col-span-6">
                <small class="text-slate-500 block">Buyer</small>
                <strong>{{ $dispute->buyer->name }}</strong>
            </div>
            <div class="col-span-6">
                <small class="text-slate-500 block">Seller</small>
                <strong>{{ $dispute->order->shop->name ?? 'Seller' }}</strong>
            </div>
        </div>

        @if($dispute->isResolved())
            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-xs mb-3">
                <strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}
                @if($dispute->refund_amount)
                    <br><strong>Refund:</strong> ${{ number_format($dispute->refund_amount, 2) }}
                @endif
            </div>
        @endif

        @if($dispute->appeal)
            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 text-xs mb-3">
                <strong>Appeal Status:</strong> {{ ucfirst($dispute->appeal->status) }}
            </div>
        @endif

        @if($dispute->canBeAppealed())
            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-xs mb-3">
                @if($dispute->appeal_deadline)
                    <strong>Appeal Deadline:</strong> {{ $dispute->getAppealDeadlineDaysLeft() }} days left
                @else
                    <strong>Appeal Available:</strong> Submit immediately
                @endif
            </div>
        @endif

        @if($dispute->isAppealDeadlineExpired() && $dispute->can_appeal)
            <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 text-xs mb-3">
                <strong>Appeal Deadline Expired</strong>
            </div>
        @endif
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="{{ route('disputes.show', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                View Details
            </a>
            
            @if($dispute->canBeAppealed())
                <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400">
                    Appeal
                </a>
            @endif
        </div>
    </div>
</div>


