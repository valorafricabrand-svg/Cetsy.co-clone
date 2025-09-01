<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EvidenceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'appeal_id', 'user_id', 'party_type', 'request_message',
        'required_evidence_types', 'deadline', 'submitted_evidence',
        'submitted_at', 'status', 'admin_notes'
    ];

    protected $casts = [
        'required_evidence_types' => 'array',
        'submitted_evidence' => 'array',
        'deadline' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_REVIEWED = 'reviewed';

    // Party type constants
    const PARTY_BUYER = 'buyer';
    const PARTY_SELLER = 'seller';

    // Relationships
    public function appeal(): BelongsTo
    {
        return $this->belongsTo(Appeal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'appeal_id', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', self::STATUS_REVIEWED);
    }

    public function scopeForParty($query, string $partyType)
    {
        return $query->where('party_type', $partyType);
    }

    // Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
    }

    public function isReviewed(): bool
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    public function isDeadlineExpired(): bool
    {
        return Carbon::now()->isAfter($this->deadline);
    }

    public function getDaysUntilDeadline(): int
    {
        return max(0, Carbon::now()->diffInDays($this->deadline, false));
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_SUBMITTED => 'badge-success',
            self::STATUS_OVERDUE => 'badge-danger',
            self::STATUS_REVIEWED => 'badge-info',
            default => 'badge-light'
        };
    }

    public function getPartyTypeLabel(): string
    {
        return match($this->party_type) {
            self::PARTY_BUYER => 'Buyer',
            self::PARTY_SELLER => 'Seller',
            default => 'Unknown'
        };
    }

    public function submitEvidence(array $evidence): void
    {
        $this->update([
            'submitted_evidence' => $evidence,
            'submitted_at' => Carbon::now(),
            'status' => self::STATUS_SUBMITTED
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->isDeadlineExpired() && $this->status === self::STATUS_PENDING) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    public function markAsReviewed(string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REVIEWED,
            'admin_notes' => $notes
        ]);
    }

    public function getRequiredEvidenceTypesList(): array
    {
        $types = $this->required_evidence_types ?? [];
        $typeLabels = [
            'screenshots' => 'Screenshots',
            'documents' => 'Documents',
            'photos' => 'Photos',
            'videos' => 'Videos',
            'receipts' => 'Receipts',
            'communication_logs' => 'Communication Logs',
            'bank_statements' => 'Bank Statements',
            'tracking_info' => 'Tracking Information',
            'other' => 'Other Evidence'
        ];

        return array_map(function($type) use ($typeLabels) {
            return $typeLabels[$type] ?? ucfirst($type);
        }, $types);
    }
}
