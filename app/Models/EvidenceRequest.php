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
        'appeal_id', 'dispute_id', 'requested_from', 'requested_by',
        'message', 'status', 'deadline', 'responded_at', 'required_evidence_types',
        'response_notes', 'submitted_evidence'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'responded_at' => 'datetime',
        'required_evidence_types' => 'array',
        'submitted_evidence' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RESPONDED = 'responded';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CLOSED = 'closed';

    // Relationships
    public function appeal(): BelongsTo
    {
        return $this->belongsTo(Appeal::class);
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function requestedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_from');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('deadline', '<', now());
    }

    // Methods
    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function markAsResponded(): void
    {
        $this->update([
            'status' => self::STATUS_RESPONDED,
            'responded_at' => now()
        ]);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => self::STATUS_OVERDUE]);
    }

    public function getDeadlineDaysLeft(): int
    {
        if (!$this->deadline) {
            return 0;
        }
        return max(0, Carbon::now()->diffInDays($this->deadline, false));
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_RESPONDED => 'badge-success',
            self::STATUS_OVERDUE => 'badge-danger',
            self::STATUS_CLOSED => 'badge-secondary',
            default => 'badge-light'
        };
    }
}
