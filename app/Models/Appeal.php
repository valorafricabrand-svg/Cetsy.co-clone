<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Appeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_id', 'appealed_by', 'reason', 'reason_category', 'new_evidence',
        'status', 'reviewed_by', 'reviewed_at', 'decision',
        'review_notes'
    ];

    protected $casts = [
        'new_evidence' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_EVIDENCE_REQUESTED = 'evidence_requested';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CLOSED = 'closed';

    // Decision constants
    const DECISION_APPROVED = 'approved';
    const DECISION_REJECTED = 'rejected';

    // Reason category constants
    const REASON_CATEGORY_NEW_EVIDENCE = 'new_evidence';
    const REASON_CATEGORY_PROCEDURAL_ERROR = 'procedural_error';
    const REASON_CATEGORY_DECISION_ERROR = 'decision_error';
    const REASON_CATEGORY_REVIEW_CONCERNS = 'review_concerns';
    const REASON_CATEGORY_SELLER_UNRESPONSIVE = 'seller_unresponsive';
    const REASON_CATEGORY_URGENT_REVIEW = 'urgent_review';
    const REASON_CATEGORY_OTHER = 'other';

    // Relationships
    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function appealedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appealed_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evidenceRequests(): HasMany
    {
        return $this->hasMany(EvidenceRequest::class);
    }

    public function buyerEvidenceRequest(): HasOne
    {
        return $this->hasOne(EvidenceRequest::class)
            ->where(EvidenceRequest::recipientColumn(), $this->dispute->buyer_id);
    }

    public function sellerEvidenceRequest(): HasOne
    {
        return $this->hasOne(EvidenceRequest::class)
            ->where(EvidenceRequest::recipientColumn(), $this->dispute->seller_id);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    // Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_UNDER_REVIEW => 'badge-info',
            self::STATUS_EVIDENCE_REQUESTED => 'badge-info',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_CLOSED => 'badge-secondary',
            default => 'badge-light'
        };
    }

    public function getDecisionLabel(): string
    {
        return match($this->decision) {
            self::DECISION_APPROVED => 'Approved',
            self::DECISION_REJECTED => 'Rejected',
            default => 'Pending'
        };
    }

    public function getReasonCategoryLabel(): string
    {
        return match($this->reason_category ?? '') {
            self::REASON_CATEGORY_NEW_EVIDENCE => 'New Evidence Available',
            self::REASON_CATEGORY_PROCEDURAL_ERROR => 'Procedural Error in Review',
            self::REASON_CATEGORY_DECISION_ERROR => 'Decision Based on Incorrect Information',
            self::REASON_CATEGORY_REVIEW_CONCERNS => 'Concerns About Review Process',
            self::REASON_CATEGORY_SELLER_UNRESPONSIVE => 'Seller Not Responding',
            self::REASON_CATEGORY_URGENT_REVIEW => 'Urgent Review Required',
            self::REASON_CATEGORY_OTHER => 'Other Reasons',
            default => 'Unknown'
        };
    }

    public function markAsUnderReview(): void
    {
        $this->update(['status' => self::STATUS_UNDER_REVIEW]);
    }

    public function approve(string $reviewNotes, int $reviewedBy): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'decision' => self::DECISION_APPROVED,
            'review_notes' => $reviewNotes,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => Carbon::now()
        ]);

        // Update dispute status
        $this->dispute->update(['status' => Dispute::STATUS_APPEAL_UNDER_REVIEW]);
    }

    public function reject(string $reviewNotes, int $reviewedBy): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'decision' => self::DECISION_REJECTED,
            'review_notes' => $reviewNotes,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => Carbon::now()
        ]);

        // Mark dispute as final
        $this->dispute->markAsFinal();
    }
}
