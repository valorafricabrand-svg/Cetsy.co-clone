<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'buyer_id', 'seller_id', 'type', 'status', 
        'description', 'evidence', 'resolution', 'resolved_by', 
        'resolved_at', 'appeal_deadline', 'can_appeal',
        'decision', 'refund_amount', 'admin_notes',
        'mutual_resolution_terms', 'buyer_agreed_at', 'seller_agreed_at'
    ];

    protected $casts = [
        'evidence' => 'array',
        'resolved_at' => 'datetime',
        'appeal_deadline' => 'datetime',
        'can_appeal' => 'boolean',
        'refund_amount' => 'decimal:2',
        'buyer_agreed_at' => 'datetime',
        'seller_agreed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_APPEALED = 'appealed';
    const STATUS_APPEAL_UNDER_REVIEW = 'appeal_under_review';
    const STATUS_FINAL = 'final';
    const STATUS_MUTUALLY_RESOLVED = 'mutually_resolved';

    // Type constants
    const TYPE_CUSTOMS_FEES = 'customs_fees';
    const TYPE_ITEM_MISREPRESENTATION = 'item_misrepresentation';
    const TYPE_SHIPPING_ISSUES = 'shipping_issues';
    const TYPE_QUALITY_ISSUES = 'quality_issues';
    const TYPE_PAYMENT_ISSUES = 'payment_issues';
    const TYPE_OTHER = 'other';

    // Decision constants
    const DECISION_BUYER_WINS = 'buyer_wins';
    const DECISION_SELLER_WINS = 'seller_wins';
    const DECISION_PARTIAL_REFUND = 'partial_refund';
    const DECISION_NO_ACTION = 'no_action';
    const DECISION_MUTUAL_AGREEMENT = 'mutual_agreement';

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }

    public function appeal(): HasOne
    {
        return $this->hasOne(Appeal::class);
    }

    public function evidenceRequests(): HasMany
    {
        return $this->hasMany(EvidenceRequest::class);
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

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeAppealed($query)
    {
        return $query->where('status', self::STATUS_APPEALED);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    public function scopeMutuallyResolved($query)
    {
        return $query->where('status', self::STATUS_MUTUALLY_RESOLVED);
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

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isAppealed(): bool
    {
        return $this->status === self::STATUS_APPEALED;
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function canBeAppealed(): bool
    {
        if (!$this->can_appeal) {
            return false;
        }

        if ($this->appeal_deadline && Carbon::now()->isAfter($this->appeal_deadline)) {
            return false;
        }

        return $this->isResolved() && !$this->appeal;
    }

    public function isAppealDeadlineExpired(): bool
    {
        return $this->appeal_deadline && Carbon::now()->isAfter($this->appeal_deadline);
    }

    public function getAppealDeadlineDaysLeft(): int
    {
        if (!$this->appeal_deadline) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->appeal_deadline, false));
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_UNDER_REVIEW => 'badge-info',
            self::STATUS_RESOLVED => 'badge-success',
            self::STATUS_APPEALED => 'badge-warning',
            self::STATUS_APPEAL_UNDER_REVIEW => 'badge-info',
            self::STATUS_FINAL => 'badge-secondary',
            self::STATUS_MUTUALLY_RESOLVED => 'badge-success',
            default => 'badge-light'
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_CUSTOMS_FEES => 'Customs Fees',
            self::TYPE_ITEM_MISREPRESENTATION => 'Item Misrepresentation',
            self::TYPE_SHIPPING_ISSUES => 'Shipping Issues',
            self::TYPE_QUALITY_ISSUES => 'Quality Issues',
            self::TYPE_PAYMENT_ISSUES => 'Payment Issues',
            self::TYPE_OTHER => 'Other',
            default => 'Unknown'
        };
    }

    public function getDecisionLabel(): string
    {
        return match($this->decision) {
            self::DECISION_BUYER_WINS => 'Buyer Wins',
            self::DECISION_SELLER_WINS => 'Seller Wins',
            self::DECISION_PARTIAL_REFUND => 'Partial Refund',
            self::DECISION_NO_ACTION => 'No Action',
            self::DECISION_MUTUAL_AGREEMENT => 'Mutual Agreement',
            default => 'Pending'
        };
    }

    public function setAppealDeadline(): void
    {
        $this->update([
            'appeal_deadline' => Carbon::now()->addDays(7),
            'can_appeal' => true
        ]);
    }

    public function markAsResolved(string $resolution, string $decision, ?float $refundAmount = null, ?int $resolvedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolution' => $resolution,
            'decision' => $decision,
            'refund_amount' => $refundAmount,
            'resolved_by' => $resolvedBy,
            'resolved_at' => Carbon::now()
        ]);

        $this->setAppealDeadline();
    }

    public function markAsAppealed(): void
    {
        $this->update([
            'status' => self::STATUS_APPEALED,
            'can_appeal' => false
        ]);
    }

    public function markAsFinal(): void
    {
        $this->update([
            'status' => self::STATUS_FINAL,
            'can_appeal' => false
        ]);
    }

    // Mutual Resolution Methods
    public function isMutuallyResolved(): bool
    {
        return $this->status === self::STATUS_MUTUALLY_RESOLVED;
    }

    public function canBeMutuallyResolved(): bool
    {
        return $this->status === self::STATUS_PENDING || $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function initiateMutualResolution(string $terms, int $initiatorId): void
    {
        $this->update([
            'mutual_resolution_terms' => $terms,
            'status' => self::STATUS_UNDER_REVIEW
        ]);

        // Set the initiator's agreement timestamp
        if ($initiatorId === $this->buyer_id) {
            $this->update(['buyer_agreed_at' => Carbon::now()]);
        } elseif ($initiatorId === $this->seller_id) {
            $this->update(['seller_agreed_at' => Carbon::now()]);
        }
    }

    public function agreeToMutualResolution(int $userId): bool
    {
        if (!$this->mutual_resolution_terms) {
            return false;
        }

        if ($userId === $this->buyer_id && !$this->buyer_agreed_at) {
            $this->update(['buyer_agreed_at' => Carbon::now()]);
        } elseif ($userId === $this->seller_id && !$this->seller_agreed_at) {
            $this->update(['seller_agreed_at' => Carbon::now()]);
        }

        // Check if both parties have agreed
        if ($this->buyer_agreed_at && $this->seller_agreed_at) {
            $this->markAsMutuallyResolved();
            return true;
        }

        return false;
    }

    public function getMutualResolutionStatus(): array
    {
        return [
            'terms' => $this->mutual_resolution_terms,
            'buyer_agreed' => !is_null($this->buyer_agreed_at),
            'seller_agreed' => !is_null($this->seller_agreed_at),
            'buyer_agreed_at' => $this->buyer_agreed_at,
            'seller_agreed_at' => $this->seller_agreed_at,
            'is_complete' => !is_null($this->buyer_agreed_at) && !is_null($this->seller_agreed_at)
        ];
    }

    public function markAsMutuallyResolved(): void
    {
        $this->update([
            'status' => self::STATUS_MUTUALLY_RESOLVED,
            'decision' => self::DECISION_MUTUAL_AGREEMENT,
            'can_appeal' => false,
            'resolved_at' => Carbon::now()
        ]);
    }
}
