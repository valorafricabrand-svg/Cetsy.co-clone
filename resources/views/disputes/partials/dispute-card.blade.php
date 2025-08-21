<div class="card h-100 dispute-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="badge {{ $dispute->getStatusBadgeClass() }}">
            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
        </span>
        <small class="text-muted">{{ $dispute->created_at->diffForHumans() }}</small>
    </div>
    
    <div class="card-body">
        <div class="mb-3">
            <h6 class="card-title mb-1">
                <strong>{{ $dispute->getTypeLabel() }}</strong>
            </h6>
            <p class="card-text text-muted small mb-0">
                Order #{{ $dispute->order->id }}
            </p>
        </div>

        <div class="mb-3">
            <p class="card-text">
                {{ Str::limit($dispute->description, 100) }}
            </p>
        </div>

        <div class="row text-center mb-3">
            <div class="col-6">
                <small class="text-muted d-block">Buyer</small>
                <strong>{{ $dispute->buyer->name }}</strong>
            </div>
            <div class="col-6">
                <small class="text-muted d-block">Seller</small>
                <strong>{{ $dispute->order->shop->name ?? 'Seller' }}</strong>
            </div>
        </div>

        @if($dispute->isResolved())
            <div class="alert alert-info small mb-3">
                <strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}
                @if($dispute->refund_amount)
                    <br><strong>Refund:</strong> ${{ number_format($dispute->refund_amount, 2) }}
                @endif
            </div>
        @endif

        @if($dispute->appeal)
            <div class="alert alert-warning small mb-3">
                <strong>Appeal Status:</strong> {{ ucfirst($dispute->appeal->status) }}
            </div>
        @endif

        @if($dispute->canBeAppealed() && !$dispute->isAppealDeadlineExpired())
            <div class="alert alert-info small mb-3">
                <strong>Appeal Deadline:</strong> {{ $dispute->getAppealDeadlineDaysLeft() }} days left
            </div>
        @endif

        @if($dispute->isAppealDeadlineExpired() && $dispute->can_appeal)
            <div class="alert alert-danger small mb-3">
                <strong>Appeal Deadline Expired</strong>
            </div>
        @endif
    </div>

    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('disputes.show', $dispute->id) }}" class="btn btn-sm btn-outline-primary">
                View Details
            </a>
            
            @if($dispute->canBeAppealed())
                <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="btn btn-sm btn-warning">
                    Appeal
                </a>
            @endif
        </div>
    </div>
</div>
